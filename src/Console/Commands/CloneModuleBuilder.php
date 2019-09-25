<?php
namespace HZ\Illuminate\Mongez\Console\Commands;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
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
        ]
    ];

    /**
     * Database options.
     *  
     * @const array
     */
    const DATABASE_OPTIONS = [
        'mysql' =>  'MYSQL',
        'mongodb' =>'MongoDB'
    ];
    
    /**
     * Removal folders.
     *  
     * @const array
     */
    const REMOVAL_FOLDERS = [
        'Models'=>'User.php',
        'Repositories'=>'UsersRepository.php'
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
        $this->setModuleToFile();
        $this->createMigration();
        $this->removeUnNeededFiles();
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
        
        $this->info = static::AVAILABLE_MODULES[$this->moduleName];
        
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
        
        $database = config('database.default');
         
        foreach (static::REMOVAL_FOLDERS as $folder => $file) {

            $targetDeletedDirectory = $modulePath . "/{$folder}";

            foreach (File::allFiles($targetDeletedDirectory) as $targetDeletedFile) {
                
                $targetFile = static::DATABASE_OPTIONS[$database].$file;
                $pathInfo = pathinfo($targetDeletedFile)['basename'];
                
                if ($pathInfo != $targetFile) {
                    File::delete($targetDeletedDirectory . '/' . $pathInfo);
                } else {
                    rename($targetDeletedDirectory . '/' . $pathInfo, $targetDeletedDirectory . "/{$file}");
                }
            }
        }
    }

    /**
     * Check if the given directory path is created
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

}