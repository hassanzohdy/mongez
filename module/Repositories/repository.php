<?php
namespace App\Repositories\RepositoryPath;

use RepositoryManager;
use Illuminate\Http\Request;
use App\Models\ModelPath as Model;
use App\Http\Resources\ResourcePath as Resource;
use HZ\Laravel\Organizer\Contracts\Repositories\RepositoryInterface;

class RepositoryName extends RepositoryManager implements RepositoryInterface
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
     * {@inheritDoc}
     */
    const FILTER_BY = [];
}