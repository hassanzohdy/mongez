<?php

namespace HZ\Illuminate\Organizer\Console\Commands;

use File;
use Illuminate\Console\Command;

class CloneModuleBuilder extends Command
{
    /**
     * Set all available modules.
     * 
     * @const array
     */
    const AVAILABLE_MODULES = ['users'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clone:module {moduleName}';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->moduleName = $this->argument('moduleName');

        if (! in_array($this->moduleName, static::AVAILABLE_MODULES)) {
            return $this->info('This module does not exits');
        }

        $modulePath = $this->modulePath();

        if ($this->checkDirectory($modulePath)) {
            $message = $this->confirm($this->moduleName . ' exists, Do you want to override it?');

            if (!$message) return;
        }

        $this->cloneModule();
    }

    /**
     * Copy files of module
     * 
     * @return void
     */
    protected function cloneModule()
    {
        $modulePath = dirname(__DIR__, 6) . '/' . 'cloneable-modules/' . $this->moduleName;
        
        File::copyDirectory($modulePath, base_path("app/Modules/" . $this->moduleName));
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