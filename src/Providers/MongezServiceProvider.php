<?php

namespace HZ\Illuminate\Mongez\Providers;

use File;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
     * Commands list of the package
     *
     * @const array
     */
    const COMMANDS_LIST = [
        EngezModel::class,
        EngezMigrate::class,
        ModuleBuilder::class,
        EngezResource::class,
        DatabaseMaker::class,
        EngezMigration::class,
        EngezController::class,
        EngezRepository::class,
        CloneModuleBuilder::class,
    ];

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
        if (!$this->app->runningInConsole()) {
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                die(json_encode([
                    'success' => true,
                    'mongez' => true,
                ]));
            }

            return;
        }

        // register commands
        $this->commands(static::COMMANDS_LIST);

        // Initialize Mongez 
        Mongez::init();

        if (!Mongez::isInstalled()) {
            $this->prepareForFirstTime();
        }
    }

    /**
     * Prepare the package for first time installation
     * 
     * @return void
     */
    private function prepareForFirstTime()
    {
        $this->addingCommentToAppConfig();

        Mongez::install();

        $database = config('database.default');

        if ($database != 'mongodb') return;

        $path = Mongez::packagePath('src/Database/migrations/mongodb');

        Artisan::call('migrate', ['--path' => $path]);
    }

    /**
     * Add line replacementLine to app/config file.
     * 
     * @return void 
     */
    private function addingCommentToAppConfig()
    {
        $config = File::get(base_path($configPath = 'config/app.php'));

        $searchString = '// Auto generated providers here: DO NOT remove this line.';

        if (Str::contains($config, $searchString)) return;
        $replacedString = "App\Providers\RouteServiceProvider::class,\n\n\t\t/** \n\t\t * Modules Service Providers...\n\t\t */\n\t\t$searchString\n";
        $updatedConfig = str_ireplace("App\Providers\RouteServiceProvider::class,", $replacedString, $config);

        File::put($configPath, $updatedConfig);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([$this->configPath() => config_path('mongez.php')]);

        // beta database
        $request = request();

        if ($betaDBName = $request->server('HTTP_BETA')) {
            $defaultDatabaseDriver = config('database.default');
            $dbConfigName = 'database.connections.' . $defaultDatabaseDriver . '.database';

            if ($betaDBName === 'true') {
                $betaDBName = 'BETA';
            }

            $betaDatabase = env("DB_DATABASE_$betaDBName");

            config([
                $dbConfigName => $betaDatabase,
            ]);
        }

        $this->config = config('mongez');

        // register the repositories as singletones, only one instance in the entire application
        foreach ($this->config('repositories', []) as $repositoryClass) {
            $this->app->singleton($repositoryClass);
        }

        $request = request();

        if ($LocaleCode = $request->server('HTTP_LOCALE')) {
            $request->request->set('locale', $LocaleCode);
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
     * Get config value from the mongez config file
     * 
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    private function config(string $key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
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

        foreach ($this->config('events', []) as $eventName => $eventListeners) {
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
        foreach ($this->config('macros', []) as $original => $mixin) {
            $mixinObject = new $mixin;
            $original::mixin($mixinObject);

            // if the original class is the query builder
            // then we will inject same macro in the eloquent builder
            if ($original == QueryBuilder::class) {
                foreach (get_class_methods($mixinObject) as $method) {
                    $callback = $mixinObject->$method();
                    // EloquentBuilder::macro($method, Closure::bind($callback, null, EloquentBuilder::class));
                    EloquentBuilder::macro($method, $callback);
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
