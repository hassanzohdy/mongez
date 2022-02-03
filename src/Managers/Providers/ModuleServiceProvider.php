<?php

namespace HZ\Illuminate\Mongez\Managers\Providers;

use ReflectionClass;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use HZ\Illuminate\Mongez\Contracts\Providers\ModuleServiceProviderInterface;

abstract class ModuleServiceProvider extends ServiceProvider implements ModuleServiceProviderInterface
{
    /**
     * Module Name
     *
     * @const strong
     */
    public const NAME = '';

    /**
     * List of routes files
     *
     * @const array
     */
    public const ROUTES_TYPES = ['site', 'admin'];

    /**
     * Module build type
     *
     * @const strong
     */
    public const BUILD_MODE = 'api';

    /**
     * Determine if the module has views
     *
     * @const string
     */
    public const VIEW_PREFIX = '';

    /**
     * Determine if the module has translations
     *
     * @const string
     */
    public const TRANSLATION_PREFIX = '';

    /**
     * Modules directory path
     * 
     * @var string
     */
    protected string $moduleBaseDirectory;

    /**
     * Namespace for old modules, now the entire class path is set in routes
     * 
     * @var string
     */
    protected $namespace = '';

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $classInfo = new ReflectionClass($this);

        $this->moduleBaseDirectory = realpath(dirname($classInfo->getFileName()) . '/./../');

        $this->mapRoutes();

        if (static::VIEW_PREFIX) {
            $this->loadViewsFrom($this->moduleBaseDirectory . '/views', static::VIEW_PREFIX);
        }

        if (static::TRANSLATION_PREFIX) {
            $this->loadTranslationsFrom($this->moduleBaseDirectory . '/lang', static::TRANSLATION_PREFIX);
        }
    }

    /**
     * Map Routes
     * 
     * @return void
     */
    public function mapRoutes()
    {
        if (!$this->app->routesAreCached()) {
            $this->mapRoutesList();
        }
    }

    /**
     * Map uncached routes
     * 
     * @return void
     */
    public function mapRoutesList()
    {
        foreach (static::ROUTES_TYPES as $routeType) {
            $appPath = $routeType == 'admin' ? '/admin' : '';

            $routeFilePath = 'routes/' . $routeType . '.php';

            $routeFilePath = $this->moduleBaseDirectory . '/' . $routeFilePath;

            $prefix = '';
            $middleware = 'web';

            if (static::BUILD_MODE === 'api') {
                $prefix = 'api';
                $middleware = 'api';
            }

            Route::prefix($prefix . $appPath)
                ->middleware($middleware)
                ->namespace($this->namespace)
                ->name($routeType . '.')
                ->group($routeFilePath);
        }
    }
}
