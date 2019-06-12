<?php
namespace HZ\Illuminate\Organizer\Console\Commands;

use File;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;

class ModuleBuilder extends Command
{
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
    protected $signature = 'make:module 
                                       {moduleName}
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

        $this->moduleName = Str::studly($this->module);

        $defaultDatabase = config('database.default');
        
        $this->databaseName = $defaultDatabase == 'mysql' ? 'MYSQL': 'MongoDB';

        $this->adjustOptionsValues();
    }

    /**
     * Adjust sent options and update its value if its default
     * 
     * @return void
     */
    protected function adjustOptionsValues()
    {
        $this->root = config('organizer.root');
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
        $this->info('Creating controller file');
        $this->createController();

        $this->info('Creating resource file');
        $this->createResource();

        $this->info('Creating model file');
        $this->createModel();

        $this->info('Creating repository file');
        $this->createRepository();

        $this->info('Generating routes files');
        $this->createRoues();

        $this->info('Module has been created successfully');
        
        $this->info('Updating configurations.');
        $this->updateConfig();
    }

    /**
     * Update configurations
     *
     * @return void
     */
    protected function updateConfig(): void 
    {
        if (! isset($this->info['repositoryName'])) return;

        $config = File::get($organizerPath =  base_path('config/organizer.php'));

        $replacementLine = '// Auto generated repositories here: DO NOT remove this line.';
        
        if (! Str::contains($config, $replacementLine)) return;

        $repositoryClassName = basename($this->info['repository']);

        $replacedString = "'{$this->info['repositoryName']}' => App\\Modules\\$repositoryClassName\\Repositories\\{$repositoryClassName}Repository::class,\n \t\t $replacementLine";

        $updatedConfig =str_replace($replacementLine, $replacedString, $config);

        File::put($organizerPath, $updatedConfig);
    }

    /**
     * Generate routes files
     * 
     * @return void
     */
    protected function createRoues()
    {
        $type = $this->option('type');

        // create routes directory
        $content = File::get($this->path("Controllers/Site/controller.php"));

        $routesDirectory = $this->modulePath("routes");

        if (!File::isDirectory($routesDirectory)) {
            File::makeDirectory($routesDirectory, 0777, true);
        }

        // get the content of the api routes file
        $apiRoutesFileContent = File::get(base_path('routes/api.php'));

        $controller = $this->info['controller'];

        $controllerName = basename($controller);

        if (in_array($type, ['all', 'site'])) {
            // generate the site routes file
            $content = File::get($this->path("routes/site.php"));
    
            // replace controller name
            $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);

            // replace module name
            $content = str_ireplace("ModuleName", "{$this->moduleName}", $content);

            // replace route prefix
            $content = str_ireplace("route-prefix", "{$this->module}", $content);

            // create the route file
            $filePath = $routesDirectory . '/site.php';

            $this->createFile($filePath, $content, 'Site routes');

            // add the routes file to the api routes file content
            if (Str::contains($apiRoutesFileContent, '// end of site routes')) {
                $apiRoutesFileContent = str_replace('// end of site routes', 
"// {$this->moduleName} module
include base_path('app/Modules/{$this->moduleName}/routes/site.php');

// end of site routes", $apiRoutesFileContent);
            }
        }

        if (in_array($type, ['all', 'admin'])) {
            // generate the admin routes file
            $content = File::get($this->path("routes/admin.php"));

            // replace controller name
            $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);

            // replace module name
            $content = str_ireplace("ModuleName", "{$this->moduleName}", $content);

            // replace route prefix
            $content = str_ireplace("route-prefix", "{$this->module}", $content);

            // create the route file
            $filePath = $routesDirectory . '/admin.php';

            $this->createFile($filePath, $content, 'Admin routes');
            // add the routes file to the api routes file content
            if (Str::contains($apiRoutesFileContent, '// end of admin routes')) {
                echo 12;
                $apiRoutesFileContent = str_replace('// end of admin routes', 
"// {$this->moduleName} module
    include base_path('app/Modules/{$this->moduleName}/routes/admin.php');

    // end of admin routes", $apiRoutesFileContent);
            }
        }

        // echo($apiRoutesFileContent);

        File::put(base_path('routes/api.php'), $apiRoutesFileContent);
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
        $controller = $this->info['controller'];

        $controllerName = basename($controller);

        $controllerType = $this->option('type');

        if (in_array($controllerType, ['all', 'site'])) {
            $content = File::get($this->path("Controllers/Site/controller.php"));

            // replace controller name
            $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);

            // replace module name
            $content = str_ireplace("ModuleName", $this->moduleName, $content);

            // repository name 
            $content = str_ireplace('repo-name', $this->info['repositoryName'], $content);

            $controllerDirectory = $this->modulePath("Controllers/Site");

            if (!File::isDirectory($controllerDirectory)) {
                File::makeDirectory($controllerDirectory, 0777, true);
            }

            // create the file
            $filePath = "$controllerDirectory/{$controllerName}Controller.php";

            $this->createFile($filePath, $content, 'Controller');
        }

        if (in_array($controllerType, ['all', 'admin'])) {
            // admin controller
            $this->info('Creating admin controller...');

            $content = File::get($this->path("Controllers/Admin/controller.php"));

            // replace controller name
            $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);

            // replace module name
            $content = str_ireplace("ModuleName", $this->moduleName, $content);

            // repository name 
            $content = str_ireplace('repo-name', $this->info['repositoryName'], $content);

            $controllerDirectory = $this->modulePath("Controllers/Admin");

            if (!File::isDirectory($controllerDirectory)) {
                File::makeDirectory($controllerDirectory, 0777, true);
            }

            // create the file
            $filePath = "$controllerDirectory/{$controllerName}Controller.php";

            $this->createFile($filePath, $content, 'Admin Controller');
        }
    }

    /**
     * Create the file
     * 
     * @param  string $filePath
     * @param  string $content
     * $param  string $fileType
     * @return void
     */
    protected function createFile($filePath, $content, $fileType)
    {
        $createFile = true;
        if (File::exists($filePath)) {
            $createFile = false;
            $createFile = $this->confirm($fileType . ' exists, override it?');
        }

        if ($createFile) {
            File::put($filePath, $content);
        }
    }

    /**
     * Create the resource file
     * 
     * @return void
     */
    protected function createResource()
    {
        $resource = $this->info['resource'];

        $resourceName = basename($resource);

        $resourcePath = dirname($resource);

        $content = File::get($this->path("Resources/resource.php"));

        // make it singular 
        $resourceName = Str::singular($resourceName);

        // share it
        $this->info['resourceName'] = $resourceName;

        // replace resource name
        $content = str_ireplace("ResourceName", "{$resourceName}", $content);

        // replace module name
        $content = str_ireplace("ModuleName", $this->moduleName, $content);

        $dataList = '';

        if (!empty($this->info['data'])) {
            // add the id to the list if not provided
            if (!in_array('id', $this->info['data'])) {
                array_unshift($this->info['data'], 'id');
            }

            $dataList = "'" . implode("', '", $this->info['data']) . "'";
        }

        // replace resource data
        $content = str_ireplace("DATA_LIST", $dataList, $content);

        // check for assets 

        $assetsList = '';

        if (!empty($this->info['uploads'])) {
            $assetsList = "'" . implode("', '", $this->info['uploads']) . "'";
        }

        // replace resource data
        $content = str_ireplace("ASSETS_LIST", $assetsList, $content);

        $resourceDirectory = $this->modulePath("Resources");

        if (!File::isDirectory($resourceDirectory)) {
            File::makeDirectory($resourceDirectory, 0777, true);
        }

        $this->info['resourcePath'] = $resourcePath . '\\' . $resourceName;

        // create the file
        $this->createFile("$resourceDirectory/{$resourceName}.php", $content, 'Resource');
    }

    /**
     * Create the repository file
     * 
     * @return void
     */
    protected function createRepository()
    {
        $repository = $this->info['repository'];

        $repositoryName = basename($repository);

        $content = File::get($this->path("Repositories/repository.php"));

        // replace repository name
        $content = str_ireplace("RepositoryName", "{$repositoryName}", $content);

        // replace module name
        $content = str_ireplace("ModuleName", $this->moduleName, $content);

        // replace database name 
        $content = str_replace('DatabaseName', $this->databaseName, $content);

        // replace model path
        $content = str_ireplace("ModelName", $this->info['modelName'], $content);

        // replace resource path
        $content = str_ireplace("ResourceName", $this->info['resourceName'], $content);

        // repository name 
        $content = str_ireplace('repo-name', $this->info['repositoryName'], $content);

        $dataList = '';

        if (!empty($this->info['data'])) {
            if (in_array('id', $this->info['data'])) {
                $this->info['data'] = Arr::remove($this->info['data'], 'id');
            }

            $dataList = "'" . implode("', '", $this->info['data']) . "'";
        }

        // replace repository data
        $content = str_ireplace("DATA_LIST", $dataList, $content);

        // uploads

        $uploadsList = '';

        if (!empty($this->info['uploads'])) {
            $uploadsList = "'" . implode("', '", $this->info['uploads']) . "'";
        }

        // replace repository data
        $content = str_ireplace("UPLOADS_LIST", $uploadsList, $content);

        $repositoryDirectory = $this->modulePath("Repositories/");

        if (!File::isDirectory($repositoryDirectory)) {
            File::makeDirectory($repositoryDirectory, 0777, true);
        }

        // create the file
        $this->createFile("$repositoryDirectory/{$repositoryName}Repository.php", $content, 'Repository');
    }

    /**
     * Create the model file
     * 
     * @return void
     */
    protected function createModel()
    {
        $model = $this->info['model'];

        $modelName = basename($model);

        $modelPath = dirname($model);

        $modelPath = array_map(function ($segment) {
            return Str::singular($segment);
        }, explode('\\', $modelPath));

        $modelPath = implode('\\', $modelPath);

        $content = File::get($this->path("Models/model.php"));

        // make it singular 
        $modelName = Str::singular($modelName);

        $this->info['modelName'] = $modelName;

        // replace model name
        $content = str_ireplace("ModelName", "{$modelName}", $content);

        // replace database name 
        $content = str_replace('DatabaseName', $this->databaseName, $content);

        // replace module name
        $content = str_ireplace("ModuleName", $this->moduleName, $content);
        
        $modelDirectory = $this->modulePath("Models/");

        if (!File::isDirectory($modelDirectory)) {
            File::makeDirectory($modelDirectory, 0777, true);
        }

        $this->info['modelPath'] = $modelPath . '\\' . $modelName;
        // create the file
        $this->createFile("$modelDirectory/{$modelName}.php", $content, 'Model');
    }

    /**
     * Get relative path to base path
     * 
     * @param  string $path
     * @return string 
     */
    protected function path($path)
    {
        return $this->root . '/module/' . $path;
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
     * Get the final path of the module for the given relative path
     * 
     * @param   string $relativePath
     * @return  string 
     */
    protected function modulePath(string $relativePath): string
    {
        return base_path("app/Modules/{$this->moduleName}/$relativePath");
    }

    /**
     * Create module repository
     * 
     * @return void
     */
    protected function initRepository()
    {
        $this->setData('repository');

        $this->info['repositoryName'] = strtolower(basename($this->info['repository']));
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
}