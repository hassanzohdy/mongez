<?php

namespace HZ\Illuminate\Mongez\Database\Eloquent\MongoDB;

use HZ\Illuminate\Mongez\Database\Eloquent\Associatable;
use HZ\Illuminate\Mongez\Database\Eloquent\ModelEvents;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use MongoDB\Laravel\Eloquent\Model as BaseModel;
use HZ\Illuminate\Mongez\Database\Eloquent\ModelTrait;

abstract class Model extends BaseModel
{
    use RecycleBin, ModelEvents, Associatable;

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
     * This is a combination of ON_MODEL_CREATE & ON_MODEL_UPDATE & ON_MODEL_DELETE_UNSET
     * Define list of other models that will be affected on creating|updating|deleting
     *
     * @example ModelClass::class => searchingColumn will be converted to ['searchingColumn['id']', 'columnName', 'sharedInfo']
     * @example ModelClass::class => [searchingColumn, creatingColumn]
     * @example ModelClass::class => [searchingColumn, creatingColumn, sharedInfoMethod]
     *
     * @const array
     */
    const MODEL_LINKS = [];

    /**
     * This is a combination of ON_MODEL_CREATE & ON_MODEL_UPDATE & ON_MODEL_DELETE
     * The main difference between this constant and MODEL_LINKS is that this constant will delete the entire record
     * unlike MODEL_LINKS it will just unset the embedded document.
     * Define list of other models that will be affected on creating|updating|deleting
     *
     * @example ModelClass::class => searchingColumn will be converted to ['searchingColumn['id']', 'columnName', 'sharedInfo']
     * @example ModelClass::class => [searchingColumn, creatingColumn]
     * @example ModelClass::class => [searchingColumn, creatingColumn, sharedInfoMethod]
     *
     * @const array
     */
    const MODEL_LINKS_DELETE = [];

    /**
     * This is a combination of ON_MODEL_CREATE_PUSH & ON_MODEL_UPDATE_ARRAY & ON_MODEL_DELETE_PULL
     * Define list of other models that will be affected on creating|updating|deleting
     *
     * i.e [Country::class => 'cities'] current model is city, city is in Country model in `cities` key
     * Once the city model is created it will be pushed to Country model in `cities`
     *
     * @example ModelClass::class => searchingColumn will be converted to ['searchingColumn['id']', 'columnName', 'sharedInfo']
     * @example ModelClass::class => [searchingColumn, creatingColumn]
     * @example ModelClass::class => [searchingColumn, creatingColumn, sharedInfoMethod]
     *
     * @const array
     */
    const MODEL_LINKS_ARRAY = [];

    /**
     * Define list of other models that will be affected
     * as the current model is sub-document to it when it gets created
     *
     * @example ModelClass::class => searchingColumn will be converted to ['searchingColumn['id']', 'columnName', 'sharedInfo']
     * @example ModelClass::class => [searchingColumn, creatingColumn]
     * @example ModelClass::class => [searchingColumn, creatingColumn, sharedInfoMethod]
     *
     * @const array
     */
    const ON_MODEL_CREATE = [];

    /**
     * Define list of other models that will be affected as the current object is part of array
     * as the current model is sub-document to it when it gets created
     *
     * @example ModelClass::class => searchingColumn will be converted to ['searchingColumn['id'], 'columnName', 'sharedInfo']
     * @example ModelClass::class => [searchingColumn, creatingColumn]
     * @example ModelClass::class => [searchingColumn, creatingColumn, sharedInfoMethod]
     *
     * @const array
     */
    const ON_MODEL_CREATE_PUSH = [];

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
     * Define list of other models that will be affected as the current object is part of array
     * as the current model is sub-document to it when it gets updated
     *
     * @example ModelClass::class => columnName will be converted to ['columnName.id', 'columnName', 'sharedInfo']
     * @example ModelClass::class => [searchingColumn, updatingColumn]
     * @example ModelClass::class => [searchingColumn, updatingColumn, sharedInfoMethod]
     *
     * @const array
     */
    const ON_MODEL_UPDATE_ARRAY = [];

    /**
     * Define list of other models that will clear the column from its records
     * A 1-1 relation
     *
     * Do not add the id, it will be appended automatically
     *
     * @example ModelClass::class => searchingColumn: string
     *
     * @const array
     */
    const ON_MODEL_DELETE_UNSET = [];

    /**
     * Define list of the models that have the current model as embedded document and pull it from the array
     *  A 1-n relation
     * Do not add the id, it will be appended automatically
     *
     * @example ModelClass::class => searchingColumn: string
     *
     * @const array
     */
    const ON_MODEL_DELETE_PULL = [];

    /**
     * Define list of other models that will be deleted
     * when this model is deleted
     * For example when a city is deleted, all related regions shall be deleted as well
     *
     * Do not add the id, it will be appended automatically
     *
     * @example Region::class => 'city'
     * @example ModelClass::class => searchingColumn: string
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
     * Set the auto increment value for generating ids
     *
     * @var int|null
     */
    protected static $autoIncrementIdBy = null;

    /**
     * Set the initial id value when collection is being created for first
     *
     * @var int|null
     */
    protected static $initialId = null;

    /**
     * Determine whether to trigger events or not on model create|update|delete
     *
     * @var true | false | `create`|`update`|`delete`
     */
    protected $triggerEvents = true;

    /**
     * Cached table name
     * 
     * @var string
     */
    protected static string $tableName = '';

    /**
     * Get table name and cache it
     * 
     * @return string
     */
    public static function tableName()
    {
        if (empty(static::$tableName)) {
            $model = new static;
            static::$tableName = ($model)->getTable();
        }

        return static::$tableName;
    }

    /**
     * Update Event State
     *
     * @param  mixed $state
     * @return Self
     */
    public function triggerEvents($state): Self
    {
        $this->triggerEvents = $state;

        return $this;
    }

    /**
     * Determine if current model can trigger the given event type
     *
     * @param  string $eventType
     * @return boolean
     */
    public function canTrigger(string $eventType)
    {
        return $this->triggerEvents === true || $this->triggerEvents === $eventType;
    }

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        static::traitBoot();

        if (!static::$autoIncrementIdBy) {
            static::$autoIncrementIdBy = mt_rand(100, 999);
        }

        // Create an auto increment id on creating new document

        // before creating, we will check if the created_by column has value
        // if so, then we will update the column for the current user id
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = static::nextId();
            }
        });

        // When model create, detect whether there are any other models that
        // shall be created with it
        static::created(function ($model) {
            if (!$model->canTrigger('create')) return;

            static::handleCreated($model);
        });

        static::updating(function ($model) {
            if (static::UPDATED_BY && ($user = user())) {
                $model->updatedBy = $user->sharedInfo();
            }
        });

        // When model update, detect whether there are any other models that
        // shall be updated with it
        static::updated(function ($model) {
            if (!$model->canTrigger('update')) return;

            static::handleUpdated($model);
        });

        // triggered when a model record is deleted from database
        static::deleted(function ($model) {
            if (!$model->canTrigger('delete')) return;

            static::handleDeleted($model);
        });
    }

    /**
     * Create and return new id for the current model
     *
     * @return int
     */
    public static function nextId(): int
    {
        $lastId = static::lastInsertId();

        $newId = $lastId + static::$autoIncrementIdBy;

        $collection = static::tableName();

        $ids = DB::table('ids');

        if (!$lastId) {
            $ids->insert([
                'collection' => $collection,
                'id' => static::$initialId ?: mt_rand(100000, 999999),
            ]);
        } else {
            // dd($ids->where('collection', $collection)->get());
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
        return static::lastInsertId() + static::$autoIncrementIdBy;
    }

    /**
     * Get last insert id of the given collection name
     *
     * @return  int
     */
    public static function lastInsertId(): int
    {
        $ids = DB::table('ids');

        $info = $ids->where('collection', static::tableName())->first();

        if (empty($info?->id)) return 0;

        return $info->id;
    }

    /**
     * Reset auto increment
     *
     * @return void
     */
    public static function resetAutoIncrement()
    {
        DB::table('ids')->where('collection', static::tableName())->delete();
    }

    /**
     * Truncate the entire records and reset the auto increment
     *
     * @return void
     */
    public static function truncate()
    {
        static::delete();
        static::resetAutoIncrement();
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
            if ($value instanceof DateTimeInterface) {
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
}
