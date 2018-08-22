<?php

namespace HZ\Laravel\Organizer\App\Providers;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

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
        
        // register macros
        $this->registerMacros();

        // manage database options
        $this->manageDatabase();
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
            $mixinObject = new $mixin;
            $original::mixin($mixinObject);

            // if the original class is the query builder
            // then we will inject same macro in the eloquent builder
            if ($original == QueryBuilder::class) {
                foreach (get_class_methods($mixinObject) as $method) {
                    $callback = $mixinObject->$method();
                    EloquentBuilder::macro($method, Closure::bind($callback, null, EloquentBuilder::class));
                }
            }
        }
    }

    /**
     * Manage database options
     * 
     * @return void
     */
    public function manageDatabase()
    {
        $defaultLength = Arr::get($this->config, 'database.mysql.defaultStringLength');

        if ($defaultLength) {
            Schema::defaultStringLength($defaultLength);
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
