<?php
namespace App\Modules\ModuleName\Repositories;

use App\Modules\ModuleName\{
    Models\ModelName as Model,
    Resources\ResourceName as Resource
};

use HZ\Illuminate\Organizer\{
    Contracts\Repositories\RepositoryInterface,
    Managers\Database\MongoDB\RepositoryManager
};

class RepositoryNameRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'repo-name';
    
    /**
     * {@inheritDoc}
     */
    const MODEL = Model::class;

    /**
     * {@inheritDoc}
     */
    const RESOURCE = Resource::class;

    /**
     * Set the columns of the data that will be auto filled in the model
     * 
     * @const array
     */
    const DATA = [DATA_LIST];       
    
    /**
     * Auto save uploads in this list
     * If it's an indexed array, in that case the request key will be as database column name
     * If it's associated array, the key will be request key and the value will be the database column name 
     * 
     * @const array
     */
    const UPLOADS = [UPLOADS_LIST];       
    
    /**
     * Filter by columns from the passed options directly
     * 
     * @const array
     */
    const FILTER_BY = [];

    /**
     * Set any extra data or columns that need more customizations
     * 
     * @param   mixed $model
     * @param   \Illuminate\Http\Request $request
     * @return  void
     */
    protected function setData($model, $request) 
    {
        // 
    }
    
    /**
     * Do any extra filtrations here
     * 
     * @return  void
     */
    protected function filter() 
    {
        // 
    }
}