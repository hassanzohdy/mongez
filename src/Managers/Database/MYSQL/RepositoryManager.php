<?php

namespace HZ\Illuminate\Mongez\Managers\Database\MYSQL;

use Storage;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use HZ\Illuminate\Mongez\Events\Events;
use Illuminate\Support\Traits\Macroable;
use HZ\Illuminate\Mongez\Traits\RepositoryTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use HZ\Illuminate\Mongez\Helpers\Repository\Select;
use HZ\Illuminate\Mongez\Traits\Repository\Fillers;
use HZ\Illuminate\Mongez\Traits\Repository\Cacheable;
use HZ\Illuminate\Mongez\Traits\Repository\Deletable;
use HZ\Illuminate\Mongez\Helpers\Filters\FilterManager;
use HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface;

abstract class RepositoryManager implements RepositoryInterface
{
    /**
     * We're injecting the repository trait as it will be used 
     * for quick access to other repositories
     */
    use RepositoryTrait;

    /**
     * Data Saving Fillers
     */
    use Fillers;

    /**
     * Deleting
     */
    use Deletable;

    /**
     * Caching
     */
    use Cacheable;

    /**
     * Repository name
     * 
     * @const string
     */
    const NAME = '';

    /**
     * Filter class.
     * 
     * @const string
     */
    const FILTERS = [];

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
        'filtering' => 'filters',
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
    const WHEN_AVAILABLE_DATA = [];

    /**
     * Filter by columns used with `list` method only
     * 
     * @const array
     */
    const FILTER_BY = [];

    // unix timestamp 12045550123
    // 12-04-2020 => 12045550123 strtotime()

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

        $this->boot();

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
        $this->initiateListing($options);

        $this->trigger("listing", $this->query, $this);

        $paginate = $this->option('paginate', static::PAGINATE);

        if ($this->request->paginate === 'false') {
            $paginate = false;
        }

        if ($paginate === true || $paginate === null && config('mongez.pagination.paginate') === true) {
            $pageNumber = $this->option('page', 1);

            $itemPerPage = (int) $this->option('itemsPerPage', static::ITEMS_PER_PAGE !== null ? static::ITEMS_PER_PAGE : config('mongez.pagination.itemsPerPage'));

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
     * Get total records based on given options
     * 
     * @param array $options
     * @return int
     */
    public function total(array $options)
    {
        $this->initiateListing($options);

        return $this->query->count();
    }

    /**
     * Initiate listing info
     * 
     * @param  array $options
     * @return void
     */
    protected function initiateListing(array $options)
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

        $filterManger = new FilterManager($this->query, $options, static::FILTER_BY);
        $filterManger->merge(array_merge(static::FILTERS, config('mongez.filters', [])));

        $this->filter();

        $defaultOrderBy = [];

        if ($orderBy = $this->option('orderBy')) {
            $defaultOrderBy = $orderBy;
        } elseif (!empty(static::ORDER_BY)) {
            $defaultOrderBy = [$this->column(static::ORDER_BY[0]), static::ORDER_BY[1]];
        }

        $this->orderBy($this->option('orderBy', $defaultOrderBy));
    }

    /**
     * Get publish Model 
     * 
     * @param int $id
     * @return Model|null
     */
    public function getPublishedModel($id)
    {
        $model = $this->getModel($id);

        if (!$model->published) return null;

        return $model;
    }

    /**
     * Get publish item 
     * 
     * @param int $id
     * @return Resource|null
     */
    public function getPublished($id)
    {
        $item = $this->get($id);

        if (!$item->published) return null;

        return $item;
    }

    /**
     * Get published items
     * 
     * @param array $options
     * @return Collection
     */
    public function published(array $options = [])
    {
        $options['published'] = true;
        return $this->list($options);
    }

    /**
     * Publish/Unpublish the model id
     *
     * @param int $id
     * @param bool $publishState
     * @return void
     */
    public function publish($id, $publishState)
    {
        $this->getQuery()->where('id', (int)$id)->update([
            'published' => (bool) $publishState
        ]);
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
        if (is_array($model)) {
            $modelName = static::MODEL;
            $model = new $modelName($model);
        }
        
        $resource = static::RESOURCE;
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
        $collection = collect($collection)->map(function ($item) {
            if (is_array($item)) {
                $modelName = static::MODEL;
                $item = new $modelName($item);
            }
            
            return $item;
        });
        
        $resource = static::RESOURCE;
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
    public function getQuery()
    {
        $model = static::MODEL;
        return new $model;
    }

    /**
     * Get new model object
     * 
     * @return Model 
     */
    public function newModel($data = [])
    {
        $modelName = static::MODEL;

        return new $modelName($data);
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

        // If there is no zero index in the array
        // it means the order will be for multiple columns
        if (!isset($orderBy[0])) {
            foreach ($orderBy as $column => $columnOrder) {
                $this->query->orderBy($column, $columnOrder);
            }
        } else {
            $this->query->orderBy(...$orderBy);
        }
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
        $model = $this->getQuery();

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
        $model = $this->getQuery()->find($id);

        $oldModel = clone $model;

        $this->oldModel = $oldModel;

        $request = $this->getRequestWithData($data);

        $this->setAutoData($model, $request);

        $this->setData($model, $request);

        $this->save($model, $oldModel);

        return $model;
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
            $this->trigger("saving creating", $model, $this->request);
            $model->save();
            $this->trigger("save create", $model, $this->request);
        }

        if (static::USING_CACHE) $this->setCache($model->id, $model);
    }

    /**
     * Make basic operations on any entered request
     * 
     * @return void
     */
    protected function boot()
    {
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
}
