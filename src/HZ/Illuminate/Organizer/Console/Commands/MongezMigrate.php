<?php
namespace HZ\Illuminate\Organizer\Console\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MongezMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:mongez-migrate {--path=}';

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
        if ($this->option('module') ) {
            $this->availableModules = explode(',', $this->argument('module'));
        } else {
            $availableModulesFilePath = storage_path('mongez/mongez.json');
            $this->availableModules = File::getJson($availableModulesFilePath)['modules'];
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
        Artisan::call('migrate', ['--path' => $this->paths]);
        return $this->info('Migrate tables has been created Successfully ');
    }

    /**
     *
     */
    protected function generateModulesPaths()
    {
        foreach ($this->availableModules as $moduleName) {
            $this->paths[] = "app\\Modules\\{$moduleName}\\database\\migrations";
        }
    }
}
