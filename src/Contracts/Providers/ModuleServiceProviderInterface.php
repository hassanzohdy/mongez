<?php
namespace HZ\Illuminate\Mongez\Contracts\Providers;

interface ModuleServiceProviderInterface
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot();
    
    /**
     * Define the routes for the module.
     *
     * @return void
     */
    public function mapRoutes();
}