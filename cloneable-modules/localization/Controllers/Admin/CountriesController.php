<?php

namespace App\Modules\Localization\Controllers\Admin;

use use HZ\Illuminate\Mongez\Http\RestfulApiController;;

class CountriesController extends RestfulApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'countries',
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
