<?php
namespace App\Modules\Settings\Providers;

use HZ\Illuminate\Mongez\Managers\Providers\ModuleServiceProvider;

class SettingServiceProvider extends ModuleServiceProvider
{
    /**
     * {@inheritDoc}
     */
    const ROUTES_TYPES = ["admin","site"];
    
    /**
     * {@inheritDoc}
     */    
    protected $namespace = 'App/Modules/Settings/';
}
