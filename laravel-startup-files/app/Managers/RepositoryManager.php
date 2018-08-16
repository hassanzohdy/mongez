<?php
namespace App\Managers;

use DB;
use Arr;
use Auth;
use Model;
use Request;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Collection;
use App\Helpers\Repository\Select;

abstract class RepositoryManager
{
    /**
     * We're injecting the repository trait as it will be used 
     * for quick access to other repositories
     */
    use RepositoryTrait;

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
     * Retrieval mode flag
     * 
     * @const string
     */
    const RETRIEVAL_MODE_KEYWORD = 'retrievalMode';

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
     * This property will has the final table name that will be used
     * i.e if the TABLE_ALIAS is not empty, then this property will be the value of the TABLE_ALIAS
     * otherwise it will be the value of the TABLE constant
     *
     * @const string
     */
    protected $table;

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
     * @var \Request
     */
    protected $request;

    /**
     * Select Helper Object
     *
     * @var \App\Helpers\Repository\Select
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
     * @param \Request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->user = user();
    }

    /**
     * {@inheritDoc}
     */
    public function has(int $id): bool
    {
        return (bool) DB::table(static::TABLE)->where('id', $id)->first();
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

        $table = static::TABLE_ALIAS ? static::TABLE . ' as ' . static::TABLE_ALIAS : static::TABLE;

        $this->query = DB::table($table);

        $this->table = static::TABLE_ALIAS ?: static::TABLE;

        $this->select();

        $retrieveModel = $this->option(static::RETRIEVAL_MODE_KEYWORD, static::DEFAULT_RETRIEVAL_MODE);

        if ($retrieveModel == static::RETRIEVE_ACTIVE_RECORDS) {
            $deletedAtColumn = $this->table . '.' . static::DELETED_AT;

            $this->query->whereNull($deletedAtColumn);    
        }

        $this->filter();

        if ($this->select->isNotEmpty()) {
            $this->query->select(...$this->select->list());
        }
        
        $this->orderBy();
        
        $records = $this->query->get();

        $records = $this->records($records);

        return $records;
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
     * @return void
     */
    abstract protected function orderBy();
    
    /**
     * Adjust records that were fetched from database
     *
     * @param \Collection
     * @return \Collection
     */
    protected function records(Collection $records): Collection
    {
        return $records;
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
     * @return void
     */
    protected function option(string $key, $default = null)
    {
        return Arr::get($this->options, $key, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function create(Request $request): Model
    {
        $model = new $this->model;

        $this->setData($model, $request);

        $model->save();

        $this->onCreate($model, $request);

        return $model;
    }

    /**
     * This is a direct insertion to the database 
     * It means that the data will be totally sent as an argument
     * 
     * @param  array $data
     * @return \Model
     */
    public function insert(array $data): Model
    {
        $model = new $this->model;

        $this->updateModel($model, $data);

        $model->save();

        return $model;    
    }

    /**
     * This is a direct update to the database 
     * It means that the data will be totally sent as an argument
     * 
     * @param  int $id
     * @param  array $data
     * @return \Model|null
     */
    public function edit(int $id, array $data):? Model
    {
        $model = $this->model::find($id);

        if (! $model) return null;

        $this->updateModel($model, $data);

        $model->save();

        return $model;    
    }

    /**
     * Update record for the given model
     *
     * @param  \Model $model
     * @param  array $columns
     * @return void
     */
    protected function updateModel(Model $model, array $columns)
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
     * This method will be triggered after item create
     *
     * @parm   \Model $model
     * @param  \Request $request
     * @return void
     */
    protected function onCreate(Model $model, Request $request) {}

    /**
     * {@inheritDoc}
     */
    public function update(int $id, Request $request): Model
    {
        $model = $this->model::find($id);

        $this->setData($model, $request);

        $model->save();

        $this->onUpdate($model, $request);

        return $model;
    }

    /**
     * This method will be triggered after item create
     *
     * @parm   \Model $model
     * @param  Request $request
     * @return void
     */
    protected function onUpdate(Model $model, Request $request) {}

    /**
     * Set data to the model
     * This method is triggered on create and update as it will be a useful 
     * method to set model data once instead of adding it on create and adding it again on update
     * 
     * In simple words, add common fields between create and update using this method
     * 
     * @parm   \Model $model
     * @param  Request $request
     * @return void
     */
    abstract protected function setData(Model $model, Request $request);

    /**
     * {@inheritDoc}
     */
    public function delete($id): bool
    {
        $model = $this->model::find($id);

        if (! $model) return false;

        $model->delete();

        return true;
    }

    /**
     * This method will be triggered after item deleted
     * This method works only with soft deletes
     * 
     * @param \Model $model
     * @param  int $id
     * @return void
     */
    protected function onDelete(Model $model, $id) {}

    /**
     * Call query builder methods dynamically
     * 
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->query->$method(...$args);
    }
}