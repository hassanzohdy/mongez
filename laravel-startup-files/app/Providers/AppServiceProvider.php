<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // register macros
        $this->registerMacros();

        $this->manageDatabase();
    }

    /**
     * Manage database options
     */
    public function manageDatabase()
    {
        $defaultLength = config('database.connections.mysql.defaultStringLength');

        if ($defaultLength) {
            Schema::defaultStringLength($defaultLength);
        }
    }

    /**
     * Register all macros
     * 
     * @return void 
     */
    protected function registerMacros()
    {
        $macros = (array) config('app.macros');

        foreach ($macros as $original => $mixin) {
            $original::mixin(new $mixin);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
