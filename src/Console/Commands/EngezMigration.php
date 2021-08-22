<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Database\Console\Migrations\TableGuesser;
use HZ\Illuminate\Mongez\Contracts\Console\EngezInterface;
use HZ\Illuminate\Mongez\Managers\Console\EngezGeneratorCommand;

class EngezMigration extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:migration {migrationName}
                                            {--module}
                                            ' . EngezGeneratorCommand::DATA_TYPES_OPTIONS
                                              . EngezGeneratorCommand::TABLE_INDEXES_OPTIONS;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations on target module';

    /**
     * Migration Name
     * 
     * @var string
     */
    protected string $migrationName;

    /**
     * Migration type
     * It can be create or table
     * If current database is mongodb, it is always table
     * 
     * @var string
     */
    protected string $migrationType;

    /**
     * Table name
     * 
     * @var string
     */
    protected string $tableName;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();

        $this->setModuleName($this->option('module'));

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
        parent::init();

        $this->migrationName = $this->snake($this->argument('migrationName'));

        $this->migrationType = $this->startsWith($this->migrationName, 'create_') && !$this->isMongoDB() ? 'create' : 'table';

        $this->tableName = $this->optionHasValue('table') ? $this->option('table') : TableGuesser::guess($this->snake($this->migrationName))[0];
    }

    /**
     * Make migration file for module
     *
     * @return void
     */
    public function create()
    {
        $columnsList = [];

        foreach (EngezGeneratorCommand::DATA_TYPES as $type) {
            if (!$this->optionHasValue($type)) continue;

            $columns = explode(',', $this->option($type));

            foreach ($columns as $column) {
                // make sure to replace int and bool shortcuts to its original names
                $columnType = str_replace(
                    ['int', 'bool', 'uploads', 'locales'],
                    ['integer', 'boolean', 'string', 'locales'],
                    $type
                );

                $columnReplacements = [
                    // column type
                    '{{ columnType }}' => $columnType,
                    // column name
                    '{{ columnName }}' => $column,
                ];

                $columnsList[] = $this->replaceStub('Migrations/column', $columnReplacements);
            }
        }

        $indexesList = [];

        foreach (['unique', 'index'] as $indexType) {
            if (!$this->optionHasValue($indexType)) continue;

            $columns = explode(',', $this->option($indexType));

            foreach ($columns as $column) {
                $columnReplacements = [
                    // index type
                    '{{ columnType }}' => $indexType,
                    // index column name
                    '{{ columnName }}' => $column,
                ];

                $indexesList[] = $this->replaceStub('Migrations/column', $columnReplacements);
            }
        }

        $replacements = [
            // table name
            '{{ tableName }}' => $this->tableName,
            // class name
            '{{ ClassName }}' => $this->studly($this->migrationName),
            // migration type create | table
            '{{ type }}' => $this->migrationType,
            // primary key
            '{{ primaryKey }}' => $this->optionHasValue('primary') ? $this->option('primary') : 'id',
            // columns
            '{{ columns }}' => $this->stubData($columnsList),
            // indexes
            '{{ indexes }}' => $this->stubData($indexesList),
        ];

        $databaseFileName = date('Y_m_d_His') . '_' . $this->migrationName;

        $databaseMigration = $this->isMongoDB() ? 'mongodb-migration' : 'mysql-migration';

        $this->putFIle("database/migrations/{$databaseFileName}.php", $this->replaceStub('Migrations/' . $databaseMigration, $replacements), 'Migration');
    }
}
