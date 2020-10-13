<?php

namespace HZ\Illuminate\Mongez\Macros\Database\Query;

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

            if (!$statements) {
                throw new Exception(sprintf('Base table "%s" does not exist', $table));
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
     * Search for location near by the given coordinates for the given distance 
     *
     * @example: $this->whereLocationNear('location', [20,59221, 4], 20); // search in location column for the given [lat, lng] coordinates in 20 km radius
     * @example: $this->whereLocationNear('location', [20,59221, 4], 20, 'km'); // search in location column for the given [lat, lng] coordinates in 20 km radius
     * @example: $this->whereLocationNear('location', [20,59221, 4], 40, 'miles'); // search in location column for the given [lat, lng] coordinates in 40 miles radius
     * 
     * @param  string $column
     * @param  array $coordinates
     * @param  float $distance
     * @param  string $distanceType
     * @return $this
     */
    public function whereLocationNear()
    {
        return function (string $column, array $coordinates, float $distance, string $distanceType = 'km') {
            $distance = (float) $distance;
            $distanceInRadian = $distance;
            if ($distanceType === 'km') {
                $distanceInRadian = $distance / 6371;
            } elseif ($distanceType === 'miles') {
                $distanceInRadian = $distance / 3959;
            }

            // as coordinates are based in [lat, lng] structure
            // we need to swap the values to be [lng, lat] 
            // @see https://docs.mongodb.com/manual/reference/operator/query/centerSphere/#op._S_centerSphere
            $lngLatCoordinates = [$coordinates[1], $coordinates[0]];

            return $this->where($column, 'geoWithin', [
                '$centerSphere' => [$lngLatCoordinates, $distanceInRadian],
            ]);
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
