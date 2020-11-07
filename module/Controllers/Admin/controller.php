<?php
namespace App\Modules\ModuleName\Controllers\Admin;

use HZ\Illuminate\Mongez\Managers\BaseController; 

class ControllerName extends BaseController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        VIEW
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