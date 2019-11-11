<?php

namespace App\Modules\Users\Repositories;

use Illuminate\Http\Request;
use App\Modules\Users\{
    Models\User,
    Traits\Auth\AccessToken,
    Resources\User as Resource
};

use HZ\Illuminate\Mongez\{
    Contracts\Repositories\RepositoryInterface,
    Managers\Database\MYSQL\RepositoryManager
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
    const DATA = ['name', 'email','password','user_group_id'];

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
     * Manage Selected Columns
     *
     * @return void
     */
    protected function select()
    {
        //
    }

    
    /**
     * Do any extra filtration here
     * 
     * @return  void
     */
    protected function filter() 
    {
        // 
    }

    /**
     * Get a specific record with full details
     * 
     * @param  int id
     * @return mixed
     */
    public function get(int $id) 
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function onCreate($user, $request)
    {
        $this->generateAccessToken($user, $request);
    }
}
