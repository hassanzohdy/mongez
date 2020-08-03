<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;
use HZ\Illuminate\Mongez\Contracts\Console\EngezInterface;

class EngezRepository extends Command implements EngezInterface
{
    use EngezTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:repository
                                                {repository} 
                                                {--module=}
                                                {--model=}
                                                {--data=}
                                                {--uploads=}
                                                {--int=}
                                                {--bool=}
                                                {--float=}
                                                {--resource=}
                                                {--parent=}
                                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new repository to specific module';

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

        $this->info('Updating configurations...');
        $this->updateConfig();

        $this->info('Repository created successfully');
    }

    /**
     * Validate The module name
     *
     * @return void
     */
    public function validateArguments()
    {
        $availableModules = Mongez::getStored('modules');

        if (!$this->option('module')) {
            return $this->missingRequiredOption('module option is required');
        }

        if (!in_array(strtolower($this->info['moduleName']), $availableModules)) {
            return $this->missingRequiredOption('This module is not available');
        }

        if ($this->option('parent')) {
            if (! in_array(strtolower($this->info['parent']), $availableModules)) {
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
        $this->info['repository'] = Str::studly($this->argument('repository'));
        $this->info['repositoryName'] = Str::camel(basename(str_replace('\\', '/', $this->info['repository'])));
        $this->info['moduleName'] = Str::studly($this->option('module'));

        $this->info['modelName'] = Str::singular($this->option('model') ?: $this->option('module'));

        $this->info['resourceName'] = Str::singular($this->option('model') ?: $this->option('module'));
        
        if ($this->optionHasValue('parent')) {
            $this->info['parent'] = $this->option('parent');
        }

        if ($this->optionHasValue('data')) {
            $this->info['data'] = explode(",",$this->option('data'));
        }

        if ($this->optionHasValue('uploads')) {
            $this->info['uploads'] = explode(",",$this->option('uploads'));
        }

        if ($this->optionHasValue('bool')) {
            $this->info['bool'] = explode(",",$this->option('bool'));
        }

        if ($this->optionHasValue('float')) {
            $this->info['float'] = explode(",",$this->option('float'));
        }

        if ($this->optionHasValue('int')) {
            $this->info['int'] = explode(",",$this->option('int'));
        }
    }

    /**
     * Create the repository file
     * 
     * @return void
     */
    public function create()
    {
        $repository = $this->info['repository'];

        $repositoryName = basename(str_replace('\\', '/', $repository));

        $database = config('database.default');

        $content = File::get($this->path("Repositories/{$database}-repository.php"));

        // replace repository name
        $content = str_ireplace("RepositoryName", Str::camel($repositoryName), $content); 
        
        // replace filter name 
        $content = str_ireplace("FilterName", $this->info['modelName'], $content); 

        $targetModule = $this->info['moduleName'];    
        if (isset($this->info['parent'])) {
            $targetModule = str::studly($this->info['parent']);
        }

        // replace module name
        $content = str_ireplace("ModuleName", $targetModule, $content);

        // replace model path
        $content = str_ireplace("ModelName", $this->info['modelName'], $content);

        // replace resource path
        $content = str_ireplace("ResourceName", $this->info['resourceName'], $content);

        $content = str_ireplace('repo-name', $this->adjustRepositoryName($this->info['repository']), $content);

        $dataList = '';
        
        if (!empty($this->info['data'])) {
            if (in_array('id', $this->info['data'])) {
                $this->info['data'] = Arr::remove($this->info['data'], 'id');
            }

            $dataList = "'" . implode("', '", $this->info['data']) . "'";
        }
        
        // replace repository data
        $content = str_ireplace("DATA_LIST", $dataList, $content);

        // uploads data
        $uploadsList = '';
        if (!empty($this->info['uploads'])) {
            $uploadsList = "'" . implode("', '", $this->info['uploads']) . "'";
        }

        // int data
        $intList = '';
        if (!empty($this->info['int'])) {
            $intList = "'" . implode("', '", $this->info['int']) . "'";
        }
        
        // float data
        $floatList = '';
        if (!empty($this->info['float'])) {
            $floatList = "'" . implode("', '", $this->info['float']) . "'";
        }

        // bool data
        $boolList = '';
        if (!empty($this->info['bool'])) {
            $boolList = "'" . implode("', '", $this->info['bool']) . "'";
        }
        // replace repository bool
        $content = str_ireplace("BOOL_LIST", $boolList, $content);
        // replace repository float
        $content = str_ireplace("FLOAT_LIST", $floatList, $content);
        // replace repository integer
        $content = str_ireplace("INTEGER_LIST", $intList, $content);


        // replace repository Upload
        $content = str_ireplace("UPLOADS_LIST", $uploadsList, $content);

        $repositoryDirectory = $this->modulePath("Repositories/");

        $this->checkDirectory($repositoryDirectory);

        // create the file
        $this->createFile("$repositoryDirectory/{$repositoryName}Repository.php", $content, 'Repository');
    }
}
