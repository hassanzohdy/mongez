<?php
namespace HZ\Laravel\Organizer\App\Managers\Database\MongoDB;

use DB;
use Illuminate\Support\Collection;
use HZ\Laravel\Organizer\App\Contracts\Repositories\RepositoryInterface;
use HZ\Laravel\Organizer\App\Managers\Database\MYSQL\RepositoryManager as BaseRepositoryManager;

abstract class RepositoryManager extends BaseRepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const RESOURCE = '';

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
        $model = static::MODEL;
        $resource = static::RESOURCE;
        return new $resource($model::find($id));
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