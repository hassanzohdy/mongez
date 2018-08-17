<?php

namespace HZ\Laravel\Organizer\App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class OrganizerServiceProvider extends ServiceProvider
{
    /**
     * Startup config
     * 
     * @var array
     */
    protected $config = [];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->config = config('organizer');
        
        // register aliases
        $this->registerAliases();

        // register macros
        $this->registerMacros();

        $this->manageDatabase();
    }

    /**
     * Register new aliases to the application
     * 
     * @return array
     */
    protected function registerAliases()
    {
        $aliases = (array) $this->config['aliases'];
        
        foreach ($aliases as $alias => $original) {
            class_alias($original, $alias);
        }
    }

    /**
     * Manage database options
     */
    public function manageDatabase()
    {
        $defaultLength = Arr::get($this->config, 'database.mysql.defaultStringLength');

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
        $macros = (array) $this->config['macros'];

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
