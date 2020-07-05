<?php
namespace App\Modules\Settings\Filters;

use HZ\Illuminate\Mongez\Helpers\Filters\mysql\Filter;

class Setting extends Filter
{
    /**
     * List with all filter. 
     *
     * Setting => functionName
     * @const array 
     */
    const FILTER_MAP = [];    
}