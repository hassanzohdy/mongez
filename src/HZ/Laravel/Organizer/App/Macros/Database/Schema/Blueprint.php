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
        return function ($createdBy = 'created_by', $updatedBy = 'updated_by', $deletedBy = 'deleted_by') {
            $this->integer($createdBy);
            $this->integer($updatedBy);
            $this->integer($deletedBy);
            $this->timestamps();
            $this->softDeletes();

            // indexes
            $this->index($createdBy);
            $this->index($updatedBy);
            $this->index($deletedBy);
            $this->index('deleted_at');
        };
    }
}
