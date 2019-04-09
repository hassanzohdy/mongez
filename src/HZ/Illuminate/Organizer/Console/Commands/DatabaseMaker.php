<?php
namespace HZ\Illuminate\Organizer\Console\Commands;

use PDO;
use PDOException;
use Illuminate\Console\Command;

class DatabaseMaker extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command creates a new database';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'db:create';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $driver = config('database.default');
        $host = config("database.connections.$driver.host");
        $port = config("database.connections.$driver.port");
        $database = config("database.connections.$driver.database");
        $username = config("database.connections.$driver.username");
        $password = config("database.connections.$driver.password");
        $charset = config("database.connections.$driver.charset");
        $collation = config("database.connections.$driver.collation");

        if (! $database) {
            $this->info('Skipping creation of database as env(DB_DATABASE) is empty');
            return;
        }

        try {
            $pdo = $this->getPDOConnection($driver, $host, $port, $username, $password);

            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS %s CHARACTER SET %s COLLATE %s;',
                $database,
                $charset,
                $collation
            ));

            $this->info(sprintf('Successfully created %s database', $database));
        } catch (PDOException $exception) {
            $this->error(sprintf('Failed to create %s database, %s', $database, $exception->getMessage()));
        }
    }

    /**
     * @param  string $host
     * @param  integer $port
     * @param  string $username
     * @param  string $password
     * @return PDO
     */
    private function getPDOConnection($driver, $host, $port, $username, $password)
    {
        return new PDO(sprintf("$driver:host=%s;port=%d;", $host, $port), $username, $password);
    }
}