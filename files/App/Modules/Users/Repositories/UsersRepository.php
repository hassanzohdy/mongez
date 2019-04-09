<?php
namespace App\Repositories\Users;

use RepositoryManager;
use Illuminate\Http\Request;
use App\Modules\Users\Models\User as Model;
use App\Modules\Users\Traits\Auth\AccessToken;
use App\Modules\Users\Resources\User as Resource;
use HZ\Illuminate\OrganizerContracts\Repositories\RepositoryInterface;

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