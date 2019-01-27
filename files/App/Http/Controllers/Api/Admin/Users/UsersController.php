<?php
namespace App\Http\Controllers\Api\Admin\Users;

class UsersController extends \AdminApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'users',
        'listOptions' => [
            'select' => ['id', 'name', 'email'],
        ],
    ];
}
