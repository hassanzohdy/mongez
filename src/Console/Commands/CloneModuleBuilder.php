<?php
namespace HZ\Illuminate\Mongez\Console\Commands;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;

class CloneModuleBuilder extends Command
{
    use EngezTrait;

    /**
     * Set all available modules.
     * 
     * @const array
     */
    const AVAILABLE_MODULES = [
        'Newsletters' => [
            'repositoryName' => 'contactus',
            'routeType'   => 'all',
            'modelName'   => 'contactUs',
            'controller'  => 'contactUs',
            'moduleName'  => 'newsletters',
            'type'        => 'all',
            'commands'    => [
                [
                ]
            ],
            'neededPermissions' => [
            ],
            'subModules' => [
            ],
            'conditionalDirectories' => [
                'Repositories',
                'Models',
                'database/migrations',
                'Filters',
                'Resources'
            ],
            'migrationFiles' => [
                'MongoDB' => [
                ],
                'MYSQL'   => [
                    '2020_07_13_102302_create_contactus_table',
                ]
            ]
        ],
        'Users'=> [
            'repositoryName' => 'users',
            'routeType'   => 'admin',
            'modelName'   => 'user',
            'controller'  => 'user',
            'moduleName'  => 'users',
            'type'        => 'admin',
            'commands'    => [
                [
                'signature' => 'engez:migrate',
                'options' =>['users']
                ]
            ],
            'neededPermissions' => [
                'Users',
                'Groups'
            ],
            'subModules' => [
                'usersGroups',
                'permissions'
            ],
            'conditionalDirectories' => [
                'Models',
                'Repositories',
                'Traits\Auth'    
            ],
            'migrationFiles' => [

            ]
        ],
        'Settings'=> [
            'repositoryName' => 'settings',
            'routeType'   => 'admin',
            'modelName'   => 'setting',
            'controller'  => 'setting',
            'moduleName'  => 'settings',
            'type'        => 'admin',
            'commands'    => [
                [
                'signature' => 'engez:migrate',
                'options' =>['settings']
                ]
            ],
            'neededPermissions' => [
                'Settings',
            ],
            'subModules' => [],
            'conditionalDirectories' => [
                'Models',
                'Repositories',
                'database/migrations',
                'Filters'    
            ],
            'migrationFiles' => [
                
            ]
        ],
        'Localization'=> [
            'repositoryName' => 'countries',
            'routeType'   => 'admin',
            'modelName'   => 'countries',
            'controller'  => 'countries',
            'moduleName'  => 'localization',
            'type'        => 'admin',
            'commands'    => [
                [
                'signature' => 'engez:migrate',
                'options' =>['localization']
                ]
            ],
            'neededPermissions' => [
                'Countries',
                'Cities',
                'Currencies',
                'Regions'
            ],
            'subModules' => [
                'countries',    
                'regions',
                'cities',
                'currencies'
            ],
            'conditionalDirectories' => [
                'Repositories',
                'Models',
                'database/migrations',
                'Filters',
                'Resources'
            ],
            'migrationFiles' => [
                'MongoDB' => [
                    '2020_06_29_101042_create_currencies_table',
                    'B2020_06_29_101131_create_countries_table',
                    '2020_06_29_101152_create_cities_table',
                    '2020_06_29_101204_create_regions_table'
                ],
                'MYSQL'   => [
                    '2020_06_28_144850_create_countries_table',
                    '2020_06_28_145245_create_cities_table',
                    '2020_06_28_145321_create_regions_table',
                    '2020_06_28_152325_create_currencies_table'
                ]
            ]
        ]
    ];

    /**
     * Database options.
     *  
     * @const array
     */
    const DATABASE_OPTIONS = [
        'mysql' =>  'MYSQL',
        'mongodb' => 'MongoDB'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clone:module {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone Modules';

    /**
     * Module name
     * 
     * @var string
     */
    protected $moduleName;

    /**
     * Database driver
     * 
     * @var string
     */
    protected $databaseDriver;

    /**
     * Sub Module name
     * 
     * @var array
     */
    protected $subModule = [];

     /**
     * Module path
     * 
     * @var string
     */
    protected $modulePath;

    /**
     * Module info
     * 
     * @var array
     */
    protected $info = [];
    
    /**
     * 
     */
    protected $unNeededSubModules = [];
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();
        $this->validateArguments();
        $this->cloneModule();
        $this->addModule();
        // $this->createRoutes();
        $this->removeUnNeededFiles();
        $this->markModuleAsInstalled();
        // $this->commandMustBeFollowed();
        $this->updateConfig();
        $this->updateRepositoriesConfig();
        if(! empty($this->subModule)) $this->removeUnNeededModules();
        if($this->checkDirectory('users')) $this->addModulePermissions;
        $this->updateServiceProviderConfig();
        $this->info('Module cloned successfully');
    }
    
    /**
     * Validate The module name
     *
     * @return void
     */
    protected function validateArguments()
    {
        if ($this->checkDirectory($this->modulePath)) {
            $message = $this->confirm($this->moduleName . ' exists, Do you want to override it?');

            if (!$message) return;
        }

    }

    /**
     * Set controller info.
     * 
     * @return void
     */
    protected function init()
    {
        $targetModule = $this->argument('module');

        if (($pos = strpos($targetModule, "/")) !== FALSE) {
            $subModules = substr($targetModule, $pos+1); 
            $this->subModule = explode(',' ,substr($targetModule, $pos+1));
            $targetModule = str_replace("/" .$subModules, "", $targetModule);
        }
        $this->moduleName = Str::studly($targetModule);

        if (! array_key_exists($this->moduleName, static::AVAILABLE_MODULES)) {
            Command::error('This module does not exits');
            die();
        }

        if (! empty($this->subModule)) {
            foreach($this->subModule as $checkModule) {
                if (! in_array($checkModule, static::AVAILABLE_MODULES[$this->moduleName]['subModules'])) {
                    Command::error($checkModule.' module does not exits');
                    die();
                }
            }
        }

        $this->info['moduleName'] = Str::studly(static::AVAILABLE_MODULES[$this->moduleName]['moduleName']);
        
        $this->info['modelName'] = static::AVAILABLE_MODULES[$this->moduleName]['modelName']; 
        
        $this->info['type']  = static::AVAILABLE_MODULES[$this->moduleName]['routeType'];
        $this->info['controller']  = static::AVAILABLE_MODULES[$this->moduleName]['controller'];

        $this->info['repository'] =  $this->info['moduleName'];

        $this->modulePath = $this->modulePath();

        $unNeededSubModules = static::AVAILABLE_MODULES[Str::studly($this->moduleName)]['subModules'];
        $indexOfTargetModules = [];
        foreach($this->subModule as $subModule) {
            $indexOfTargetModules [] = array_search($subModule, $unNeededSubModules);
        }
        foreach($indexOfTargetModules as $indexOfTargetModule) {
            unset($unNeededSubModules[$indexOfTargetModule]);
        }
        $this->unNeededSubModules = $unNeededSubModules;
        $this->databaseDriver = config('database.default');
    }
    
    /**
     * Copy files of module
     * 
     * @return void
     */
    protected function cloneModule()
    {
        $modulePath = Mongez::packagePath('/' . 'cloneable-modules/' . $this->moduleName);
        
        File::copyDirectory($modulePath, $this->modulePath($this->moduleName));
    }

    /**
     * Remove un needed files.
     * 
     * @return void 
     */
    protected function removeUnNeededFiles()
    {
        $modulePath = $this->modulePath($this->moduleName);
        
        $targetDatabaseFileName = static::DATABASE_OPTIONS[$this->databaseDriver];

        $deletedDatabaseFileName = 'MongoDB';
        if ($targetDatabaseFileName == 'MongoDB') {
            $deletedDatabaseFileName = 'MYSQL';
        }
        $conditionalDirectories = static::AVAILABLE_MODULES[$this->moduleName]['conditionalDirectories'];

        foreach ($conditionalDirectories as $folder) {
            $targetFolder = $modulePath ."/{$folder}";
            
            foreach(File::allFiles($targetFolder) as $directoryFile){
                $pathInfo = pathinfo($directoryFile)['basename'];
            
                if (Str::startsWith(pathinfo($directoryFile)['filename'], $deletedDatabaseFileName)) {
                    File::delete($targetFolder . '/' . $pathInfo);                    
                } else {
                    $file = str_replace($targetDatabaseFileName, "",$pathInfo);
                    rename($targetFolder . '/' . $pathInfo, $targetFolder . "/{$file}");
                }
            }
        }
    }

    /**
     * 
     * Check if the given directory path is created.
     * 
     * @param  string $directoryPath
     * @return  void
     */
    public function checkDirectory(string $directoryPath)
    {
        return File::isDirectory($directoryPath);
    }

    /**
     * Get the final path of the module
     * 
     * @return  string 
     */
    protected function modulePath()
    {
        return base_path("app" .DIRECTORY_SEPARATOR ."Modules" .DIRECTORY_SEPARATOR ."{$this->moduleName}");
    }

    /**
     * Command must be followed.
     * 
     * @return void 
     */
    protected function commandMustBeFollowed()
    {
        $commands = static::AVAILABLE_MODULES[$this->moduleName]['commands'];
        foreach($commands as $command){
            $this->call($command['signature'], $command['options']);
        }
    }

    /**
     * Update repository config if cloned module has sub repository
     * 
     * @return void
     */
    public function updateRepositoriesConfig() 
    {
        $parent = strtolower($this->moduleName);
        $subModules = static::AVAILABLE_MODULES[$this->moduleName]['subModules'];
        
        foreach ($subModules as $repositoryName) {
            $this->moduleName = $repositoryName;
            $this->info['repository'] = Str::studly($repositoryName);
            $this->updateConfig();
        }

        $this->moduleName = Str::studly($parent);
    }

    /**
     * Add needed permissions of clone module
     * 
     * @return void 
     */
    public function addModulePermissions()
    {
        $neededModules = static::AVAILABLE_MODULES[Str::studly($this->argument('module'))]['neededPermissions'];
        foreach($neededModules as $module) {
            $this->moduleName = $module;
            $this->addRoutesToPermissionTable();
        }
    }

    /**
     * Remove un needed modules
     * 
     * @return void
     */
    protected function removeUnNeededModules()
    {
        $modulePath = $this->modulePath($this->moduleName);
        
        $conditionalDirectories    = static::AVAILABLE_MODULES[$this->moduleName]['conditionalDirectories'];
        $conditionalDirectories [] = 'Controllers\Admin';
        $conditionalDirectories [] = 'Controllers\Site';
        $conditionalDirectories [] = 'docs';
        $conditionalDirectories [] = 'database\migrations';

        foreach ($conditionalDirectories as $folder) {
            $targetFolder = $modulePath ."/{$folder}";
            foreach(File::allFiles($targetFolder) as $directoryFile) {
                
                $pathInfo = pathinfo($directoryFile)['basename'];
                $fileName = strtolower(Str::plural(pathinfo($directoryFile)['filename']));
                $fileName = str_replace("repositories", "", $fileName);
                $fileName = str_replace("controllers", "", $fileName);
                $fileName = str_replace(".postmen", "", $fileName);

                if (in_array($fileName, $this->unNeededSubModules)) {
                    File::delete($targetFolder . '/' . $pathInfo);
                }
            }
        }
        $this->removeUnNeededRoutes();
        $this->removeUnNeededMigrationFiles();
    }

    /**
     * Removed Unneeded Routes
     * 
     * @return void 
     */
    protected function removeUnNeededRoutes()
    {
        $modulePath = $this->modulePath($this->moduleName);
        $adminRoutePath  = $modulePath .DIRECTORY_SEPARATOR .'routes' .DIRECTORY_SEPARATOR .'admin.php';
        $siteRoutePath  = $modulePath .DIRECTORY_SEPARATOR .'routes' .DIRECTORY_SEPARATOR .'site.php';
        $adminFile = File::get($adminRoutePath);
        $siteFile = File::get($siteRoutePath);
        foreach($this->unNeededSubModules as $unNeededSubModule) {
            // Route::apiResource('/regions', 'RegionsController');
            $controllerName = ucfirst($unNeededSubModule);
            $adminTargetLine = "Route::apiResource('/{$unNeededSubModule}', '{$controllerName}Controller');";
            // Route::get('/regions',  'RegionsController@index');
            $siteTargetLine  =  [
                "Route::get('/{$unNeededSubModule}', '{$controllerName}Controller@index');",
                "Route::get('/{$unNeededSubModule}/{id}', '{$controllerName}Controller@show');"
            ];
            $adminFile = str_replace($adminTargetLine, "", $adminFile);
            $siteFile  = str_replace($siteTargetLine, "", $siteFile);
        }
        File::put($adminRoutePath, $adminFile);
        File::put($siteRoutePath, $siteFile);
    }

    /**
     * Remove Unneeded Migration Files
     * 
     * @return void
     */
    protected function removeUnNeededMigrationFiles()
    {
        $modulePath = $this->modulePath($this->moduleName);
        $migrationFiles  = static::AVAILABLE_MODULES[$this->moduleName]['migrationFiles'][static::DATABASE_OPTIONS[$this->databaseDriver]];
        foreach($this->unNeededSubModules as $unNeededSubModule) {
            foreach($migrationFiles as $migrationFile) {
                $pathInfo = pathinfo($migrationFile)['basename'];
                $fileName = strtolower(Str::plural(pathinfo($migrationFile)['filename']));
                if (strpos($fileName, $unNeededSubModule) !== false) {
                    $targetFile = $modulePath . DIRECTORY_SEPARATOR."database" .DIRECTORY_SEPARATOR ."migrations" . DIRECTORY_SEPARATOR."{$pathInfo}.php";
                    File::delete($targetFile);
                }
            }
        }
    }
}