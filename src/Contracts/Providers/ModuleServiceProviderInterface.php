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
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    public function mapApiRoutes();
}