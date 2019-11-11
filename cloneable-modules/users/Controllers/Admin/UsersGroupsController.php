<?php
namespace App\Modules\Users\Controllers\Admin;

use HZ\Illuminate\Mongez\Managers\AdminApiController; 

class UsersGroupsController extends AdminApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'usersGroups',
        'listOptions' => [
            'select' => ['id','name','permissions'],
            'filterBy' => [],
            'paginate' => null, // if set null, it will be automated based on repository configuration option
        ],
        'rules' => [
            'all' => [],
            'store' => [],
            'update' => [],
        ],
    ];
}