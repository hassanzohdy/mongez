<?php

namespace App\Modules\Users\Controllers\Admin;

use use HZ\Illuminate\Mongez\Http\RestfulApiController;;

class PermissionsController extends RestfulApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'permissions',
        'listOptions' => [
            'select' => ['id', 'name', 'route', 'key', 'type'],
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
