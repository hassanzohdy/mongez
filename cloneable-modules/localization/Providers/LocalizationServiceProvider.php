<?php

namespace App\Modules\Localization\Providers;

use HZ\Illuminate\Mongez\Providers\ModuleServiceProvider;

class LocalizationServiceProvider extends ModuleServiceProvider
{
    /**
     * {@inheritDoc}
     */
    const ROUTES_TYPES = ["admin", "site"];

    /**
     * {@inheritDoc}
     */
    protected $namespace = 'App/Modules/Localization/';
}
