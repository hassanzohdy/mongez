<?php
namespace App\Modules\Users\Repositories;

use App\Modules\Users\{
    Models\User,
    Models\UserGroup,
    Traits\Auth\AccessToken,
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
    const RESOURCE = Resource::CLASS;

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
     * Set the columns will be filled with single record of collection data
     * i.e [country => CountryModel::class]
     * 
     * @const array
     */
    const DOCUMENT_DATA = [
        'group' => UserGroup::class
    ];

    /**
     * Add the column if and only if the value is passed in the request.
     * 
     * @cont array  
     */
    const WHEN_AVAILABLE_DATA = ['name' , 'email','password'];

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
     * Do any extra filtration here
     * 
     * @return  void
     */
    protected function filter() 
    {
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
     * @param  UserGroup $usersGroup
     * @return void  
     */
    public function updateUserGroup(UserGroup $usersGroup)
    {
        User::where('group.id', $usersGroup->id)->update([
            'group' => $usersGroup->sharedInfo(),
        ]);
    }
}