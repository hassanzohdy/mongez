<?php

namespace App\Modules\NewsLetters\Controllers\Site;

use use HZ\Illuminate\Mongez\Http\RestfulApiController;;

class ContactUsController extends RestfulApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'contactUse',
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
