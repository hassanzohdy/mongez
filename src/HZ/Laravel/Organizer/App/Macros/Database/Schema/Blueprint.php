<?php
namespace HZ\Laravel\Organizer\App\Macros\Database\Schema;

class Blueprint
{
    /**
     * Create the logger columns which are:
     * created_at | created_by | updated_at | updated_b | deleted_at | deleted_by
     * 
     * @return void
     */
    public static function loggers()
    {
        return function () {
            $this->integer('created_by');
            $this->integer('updated_by');
            $this->integer('deleted_by');
            $this->timestamps();
            $this->softDeletes();

            // indexes
            $this->index('created_by');
            $this->index('updated_by');
            $this->index('deleted_by');
            $this->index('deleted_at');
        };
    }
}
