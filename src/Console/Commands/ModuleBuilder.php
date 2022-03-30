<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Support\Str;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Console\Postman;
use HZ\Illuminate\Mongez\Console\Markdown;
use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;

// class ModuleBuilder extends EngezGeneratorCommand
class ModuleBuilder extends EngezGeneratorCommand implements EngezInterface
{
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
                                       {--controller=}
                                       {--model=}
                                       {--translatable=true}
                                       {--data=}
                                       {--resource=}
                                       {--repository=}
                                       ' . EngezGeneratorCommand::DATA_TYPES_OPTIONS
        . EngezModel::MODEL_OPTIONS
        . EngezController::CONTROLLER_OPTIONS
        . EngezGeneratorCommand::TABLE_INDEXES_OPTIONS;

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
        $this->setModuleName($this->argument('moduleName'));

        if ($this->optionHasValue('data')) {
            $this->terminate('data option is deprecated, use instead: --string | --int | --bool | --date | --uploads');
        }

        if ($this->optionHasValue('parent')) {
            $this->info['parent'] = $this->option('parent');
        }

        $this->validateArguments();

        $this->init();

        $this->create();
    }

    /**
     * Init data
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        if (in_array('users', Mongez::getStored('modules'))) {
            $this->isUserModuleExits = true;
        }

        $this->info('Preparing data...');

        $this->initData();
    }

    /**
     * Validate command arguments
     *
     * @return void
     */
    public function validateArguments()
    {
        $modulePath = $this->modulePath("");

        // if the module path exists and it has no parent, then terminate the command
        if ($this->files->isDirectory($modulePath) && !isset($this->info['parent'])) {
            $this->terminate('This module is already exist');
        }

        // check if the module directory exists
        // if so, terminate the command
        if (isset($this->info['parent']) && !$this->moduleExists($this->info['parent'])) {
            $this->terminate('This parent module is not available');
        }

        if ($this->moduleExists()) {
            return $this->terminate('This module is already created');
        }
    }

    /**
     * Create files
     *
     * @return void
     */
    public function create()
    {
        $this->addModule();
        $this->info('Creating filter class');
        $this->createFilter();

        $this->info('Creating controller file');
        $this->createController();

        $this->info('Creating resource file');
        $this->createResource();

        $this->info('Creating model file');
        $this->createModel();

        if ($this->optionHasValue('translatable')) {
            $this->info('Creating translation files..');
            $this->createTranslation();
        }

        $this->info('Creating repository file');
        $this->createRepository();

        if (!$this->hasParentModule()) {
            $this->createServiceProvider();
        }

        // $this->info('Creating database files');
        // $this->createDatabase();

        $this->info('Planting seeds...');
        $this->createSeeder();

        // $this->info('Generating Module Postman File');
        // $this->generatePostmanModule();

        // $this->info('Generating Module Docs');
        // $this->generateModuleDocs();

        // if ($this->isUserModuleExits) {
        //     $this->addRoutesToPermissionTable();
        // }

        $this->info('Creating tests file');
        $this->createTest();

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
        foreach (static::DATA_TYPES as $option) {
            if ($this->optionHasValue($option)) {
                $value = $this->option($option);
                $this->info[$option] = explode(',', $value);
            }
        }
    }

    /**
     * Create controller file
     *
     * @return void
     */
    protected function createController()
    {
        $parent = $this->topParentModule();
        $module = $this->getModule();

        $controllerOptions = [
            'controller' => $module,
            '--module' => $parent,
            '--repository' => $this->repositoryName($module),
            '--route' => $module,
        ];

        if ($parent !== $module) {
            $controllerOptions['--parent'] = $parent;
        }

        $options = $this->optionsValues(EngezController::CONTROLLER_OPTIONS_LIST);

        $this->call('engez:controller', array_merge($controllerOptions, $options));
    }

    /**
     * Create test file
     *
     * @return void
     */
    protected function createTest()
    {
        $parent = $this->topParentModule();
        $module = $this->getModule();

        $testOptions = [
            'test' => $module,
            '--module' => $parent,
        ];

        $this->call('engez:test', $testOptions);
    }

    /**
     * Create the resource file
     *
     * @return void
     */
    protected function createResource()
    {
        $resourceOptions = [
            'resource' => $this->singularModule(),
            '--module' => $this->topParentModule(),
        ];

        $this->call(
            'engez:resource',
            $this->withDataTypes($resourceOptions)
        );
    }

    /**
     * Create the repository file
     *
     * @return void
     */
    protected function createRepository()
    {
        $module = $this->singularModule();

        $repositoryOptions = [
            'repository' => $this->repositoryName($this->moduleName),
            '--module' => $this->topParentModule(),
            '--model' => $module,
            '--resource' => $module . 'Resource',
            '--filter' => $module . 'Filter',
        ];


        $this->call(
            'engez:repository',
            $this->withDataTypes($repositoryOptions)
        );
    }

    /**
     * Create the Seeder file
     *
     * @return void
     */
    protected function createSeeder()
    {
        $repositoryOptions = [
            'seeder' => $this->singularModule(),
            '--module' => $this->topParentModule(),
            '--repository' => $this->repositoryName($this->getModule()),
        ];

        $this->call(
            'engez:seeder',
            $repositoryOptions
            // $this->withDataTypes($repositoryOptions)
        );
    }

    /**
     * Create the model file
     *
     * @return void
     */
    protected function createModel()
    {
        $modelName = $this->singularModule();

        $modelOptions = [
            'model' => $modelName,
            '--module' => $this->topParentModule(),
        ];

        $this->call(
            'engez:model',
            $this->withDataTypes(
                $modelOptions,
                array_merge(EngezGeneratorCommand::TABLE_INDEXES, EngezModel::MODEL_OPTIONS_LIST)
            )
        );
    }

    /**
     * Create the translation files
     *
     * @return void
     */
    protected function createTranslation()
    {
        $options = [
            'file' => $this->singularModule(),
            '--module' => $this->topParentModule(),
        ];

        $this->call('engez:translation', $options);
    }

    /**
     * Create module service provider
     *
     * @return void
     */
    protected function createServiceProvider()
    {
        $types = $this->option('type');

        if ($types == 'all') {
            $types = 'admin,site';
        }

        $replacements = [
            // Module Name, also the class name suffixed with ServiceProvider
            '{{ ModuleName }}' => $moduleName = $this->getModule(),
            // routes types
            '{{ routesTypes }}' => $this->stubStringAsArray($types),
            // build mode: api|ui
            '{{ buildMode }}' => $this->buildMode,
            '{{ viewable }}' => $this->buildMode === 'ui' ? $this->replaceStub('Providers/viewable', [
                '{{ viewName }}' => $this->kebab($this->getModule()),
            ]) : '',
            '{{ translatable }}' => $this->optionHasValue('translatable') ? $this->replaceStub('Providers/translatable', [
                '{{ translationName }}' => $this->studly($this->getModule()),
            ]) : '',
        ];

        $this->putFile("Providers/{$moduleName}ServiceProvider.php", $this->replaceStub('Providers/provider', $replacements), 'Provider');

        $this->updateServiceProviderConfig();
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
     * Create Module Filters
     *
     * @return void
     */
    public function createFilter()
    {
        $modelOptions = [
            'filter' => $this->getModule(),
            '--module' => $this->topParentModule(),
        ];

        $this->call(
            'engez:filter',
            $modelOptions,
        );
    }

    /**
     * Generate module postman.
     *
     * @return void
     */
    protected function generatePostmanModule()
    {
        $data = [];

        $uploads = '';

        if ($this->optionHasValue('uploads')) {
            $uploads = $this->option('uploads');
        }

        $dataOptions = [
            'string' => 'String',
            'float' =>  'Float',
            'bool'   =>  'Bool',
            'int'    =>  'Int'
        ];

        $options = [];

        foreach ($dataOptions as $dataOption => $value) {
            if ($this->optionHasValue($dataOption)) {
                foreach (explode(',', $this->option($dataOption)) as $option) {
                    $options[$option] = $value;
                }
            }
        }

        $parent = '';

        if (isset($this->info['parent'])) {
            $parent = $this->info['parent'];
        }

        $moduleName = $this->getModule();

        $postman =  new Postman([
            'modelName'  => $moduleName,
            'data'       => array_merge($data, $options),
            'uploads'    => $uploads,
            'parent'     => $parent
        ]);

        $path = $this->modulePath("docs");

        $this->makeDirectory($path);

        $fileName = strtolower($moduleName) . '.postman.json';
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

        $dataOptions = [
            'int' =>  'Int',
            'bool' =>  'Bool',
            'float' =>  'Float',
            'uploads' => 'File',
            'string' => 'String',
        ];

        $options = [];

        foreach ($dataOptions as $dataOption => $value) {
            if ($this->optionHasValue($dataOption)) {
                foreach (explode(',', $this->option($dataOption)) as $option) {
                    $options[$option] = $value;
                }
            }
        }

        $markDownOption = [
            'data' => array_merge($data, $options),
            'moduleName' => $this->getModule(),
        ];

        if (isset($this->info['parent'])) {
            $markDownOption['parent'] = $this->info['parent'];
        }

        $markDown =  new Markdown($markDownOption);

        $moduleFileName = 'README.md';

        if (isset($this->info['parent'])) {
            $moduleFileName = $this->singularModule() . '.md';
        }

        $path = $this->modulePath("docs");

        $content = $markDown->getContent();

        $this->createFile("{$path}/{$moduleFileName}", $content, 'Docs');
    }

    /**
     * Determine if current generated module is a subset of parent module
     *
     * @return bool
     */
    protected function hasParentModule(): bool
    {
        return $this->optionHasValue('parent') && $this->option('parent') !== '';
    }

    /**
     * Get top module name
     * If parent exists, then return the parent, otherwise return the original module
     *
     * @return string
     */
    protected function topParentModule(): string
    {
        return $this->hasParentModule() ? $this->plural(
            $this->studly($this->option('parent'))
        ) : $this->getModule();
    }
}
