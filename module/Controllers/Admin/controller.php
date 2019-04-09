<?php
namespace App\ModuleName\Controllers\Admin;

class ControllerName extends \AdminApiController
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