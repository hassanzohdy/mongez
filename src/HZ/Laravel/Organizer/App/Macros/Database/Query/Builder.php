<?php
namespace HZ\Laravel\Organizer\App\Macros\Database\Query;

class Builder
{
    /**
     * Get the next auto increment id of current table.
     *
     * @return int
     */
    public function nextId()
    {
        return function () {
            $statement = $this->newQuery->select("SHOW TABLE STATUS LIKE '{$this->from}'");
            return $statement[0]->Auto_increment;
        };
    }
}
