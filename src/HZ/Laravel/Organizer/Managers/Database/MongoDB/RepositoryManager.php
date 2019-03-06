<?php
namespace HZ\Laravel\Organizer\Managers\Database\MongoDB;

use DB;
use Illuminate\Support\Collection;
use HZ\Laravel\Organizer\Contracts\Repositories\RepositoryInterface;
use HZ\Laravel\Organizer\Managers\Database\MYSQL\RepositoryManager as BaseRepositoryManager;

abstract class RepositoryManager extends BaseRepositoryManager implements RepositoryInterface
{
    /**
     * Get the table name that will be used in the query 
     * 
     * @return string
     */
    protected function tableName(): string 
    {
        return static::TABLE;
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
        return $this->getBy('id', $id);
    }
    
    /**
     * Get model for the given id
     * 
     * @param  int $id
     * @return mixed
     */
    public function getModel(int $id)
    {
        return $this->getByModel('id', $id);
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
        $resource = static::RESOURCE;

        $object = $this->getByModel($column, $value);

        return $object ? new $resource($object) : null;
    }

    /**
     * Get the current model by the given column name and value
     * 
     * @param  string $column
     * @param  mixed value
     * @return mixed
     */
    public function getByModel($column, $value) 
    {        
        $model = static::MODEL;

        return $model::where($column, $value)->first();
    }

    /**
     * {@inheritDoc}
     */
    protected function setData($model, $request) {}
    
    /**
     * {@inheritDoc}
     */
    protected function select() {} 
    
    /**
     * {@inheritDoc}
     */
    protected function filter() {}  

    /**
     * Get the query handler
     * 
     * @return mixed
     */
    protected function getQuery()
    {
        $model = static::MODEL;
        return $model::where('id', '!=', -1);
    }
    
    /**
     * Get the table name that will be used in the rest of the query like select, where...etc
     * 
     * @return string
     */
    protected function columnTableName(): string 
    {
        return static::TABLE;
    }
}