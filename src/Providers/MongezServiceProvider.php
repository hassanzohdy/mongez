<?php

namespace HZ\Illuminate\Mongez\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use HZ\Illuminate\Mongez\Events\Events;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use Illuminate\Support\Facades\Validator;
use HZ\Illuminate\Mongez\Console\Commands\EngezTrait;
use HZ\Illuminate\Mongez\Console\Commands\EngezTest;
use HZ\Illuminate\Mongez\Console\Commands\EngezModel;
use HZ\Illuminate\Mongez\Console\Commands\EngezRequest;
use HZ\Illuminate\Mongez\Console\Commands\EngezSeeder;
use HZ\Illuminate\Mongez\Console\Commands\EngezFilter;
use Illuminate\Database\Query\Builder as QueryBuilder;
use HZ\Illuminate\Mongez\Console\Commands\EngezRemove;
use HZ\Illuminate\Mongez\Console\Commands\EngezMigrate;
use HZ\Illuminate\Mongez\Console\Commands\DatabaseMaker;
use HZ\Illuminate\Mongez\Console\Commands\ModuleBuilder;
use HZ\Illuminate\Mongez\Console\Commands\EngezResource;
use HZ\Illuminate\Mongez\Console\Commands\EngezMigration;
use HZ\Illuminate\Mongez\Console\Commands\EngezController;
use HZ\Illuminate\Mongez\Console\Commands\EngezRepository;
use HZ\Illuminate\Mongez\Console\Commands\EngezTranslation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use HZ\Illuminate\Mongez\Console\Commands\PostmanCollection;
use HZ\Illuminate\Mongez\Console\Commands\CloneModuleBuilder;
use HZ\Illuminate\Mongez\Console\Commands\MongezTestCommand;

class MongezServiceProvider extends ServiceProvider
{
    /**
     * Commands list of the package
     *
     * @const array
     */
    const COMMANDS_LIST = [
        EngezModel::class,
        EngezSeeder::class,
        EngezRemove::class,
        EngezMigrate::class,
        ModuleBuilder::class,
        EngezResource::class,
        DatabaseMaker::class,
        EngezFilter::class,
        EngezMigration::class,
        EngezController::class,
        EngezRepository::class,
        EngezTranslation::class,
        CloneModuleBuilder::class,
        PostmanCollection::class,
        MongezTestCommand::class,
        EngezTest::class,
        EngezRequest::class,
        EngezTrait::class,
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
        $this->initializeLocalization();

        $this->registerCustomValidationRules();

        $this->makeCarbonImmutable();

        if (!$this->app->runningInConsole()) {
            if ($this->app->request->method() == 'OPTIONS') {
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
     * Make carbon instances immutable
     * 
     * @return void
     */
    protected function makeCarbonImmutable()
    {
        $carbonImmutable = $this->config['misc']['carbonImmutable'] ?? true;

        if (!$carbonImmutable) {
            return;
        }

        Date::use(CarbonImmutable::class);
    }

    /**
     * Register custom validation rules
     * 
     * @return void
     */
    protected function registerCustomValidationRules()
    {
        $rules = $this->config['validation']['rules'] ?? [];

        foreach ($rules as $rule => $class) {
            Validator::extend($rule, is_string($class) ? $class . '@passes' : $class[0] . '@' . $class[1]);
        }
    }

    /**
     * Initialize localization and prepare locale code
     *
     * @return void
     */
    private function initializeLocalization()
    {
        $this->prepareLocaleCode();

        // load the package translation files
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'mongez');
    }

    /**
     * Prepare locale code
     *
     * @return void
     */
    private function prepareLocaleCode()
    {
        $request = $this->app->request;

        $localeCode = $request->header('LOCALE-CODE') ?: ($request->input('localeCode') ?: $request->input('acceptLanguage'));

        if ($localeCode) {
            $this->app->setLocale($localeCode);
            Mongez::setRequestLocaleCode($localeCode);
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

        //
        if (isset($this->config['serialize_precision'])) {
            ini_set('serialize_precision', $this->config['serialize_precision']);
        }

        // register the repositories as singletones, only one instance in the entire application
        foreach ($this->config('repositories', []) as $repositoryClass) {
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
     * Register Routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        // $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
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
