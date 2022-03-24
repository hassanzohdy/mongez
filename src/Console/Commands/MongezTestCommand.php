<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MongezTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mongez:test     
    {--without-tty : Disable output to TTY}
    {--p|parallel : Indicates if the tests should run in parallel}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Unit Testing With fresh database migrations and seeds';

    /**
     * The database name 
     * 
     * @var string
     */
    protected string $filterName;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->ignoreValidationErrors();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::disconnect();

        $databaseTest = config('database.connections.testing.database');

        if (!$databaseTest) {
            return $this->error('No database set in `database.connection.testing.database`');
        }

        DB::purge('mongodb');

        $this->info(sprintf('Connecting To Testing Database <comment>%s</comment>', $databaseTest));

        Config::set('database.connections.mongodb.database', $databaseTest);

        DB::reconnect();

        $db = DB::getMongoDB();

        $this->info(sprintf('Dropping Testing Database <comment>%s</comment>', $databaseTest));

        $db->drop();

        $otherOptions = array_slice($_SERVER['argv'], $this->option('without-tty') ? 3 : 2);

        $this->call('migrate');
        $this->call('db:seed');
        $this->call('test', array_merge($this->options(), $otherOptions));
    }
}
