<?php

namespace HZ\Illuminate\Mongez\Models;

use Illuminate\Support\Str;
use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

class ServiceLog extends Model
{
    /**
     * Log the given service data
     * 
     * @param  array|object $data
     * @return $this
     */
    public static function log($data)
    {
        $mapData = function ($data) use (&$mapData) {
            $details = [];

            foreach ((array) $data as $key => $value) {
                $details[Str::camel(str_replace('.', '_', $key))] = is_array($value) || is_object($value) ? $mapData((array) $value) : $value;
            }

            return $details;
        };

        $details = $mapData($data);

        return static::create($details);
    }
}
