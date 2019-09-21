<?php
namespace App\Modules\ModuleName\Controllers\Admin;

use HZ\Illuminate\Mongez\Managers\AdminApiController; 

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
            'paginate' => null, // if set null, it will be automated based on repository configuration option
        ],
        'rules' => [
            'all' => [],
            'store' => [],
            'update' => [],
        ],
    ];
}