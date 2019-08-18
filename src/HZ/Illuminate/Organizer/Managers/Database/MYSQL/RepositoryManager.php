<?php
namespace HZ\Illuminate\Organizer\Managers\Database\MYSQL;

use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use HZ\Illuminate\Organizer\Events\Events;
use HZ\Illuminate\Organizer\Traits\RepositoryTrait;
use HZ\Illuminate\Organizer\Helpers\Repository\Select;
use HZ\Illuminate\Organizer\Contracts\Repositories\RepositoryInterface;

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
     * Filter by columns
     * 
     * @const array
     */
    const FILTER_BY = [];

    /**
     * Auto fill the following columns directly from the request
     * 
     * @const var
     */
    const DATA = [];

    /**
     * Auto fill the following columns as arrays directly from the request
     * 
     * @const var
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
     * @var \HZ\Illuminate\Organizer\Helpers\Repository\Select
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
                $eventNameModelBased = basename(str_replace('\\', '/', static::MODEL));
 
                $this->eventName = strtolower($eventNameModelBased);

                $this->eventName = Str::plural($this->eventName);                       
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
                $deletedAtColumn = $this->table . '.' . static::DELETED_AT;
    
                $this->query->whereNull($deletedAtColumn);    
            } elseif ($retrieveMode == static::RETRIEVE_DELETED_RECORDS) {
                $deletedAtColumn = $this->table . '.' . static::DELETED_AT;
    
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

        if ($this->select->isNotEmpty()) {
            $this->query->select(...$this->select->list());
        }
        
        $this->orderBy($this->option('orderBy', [$this->column('id'), 'DESC']));
        
        $this->events->trigger("{$this->eventName}.listing", $this->query, $this);

        $records = $this->query->get();

        $records = $this->records($records);

        $results = $this->events->trigger("{$this->eventName}.list", $records);

        if ($results instanceof Collection) {
            $records = $results;
        }    

        return $records;
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
     * @param   array|null $orderBy
     * @return  void
     */
    protected function orderBy($orderBy)
    {
        if (! $orderBy) return;
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
            if (! empty(static::ARRAYBLE_DATA)) {
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
    public function create(Request $request)
    {
        $modelName = static::MODEL;

        $model = new $modelName;

        $this->setAutoData($model, $request);

        $this->setData($model, $request);

        $this->events->trigger("{$this->eventName}.saving {$this->eventName}.creating", $model, $request);

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

        $this->setAutoData($model, $request);

        $this->setData($model, $request);

        $this->events->trigger("{$this->eventName}.saving {$this->eventName}.updating", $model, $request, $oldModel);

        $model->save();
        
        $this->events->trigger("{$this->eventName}.save {$this->eventName}.update", $model, $request, $oldModel);

        return $model;
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
        foreach (static::DATA as $column) {
            if ($column == 'password') {
                $password = $request->password;
                if ($password) {
                    $model->password = bcrypt($password);
                }
            } else {
                $model->$column = $request->$column;
            }
        }

        foreach (static::ARRAYBLE_DATA as $column) {
            $value = array_filter((array) $request->$column);
            $value = $this->handleArrayableValue($value);
            $model->$column = $value;
        }

        foreach (static::UPLOADS as $column => $name) {
            if (is_numeric($column)) {
                $column = $name;
            }

            if ($request->$name) {
                $model->$column = $request->$name->store(static::NAME);
            }
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
        $model = (static::MODEL)::find($id);

        if (! $model) return false;

        if ($this->events->trigger("{$this->eventName}.deleting", $model, $id) === false) return false;

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
}