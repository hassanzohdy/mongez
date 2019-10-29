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
     * User module is exist
     *
     * @var bool
     */
    protected $isUserModuleExits = false;

    /**
     * Module info
     * 
     * @var array
     */
    protected $info = [];
    
    /**
     * Available Options
     *
     */
    protected $availableOptions = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:module 
                                       {moduleName}
                                       {--parent=}
                                       {--singleName=}
                                       {--controller=}
                                       {--type=all}
                                       {--model=}
                                       {--table=}
                                       {--data=}
                                       {--uploads=}
                                       {--index=}
                                       {--unique=}
                                       {--int=}
                                       {--double=}
                                       {--bool=}
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

        if ($this->optionHasValue('parent')) $this->info['parent'] = $this->option('parent');

        $this->moduleName = Str::studly($this->module);

        $this->info['moduleName'] = $this->moduleName;
        
        $this->validateArguments();
        $this->adjustOptionsValues();
    }
    /**
     * Validate command arguments 
     * 
     * @return void
     */
    protected function validateArguments()
    {
        $modulePath = $this->modulePath("");
        
        if (File::isDirectory($modulePath) && !isset($this->info['parent'])) {
            Command::error('This module is already exist');
            die();
        }
        
        // check if the module directory exists
        // if so, throw error        
        if (isset($this->info['parent'])) {
            $availableModules = Mongez::getStored('modules');
            if (! in_array(strtolower($this->info['parent']), $availableModules)) {
                Command::error('This parent module is not available');
                die();
            }
        } 
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
     * Get option from available options
     * 
     * @param string $key
     * @return 
     */
    /**
     * Init data
     * 
     * @return void
     */
    protected function init()
    {
        if (in_array('users', Mongez::getStored('modules'))) {
            $this->isUserModuleExits = true;
        }

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

        if (! isset($this->info['parent'])) $this->createServiceProvider();

        $this->info('Generating Module Postman File');
        $this->generatePostmanModule();

        $this->info('Generating Module Docs');
        $this->generateModuleDocs();

        if ($this->isUserModuleExits) $this->addRoutesToPermissionTable();
   
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
     * Handle controller file.
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
        $controllerName = basename(str_replace('\\', '/', $this->info['controller']));
        if (File::exists($this->modulePath("Controllers/Admin/{$controllerName}Controller.php"))) {
            Command::error('You already have this module');
            die();    
        }
        $controllerOptions = [
            'controller' => $controllerName,
            '--module' => $this->moduleName,
        ];
        $options = $this->setOptions([
            'parent',
            'type',
        ]);
        
        $this->call('engez:controller', array_merge($controllerOptions, $options));
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
        ];

        $options = $this->setOptions([
            'parent',
            'assets'=>'uploads',
            'data'
        ]);
        
        $this->call('engez:resource', array_merge($resourceOptions, $options));
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
        $options = $this->setOptions([
            'parent',
            'uploads',
            'data',
            'int',
            'double',
            'bool'
        ]);
        
        $this->call('engez:repository', array_merge($repositoryOptions, $options));
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
        $options = $this->setOptions([
            'index',
            'unique',
            'data',
            'uploads',
            'double',
            'bool',
            'int',
            'data',
            'parent'
        ]);
        
        $this->call('engez:model', array_merge($modelOptions, $options));
    }

    /**
     * Create module model
     * 
     * @return void
     */
    protected function initModel()
    {
        if ($this->optionHasValue('singleName')) {
            return $this->info['model'] = $this->option('singleName');
        }

        $this->setData('model');
    }

    /**
     * Create module service provider
     * 
     * @return void 
     */
    protected function createServiceProvider()
    {
        $moduleServiceProviderPath = $this->path("Providers/ModuleServiceProvider.php");
        $content = File::get($moduleServiceProviderPath);

        $types = $this->option('type');

        if ($types == 'all') {
            $types = 'admin,site';
        }
        $types = explode(',', $types);
        
        $stringTypes = json_encode($types);

        // replace Route list
        $content = str_ireplace("ROUTES_LIST", $stringTypes, $content);
        
        // replace module name
        $content = str_ireplace("ModuleName", $this->moduleName, $content);
        $content = str_ireplace("ClassName", Str::singular($this->moduleName), $content);        
        $serviceProviderName = Str::singular($this->moduleName) .'ServiceProvider';
        $serviceProviderDirectory = $this->modulePath("Providers");
        
        $this->checkDirectory($serviceProviderDirectory);
        $this->createFile("$serviceProviderDirectory/{$serviceProviderName}.php", $content, 'ServiceProvider');
        $this->updateServiceProviderConfig();
    }
    
    /**
     * Create module resource
     * 
     * @return void
     */
    protected function initResource()
    {
        if ($this->optionHasValue('singleName')) {
            return $this->info['resource'] = $this->option('singleName');
        } 
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

        $this->info['repositoryName'] = $this->adjustRepositoryName($this->info['repository']); 
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
     * Generate module postman.
     *   
     * @return void
     */
    protected function generatePostmanModule()
    {
        $data = [];
        if (isset($this->info['data'])) $data = $this->info['data'];

        $uploads = [];
        
        if ($this->optionHasValue('uploads')) {
            $uploads[] = $this->option('uploads');
        }

        if ($this->optionHasValue('index')) {
            $data[] = $this->option('index');
        }

        if ($this->optionHasValue('unique')) {
            $data[] = $this->option('unique');
        }

        if ($this->optionHasValue('double')) {
            $data[] = $this->option('double');
        }
        
        if ($this->optionHasValue('bool')) {
            $data[] = $this->option('bool');
        }
        
        if ($this->optionHasValue('int')) {
            $data[] = $this->option('int');
        }

        $postman =  new Postman([
            'modelName' => $this->info['modelName'],
            'data'       => $data,
            'uploads'    => $uploads
        ]);

        $path = $this->modulePath("docs");
        $this->checkDirectory($path);

        $fileName = strtolower($this->info['moduleName']) . '.postman.json';
        $content = $postman->getContent();

        $this->createFile("{$path}/{$fileName}", $content, 'PostmanFile');
    }

    /**
     * Generate module documentation.
     *   
     * @return void
     */
    protected function generateModuleDocs()
    {
        $data = [];
        if (isset($this->info['data'])) $data = $this->info['data'];

        $uploads = [];
        if ($this->optionHasValue('uploads')) $uploads = $this->option('uploads');

        $markDownOption = [
            'moduleName' => $this->info['modelName'],
            'data'       => $data,
            'uploads'    => $uploads
        ];
        if (isset($this->info['parent'])) {
            $markDownOption['parent'] = $this->info['parent'];
        }
        
        $markDown =  new Markdown($markDownOption);
        
        $moduleFileName = 'README.md';
        if(isset($this->info['parent'])) {
            $moduleFileName = strtolower($this->info['moduleName']) .'.md';
        }

        $path = $this->modulePath("docs");
        $content = $markDown->getContent();
        $this->createFile("{$path}/{$moduleFileName}", $content, 'Docs');
    }
}
