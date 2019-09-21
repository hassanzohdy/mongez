<?php
namespace HZ\Illuminate\Mongez\Providers;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use HZ\Illuminate\Mongez\Events\Events;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use Illuminate\Database\Query\Builder as QueryBuilder;
use HZ\Illuminate\Mongez\Console\Commands\EngezModel;
use HZ\Illuminate\Mongez\Console\Commands\EngezMigrate;
use HZ\Illuminate\Mongez\Console\Commands\DatabaseMaker;
use HZ\Illuminate\Mongez\Console\Commands\ModuleBuilder;
use HZ\Illuminate\Mongez\Console\Commands\EngezResource;
use HZ\Illuminate\Mongez\Console\Commands\EngezMigration;
use HZ\Illuminate\Mongez\Console\Commands\EngezController;
use HZ\Illuminate\Mongez\Console\Commands\EngezRepository;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use HZ\Illuminate\Mongez\Console\Commands\CloneModuleBuilder;

class MongezServiceProvider extends ServiceProvider
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
        if ($this->app->runningInConsole()) {
            $this->commands([
                EngezModel::class,
                EngezMigrate::class,
                ModuleBuilder::class,
                EngezResource::class, 
                DatabaseMaker::class,
                EngezMigration::class,
                EngezController::class,
                EngezRepository::class,
                CloneModuleBuilder::class,
            ]);

            Mongez::init();
            
            $database = config('database.default');
        
            if ($database != 'mongodb') return;
            
            if (Mongez::getStored('installed') === null) {
                $path = Mongez::packagePath('src\HZ\Illuminate\Mongez\Database\migrations\mongodb');
                Mongez::setStored('installed', true);
                Mongez::updateStorageFile('installed', true);
                Artisan::call('migrate', ['--path' => $path]);
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([$this->configPath() => config_path('mongez.php')]);

        $this->config = config('mongez');

        // register the repositories as singletones, only one instance in the entire application
        foreach ((array) config('mongez.repositories') as $repositoryClass) {
            $this->app->singleton($repositoryClass);
        }

        $this->app->singleton(Events::class);

        // register macros
        $this->registerMacros();

        $this->registerEventsListeners();

        if (strtolower(config('database.driver')) === 'mysql') {
            // manage database options
            $this->manageDatabase();
        }
    }

    /**
     * Get config path
     *
     * @return string
     */
    protected function configPath(): string
    {
        return Mongez::packagePath('files/config/mongez.php');
    }

    /**
     * Register the events listeners
     *
     * @return void
     */
    protected function registerEventsListeners()
    {
        $events = $this->app->make(Events::class);

        foreach ((array) config('mongez.events') as $eventName => $eventListeners) {
            $eventListeners = (array) $eventListeners;
            foreach ($eventListeners as $eventListener) {
                $events->subscribe($eventName, $eventListener);
            }
        }
    }

    /**
     * Register all macros
     *
     * @return void
     */
    protected function registerMacros()
    {
        if (empty($this->config['macros'])) {
            return;
        }

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
}
