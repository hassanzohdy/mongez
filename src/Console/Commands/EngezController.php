<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;
use HZ\Illuminate\Mongez\Console\Traits\RoutesAdapter;

class EngezController extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * The adapter creates or updates the routes file for the module
     */
    use RoutesAdapter;

    /**
     * Controller options
     *
     * @const string
     */
    public const CONTROLLER_OPTIONS = '
    {--build=}
    {--auth=true}
    {--route=}
    {--with-service=}
    {--type=all}
    {--request=}
    ';

    /**
     * Controller options list
     *
     * @const array
     */
    public const CONTROLLER_OPTIONS_LIST = [
        'build', 'auth', 'type', 'route',
    ];

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
                                               {--module=} 
                                               {--serviceClass=} 
                                               {--service=} 
                                               {--parent=} 
                                               {--repository=}' . EngezController::CONTROLLER_OPTIONS;

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
     * Controller name
     *
     * @var string
     */
    protected string $controllerName;

    /**
     * Controller type
     * Available Values: site|admin|all
     *
     * @var string
     */
    protected string $controllerType;

    /**
     * The path of the generated controller
     *
     * @var string
     */
    protected string $controllerPath;

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
     * Set controller info
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->setModuleName($this->option('module'));

        $this->controllerName = $this->plural(
                $this->studly(
                    str_replace('Controller', '', $this->argument('controller'))
                )
            ) . 'Controller';

        $this->controllerType = $this->option('type');
    }

    /**
     * Validate The module name
     *
     * @return void
     */
    public function validateArguments()
    {
        parent::validateArguments();

        if ($this->isAdminController()) {
            if ($this->files->exists($this->modulePath('Controllers/Admin/' . $this->controllerName . '.php'))) {
                $this->terminate('You already have this controller');
            }
        }

        if ($this->isSiteController()) {
            if ($this->files->exists($this->modulePath('Controllers/Site/' . $this->controllerName . '.php'))) {
                $this->terminate('You already have this controller');
            }
        }

        if (!in_array($this->option('type'), static::CONTROLLER_TYPES)) {
            return $this->missingRequiredOption('This controller type does not exits, Did you mean? ' . implode(PHP_EOL, static::CONTROLLER_TYPES));
        }
    }

    /**
     * Create controller File.
     *
     * @return void
     */
    public function create()
    {
        if ($this->isSiteController()) {
            $this->createController('site');
        }

        if ($this->isAdminController()) {
            $this->createController('admin');
        }

        $this->info('Generating routes files');

        $this->createRoutes();
    }

    /**
     * Create a controller for the given type
     *
     * @param  string $controllerType
     * @return void
     */
    private function createController(string $controllerType)
    {
        $capitalizeControllerType = ucfirst($controllerType);

        $this->controllerPath = "Controllers/{$capitalizeControllerType}/{$this->controllerName}.php";

        // admin controller
        $this->info("Creating $controllerType controller...");

        $moduleName = $this->getModule();

        if ($this->optionHasValue('request')) {
            $requestOptions = $this->option('request');
            $storeRequest = $requestOptions['store'];
            $updateRequest = $requestOptions['update'];
            $patchRequest = $requestOptions['patch'];
        }

        $replaces = [
            // replace the controller name
            '{{ ControllerName }}' => $this->controllerName,
            // replace module name
            '{{ ModuleName }}' => $moduleName,
            // service
            '{{ serviceClass }}' => $this->optionHasValue('serviceClass') ? 'use ' . $this->option('serviceClass') . ';' : "''",
            '{{ serviceName }}' => $this->option('service') ?: "''",
            // repository
            '{{ repositoryName }}' =>  $this->optionHasValue('repository') ? $this->repositoryName($this->option('repository')) : "''",
            '{{ storeRequestName }}' => $storeRequest,
            '{{ updateRequestName }}' => $updateRequest,
            '{{ patchRequestName }}' => $patchRequest,
        ];

        // create the file

        $controllerStub = 'Controllers/' . $capitalizeControllerType . '/';

        if ($this->isApiMode()) {
            $controllerStub .= 'api-controller';
        } else {
            $controllerStub .= 'ui-controller';
        }

        $this->putFile($this->controllerPath, $this->replaceStub($controllerStub, $replaces), 'Controller');
    }

    /**
     * Determine if current generated controller is admin controller
     * This is true when type is admin or all
     *
     * @return bool
     */
    protected function isAdminController(): bool
    {
        return in_array($this->option('type'), ['all', 'admin']);
    }

    /**
     * Determine if current generated controller is site controller
     * This is true when type is site or all
     *
     * @return bool
     */
    protected function isSiteController(): bool
    {
        return in_array($this->option('type'), ['all', 'site']);
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
        $this->makeDirectory(dirname($path));
        $this->files->put($path, $content);
    }
}
