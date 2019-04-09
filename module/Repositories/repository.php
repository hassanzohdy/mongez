<?php
namespace App\Modules\ModuleName\Repositories;

use App\Modules\ModuleName\Models\ModelName as Model;
use App\Modules\ModuleName\Resources\ResourceName as Resource;
use HZ\Illuminate\Organizer\Contracts\Repositories\RepositoryInterface;
use HZ\Illuminate\Organizer\Managers\Database\DatabaseName\RepositoryManager;

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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    const FILTER_BY = [];
}