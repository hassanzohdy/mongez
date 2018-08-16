<?php
namespace App\Macros\Support;

use Illuminate\Support\Str as OriginalStr;

class Str
{
    /**
     * Remove the first occurrence of the given needle 
     * 
     * @param string $needle
     * @param string $object
     * @return string
     */
    public static function removeFirst()
    {
        return function ($needle, string $object) {
            return OriginalStr::replaceFirst($needle, '', $object);
        };
    }
}
