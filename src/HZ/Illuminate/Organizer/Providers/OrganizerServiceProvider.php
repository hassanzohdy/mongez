<?php
namespace HZ\Illuminate\Organizer\Providers;

use File;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use HZ\Illuminate\Organizer\Events\Events;
use HZ\Illuminate\Organizer\Helpers\Mongez;
use Illuminate\Database\Query\Builder as QueryBuilder;
use HZ\Illuminate\Organizer\Console\Commands\MongezSeeder;
use HZ\Illuminate\Organizer\Console\Commands\DatabaseMaker;
use HZ\Illuminate\Organizer\Console\Commands\ModuleBuilder;
use HZ\Illuminate\Organizer\Console\Commands\MongezMigrate;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use HZ\Illuminate\Organizer\Console\Commands\MongezMigration;
use HZ\Illuminate\Organizer\Console\Commands\CloneModuleBuilder;

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
        $this->publishes([$this->configPath() => config_path('organizer.php')]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MongezSeeder::class,
                DatabaseMaker::class,
                ModuleBuilder::class,
                MongezMigrate::class,
                MongezMigration::class,
                CloneModuleBuilder::class,
            ]);

            Mongez::init();
            
            $database = config('database.default');
        
            if ($database != 'mongodb') return;
            
            if (Mongez::getStored('installed') === null) {
                $path = dirname(__DIR__, 1);
                $path .= '\Database\migrations\mongodb';
                Mongez::setStored('installed', true);
                Mongez::updateStorageFile('installed', true);
                \Artisan::call('migrate', ['--path' => $path]);
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
        $this->config = config('organizer');

        // register the repositories as singletones, only one instance in the entire application
        foreach ((array) config('organizer.repositories') as $repositoryClass) {
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
        return __DIR__ . '/../../../../../files/config/organizer.php';
    }

    /**
     * Register the events listeners
     *
     * @return void
     */
    protected function registerEventsListeners()
    {
        $events = $this->app->make(Events::class);

        foreach ((array) config('organizer.events') as $eventName => $eventListeners) {
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
