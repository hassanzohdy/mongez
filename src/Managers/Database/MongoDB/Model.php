<?php

namespace HZ\Illuminate\Mongez\Managers\Database\MongoDB;

use DateTime;
use Illuminate\Support\Facades\DB;
use HZ\Illuminate\Mongez\Traits\ModelTrait;
use HZ\Illuminate\Mongez\Traits\MongoDB\RecycleBin;
use Jenssegers\Mongodb\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use RecycleBin;

    use ModelTrait {
        boot as traitBoot;
    }

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createdAt';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updatedAt';

    /**
     * The name of the "deleted at" column.
     *
     * @var string
     */
    const DELETED_AT = 'deletedAt';

    /**
     * Created By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const CREATED_BY = 'createdBy';

    /**
     * Updated By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const UPDATED_BY = 'updatedBy';

    /**
     * Deleted By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const DELETED_BY = 'deletedBy';

    /**
     * Shared info of the model
     * This is used for getting simple info 
     * 
     * @const array
     */
    const SHARED_INFO = [];

    /**
     * Define list of other models that will be affected
     * as the current model is sub-document to it when it gets updated
     *  
     * @example ModelClass::class => columnName will be converted to ['columnName.id', 'columnName', 'sharedInfo']
     * @example ModelClass::class => [searchingColumn, updatingColumn]
     * @example ModelClass::class => [searchingColumn, updatingColumn, sharedInfoMethod]
     * 
     * @const array
     */
    const ON_MODEL_UPDATE = [];

    /**
     * Define list of other models that will be deleted
     * when this model is deleted
     * For example when a city is deleted, all related regions shall be deleted as well
     *  
     * @example ModelClass::class => searchingColumn: string
     * @example ModelClass::class => [searchingColumn: string, pullFrom: string, pullingKey: string]
     * 
     * @const array
     */
    const ON_MODEL_DELETE = [];

    /**
     * Disable guarded fields
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        static::traitBoot();

        // Create an auto increment id on creating new document

        // before creating, we will check if the created_by column has value
        // if so, then we will update the column for the current user id
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = static::nextId();
                // why am i generating this _id column ?!!
                // $model->_id = sha1(time() . Str::random(40));
            }
        });

        static::updating(function ($model) {
            if (static::UPDATED_BY && ($user = user())) {
                $model->updatedBy = user()->sharedInfo();
            }
        });

        // When model update, detect whether there are any other models that
        // shall be updated with it        
        static::updated(function ($model) {
            $otherModels = config('mongez.database.onModelUpdate.' . static::class);

            if (empty(static::ON_MODEL_UPDATE) && empty($otherModels)) return;

            $modelsList = array_merge((array) static::ON_MODEL_UPDATE, (array) $otherModels);

            // the model options is can be an string or array
            // the array can have up to 3 elements: search-column, updating field and shared info method
            // if the model options is set to string, then it will be converted to
            // $modelOptions.id, $modelOptions, sharedInfo 
            foreach ($modelsList as $modelClass => $modelOptions) {
                if (is_string($modelOptions)) {
                    $modelOptions = [$modelOptions . '.id', $modelOptions, 'sharedInfo'];
                } elseif (count($modelOptions) === 2) {
                    $modelOptions[] = 'sharedInfo';
                }

                [$searchingColumn, $updatingColumn, $sharedInfoMethod] = $modelOptions;

                $modelClass::query()->where($searchingColumn, $model->id)->update([
                    $updatingColumn => $model->$sharedInfoMethod(),
                ]);
            }
        });

        // triggered when a model record is deleted from database
        static::deleted(function ($model) {
            if (empty(static::ON_MODEL_DELETE) && empty($otherModels = config('mongez.database.onModelDelete.' . static::class))) return;

            $modelsList = array_merge((array) static::ON_MODEL_DELETE, (array) $otherModels);

            foreach ($modelsList as $modelClass => $searchingColumn) {
                if (is_string($searchingColumn)) {
                    $modelClass::where($searchingColumn, $model->id)->delete();
                } else {
                    [$searchingColumn, $pullFrom, $pullingKey] = $searchingColumn;
                    $modelClass::where($searchingColumn, $model->id)->pull($pullFrom, [
                        $pullingKey => $model->id,
                    ]);
                }
            }
        });
    }

    /**
     * Create and return new id for the current model
     * 
     * @return int
     */
    public static function nextId(): int
    {
        $newId = static::getNextId();

        $lastId = $newId - 1;

        $ids = DB::collection('ids');

        $collection = (new static)->getTable();

        if (!$lastId) {
            $ids->insert([
                'collection' => $collection,
                'id' => $newId,
            ]);
        } else {
            $ids->where('collection', $collection)->update([
                'id' => $newId
            ]);
        }

        return $newId;
    }

    /**
     * Get next id
     * 
     * @return int
     */
    public static function getNextId(): int
    {
        return static::lastInsertId() + 1;
    }

    /**
     * Get last insert id of the given collection name
     * 
     * @return  int
     */
    public static function lastInsertId(): int
    {
        $ids = DB::collection('ids');

        $info = $ids->where('collection', (new static)->getTable())->first();

        return $info ? $info['id'] : 0;
    }

    /**
     * This method should return the info of the document that will be stored in another document, default to full info
     * 
     * @return array
     */
    public function sharedInfo(): array
    {
        $info = !empty(static::SHARED_INFO) ? $this->pluck(static::SHARED_INFO)
            : $this->getAttributes();

        unset($info['_id']);

        $this->adjustDateInSharedInfo($info);

        return $info;
    }

    /**
     * Check if the given info data has date, then adjust it recursively
     * 
     * @param  array $info
     * @return void
     */
    public function adjustDateInSharedInfo(&$info)
    {
        foreach ($info as &$value) {
            if ($value instanceof DateTime) {
                $value = $value->getTimestamp();
            } elseif (is_array($value)) {
                $this->adjustDateInSharedInfo($value);
            }
        }
    }

    /**
     * Get shared info plus the given columns
     * 
     * @param ...string $columns
     * @return array
     */
    public function sharedInfoWith(...$columns): array
    {
        return array_merge($this->sharedInfo(), $this->pluck($columns));
    }

    /**
     * Get shared info except the given columns
     * 
     * @param ...string $columns
     * @return array
     */
    public function sharedInfoExcept(...$columns): array
    {
        return array_diff_key($this->sharedInfo(), $this->pluck($columns));
    }

    /**
     * {@inheritDoc}
     */
    public static function find($id)
    {
        return static::where('id', (int) $id)->first();
    }

    /**
     * Get user by id that will be used with created by, updated by and deleted by
     * 
     * @return mixed
     */
    protected function byUser()
    {
        $user = user();
        return $user ? $user->sharedInfo() : null;
    }

    /**
     * Associate the given value to the given key
     * 
     * @param mixed $modelInfo
     * @param string $column
     * @return this
     */
    public function associate($modelInfo, $column)
    {
        $listOfValues = $this->$column ?? [];

        if ($modelInfo instanceof Model) {
            $listOfValues[] = $modelInfo->sharedInfo();
        } else {
            $listOfValues[] = $modelInfo;
        }

        $this->setAttribute($column, $listOfValues);

        return $this;
    }

    /**
     * Re-associate the given document
     * 
     * @param   mixed $modelInfo
     * @param   string $column
     * @param   string $searchingColumn
     * @return $this
     */
    public function reassociate($modelInfo, string $column, string $searchingColumn = 'id')
    {
        $documents = $this->$column ?? [];

        if ($modelInfo instanceof Model) {
            $modelInfo = $modelInfo->sharedInfo();
        }

        $found = false;

        foreach ($documents as $key => $document) {
            if (is_scalar($document) && $document === $modelInfo) {
                $documents[$key] = $modelInfo;
                $found = true;
                break;
            } else {
                $document = (array) $document;
                if (isset($document[$searchingColumn]) && $document[$searchingColumn] == $modelInfo[$searchingColumn]) {
                    $documents[$key] = $modelInfo;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $documents[] = $modelInfo;
        }

        $this->setAttribute($column, $documents);

        return $this;
    }

    /**
     * Disassociate the given value to the given key
     * 
     * @param mixed $modelInfo
     * @param string $column
     * @param string $searchBy
     * @return this
     */
    public function disassociate($modelInfo, $column, $searchBy = 'id')
    {
        $array = $this->$column ?? [];

        $newArray = [];

        foreach ($array as $value) {
            if (
                is_scalar($modelInfo) && $modelInfo === $value ||
                is_array($value) && isset($value[$searchBy]) && $value[$searchBy] == $modelInfo[$searchBy]
            ) {
                continue;
            }

            $newArray[] = $value;
        }

        $this->setAttribute($column, $newArray);

        return $this;
    }
}
