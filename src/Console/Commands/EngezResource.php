<?php
namespace HZ\Illuminate\Mongez\Console\Commands;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;
use HZ\Illuminate\Mongez\Contracts\Console\EngezInterface;

class EngezResource extends Command implements EngezInterface
{
    use EngezTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:resource {resource} 
                                           {--module=} 
                                           {--data=}
                                           {--uploads=}
                                           {--parent=} 
                                           ';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new resource to specific module';

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
        $this->info('resource created successfully');
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
            return $this->missingRequiredOption('module option is required');
        }
        if (! in_array(strtolower($this->info['moduleName']), $availableModules)) {
            return $this->missingRequiredOption('This module is not available');
        }

        if ($this->option('parent')) {
            if (! in_array(strtolower($this->info['parent']), $availableModules)) {
                return Command::error('This parent module is not available');
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
        $this->info['resource'] = Str::studly($this->argument('resource'));
        $this->info['moduleName'] = Str::studly($this->option('module'));

        $this->info['data'] = explode(",",$this->option('data')) ?: [];
    
        if ($this->hasOption('parent')) {
            $this->info['parent'] = $this->option('parent');
        }
    }
    
    /**
     * Create the repository file
     * 
     * @return void
     */
    public function create()
    {
        $resource = $this->info['resource'];

        $resourceName = basename(str_replace('\\', '/', $resource));

        $resourcePath = dirname($resource);

        $content = File::get($this->path("Resources/resource.php"));

        // make it singular 
        $resourceName = Str::singular($resourceName);

        // share it
        $this->info['resourceName'] = $resourceName;

        // replace resource name
        $content = str_ireplace("ResourceName", "{$resourceName}", $content);

        $targetModule = $this->info['moduleName'];    
        if (isset($this->info['parent'])) {
            $targetModule = str::studly($this->info['parent']);
        }

        // replace module name
        $content = str_ireplace("ModuleName", $targetModule, $content);

        $dataList = '';
        
        if (!empty($this->info['data'])) {
            // add the id to the list if not provided
            if (!in_array('id', $this->info['data'])) {
                array_unshift($this->info['data'], 'id');
            }

            $dataList = "'" . implode("', '", $this->info['data']) . "'";
        }

        // replace resource data
        $content = str_ireplace("DATA_LIST", $dataList, $content);

        // check for assets 

        $assetsList = '';

        if (!empty($this->info['uploads'])) {
            $assetsList = "'" . implode("', '", $this->info['uploads']) . "'";
        }

        // replace resource data
        $content = str_ireplace("ASSETS_LIST", $assetsList, $content);

        $resourceDirectory = $this->modulePath("Resources");

        $this->checkDirectory($resourceDirectory);

        $this->info['resourcePath'] = $resourcePath . '\\' . $resourceName;

        // create the file
        $this->createFile("$resourceDirectory/{$resourceName}.php", $content, 'Resource');
    }
}
