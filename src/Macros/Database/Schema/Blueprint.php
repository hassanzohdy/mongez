<?php
namespace HZ\Illuminate\Mongez\Macros\Database\Schema;

use HZ\Illuminate\Mongez\Helpers\Database\MongoDB\Database;

class Blueprint
{
    /**
     * Create the logger columns which are:
     * created_at | created_by | updated_at | updated_b | deleted_at | deleted_by
     * 
     * @return void
     */
    public function loggers()
    {
        return function (string $createdBy = 'created_by', string $updatedBy = 'updated_by', string $deletedBy = 'deleted_by') {
            $this->integer($createdBy);
            $this->integer($updatedBy);
            $this->integer($deletedBy);
            $this->timestamps(); // created_at + updated_at
            $this->softDeletes()->index()->nullable(); // deleted_at
        };
    }
}
