<?php
namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use HZ\Illuminate\Mongez\Helpers\Mongez;

class EngezMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:migration {moduleName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations on target module';

    /**
     * The module name
     *
     * @var string
     */
    protected $module;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->module = Str::studly($this->argument('moduleName'));
        
        if (! $this->validateModuleName()) {
            return $this->missingRequiredOption('This module is not available');
        }
        
        $this->makeMigrationFile();
    }

    /**
     * Validate The module name
     *
     * @return void
     */
    protected function validateModuleName()
    {
        $availableModules = Mongez::getStored('modules');

        return in_array($this->module, $availableModules['modules']);
    }

    /**
     * Make migration file for module
     *
     * @return void
     */
    protected function makeMigrationFile()
    {
        $fileName = strtolower(str::plural($this->module));

        $path = app_path("Modules\\{$this->module}\\database\\migrations");
        
        $fileName = date('Y_m_d_His') . '_' . $fileName;

        Artisan::call('make:migration', ['name' => $fileName, '--table' => $fileName, '--path' => $path]);

        return $this->info('Migration has been created Successfully');
    }
}
