<?php

namespace App\Modules\Users\Controllers\Admin;

use use HZ\Illuminate\Mongez\Http\RestfulApiController;;

class UsersGroupsController extends RestfulApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'usersGroups',
        'listOptions' => [
            'select' => ['id', 'name', 'permissions'],
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
