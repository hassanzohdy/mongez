<?php

namespace HZ\Illuminate\Mongez\Traits\Console;

use HZ\Illuminate\Mongez\Helpers\Mongez;
use Illuminate\Support\Str;

trait DatabaseConcerns
{
    /**
     * Current database name
     *
     * @var string
     */
    protected string $databaseName;

    /**
     * Prepare database
     * 
     * @return void
     */
    protected function prepareDatabase()
    {
        $this->databaseName = config('database.default');
    }

    /**
     * Determine if current database is MongoDB
     * 
     * @return bool
     */
    protected function isMongoDB(): bool
    {
        return $this->databaseName === 'mongodb';
    }

    /**
     * Determine if current database is MySQL
     * 
     * @return bool
     */
    protected function isMySQL(): bool
    {
        return $this->databaseName === 'mysql';
    }

    /**
     * Get database name in all lower case
     * 
     * @return string
     */
    protected function databaseName(): string
    {
        return strtolower($this->databaseName);
    }

    /**
     * Get database name in well formatted string
     * i.e MongoDB | MYSQL...etc
     */
    protected function getDatabaseName(): string
    {
        return $this->isMongoDB() ? 'MongoDB' : 'MYSQL';
    }
}
