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
    protected $signature = 'mongez:test';

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

        Config::set('database.connections.mongodb.database', $databaseTest);

        DB::purge('mongodb');

        DB::reconnect();

        $db = DB::getMongoDB();

        $db->drop();

        $this->call('migrate');
        $this->call('db:seed');
        $this->call('test', $this->arguments());
    }
}
