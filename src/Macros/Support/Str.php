<?php
namespace HZ\Illuminate\Mongez\Macros\Support;

class Str
{
    /**
     * Remove the first occurrence of the given needle from the object 
     * 
     * @param string $needle
     * @param string $object
     * @return string
     */
    public static function removeFirst()
    {
        return function (string $needle, string $object): string {
            return static::replaceFirst($needle, '', $object);
        };
    }
}
