<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;

class EngezModel extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * List of options that can be used in other generators like the module builder
     * 
     * @const string
     */
    public const MODEL_OPTIONS = '
    {--share=}
    ';

    /**
     * List of options text only
     * 
     * @const array
     */
    public const MODEL_OPTIONS_LIST = ['share'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:model {model} 
                                        {--module=} 
                                        ' . EngezGeneratorCommand::DATA_TYPES_OPTIONS
        . EngezGeneratorCommand::TABLE_INDEXES_OPTIONS
        . EngezModel::MODEL_OPTIONS;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new model to specific module';

    /**
     * The model name 
     * 
     * @var string
     */
    protected string $modelName;

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

        $this->info('Model created successfully');
    }

    /**
     * Prepare data
     * 
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->setModuleName($this->option('module'));

        $this->modelName = $this->modelClass($this->argument('model'));
    }

    /**
     * Create Model 
     *
     * @return void
     */
    public function create()
    {
        $this->createModel();
        $this->info('Creating Migration File');
        $this->createMigration();
    }

    /**
     * Create the model file
     * 
     * @return void
     */
    protected function createModel()
    {
        // make it singular 

        $modelName = $this->modelName;

        $data = [];

        // Add shared info constant in mongodb driver
        if ($this->isMongoDB()) {
            $sharedColumns = $this->optionHasValue('share') ? $this->option('share') : 'id';

            $data[] = $this->replaceStub('Models/shared-info', [
                '{{ columns }}' => $this->stubStringAsArray($sharedColumns),
            ]);
        }

        if ($this->optionHasValue('date')) {
            $dateColumns = explode(',', $this->option('date'));

            foreach ($dateColumns as $key => $dateColumn) {
                $index = $key;
                $key = $dateColumn;
                $dateColumn = 'datetime'; 
                $dateColumns[$key] = $dateColumn;
                unset($dateColumns[$index]);
            }

            $data[] = $this->replaceStub('Models/casts', [
                '{{ columns }}' => $this->stubStringAsArray($dateColumns, true),
            ]);
        }

        $replaces = [
            // replace model name
            '{{ ModelName }}' => $modelName,
            // replace database name 
            '{{ DatabaseName }}' => $this->getDatabaseName(),
            // replace module name
            '{{ ModuleName }}' => $this->getModule(),
            // replace data
            '{{ data }}' => $this->stubData($data, "\t//"),
        ];

        $this->putFile("Models/$modelName.php", $this->replaceStub('Models/model', $replaces), 'Model');
    }

    /**
     * Create migration file of table 
     *
     * @param string $dataFileName
     * @return void 
     */
    protected function createMigration()
    {
        $moduleWithModelName = $this->snake($this->getModule() . $this->plural($this->modelName));

        $migrationsOptions = [
            'migrationName' => 'create_' . $moduleWithModelName . '_table',
            '--module' => $this->option('module'),
            '--table' => strtolower($this->plural($this->modelName)),
        ];

        Artisan::call('engez:migration', $this->withDataTypes($migrationsOptions, EngezGeneratorCommand::TABLE_INDEXES));
    }

    /**
     * Create schema of table in mongo 
     *
     * @param string $dataFileName
     * @return void 
     */
    protected function createSchema()
    {
        $defaultContent = [
            '_id' => "objectId",
            'id' => 'int',
        ];

        $path = $this->modulePath("Database/migrations");

        $this->makeDirectory($path);

        $stringData = explode(',', $this->option('string')) ?? [];

        unset($stringData['id'], $stringData['_id']);

        $stringData = array_fill_keys($stringData, 'string');

        $content = array_merge($defaultContent, $stringData);

        $this->createFile("$path/{$this->modelName}.json", json_encode($content, JSON_PRETTY_PRINT), 'Schema');
    }
}
