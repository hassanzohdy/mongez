<?php
namespace App\Modules\ModuleName\Providers;

use HZ\Illuminate\Mongez\Managers\Providers\ModuleServiceProvider;

class ClassNameServiceProvider extends ModuleServiceProvider
{
    /**
     * List of routes files
     * 
     * @const array
     */
    const ROUTES_TYPES = ROUTES_LIST;

    /**
     * Module build type
     * 
     * @const strong
     */
    const BUILD_MODE = 'BUILD_MODE_VALUE';

    /**
     * Views Name
     * 
     * @const strong
     */
    const VIEWS_NAME = 'VIEWS_NAME_VALUE';
    
    /**
     * {@inheritDoc}
     */    
    protected $namespace = 'App/Modules/ModuleName/';
}
