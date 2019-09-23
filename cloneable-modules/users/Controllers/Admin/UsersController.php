<?php
namespace App\Modules\Users\Controllers\Admin;

use HZ\Illuminate\Organizer\Managers\AdminApiController;

class UsersController extends AdminApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'users',
        'listOptions' => [],
    ];
}
