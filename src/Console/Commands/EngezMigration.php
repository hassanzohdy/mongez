<?php
namespace HZ\Illuminate\Mongez\Console\Commands;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;
use HZ\Illuminate\Mongez\Contracts\Console\EngezInterface;

class EngezMigration extends Command implements EngezInterface
{
    use EngezTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:migration {moduleName} {--data=} {--uploads=} {--index=} {--unique=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations on target module';

    /**
     * info used for creating controller 
     * 
     * @var array 
     */
    protected $info = [];

    /**
     * Module directory path
     * 
     * @var string
     */
    protected $root;

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
        $this->info('Migration has been created Successfully');
    }
    
    /**
     * Set Migration info
     * 
     * @return void
     */
    public function init()
    {
        $this->root = Mongez::packagePath();
        
        $this->info['moduleName'] = Str::studly($this->argument('moduleName'));        
        $this->info['index'] =  [];
        $this->info['unique'] =  [];
        $this->info['uploads'] = [];

        if ($this->hasOption('index')) {
            $this->info['index'] = explode(',', $this->option('index'));
        }

        if ($this->hasOption('unique')) {
            $this->info['unique'] = explode(',', $this->option('unique'));
        }

        if ($this->hasOption('data')) {
            $this->info['data'] = explode(',', $this->option('data'));
        }

        if ($this->hasOption('uploads')) {
            $this->info['uploads'] = explode(',', $this->option('uploads'));
        }
    }

    /**
     * Validate The module name
     *
     * @return void
     */
    public function validateArguments()
    {
        $availableModules = Mongez::getStored('modules');
        
        if (! in_array($this->info['moduleName'], $availableModules)) {
            return $this->missingRequiredOption('This module does not available in your modules');
        }
    }

    /**
     * Make migration file for module
     *
     * @return void
     */
    public function create()
    {
        $databaseDriver = config('database.default');

        $path = 'app/modules/'.$this->info['moduleName'].'/database/migrations';

        $databaseFileName = strtolower(str::plural($this->info['moduleName']));
                
        $this->checkDirectory($path);

        $content = File::get($this->path("Migrations/".$databaseDriver."-migration.php"));
                
        $content = str_ireplace("TableName", "{$databaseFileName}", $content);
 
        foreach($this->info['index'] as $singleIndexData) {
            if (in_array($singleIndexData, $this->info['unique'])) {
                unset($this->info['index'][array_search($singleIndexData, $this->info['index'])]);
            }
        }
        $allData = array_filter(array_merge($this->info['data'], $this->info['uploads']));

        if (!empty($allData)) {
            $schema = '';
            $tabs = "\n" . str_repeat("\t", 3);
            foreach ($allData as $data) {
                $dataSchema = "{$tabs}\$table->string('$data');";

                if (in_array($data, $this->info['index'])) {
                    $dataSchema = "{$tabs}\$table->string('$data')->index();";                    
                }

                if (in_array($data, $this->info['unique'])) {
                    $dataSchema = "{$tabs}\$table->string('$data')->unique();";
                }

                $schema .= $dataSchema;
            }

            $content = str_ireplace("// Table-Schema", $schema, $content);
        }
                
        $databaseFileName = date('Y_m_d_His').'_'.$databaseFileName;
        
        $this->createFile("$path/{$databaseFileName}.php",$content, 'Migration');
    }
}
