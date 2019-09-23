<?php

namespace App\Modules\Users\Repositories;

use Illuminate\Http\Request;
use App\Modules\Users\{
    Models\User,
    Traits\Auth\AccessToken,
    Resources\User as Resource
};

use HZ\Illuminate\Organizer\{
    Contracts\Repositories\RepositoryInterface,
    Managers\Database\MongoDB\RepositoryManager
};

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    use AccessToken;

    /**
     * {@inheritDoc}
     */
    const NAME = 'users';

    /**
     * {@inheritDoc}
     */
    const MODEL = User::class;

    /**
     * {@inheritDoc}
     */
    const RESOURCE = Resource::CLASS;

    /**
     * {@inheritDoc}
     */
    const DATA = ['name', 'email'];

    /**
     * Store the list here as array
     * 
     * @const array
     */
    const ARRAYBLE_DATA = [];

    /**
     * {@inheritDoc}
     */
    const UPLOADS = [];

    /**
     * {@inheritDoc}
     */
    const FILTER_BY = [];

    /**
     * {@inheritDoc}
     */
    public $deleteDependenceTables = [];

    /**
     * {@inheritDoc}
     */
    protected function setData($model, $request)
    {
        // add additional data
     }

    /**
     * {@inheritDoc}
     */
    public function onCreate($user, Request $request)
    {
        $this->generateAccessToken($user, $request);
    }
}
