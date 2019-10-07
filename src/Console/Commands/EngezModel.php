<?php
namespace HZ\Illuminate\Mongez\Console\Commands;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;
use HZ\Illuminate\Mongez\Contracts\Console\EngezInterface;

class EngezModel extends Command implements EngezInterface
{
    use EngezTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:model {model} 
                                        {--module=} 
                                        {--data=}
                                        {--uploads=}
                                        {--index=}
                                        {--int=}
                                        {--table=}                                        
                                        {--unique=}
                                        {--index=}
                                        {--parent=}
                                        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new model to specific module';

    /**
     * The database name 
     * 
     * @var string
     */
    protected $databaseName;
    
    /**
     * The module name
     *
     * @var array
     */
    protected $availableModules = [];
    
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

        if ($this->databaseName == 'mongodb' && $this->option('data')) {
            $this->createSchema();
        }
        $this->createMigration();
        $this->info('Model created successfully');
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
            return $this->info('module option is required');
        }

        if (! in_array(strtolower($this->info['moduleName']), $availableModules)) {
            return $this->info('This module is not available');
        }

        if ($this->option('parent')) {
            if (! in_array(strtolower($this->info['parent']), $availableModules)) {
                Command::error('This parent module is not available');
                die();
            }    
        }
    }

    /**
     * Set controller info.
     * 
     * @return void
     */
    public function init()
    {
        $this->databaseName = config('database.default');

        $this->info['modelName'] = Str::studly($this->argument('model'));
        $this->info['moduleName'] = Str::studly($this->option('module'));    
        
        if ($this->optionHasValue('parent')) {
            $this->info['parent'] = $this->option('parent');
        }
    }
    
    /**
     * Create Model 
     *
     * @return void
     */
    public function create()
    {
        $model = $this->info['modelName'];

        $modelName = basename(str_replace('\\', '/', $model));

        // make it singular 
        
        $modelName = Str::singular($modelName);

        $this->info['modelName'] = $modelName;
        
        $modelPath = dirname($model);

        $modelPath = array_map(function ($segment) {
            return Str::singular($segment);
        }, explode('\\', $modelPath));

        $modelPath = implode('\\', $modelPath);

        $content = File::get($this->path("Models/model.php"));

        // replace model name
        $content = str_ireplace("ModelName", "{$modelName}", $content);

        // replace database name 
        $content = str_replace('DatabaseName', $this->databaseName, $content);

        // replace module name
        $targetModule = $this->info['moduleName'];   
        if (isset($this->info['parent'])) {
            $targetModule = str::studly($this->info['parent']);
        }
        
        $content = str_ireplace("ModuleName", $targetModule, $content);
        
        $modelDirectory = $this->modulePath("Models/");

        $this->checkDirectory($modelDirectory);

        $this->info['modelPath'] = $modelPath . '\\' . $modelName;
        // create the file
        $this->createFile("$modelDirectory/{$modelName}.php", $content, 'Model');
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
            'id'=>'int', 
        ];
        
        $databaseFileName = strtolower(str::plural($this->info['moduleName']));
        
        $path = $this->modulePath("database/migrations");
        
        $customData = explode(',', $this->option('data')) ?? [];
        
        unset($customData['id'], $customData['_id']);

        $customData = array_fill_keys($customData, 'string');
        
        $content = array_merge($defaultContent, $customData);   

        $this->createFile("$path/{$databaseFileName}.json", json_encode($content, JSON_PRETTY_PRINT), 'Schema');
    }
}
