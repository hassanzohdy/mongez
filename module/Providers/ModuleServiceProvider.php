<?php
namespace App\Modules\ModuleName\Providers;

use HZ\Illuminate\Mongez\Managers\Providers\ModuleServiceProvider;

class ClassNameServiceProvider extends ModuleServiceProvider
{
    /**
     * {@inheritDoc}
     */
    const ROUTES_TYPES = ROUTES_LIST;
    
    /**
     * {@inheritDoc}
     */    
    protected $namespace = 'App/Modules/ModuleName/';
}
