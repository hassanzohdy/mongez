<?php
namespace App\Modules\NewsLetters\Providers;

use HZ\Illuminate\Mongez\Managers\Providers\ModuleServiceProvider;

class NewsLetterServiceProvider extends ModuleServiceProvider
{
    /**
     * {@inheritDoc}
     */
    const ROUTES_TYPES = ["admin","site"];
    
    /**
     * {@inheritDoc}
     */    
    protected $namespace = 'App/Modules/NewsLetters/';
}
