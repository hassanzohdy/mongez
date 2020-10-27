<?php

namespace App\Modules\Users\Repositories;

use App\Modules\Users\{
    Models\User,
    Traits\Auth\AccessToken,
    Models\UserGroup,
    Resources\User as Resource
};
use HZ\Illuminate\Mongez\{
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
    const RESOURCE = Resource::class;

    /**
     * {@inheritDoc}
     */
    const DATA = ['name', 'email','password'];

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
     * Set the columns will be filled with single record of collection data
     * i.e [country => CountryModel::class]
     * 
     * @const array
     */
    const DOCUMENT_DATA = [
        'group' => UserGroup::class,
    ];

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
    public function onCreate($user, $request)
    {
        $this->generateAccessToken($user, $request);
    }
    /**
     * Update all users that matches the given group
     * 
     * @param  Group $usersGroup
     * @return void  
     */
    public function updateUserGroup(UserGroup $usersGroup)
    {
        User::where('group.id', $usersGroup->id)->update([
            'group' => $usersGroup->sharedInfo(),
        ]);

        // check if current user is in the same group, then update its group as well
        $user = user();

        if ($user->group['id'] == $usersGroup->id) {
            $user->group = $usersGroup;
        }
    }
}
