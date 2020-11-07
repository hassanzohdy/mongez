<?php

namespace HZ\Illuminate\Mongez\Managers\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use HZ\Illuminate\Mongez\Contracts\Providers\ModuleServiceProviderInterface;
use ReflectionClass;

abstract class ModuleServiceProvider extends ServiceProvider implements ModuleServiceProviderInterface
{
    /**
     * List of routes files
     * 
     * @const array
     */
    const ROUTES_TYPES = ['site', 'admin'];

    /**
     * Module build type
     * 
     * @const strong
     */
    const BUILD_MODE = 'api';

    /**
     * Views Name
     * 
     * @const strong
     */
    const VIEWS_NAME = '';

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        if (static::VIEWS_NAME) {
            $classInfo = new ReflectionClass($this);
            $viewsPath = dirname($classInfo->getFileName()) . './../views';

            $this->loadViewsFrom($viewsPath, static::VIEWS_NAME);
        }

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

            $routeFilePath = 'routes/' . $routeType . '.php';
            $routeFilePath = lcfirst($this->namespace) . $routeFilePath;

            $prefix = '';
            $middleware = 'web';

            if (static::BUILD_MODE === 'api') {
                $prefix = 'api';
                $middleware = 'api';
            }

            Route::prefix($prefix . $prefix)
                ->middleware($middleware)
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
