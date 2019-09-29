<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use File;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Helpers\Console\Postman;
use HZ\Illuminate\Mongez\Helpers\Console\Markdown;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;

class ModuleBuilder extends Command
{
    use EngezTrait;
    /**
     * Controller types
     * 
     * @const array
     */
    const CONTROLLER_TYPES = ['site', 'admin', 'all'];

    /**
     * Module directory path
     * 
     * @var string
     */
    protected $root;

    /**
     * The module name
     * 
     * @var string
     */
    protected $module;

    /**
     * The module name in studly case
     * 
     * @var string
     */
    protected $moduleName;

    /**
     * Current database name
     * 
     * @var string
     */
    protected $databaseName;

    /**
     * Module info
     */
    protected $info = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:module 
                                       {moduleName}
                                       {--parent=}
                                       {--controller=}
                                       {--type=all}
                                       {--model=}
                                       {--data=}
                                       {--uploads=}
                                       {--resource=}
                                       {--repository=}
                                       {--path=}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Module builder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->module = $this->argument('moduleName');

        $this->parentModule = $this->option('parent') ?: $this->module;

        $this->moduleName = Str::studly($this->module);

        $this->info['moduleName'] = $this->moduleName;

        if (isset($this->info['parent'])) {
            $availableModules = Mongez::getStored('modules');
            if (! in_array(strtolower($this->info['parent']), $availableModules)) {
                Command::error('This parent module is not available');
                die();
            }    
        }
        
        // check if the module directory exists
        // if so, throw error

        if (File::isDirectory($this->modulePath(''))) {
            Command::error('This module already exits');
            die();
        }

        $this->adjustOptionsValues();
    }

    /**
     * Adjust sent options and update its value if its default
     * 
     * @return void
     */
    protected function adjustOptionsValues()
    {
        $this->init();
        $this->create();
    }

    /**
     * Init data
     * 
     * @return void
     */
    protected function init()
    {
        $this->info('Preparing data...');
        $this->initController();
        $this->initModel();
        $this->initResource();
        $this->initRepository();
        $this->initData();
    }

    /**
     * Create files
     * 
     * @return void
     */
    protected function create()
    {
        $this->addModule();

        $this->info('Creating controller file');
        $this->createController();

        $this->info('Creating resource file');
        $this->createResource();

        $this->info('Creating model file');
        $this->createModel();

        $this->info('Creating repository file');
        $this->createRepository();

        $this->info('Creating database files');
        $this->createDatabase();

        $this->info('Generating routes files');
        $this->createRoutes();

        $this->info('Generating Module Postman File');
        $this->generatePostmanModule();

        $this->info('Generating Module Docs');
        $this->generateModuleDocs();
        
        $this->markModuleAsInstalled();
    }

    /**
     * Some sections like repository and resource has the DATA constant
     * If the developer passed a list of data separated by comma it will be set there
     * 
     * @return void
     */
    protected function initData()
    {
        foreach (['data', 'uploads'] as $option) {
            $value = $this->option($option);
            if ($value) {
                $this->info[$option] = explode(',', $value);
            }
        }
    }

    /**
     * Handle controller file
     * 
     * @return void
     */
    protected function initController()
    {
        $this->setData('controller');

        $controller = $this->info['controller'];

        $controllerPath = $this->option('path'); // the parent directory inside the Api directory

        if ($controllerPath) {
            $controller = "$controllerPath\\$controller";
        }

        $controllerType = $this->option('type');

        if (!in_array($controllerType, static::CONTROLLER_TYPES)) {
            throw new Exception(sprintf('Unknown controller type %s, available types: %s', $controllerType, implode(',', static::CONTROLLER_TYPES)));
        }

        $this->info['controller'] = $controller;
    }

    /**
     * Create controller file
     * 
     * @return void
     */
    protected function createController()
    {
        $controllerOptions = [
            'controller' => $this->info['controller'],
            '--module' => $this->moduleName,
            '--repository' => $this->info['repositoryName'],
            '--type' => $this->option('type'),
        ];
        
        if (isset($this->info['parent'])) $controllerOptions['--parent'] = $this->info['parent'];
        
        Artisan::call('engez:controller', $controllerOptions);
    }

    /**
     * Create the resource file
     * 
     * @return void
     */
    protected function createResource()
    {
        $resourceOptions = [
            'resource' => $this->info['resource'],
            '--module' => $this->moduleName,
            '--data'   => $this->option('data'),
        ];
        if (isset($this->info['parent'])) $resourceOptions['--parent'] = $this->info['parent'];

        Artisan::call('engez:resource', $resourceOptions);
    }

    /** 
     * Create the database file
     * 
     * @return void
     */
    protected function createDatabase()
    {
        $databaseFileName = strtolower(str::plural($this->moduleName));

        // Create Schema only in monogo database
        $databaseDriver = config('database.default');
        if ($databaseDriver == 'mongodb') {
            $this->createSchema($databaseFileName);
        }

        $this->createMigration();
    }

    /**
     * Create schema of table in mongo 
     *
     * @param string $dataFileName
     * @return void 
     */
    protected function createSchema($databaseFileName)
    {
        $path = $this->modulePath("database/schema");
        $this->checkDirectory($path);

        $defaultContent = [
            '_id' => "objectId",
            'id' => 'int',
            'createdAt'=>'dataTime',
            'updatedAt'=>'dataTime',
            'deletedAt'=>'dataTime',
            'deletedBy'=>'@Users/users',
            'createdBy'=>'@Users/users',
            'updatedBy'=>'@Users/users',
        ];

        $customData = $this->info['data'] ?? [];

        $uploadsData = $this->info['uploads'] ?? [];

        unset($customData['id'], $customData['_id']);

        $customData = array_fill_keys($customData, 'string');

        $uploadsData = array_fill_keys($uploadsData, 'string');

        $content = array_merge($defaultContent, $customData, $uploadsData);

        $this->createFile("$path/{$databaseFileName}.json", json_encode($content, JSON_PRETTY_PRINT), 'Schema');
    }

    /**
     * Create the repository file
     * 
     * @return void
     */
    protected function createRepository()
    {
        $repositoryOptions = [
            'repository' => $this->info['repositoryName'],
            '--module' => $this->moduleName,
        ];
        if (isset($this->info['parent'])) $repositoryOptions['--parent'] = $this->info['parent'];

        Artisan::call('engez:repository', $repositoryOptions);
    }

    /**
     * Create the model file
     * 
     * @return void
     */
    protected function createModel()
    {
        $model = $this->info['model'];

        $modelName = basename(str_replace('\\', '/', $model));

        // make it singular 
        $modelName = Str::singular($modelName);

        $this->info['modelName'] = $modelName;

        $modelOptions = [
            'model' => $this->info['model'],
            '--module' => $this->moduleName,
        ];
        if (isset($this->info['parent'])) $modelOptions['--parent'] = $this->info['parent'];
        
        Artisan::call('engez:model', $modelOptions);
    }

    /**
     * Create module model
     * 
     * @return void
     */
    protected function initModel()
    {
        $this->setData('model');
    }

    /**
     * Create module resource
     * 
     * @return void
     */
    protected function initResource()
    {
        $this->setData('resource');
    }

    /**
     * Create module repository
     * 
     * @return void
     */
    protected function initRepository()
    {
        $this->setData('repository');

        $this->info['repositoryName'] = Str::camel(basename(str_replace('\\', '/', $this->info['repository'])));
    }

    /**
     * Set to the data container the value of the given option
     *
     * @param  string $option
     * @return void
     */
    protected function setData($option)
    {
        // repository
        $optionValue = $this->option($option);

        $module = ucfirst($this->module);

        if (!$optionValue) {
            // get it from the module name
            $optionValue = "{$module}\\{$module}";
        }

        $this->info[$option] = Str::studly(str_replace('/', '\\', $optionValue));
    }

    /**
     * Generate module postman
     *   
     * @return void
     */
    protected function generatePostmanModule()
    {
        $data = [];
        if (isset($this->info['data'])) $data = $this->info['data'];

        $postman =  new Postman([
            'modelName' => $this->info['modelName'],
            'data'       => $data
        ]);

        $path = $this->modulePath("docs");
        $this->checkDirectory($path);

        $fileName = strtolower($this->info['moduleName']) . '.postman.json';
        $content = $postman->getContent();

        $this->createFile("{$path}/{$fileName}", $content, 'PostmanFile');
    }

    /**
     * Generate module documentation
     *   
     * @return void
     */
    protected function generateModuleDocs()
    {
        $data = [];
        if (isset($this->info['data'])) $data = $this->info['data'];

        $postman =  new MarkDown([
            'moduleName' => $this->info['modelName'],
            'data'       => $data
        ]);

        $path = $this->modulePath("docs");
        $content = $postman->getContent();
        $this->createFile("{$path}/README.md", $content, 'Docs');
    }
}
