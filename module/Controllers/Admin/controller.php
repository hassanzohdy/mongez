<?php
namespace App\ModuleName\Controllers\Admin;

use HZ\Illuminate\Organizer\Managers\AdminApiController; 

class ControllerName extends AdminApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'repo-name',
        'listOptions' => [
            'select' => [],
        ],
        'rules' => [
            'all' => [],
            'store' => [],
            'update' => [],
        ],
    ];
}