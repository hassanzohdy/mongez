<?php
namespace HZ\Illuminate\Mongez\Managers\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use HZ\Illuminate\Mongez\Contracts\Providers\ModuleServiceProviderInterface;

abstract class ModuleServiceProvider extends ServiceProvider implements ModuleServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */    
    public function boot()
    {
        $this->mapApiRoutes();
    }

    /**
     * {@inheritDoc}
     */    
    public function mapApiRoutes()
    {
        foreach (static::ROUTES_TYPES as $routeType) {
            $routeFilePath = 'routes/' .$routeType .'.php';
            $routeFilePath = $this->namespace .'/' .$routeFilePath;

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path($routeFilePath));
        }
    }
}
