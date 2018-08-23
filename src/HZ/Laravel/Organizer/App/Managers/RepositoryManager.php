<?php
namespace HZ\Laravel\Organizer\App\Managers;

use DB;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use HZ\Laravel\Organizer\App\Traits\RepositoryTrait;
use HZ\Laravel\Organizer\App\Helpers\Repository\Select;
use HZ\Laravel\Organizer\App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class RepositoryManager implements RepositoryInterface
{
    /**
     * We're injecting the repository trait as it will be used 
     * for quick access to other repositories
     */
    use RepositoryTrait;

    /**
     * Model name
     * 
     * @const string
     */
    const MODEL = '';

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
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Select Helper Object
     *
     * @var \HZ\Laravel\Organizer\App\Helpers\Repository\Select
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

        $this->filter();

        if ($this->select->isNotEmpty()) {
            $this->query->select(...$this->select->list());
        }
        
        $this->orderBy(array_filter((array) $this->option('orderBy')));
        
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
    protected function orderBy(array $orderBy)
    {
        if (empty($orderBy)) {
            $orderBy = [$this->column('id'), 'DESC'];
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
    private function setOptions(array $options): void
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
    public function create(Request $request): Model
    {
        $modelName = static::MODEL;

        $model = new $modelName;

        $this->setData($model, $request);

        $model->save();

        $this->onCreate($model, $request);
        
        $this->onSave($model, $request);

        return $model;
    }

    /**
     * This method will be triggered after creating new record in database
     *
     * @parm   \Illuminate\Database\Eloquent\Model $model
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function onCreate($model, Request $request) {}

    /**
     * {@inheritDoc}
     */
    public function update(int $id, Request $request): Model
    {
        $model = (static::MODEL)::find($id);

        $this->setData($model, $request);

        $model->save();

        $this->onUpdate($model, $request);

        $this->onSave($model, $request);

        return $model;
    }

    /**
     * This method will be triggered after record is updated 
     *
     * @parm   \Illuminate\Database\Eloquent\Model $model
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function onUpdate($model, Request $request) {}

    /**
     * This method will be triggered after creating or updating
     *
     * @parm   \Illuminate\Database\Eloquent\Model $model
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function onSave($model, Request $request) {}

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
    protected function updateModel($model, array $columns)
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
     * {@inheritDoc}
     */
    public function delete($id): bool
    {
        $model = (static::MODEL)::find($id);

        if (! $model) return false;

        $this->beforeDeleting($model);

        $model->delete();

        $this->onDelete($model, $id);

        return true;
    }

    /**
     * This method is triggered before deleting the model
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    protected function beforeDeleting($model) {}

    /**
     * This method will be triggered after item deleted
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param  int $id
     * @return void
     */
    protected function onDelete($model, int $id) {}

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