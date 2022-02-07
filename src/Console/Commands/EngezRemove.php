<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Console\Traits\EngezTrait;

class EngezRemove extends Command
{
    use EngezTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:remove {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove existing module';

    /**
     * info used for removing module 
     * 
     * @var array 
     */
    protected $info = [];

    /**
     * Module name
     * 
     * @var string
     */
    protected $moduleName;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();
        $this->validateArguments();
        $this->removeModuleDirectory();
        $this->removeModuleConfig();
        $this->info('Module removed successfully');
    }

    /**
     * Set controller info
     * 
     * @return void
     */
    public function init()
    {
        $this->moduleName = $this->info['moduleName'] = Str::studly($this->argument('module'));
    }

    /**
     * Validate The module name
     *
     * @return void
     */
    protected function validateArguments()
    {
        $availableModules = Mongez::getStored('modules');
        if (!in_array(strtolower($this->info['moduleName']), $availableModules)) {
            Command::error('This module is not available');
            die();
        }
    }

    /**
     * Remove the module directory
     *  
     * @return void
     */
    protected function removeModuleDirectory()
    {
        $targetModule = $this->modulePath("");
        File::deleteDirectory($targetModule);
    }

    /**
     * Remove Module config
     * 
     * @return void
     */
    protected function removeModuleConfig()
    {
        $this->unsetModuleServiceProvider();
        $this->unsetModuleNameFromMongez();
    }
}
