<?php

namespace App\Repositories\Users;

use Str;
use Request;
use RepositoryManager;
use App\Models\User\User;
use App\Traits\Auth\AccessToken;
use App\Http\Resources\Users\User as Resource;
use HZ\Laravel\Organizer\Contracts\Repositories\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    use AccessToken;

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