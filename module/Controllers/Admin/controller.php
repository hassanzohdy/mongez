<?php
namespace App\Modules\ModuleName\Controllers\Admin;

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
            'filterBy' => [],
            'paginate' => null, // if set null, it will be depend on repository paginate constant
        ],
        'rules' => [
            'all' => [],
            'store' => [],
            'update' => [],
        ],
    ];
}