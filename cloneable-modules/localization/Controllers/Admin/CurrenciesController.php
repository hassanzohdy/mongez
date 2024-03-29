<?php

namespace App\Modules\Localization\Controllers\Admin;

use use HZ\Illuminate\Mongez\Http\RestfulApiController;;

class CurrenciesController extends RestfulApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'currencies',
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
