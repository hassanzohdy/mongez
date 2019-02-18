<?php
namespace HZ\Laravel\Organizer\Managers\Database\MYSQL;

use DB;
use App;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use HZ\Laravel\Organizer\Events\Events;
use HZ\Laravel\Organizer\Traits\RepositoryTrait;
use HZ\Laravel\Organizer\Helpers\Repository\Select;
use HZ\Laravel\Organizer\Contracts\Repositories\RepositoryInterface;

abstract class RepositoryManager implements RepositoryInterface
{
    /**
     * We're injecting the repository trait as it will be used 
     * for quick access to other repositories
     */
    use RepositoryTrait;

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
     * Resource class handler
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
     * Event name to be triggered
     * If set to empty, then it will be the class model name
     * 
     * @const string
     */
    const EVENTS_LIST = [
        'listing' => 'onListing',
        'create' => 'onCreate',
        'save' => 'onSave',
        'update' => 'onUpdate',
        'before-deleting' => 'beforeDeleting',
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
     * Auto fill the following columns directly from the request
     * 
     * @const array
     */
    const DATA = [];

    /**
     * Filter by the given inputs if exists in the request body
     * i.e ['name', 'email']
     * or 
     * ['name' => 'u.name', 'email' => 'u.email']
     * @const array
     */
    const FILTER_BY = [];

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
     * Select Helper Object
     *
     * @var \HZ\Laravel\Organizer\Helpers\Repository\Select
     */
    protected $select;

    /**
     * Options list
     *
     * @param array
     */
    protected $options = [];

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

        if (! empty(static::EVENTS_LIST)) {
            $this->eventName = static::EVENT;

            if (! $this->eventName) {
                $eventNameModelBased = basename(static::MODEL);
    
                $this->eventName = strtolower($eventNameModelBased);
                     
                if (Str::endsWith($this->eventName, 'y')) {
                    $this->eventName = Str::replaceLast('y', 'ies', $this->eventName);
                } elseif (Str::endsWith($this->eventName, 's')) {
                    $this->eventName = $this->eventName . 'es';
                } else {
                    $this->eventName = $this->eventName . 's';
                }           
            }

            // register events
            $this->registerEvents();    
        }
    }

    /**
     * Register repository events
     * 
     * @return void
     */
    protected function registerEvents()
    {
        if (! $this->eventName) return;

        foreach (static::EVENTS_LIST as $eventName => $methodCallback) {
            if (method_exists($this, $methodCallback)) {
                $this->events->subscribe("{$this->eventName}.$eventName", static::class . '@' . $methodCallback);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has(int $id): bool
    {        
        $model = static::MODEL;
        return (bool) $model::find($id);
        // return (bool) DB::table(static::TABLE)->where('id', $id)->first();
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
    function list(array $options): Collection 
    {
        $this->setOptions($options);

        $this->query = $this->getQuery();

        $this->table = $this->columnTableName();

        $this->select();

        if (static::USING_SOFT_DELETE === true) {
            $retrieveMode = $this->option(static::RETRIEVAL_MODE, static::DEFAULT_RETRIEVAL_MODE);

            if ($retrieveMode == static::RETRIEVE_ACTIVE_RECORDS) {
                $deletedAtColumn = $this->table . '.' . static::DELETED_AT;
    
                $this->query->whereNull($deletedAtColumn);    
            } elseif ($retrieveMode == static::RETRIEVE_DELETED_RECORDS) {
                $deletedAtColumn = $this->table . '.' . static::DELETED_AT;
    
                $this->query->whereNotNull($deletedAtColumn);    
            }
        }

        if (! empty(static::FILTER_BY)) {
            foreach (static::FILTER_BY as $requestParam => $column) {
                if (is_numeric($requestParam)) {
                    $requestParam = $column;
                }
                
                if (! is_null($value = $this->option($requestParam))) {
                    if (is_array($value)) {
                        $this->query->whereIn($column, $value);
                    } else {
                        $this->query->where($column, $value);
                    }
                }
            }
        }
        
        $this->filter();

        if ($this->select->isNotEmpty()) {
            $this->query->select(...$this->select->list());
        }
        
        $this->orderBy(array_filter((array) $this->option('orderBy')));
        
        $records = $this->query->get();

        $records = $this->records($records);

        $results = $this->events->trigger("{$this->eventName}.listing", $records);

        if ($results instanceof Collection) {
            $records = $results;
        }    

        return $records;
    }

    /**
     * {@inheritDoc}
     */
    public function onListing(Collection $records): Collection
    {
        return $records->map(function ($record) {
            $resource = static::RESOURCE;
            return new $resource((object) $record);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function get(int $id)
    {
        $model = static::MODEL;
        $resource = static::RESOURCE;
        $modelObject = $model::find($id);

        if (! $modelObject) return null;

        return new $resource($modelObject);
    }

    /**
     * Get by the given column name
     * 
     * @param  string $column
     * @param  mixed value
     * @return mixed
     */
    public function getBy($column, $value) 
    {
        $model = static::MODEL;
        $resource = static::RESOURCE;

        $object = $model::where($column, $value)->first();

        return $object ? new $resource($object) : null;
    }
    
    /**
     * Wrap the given model to its resource
     * 
     * @param \Model $model
     * @return \JsonResource
     */
    public function wrap($model) 
    {
        $resource = static::RESOURCE;
        return new $resource($model);
    }


    /**
     * Get the query handler
     * 
     * @return mixed
     */
    protected function getQuery()
    {
        if (static::MODEL) {
            $model = static::MODEL;
            return $model::table();
        } else {
            return DB::table($this->tableName);
        }
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
    protected function filter() {}

    /**
     * Manage Selected Columns
     *
     * @return void
     */
    protected function select() {}

    /**
     * Perform records ordering
     * 
     * @return void
     */
    protected function orderBy(array $orderBy)
    {
        if (empty($orderBy)) {
            $orderBy = [$this->column('id'), 'desc'];
        }

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
        return $records;
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
    public function create(Request $request)
    {
        $modelName = static::MODEL;

        $model = new $modelName;

        if (! empty(static::DATA)) {
            foreach (static::DATA as $column) {
                if ($column == 'password') {
                    if ($request->password) {                        
                        $model->password = bcrypt($request->password);
                    }

                    continue;
                }
        
                $model->$column = $request->$column;
            }
        }

        $this->setData($model, $request);

        $model->save();

        $this->events->trigger("{$this->eventName}.save {$this->eventName}.create", $model, $request);

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, Request $request)
    {
        $model = (static::MODEL)::find($id);

        $oldModel = clone $model;

        if (static::DATA) {
            foreach (static::DATA as $column) {
                if ($column == 'password') {
                    if ($request->password) {                        
                        $model->password = bcrypt($request->password);
                    }

                    continue;
                }
                $model->$column = $request->$column;
            }
        }

        $this->setData($model, $request);

        $model->save();
        
        $this->events->trigger("{$this->eventName}.save {$this->eventName}.update", $model, $request, $oldModel);

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
    protected function setData($model, Request $request) {}

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
                $model->key = $this->request->$value;
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
    public function delete(int $id): bool
    {
        $modelName = static::MODEL;
        
        $model = (static::MODEL)::find($id);

        if (! $model) return false;

        if ($this->events->trigger("{$this->eventName}.before-deleting", $model, $id) === false) return false;

        $model->delete();

        $this->events->trigger("{$this->eventName}.delete", $model, $id);

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

        // return $this->query->$method(...$args);
        
        return $this->marcoableMethods($method, $args);
    }
}