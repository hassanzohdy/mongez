<?php

namespace HZ\Illuminate\Mongez\Managers\Database\MongoDB;

use DateTime;
use Illuminate\Support\Str;
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
                $model->_id = sha1(time() . Str::random(40)); 
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
     * Get model info
     * 
     * @return mixed
     */
    public function info(): array
    {
        return $this->getAttributes();
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

        foreach ($info as &$value) {
            if ($value instanceof DateTime) {
                $value = $value->getTimestamp();
            }
        }

        return $info;
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
     * Re-associate the given document
     * 
     * @param   mixed $modelInfo
     * @param   string $column
     * @return $this
     */
    public function reassociate($modelInfo, $column)
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
                if (isset($document['id']) && $document['id'] == $modelInfo['id']) {
                    $documents[$key] = $modelInfo;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $documents[] = $modelInfo;
        }

        $this->$column = $documents;

        return $this;
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

        $this->$column = $listOfValues;

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

        $this->$column = $newArray;

        return $this;
    }
}
