<?php
namespace App\Modules\Users\Controllers\Admin;

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
