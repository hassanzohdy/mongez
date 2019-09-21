<?php
namespace HZ\Illuminate\Mongez\Console\Commands;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;
use HZ\Illuminate\Mongez\Contracts\Console\EngezInterface;

class EngezController extends Command implements EngezInterface 
{
    use EngezTrait;
    
    /**
     * The controller types 
     *
     * @var array
     */
    const CONTROLLER_TYPES = ['admin','site','all'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:controller  {controller} 
                                               {--module=} 
                                               {type=site}
                                               {--repository=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new controller into module';

    /**
     * info used for creating controller 
     * 
     * @var array 
     */
    protected $info = [];

    /**
     * Module directory path
     * 
     * @var string
     */
    protected $root;
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();
        $this->validateArguments();   
        $this->create();
        $this->info('Controller created successfully');
    }
    
    /**
     * Validate The module name
     *
     * @return void
     */
    public function validateArguments()
    {
        $availableModules = Mongez::getStored('modules');

        if (! $this->option('module')) {
            return $this->missingRequiredOption('Module option is required');
        }
        
        if (! in_array($this->info['moduleName'], $availableModules)) {
            return $this->missingRequiredOption('This module is not available');
        }

        if (! in_array($this->info['type'], static::CONTROLLER_TYPES)) {
            return $this->missingRequiredOption('This controller type does not exits');
        }
    }

    /**
     * Set controller info
     * 
     * @return void
     */
    public function init()
    {
        $this->info['controllerName'] = Str::studly($this->argument('controller'));
        $this->info['moduleName'] = Str::studly($this->option('module'));
        $this->info['type'] = $this->argument('type');

        $repositoryName = $this->info['controllerName'];
        
        if ($this->option('repository')) {
            $repositoryName = $this->option('repository');
        }

        $this->info['repositoryName'] = $repositoryName;

    }
    
    /**
     * Create controller File. 
     *
     * @return void
     */
    public function create()
    {
        $controller = $this->info['controllerName'];
        
        $controllerName = basename(str_replace('\\', '/', $controller));

        $controllerType = $this->info['type'];

        if (in_array($controllerType, ['all', 'site'])) {

            $content = File::get($this->path("Controllers/Site/controller.php"));

            // replace controller name
            $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);

            // replace moule name
            $content = str_ireplace("ModuleName", $this->info['moduleName'], $content);

            // repository name 
            $content = str_ireplace('repo-name', $this->info['repositoryName'], $content);
            
            $controllerDirectory = $this->modulePath("Controllers/Site");

            $this->checkDirectory($controllerDirectory);

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
            $content = str_ireplace("ModuleName", $this->info['moduleName'], $content);

            // repository name 
            $content = str_ireplace('repo-name', $this->info['repositoryName'], $content);

            $controllerDirectory = $this->modulePath("Controllers/Admin");

            $this->checkDirectory($controllerDirectory);

            // create the file
            $filePath = "$controllerDirectory/{$controllerName}Controller.php";

            $this->createFile($filePath, $content, 'Admin Controller');
        }
    }
}
