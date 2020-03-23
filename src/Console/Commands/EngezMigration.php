<?php
namespace HZ\Illuminate\Mongez\Console\Commands;
use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;
use Illuminate\Database\Console\Migrations\TableGuesser;
use HZ\Illuminate\Mongez\Contracts\Console\EngezInterface;
class EngezMigration extends Command implements EngezInterface
{
    use EngezTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:migration {migrationName}
                                            {module}
                                            {--create=} 
                                            {--table=} 
                                            {--data=}
                                            {--uploads=}
                                            {--int=}
                                            {--double=}
                                            {--bool=}
                                            {--index=} 
                                            {--parent=}
                                            {--unique=}';
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
     * Set Migration info.
     * 
     * @return void
     */
    public function init()
    {
        $this->root = Mongez::packagePath();
        $allData = '';
        $this->info['moduleName'] = Str::studly($this->argument('module'));
        $this->info['migrationName'] = $this->argument('migrationName');
        $this->info['index'] =  [];
        $this->info['unique'] =  [];
        $this->info['int'] =  [];
        $this->info['double'] =  [];
        $this->info['bool'] =  [];
        
        if ($this->optionHasValue('index')) {
            $this->info['index'] = explode(',', $this->option('index'));
        }
        if ($this->optionHasValue('unique')) {
            $this->info['unique'] = explode(',', $this->option('unique'));
        }
        if ($this->optionHasValue('data')) {
            $allData .= $this->option('data').','; 
        }
        
        if ($this->optionHasValue('uploads')) {
            $allData .= $this->option('uploads'); 
        }
        if ($this->optionHasValue('int')) {
            $this->info['int'] = explode(',', $this->option('int'));
        }
        if ($this->optionHasValue('bool')) {
            $this->info['bool'] = explode(',', $this->option('bool'));
        }
        if ($this->optionHasValue('double')) {
            $this->info['double'] = explode(',', $this->option('double'));
        }
        
        if ($this->optionHasValue('parent')) {
            $this->info['parent'] = $this->option('parent');
        }
        $this->info['data'] = explode(',', $allData);
        $this->setMigrationType();
    }
    /**
     * Validate The module name.
     *
     * @return void
     */
    public function validateArguments()
    {   
        $availableModules = Mongez::getStored('modules');
        
        if (! in_array($this->info['moduleName'], $availableModules)) {
            return $this->missingRequiredOption('This module does not available in your modules');
        }
        if ($this->option('parent')) {
            if (! in_array(Str::Studly($this->info['parent']), $availableModules)) {
                return Command::error('This parent module is not available');
                die();
            }    
        }
    }
    /**
     * Update table array in mongez file
     * 
     * @return void 
     */
    protected function setMigrationType()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
        $name = Str::snake(trim($this->info['migrationName']));
                
        $table = $this->optionHasValue('table');
        $create = $this->optionHasValue('create') ?: false;
        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
        if (! $table && is_string($create)) {
            $table = $create;
            $create = true;
        }
        // Next, we will attempt to guess the table name if this the migration has
        // "create" in the name. This will allow us to provide a convenient way
        // of creating migrations that create new tables for the application.
        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }
        $this->info['table'] = $table;
        $this->info['migrationType'] = 'create';
        if (!$create) $this->info['migrationType'] = 'table';
    }
    
    /**
     * Make migration file for module
     *
     * @return void
     */
    public function create()
    {
        $databaseDriver = config('database.default');
        $targetModule = $this->info['moduleName'];
        
        if (isset($this->info['parent'])) {
            $targetModule = $this->info['parent'];
        }
        $path = 'app/modules/' . $targetModule . '/database/migrations';
        $databaseFileName = $this->info['migrationName'];
        $className = Str::studly($databaseFileName);

        $this->checkDirectory($path);
        
        $content = File::get($this->path("Migrations/".$databaseDriver."-migration.php"));
        $content = str_ireplace("className", $className, $content);
        $content = str_ireplace("TableName", $this->info['table'], $content);
        $content = str_ireplace("{'type'}", $this->info['migrationType'], $content);
        
        foreach($this->info['index'] as $singleIndexData) {
            if (in_array($singleIndexData, $this->info['unique'])) {
                unset($this->info['index'][array_search($singleIndexData, $this->info['index'])]);
            }
        }
        $allData = array_filter(array_unique(array_merge($this->info['data'], $this->info['int'], $this->info['bool'], $this->info['index'], $this->info['unique'], $this->info['double'])));
        if (! empty($allData)) {
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
                if (in_array($data, $this->info['int'])) {
                    $dataSchema = "{$tabs}\$table->integer('$data');";
                    if (in_array($data, $this->info['index'])){
                        $dataSchema = "{$tabs}\$table->integer('$data')->index();";
                    }
                    if (in_array($data, $this->info['unique'])){
                        $dataSchema = "{$tabs}\$table->integer('$data')->unique();";
                    }
                }
                if (in_array($data, $this->info['double'])) {
                    $dataSchema = "{$tabs}\$table->double('$data');";
                    if (in_array($data, $this->info['index'])){
                        $dataSchema = "{$tabs}\$table->double('$data')->index();";
                    }
                    if (in_array($data, $this->info['unique'])){
                        $dataSchema = "{$tabs}\$table->double('$data')->unique();";
                    }
                }
                
                if (in_array($data, $this->info['bool'])) {
                    $dataSchema = "{$tabs}\$table->boolean('$data');";
                    if (in_array($data, $this->info['index'])){
                        $dataSchema = "{$tabs}\$table->boolean('$data')->index();";
                    }
                    if (in_array($data, $this->info['unique'])){
                        $dataSchema = "{$tabs}\$table->boolean('$data')->unique();";
                    }
                }
                $schema .= $dataSchema;
            }
            $content = str_ireplace("// Table-Schema", $schema, $content);
        }
                
        $databaseFileName = date('Y_m_d_His').'_'.$databaseFileName;
        
        $this->createFile("$path/{$databaseFileName}.php",$content, 'Migration');
    }
}