<?php

namespace HZ\Illuminate\Mongez\Managers\Database\MYSQL;

use DB;
use File;
use Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use HZ\Illuminate\Mongez\Events\Events;
use Illuminate\Support\Traits\Macroable;
use HZ\Illuminate\Mongez\Traits\RepositoryTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use HZ\Illuminate\Mongez\Helpers\Repository\Select;
use HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface;

abstract class RepositoryManager implements RepositoryInterface
{
    /**
     * We're injecting the repository trait as it will be used 
     * for quick access to other repositories
     */
    use RepositoryTrait;

    /**
     * Repository name
     * 
     * @const string
     */
    const NAME = '';

    /**
     * Allow repository to be extended
     */
    use Macroable {
        __call as marcoableMethods;
    }

    /**
     * Model name
     * 
     * @const string
     */
    const MODEL = '';

    /**
     * Resource class 
     * 
     * @const string
     */
    const RESOURCE = '';

    /**
     * Event name to be triggered
     * If set to empty, then it will be the class model name
     * 
     * @const string
     */
    const EVENT = '';

    /**
     * Uploads directory name
     * 
     * @const string
     */
    const UPLOADS_DIRECTORY = '';

    /**
     * If set to true, then the file will be stored as its uploaded name
     * 
     * @const bool
     */
    const UPLOADS_KEEP_FILE_NAME = false;

    /**
     * Event name to be triggered
     * If set to empty, then it will be the class model name
     * 
     * @const string
     */
    const EVENTS_LIST = [
        'listing' => 'onListing',
        'list' => 'onList',
        'creating' => 'onCreating',
        'create' => 'onCreate',
        'saving' => 'onSaving',
        'save' => 'onSave',
        'updating' => 'onUpdating',
        'update' => 'onUpdate',
        'deleting' => 'onDeleting',
        'delete' => 'onDelete',
    ];

    /**
     * Set if the current repository uses a soft delete method or not
     * This is mainly used in the where clause
     * 
     * @var bool
     */
    const USING_SOFT_DELETE = true;

    /**
     * Using redis cache
     * 
     * @var bool
     */
    const USING_CACHE = false;

    /**
     * Deleted at column
     * 
     * @const string
     */
    const DELETED_AT = 'deleted_at';

    /**
     * Retrieve only the active `un-deleted` records
     * 
     * @const string
     */
    const RETRIEVE_ACTIVE_RECORDS = 'ACTIVE';

    /**
     * Retrieve All records
     * 
     * @const string
     */
    const RETRIEVE_ALL_RECORDS = 'ALL';

    /**
     * Retrieve Deleted records
     * 
     * @const string
     */
    const RETRIEVE_DELETED_RECORDS = 'DELETED';

    /**
     * Retrieval mode keyword to be used in the options list flag
     * 
     * @const string
     */
    const RETRIEVAL_MODE = 'retrievalMode';

    /**
     * Default retrieval mode
     * 
     * @const string
     */
    const DEFAULT_RETRIEVAL_MODE = self::RETRIEVE_ACTIVE_RECORDS;

    /**
     * Table name
     *
     * @const string
     */
    const TABLE = '';

    /**
     * Table alias
     *
     * @const string
     */
    const TABLE_ALIAS = '';

    /**
     * Set the default order by for the repository
     * i.e ['id', 'DESC']
     * 
     * @const array
     */
    const ORDER_BY = ['id', 'DESC'];

    /**
     * Auto fill the following columns directly from the request
     * 
     * @const array
     */
    const DATA = [];

    /**
     * Auto fill the following columns as arrays directly from the request
     * It will encoded and stored as `JSON` format, 
     * it will be also auto decoded on any database retrieval either from `list` or `get` methods
     * 
     * @const array
     */
    const ARRAYBLE_DATA = [];

    /**
     * Auto save uploads in this list
     * If it's an indexed array, in that case the request key will be as database column name
     * If it's associated array, the key will be request key and the value will be the database column name 
     * 
     * @const array
     */
    const UPLOADS = [];

    /**
     * Set columns list of integers values.
     * 
     * @cont array  
     */
    const INTEGER_DATA = [];

    /**
     * Set columns list of float values.
     * 
     * @cont array  
     */
    const FLOAT_DATA = [];

    /**
     * Set columns list of date values.
     * 
     * @cont array  
     */
    const DATE_DATA = [];

    /**
     * Set columns of booleans data type.
     * 
     * @cont array  
     */
    const BOOLEAN_DATA = [];

    /**
     * Add the column if and only if the value is passed in the request.
     * 
     * @cont array  
     */
    const WHEN_AVAILABLE_DATA = ['name', 'icon'];

    /**
     * Filter by columns used with `list` method only
     * 
     * @const array
     */
    const FILTER_BY = [];

    /**
     * Determine wether to use pagination in the `list` method
     * if set null, it will depend on pagination configurations
     * 
     * @const bool
     */
    const PAGINATE = null;

    /**
     * Number of items per page in pagination
     * If set to null, then it will taken from pagination configurations
     * 
     * @const int|null
     */
    const ITEMS_PER_PAGE = null;

    /**
     * This property will has the final table name that will be used
     * i.e if the TABLE_ALIAS is not empty, then this property will be the value of the TABLE_ALIAS
     * otherwise it will be the value of the TABLE constant
     *
     * @const string
     */
    protected $table;

    /**
     * The base event name that will be used
     *
     * @const string
     */
    protected $eventName;

    /**
     * User data
     *
     * @cont mixed
     */
    protected $user;

    /**
     * Query Builder Object
     *
     * @var \Illuminate\Database\Query\Builder
     */
    protected $query;

    /**
     * Request Object
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Events Object
     *
     * @var Events
     */
    protected $events;

    /**
     * Old model object
     * Works with update method only
     * 
     * @var Model
     */
    protected $oldModel;

    /**
     * Select Helper Object
     *
     * @var \HZ\Illuminate\Mongez\Helpers\Repository\Select
     */
    protected $select;

    /**
     * Options list
     *
     * @param array
     */
    protected $options = [];

    /**
     * Dependency tables of deleting
     *
     * @param array
     */
    protected $deleteDependenceTables = [];

    /**
     * Pagination info
     * 
     * @var array 
     */
    protected $paginationInfo = [];

    /**
     * Constructor
     * 
     * @param \Illuminate\Http\Request
     */
    public function __construct(Request $request, Events $events)
    {
        $this->request = $request;

        $this->events = $events;

        $this->user = user();

        $this->eventName = static::EVENT ?: static::NAME;

        // register events
        $this->registerEvents();
    }

    /**
     * Register repository events
     * 
     * @return void
     */
    protected function registerEvents()
    {
        if (!$this->eventName) return;

        foreach (static::EVENTS_LIST as $eventName => $methodCallback) {
            if (method_exists($this, $methodCallback)) {
                $this->events->subscribe("{$this->eventName}.$eventName", static::class . '@' . $methodCallback);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has($value, string $column = 'id'): bool
    {
        if (is_numeric($value)) {
            $value = (float) $value;
        }

        $model = static::MODEL;

        return $model::where($column, $value)->exists();
    }

    /**
     * Get a normal record by id
     * Please use the `get` method to get full details about the record
     * 
     * @param  int $id
     * @param  array $otherOptions
     * @return mixed
     */
    public function first(int $id, array $otherOptions = [])
    {
        $otherOptions['id'] = $id;

        return $this->list($otherOptions)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function list(array $options): Collection
    {
        $this->setOptions($options);

        $this->query = $this->getQuery();

        $this->table = $this->columnTableName();

        $this->select();

        if (static::USING_SOFT_DELETE === true) {
            $retrieveMode = $this->option(static::RETRIEVAL_MODE, static::DEFAULT_RETRIEVAL_MODE);

            if ($retrieveMode == static::RETRIEVE_ACTIVE_RECORDS) {
                $deletedAtColumn = $this->column(static::DELETED_AT);

                $this->query->whereNull($deletedAtColumn);
            } elseif ($retrieveMode == static::RETRIEVE_DELETED_RECORDS) {
                $deletedAtColumn = $this->column(static::DELETED_AT);
                $this->query->whereNotNull($deletedAtColumn);
            }
        }

        foreach (static::FILTER_BY as $column => $option) {
            if ($value = $this->option($option)) {
                $column = is_numeric($column) ? $option : $column;
                if (is_array($value)) {
                    // make sure values are integers
                    if ($column == 'id') {
                        $value = array_map('intval', $value);
                    }
                    $this->query->whereIn($column, $value);
                } else {
                    $this->query->where($column, $value);
                }
            }
        }

        $this->filter();

        $defaultOrderBy = [];

        if ($orderBy = $this->option('orderBy')) {
            $defaultOrderBy = $orderBy;
        } elseif (!empty(static::ORDER_BY)) {
            $defaultOrderBy = [$this->column(static::ORDER_BY[0]), static::ORDER_BY[1]];
        }

        $this->orderBy($this->option('orderBy', $defaultOrderBy));

        $this->trigger("listing", $this->query, $this);

        $paginate = $this->option('paginate', static::PAGINATE);

        if ($this->request->paginate === 'false') {
            $paginate = false;
        }

        if ($paginate === true || $paginate === null && config('mongez.pagination.paginate') === true) {
            $pageNumber = $this->option('page', 1);

            $itemPerPage = $this->option('itemsPerPage', static::ITEMS_PER_PAGE !== null ? static::ITEMS_PER_PAGE : config('mongez.pagination.itemsPerPage'));

            $selectedColumns = !empty($this->select->list()) ? $this->select->list() : ['*'];

            $data = $this->query->paginate($itemPerPage, $selectedColumns, 'page', $pageNumber);

            $this->setPaginateInfo($data);

            $records = collect($data->items());
        } else {
            if ($this->select->isNotEmpty()) {
                $this->query->select(...$this->select->list());
            }

            if ($limit = $this->option('limit')) {
                $this->query->limit((int) $limit);
            }

            $records = $this->query->get();
        }

        $records = $this->records($records);

        $results = $this->trigger("list", $records);

        if ($results instanceof Collection) {
            $records = $results;
        }

        return $records;
    }

    /**
     * Trigger the given event related to current repository
     * 
     * @param  string $events
     * @param ...$values
     * @return mixed
     */
    public function trigger(string $events, ...$values)
    {
        $events = array_map(function ($event) {
            return "{$this->eventName}.{$event}";
        }, explode(' ', $events));

        return $this->events->trigger(implode(' ', $events), ...$values);
    }

    /**
     * Set pagination info from pagination data
     * 
     * @param object $data
     * @return void
     */
    protected function setPaginateInfo($data)
    {
        $this->paginationInfo = [
            'currentResults' => $data->count(),
            'totalRecords' => $data->total(),
            'numberOfPages' => $data->lastPage(),
            'itemsPerPage' => $data->perPage(),
            'currentPage' => $data->currentPage()
        ];
    }

    /**
     * Get pagination info
     * 
     * @return array $paginationInfo
     */
    public function getPaginateInfo(): array
    {
        return $this->paginationInfo;
    }

    /**
     * Wrap the given model to its resource
     * 
     * @param \Model $model
     * @return \JsonResource
     */
    public function wrap($model): JsonResource
    {
        $resource = static::RESOURCE;

        if (is_array($model)) {
            $modelName = static::MODEL;
            $model = new $modelName($model);
        }

        return new $resource($model);
    }

    /**
     * Wrap the given collection into collection of resources
     * 
     * @param \Illuminate\Support\Collection $collection
     * @return \JsonResource
     */
    public function wrapMany($collection)
    {
        $resource = static::RESOURCE;
        $collection = collect($collection)->map(function ($item) {
            if (is_array($item)) {
                $modelName = static::MODEL;
                $item = new $modelName($item);
            }

            return $item;
        });
        return $resource::collection($collection);
    }

    /**
     * Get table name of the primary model of the repo
     * 
     * @return string
     */
    public function getTableName(): string
    {
        return static::TABLE ?: (static::MODEL)::getTableName();
    }

    /**
     * Get the query handler
     * 
     * @return mixed
     */
    protected function getQuery()
    {
        return DB::table($this->getTableName());
    }

    /**
     * Get new model object
     * 
     * @return Model 
     */
    public function newModel()
    {
        $modelName = static::MODEL;

        return new $modelName;
    }

    /**
     * Get the table name that will be used in the query 
     * 
     * @return string
     */
    protected function tableName(): string
    {
        return static::TABLE_ALIAS ? static::TABLE . ' as ' . static::TABLE_ALIAS : static::TABLE;
    }

    /**
     * Get the table name that will be used in the rest of the query like select, where...etc
     * 
     * @return string
     */
    protected function columnTableName(): string
    {
        return static::TABLE_ALIAS ?: static::TABLE;
    }

    /**
     * This method mainly used to filtering records `the where clause`
     *
     * @return void
     */
    abstract protected function filter();

    /**
     * Manage Selected Columns
     *
     * @return void
     */
    abstract protected function select();

    /**
     * Perform records ordering
     * 
     * @param   array $orderBy
     * @return  void
     */
    protected function orderBy(array $orderBy)
    {
        if (empty($orderBy)) return;

        $this->query->orderBy(...$orderBy);
    }

    /**
     * Adjust records that were fetched from database
     *
     * @param \Illuminate\Support\Collection $records
     * @return \Illuminate\Support\Collection
     */
    protected function records(Collection $records): Collection
    {
        return $records->map(function ($record) {
            if (!empty(static::ARRAYBLE_DATA)) {
                foreach (static::ARRAYBLE_DATA as $column) {
                    $record[$column] = json_encode($record[$column]);
                }
            }

            return $record;
        });
    }

    /**
     * Get column name appended by table|table alias 
     * 
     * @param  string $column
     * @return string
     */
    protected function column(string $column): string
    {
        return $this->table . '.' . $column;
    }

    /**
     * Set options list
     *
     * @param array $options
     * @return void
     */
    protected function setOptions(array $options): void
    {
        $this->options = $options;

        $selectColumns = (array) $this->option('select');

        $this->select = new Select($selectColumns);
    }

    /**
     * Get option value
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    protected function option(string $key, $default = null)
    {
        return Arr::get($this->options, $key, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function create($data)
    {
        $modelName = static::MODEL;

        $model = new $modelName;

        $request = $this->getRequestWithData($data);

        $this->setAutoData($model, $request);

        $this->setData($model, $request);
        
        $this->save($model);
        
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, $data)
    {
        $model = (static::MODEL)::find($id);

        $oldModel = clone $model;

        $this->oldModel = $oldModel;

        $request = $this->getRequestWithData($data);

        $this->setAutoData($model, $request);

        $this->setData($model, $request);

        $this->save($model, $oldModel);

        return $model;
    }

    /**
     * Get request object with data
     * 
     * @param  Request|array $data
     * @return Request
     */
    protected function getRequestWithData($data): Request
    {
        if (is_array($data)) {
            $request = $this->request;
            foreach ($data as $key => $value) {
                Arr::set($request, $key, $value);
            }
        } else {
            $request = $data;
        }

        return $request;
    }

    /**
     * Set data automatically from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void
     */
    protected function setAutoData($model, $request)
    {
        $this->setMainData($model, $request);

        $this->setArraybleData($model, $request);

        $this->upload($model, $request);

        $this->setIntData($model, $request);

        $this->setFloatData($model, $request);

        $this->setDateData($model, $request);

        $this->setBoolData($model, $request);
    }

    /**
     * Set date data
     * 
     * @param  Model $model
     * @param  Request $request
     * @return void
     */
    protected function setDateData($model, $request, $columns = null)
    {
        if (!$columns) {
            $columns = static::DATE_DATA;
        }

        foreach ((array) $columns as $column) {
            if (in_array($column, static::WHEN_AVAILABLE_DATA) && !isset($request->$column)) continue;

            $date = $request->input($column);

            if (!$date) continue;

            $model->$column = is_numeric($date) ? $date : strtotime($date);
        }
    }

    /**
     * Set main data automatically from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setMainData($model, $request)
    {
        foreach (static::DATA as $column) {
            if (in_array($column, static::WHEN_AVAILABLE_DATA) && !isset($request->$column)) continue;

            if (!isset($request->$column)) {
                $model->$column = null;
            } else {
                if ($column == 'password' && $request->password) {
                    $model->password = bcrypt($request->password);
                } else {
                    $model->$column = $request->$column;
                }
            }
        }
    }
    /**
     * Set Arrayble data automatically from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setArraybleData($model, $request)
    {
        foreach (static::ARRAYBLE_DATA as $column) {
            if (in_array($column, static::WHEN_AVAILABLE_DATA) && !isset($request->$column)) continue;
            $value = array_filter((array) $request->$column);
            $value = $this->handleArrayableValue($value);
            $model->$column = $value;
        }
    }
    /**
     * Set uploads data automatically from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function upload($model, $request, $columns = null)
    {
        if (!$columns) {
            $columns = static::UPLOADS;
        }

        $storageDirectory = $this->getUploadsStorageDirectoryName();

        if (true === static::UPLOADS_KEEP_FILE_NAME) {
            $storageDirectory .= '/' . $model->getId();
        }

        $getFileName = function (UploadedFile $fileObject): string {
            $originalName = $fileObject->getClientOriginalName();
            $extension = File::extension($originalName) ?: $fileObject->guessExtension();
            $fileName = false === static::UPLOADS_KEEP_FILE_NAME ? Str::random(40) . '.' . $extension : $originalName;
            return $fileName;
        };

        foreach ((array) $columns as $column => $name) {
            if (is_numeric($column)) {
                $column = $name;
            }

            $file = $request->file($name);

            if (!$file) continue;

            if (is_array($file)) {
                $files = [];

                foreach ($file as $index => $fileObject) {
                    if (!$fileObject->isValid()) continue;

                    $files[$index] = $fileObject->storeAs($storageDirectory, $getFileName($fileObject));
                }

                $model->$column = $files;
            } else {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $model->$column = $file->storeAs($storageDirectory, $getFileName($file));
                }
            }
        }
    }

    /**
     * Get the uploads storage directory name
     * 
     * @return string
     */
    protected function getUploadsStorageDirectoryName(): string
    {
        return static::UPLOADS_DIRECTORY ?: static::NAME;
    }

    /**
     * Cast specific data automatically to int from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setIntData($model, Request $request)
    {
        foreach (static::INTEGER_DATA as $column) {
            if (in_array($column, static::WHEN_AVAILABLE_DATA) && !isset($request->$column)) continue;
            $model->$column = (int) $request->input($column);
        }
    }

    /**
     * Cast specific data automatically to float from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setFloatData($model, Request $request)
    {
        foreach (static::FLOAT_DATA as $column) {
            if (in_array($column, static::WHEN_AVAILABLE_DATA) && !isset($request->$column)) continue;
            $model->$column = (float) $request->input($column);
        }
    }

    /**
     * Cast specific data automatically to bool from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setBoolData($model, Request $request)
    {
        foreach (static::BOOLEAN_DATA as $column) {
            if (in_array($column, static::WHEN_AVAILABLE_DATA) && !isset($request->$column)) continue;
            $model->$column = (bool) $request->input($column);
        }
    }

    /**
     * Pare the given arrayed value
     *
     * @param array $value
     * @return mixed
     */
    protected function handleArrayableValue(array $value)
    {
        return json_encode($value);
    }

    /**
     * If the given id exists then we will retrieve an existing record
     * otherwise, create new model
     * 
     * @param  string $model
     * @param  int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function findOrCreate(string $model, int $id): Model
    {
        return $model::find($id) ?: new $model;
    }

    /**
     * Set data to the model
     * This method is triggered on create and update as it will be a useful 
     * method to set model data once instead of adding it on create and adding it again on update
     * 
     * In simple words, add common fields between create and update using this method
     * 
     * @parm   \Illuminate\Database\Eloquent\Model $model
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    abstract protected function setData($model, Request $request);

    /**
     * Update record for the given model
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array $columns
     * @return void
     */
    protected function updateModel(Model $model, array $columns): void
    {
        // available syntax for the columns
        // 1- Associative array: ['name' => 'My Name', 'email' => 'MY@email.com']
        // 2- Indexed array: it means we will get the value from the request object
        // i.e ['name', 'email'] then it will get it like: 
        // $model->name = $request->name and so on  

        foreach ($columns as $key => $value) {
            if (is_string($key)) {
                $model->$key = $value;
            } else {
                $model->$value = $this->request->$value;
            }
        }

        $model->save();
    }

    /**
     * Set the given data to the given model `without` saving it
     * 
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array $columns
     * @return void
     */
    protected function setModelData(Model $model, array $columns): void
    {
        foreach ($columns as $column => $value) {
            $model->$column = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete($model): bool
    {
        if (is_numeric($model)) {
            $model = (static::MODEL)::find($model);
            if (!$model) return false;
        }

        // delete uploaded files
        foreach (static::UPLOADS as $file) {
            if (!$model->$file) continue;

            if (is_array($model->$file)) {
                foreach ($model->$file as $singleFile) {
                    $this->unlink($singleFile);
                }
            } else {
                $this->unlink($model->$file);
            }
        }

        if ($this->trigger("deleting", $model, $model->id) === false) return false;

        $model->delete();

        if (static::USING_CACHE) $this->forgetCache($model->id);

        $this->trigger("delete", $model, $id);

        return true;
    }

    /**
     * Call query builder methods dynamically
     * 
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->query, $method)) {
            return $this->query->$method(...$args);
        }

        return $this->marcoableMethods($method, $args);
    }

    /**
     * Check if model has deleting depended tables.
     *
     * @return bool
     */
    public function deleteHasDependence(): bool
    {
        return !empty($this->deleteDependenceTables);
    }

    /**
     * Get model deleting depended tables
     *
     * @return array
     */
    public function getDeleteDependencies(): array
    {
        return $this->deleteDependenceTables;
    }

    /**
     * Check if soft delete used or not
     *
     * @return bool
     */
    public function isUsingSoftDelete(): bool
    {
        return static::USING_SOFT_DELETE;
    }

    /**
     * Remove the given file path from storage 
     * 
     * @param  string $path
     * @return mixed
     */
    public function unlink(string $path)
    {
        return Storage::delete($path);
    }

    /**
     * Get record from redis cache
     * 
     * @param string $key
     * @return mixed  
     */
    public function getCache($key)
    {
        $key = static::NAME .$key;
        return $this->getCacheDriver()->get($key);
    }

    /**
     * Set record to redis cache
     * 
     * @param string $key
     * @param mixed $value
     * @return void  
     */
    public function setCache($key, $value)
    {
        $key = static::NAME .$key;
        return $this->getCacheDriver()->put($key, $value);
    }

    /**
     * Forget from cache by key  
     * 
     * @param string $key
     * @param mixed $value
     * @return void  
     */
    public function forgetCache($key)
    {
        $key = static::NAME .$key;
        return $this->getCacheDriver()->forget($key);     
    }

    /**
     * Get cache driver
     * 
     * @return string cache drive 
     */
    protected function getCacheDriver()
    {
        return Cache::store(config('mongez.cache.driver'));
    } 

    /**
     * Saving triggers 
     * 
     * @param object $model
     * @return void 
     */
    protected function save($model, $oldModel = null)
    {
        if ($model->id) {
            $this->trigger("saving updating", $model, $this->request, $oldModel);
            $model->save();    
            $this->trigger("save update", $model, $this->request, $oldModel);
        } else {
            $this->trigger("save create", $model, $this->request);
            $model->save();
            $this->trigger("saving creating", $model, $this->request);    
        }

        if (static::USING_CACHE) $this->setCache($model->id, $model);
    }
}
