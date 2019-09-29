<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;

class EngezMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:migrate {modules?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations to modules';

    /**
     * The module name
     *
     * @var array
     */
    protected $availableModules = [];

    /**
     * The module path
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argumentHasValue('modules')) {
            $this->availableModules = explode(',', $this->argument('modules'));
        } else {
            $this->paths[] = Mongez::packagePath('src/database/migrations/' . config('database.default'));
            $this->availableModules = Mongez::getStored('modules');
        }

        $this->generateModulesPaths();
        $this->makeMigrate();
    }

    /**
     * Make migration file for module
     *
     * @return void
     */
    protected function makeMigrate()
    {
        Artisan::call('migrate', ['--path' => $this->paths, '--realpath' => true]);

        return $this->info('Migrate tables has been created Successfully ');
    }

    /**
     * Generate Module path 
     * 
     * @return void
     */
    protected function generateModulesPaths()
    {
        foreach ($this->availableModules as $moduleName) {
            $this->paths[] = app_path("Modules/{$moduleName}/database/migrations");
        }
    }
}
