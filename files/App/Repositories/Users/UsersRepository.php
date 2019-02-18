<?php

namespace App\Repositories\Users;

use Str;
use RepositoryManager;
use Illuminate\Http\Request;
use App\Traits\Auth\AccessToken;
use App\Models\User\User as Model;
use App\Http\Resources\Users\User as Resource;
use HZ\Laravel\Organizer\Contracts\Repositories\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    use AccessToken;

    /**
     * {@inheritDoc}
     */
    const MODEL = Model::class;

    /**
     * {@inheritDoc}
     */
    const RESOURCE = Resource::CLASS;
    
    /**
     * {@inheritDoc}
     */
    const DATA = ['name', 'email', 'mobile', 'password'];

    /**
     * {@inheritDoc}
     */
    const FILTER_BY = ['email', 'status'];

    /**
     * {@inheritDoc}
     */
    public function onCreate($user, Request $request)
    {
        $this->generateAccessToken($user, $request);
    }
}