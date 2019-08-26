<?php
namespace HZ\Illuminate\Organizer\Macros\Database\Query;

use DB;
use Exception;

class Builder
{
    /**
     * Get the next auto increment id of current table.
     *
     * @return int
     */
    public function getNextId()
    {
        return function () {
            // if the form property doesn't exist, then it means we're executing this 
            // method inside the Eloquent builder
            $table = $this->from ?? $this->model->getTable();

            $statements = DB::select("SHOW TABLE STATUS LIKE '{$table}'");
           
            if (! $statements) {
                throw new Exception (sprintf('Base table "%s" does not exist', $table));
            }
            
            return $statements[0]->Auto_increment;
        };
    }
    
    /**
     * A shorthand method for the `where like ` clause
     *
     * @param  string $column
     * @param  mixed $value
     * @return $this
     */
    public function whereLike()
    {
        return function (string $column, $value) {
            return $this->where($column, 'LIKE', "%$value%");
        };
    }

    /**
     * A shorthand method for the `or where like ` clause
     *
     * @param  string $column
     * @param  mixed $value
     * @return $this
     */
     public function orWhereLike()
     {
         return function (string $column, $value) {
             return $this->orWhere($column, 'LIKE', "%$value%");
         };
     }
}