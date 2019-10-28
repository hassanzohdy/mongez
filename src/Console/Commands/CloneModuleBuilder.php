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
            'subRepositories' => [
                'usersGroups',
                'permissions'
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
     * Get files of these directories based on current database driver.
     *  
     * @const array
     */
    const CONDITIONAL_DIRECTORIES = [
        'Models',
        'Repositories',
        'Traits\Auth'
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
        $this->createRoutes();
        $this->removeUnNeededFiles();
        $this->markModuleAsInstalled();
        $this->commandMustBeFollowed();
        $this->updateConfig();
        $this->updateRepositoriesConfig();
        $this->addModulePermissions();
        $this->info('Module cloned successfully');
    }
    
    /**
     * Validate The module name
     *
     * @return void
     */
    protected function validateArguments()
    {
        if (! array_key_exists($this->moduleName, static::AVAILABLE_MODULES)) {
            return $this->info('This module does not exits');
        }

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
        $this->moduleName = Str::studly($this->argument('module'));
        
        $this->info['moduleName'] = Str::studly(static::AVAILABLE_MODULES[$this->moduleName]['moduleName']);
        
        $this->info['modelName'] = static::AVAILABLE_MODULES[$this->moduleName]['modelName']; 
        
        $this->info['type']  = static::AVAILABLE_MODULES[$this->moduleName]['routeType'];
        $this->info['controller']  = static::AVAILABLE_MODULES[$this->moduleName]['controller'];

        $this->info['repository'] =  $this->info['moduleName'];

        $this->modulePath = $this->modulePath();
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
        
        $databaseDriver = config('database.default');
        
        $targetDatabaseFileName = static::DATABASE_OPTIONS[$databaseDriver];

        $deletedDatabaseFileName = 'MongoDB';
        if ($targetDatabaseFileName == 'MongoDB') {
            $deletedDatabaseFileName = 'MYSQL';
        }

        foreach (static::CONDITIONAL_DIRECTORIES as $folder) {
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
        return base_path("app/Modules/{$this->moduleName}");
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
        foreach (static::AVAILABLE_MODULES[$this->moduleName]['subRepositories'] as $repositoryName) {

            $this->parent = strtolower($this->moduleName);
            $this->moduleName = $repositoryName;
            $this->info['repository'] = Str::studly($repositoryName);
            $this->updateConfig();
        }

        $this->moduleName = Str::studly($this->parent);
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
}