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
    const CONTROLLER_TYPES = ['admin', 'site', 'all'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:controller  {controller} 
                                               {--parent=}
                                               {--module=} 
                                               {--build=} 
                                               {--type=all}
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
        $this->info('Controller has been created successfully.');
    }

    /**
     * Validate The module name
     *
     * @return void
     */
    public function validateArguments()
    {
        $controllerName = basename(str_replace('\\', '/', $this->info['controllerName']));
        if (File::exists($this->modulePath("Controllers/Admin/{$controllerName}Controller.php"))) {
            Command::error('You already have this controller');
            die();
        }

        $availableModules = Mongez::getStored('modules');
        if (!$this->optionHasValue('module')) {
            return $this->missingRequiredOption('Module option is required');
        }

        if (!in_array(strtolower($this->info['moduleName']), $availableModules)) {
            return $this->missingRequiredOption('This module is not available');
        }

        if (!in_array($this->info['type'], static::CONTROLLER_TYPES)) {
            return $this->missingRequiredOption('This controller type does not exits');
        }

        if ($this->optionHasValue('parent')) {
            if (!in_array(strtolower($this->info['parent']), $availableModules)) {
                Command::error('This parent module is not available');
                die();
            }
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
        $this->info['build'] = $this->option('build') ?: config('mongez.module-builder.build', 'api');
        $this->info['type'] = $this->option('type');

        $repositoryName = $this->info['controllerName'];

        if ($this->optionHasValue('repository')) {
            $repositoryName = $this->option('repository');
        }

        $this->info['repositoryName'] = $repositoryName;

        if ($this->optionHasValue('parent')) {
            $this->info['parent'] = $this->option('parent');
        }
    }

    /**
     * Create controller File. 
     *
     * @return void
     */
    public function create()
    {
        $controllerType = $this->info['type'];

        if (in_array($controllerType, ['all', 'site'])) {
            $this->createController('site');
        }

        if (in_array($controllerType, ['all', 'admin'])) {
            $this->createController('admin');
        }
    }

    /**
     * Create a controller for the given type
     * 
     * @param  string $controllerType
     * @return void
     */
    private function createController(string $controllerType)
    {
        $controller = $this->info['controllerName'];

        $controllerName = basename(str_replace('\\', '/', $controller));

        $targetModule = $this->info['moduleName'];

        if (isset($this->info['parent'])) {
            $targetModule = str::studly($this->info['parent']);
        }

        // admin controller
        $this->info("Creating $controllerType controller...");

        $bigControllerType = ucfirst($controllerType);

        $content = File::get($this->path("Controllers/$bigControllerType/controller.php"));

        // replace base controller name
        $baseController = null;
        $view = '';

        if ($controllerType === 'admin') {
            $baseController = $this->info['build'] === 'api' ? 'AdminApiController' : 'AdminViewController';
        } else {
            $baseController = $this->info['build'] === 'api' ? 'ApiController' : 'ViewController';
        }

        if ($this->info['build'] === 'view') {
            $viewModule = Str::camel($targetModule);
            if ($controllerType === 'site') {
                $this->put($this->modulePath("views/site/list.blade.php"), '//');
                $this->put($this->modulePath("views/site/show.blade.php"), '//');
                $view =
                    "   /**
     * View path
     * 
     * @var string
     */
    protected const VIEW_PATH = '{$viewModule}::site';";
            } else {
                $this->put($this->modulePath("views/admin/list.blade.php"), '//');
                $this->put($this->modulePath("views/admin/form.blade.php"), '//');
                $view = "'view-path' => '{$viewModule}::admin',";
            }
        }

        $content = str_ireplace("VIEW", $view, $content);
        $content = str_ireplace("BaseController", $baseController, $content);

        // replace controller name
        $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);

        // replace module name
        $content = str_ireplace("ModuleName", $targetModule, $content);

        // repository name  
        $content = str_ireplace('repo-name', $this->repositoryShortcutName($this->info['repositoryName']), $content);

        $controllerDirectory = $this->modulePath("Controllers/$bigControllerType");

        $this->checkDirectory($controllerDirectory);

        // create the file
        $filePath = "$controllerDirectory/{$controllerName}Controller.php";

        $this->createFile($filePath, $content, "$bigControllerType Controller");
    }

    /**
     * Put the given content in the given path
     * 
     * @param string $path
     * @param string $content
     * @return void
     */
    protected function put(string $path, string $content)
    {
        $this->checkDirectory(dirname($path));
        File::put($path, $content);
    }
}
