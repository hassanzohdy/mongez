<?php
namespace HZ\Illuminate\Mongez\Managers\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use HZ\Illuminate\Mongez\Contracts\Providers\ModuleServiceProviderInterface;

abstract class ModuleServiceProvider extends ServiceProvider implements ModuleServiceProviderInterface
{    
    /**
     * {@inheritDoc}
     */    
    public function boot()
    {
        $this->map();
    }

    /**
     * {@inheritDoc}
     */
    public function map()
    {
        if ($this->routesAreCached()) {
            $this->loadCachedRoutes();
        } else {
            $this->mapApiRoutes();

            $this->app->booted(function () {
                $this->app['router']->getRoutes()->refreshNameLookups();
                $this->app['router']->getRoutes()->refreshActionLookups();
            });
        }
    }

    /**
     * {@inheritDoc}
     */    
    public function mapApiRoutes()
    {
        foreach (static::ROUTES_TYPES as $routeType) {
            $prefix = $routeType == 'admin' ? '/admin' : '';

            $routeFilePath = 'routes/' .$routeType .'.php';
            $routeFilePath = $this->namespace .'/' .$routeFilePath;
            Route::prefix('api' . $prefix)
                ->middleware('api')
                ->namespace('App')
                ->group(base_path($routeFilePath));
        }
    }

    /**
     * Determine if the application routes are cached.
     *
     * @return bool
     */
    protected function routesAreCached()
    {
        return $this->app->routesAreCached();
    }
    
    /**
     * Load the cached routes for the application.
     *
     * @return void
     */
    protected function loadCachedRoutes()
    {
        $this->app->booted(function () {
            require $this->app->getCachedRoutesPath();
        });
    }
}
