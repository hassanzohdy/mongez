<?php
namespace HZ\Laravel\Organizer\App\Macros\Support;

class Arr
{
    /**
     * Remove from array by the given value 
     * 
     * @param  mixed $value
     * @param  array $array
     * @param  bool $removeOnlyFirst
     * @return array
     */
    public static function remove()
    {
        return function ($value, array $array, bool $removeOnlyFirst = false): array {
            foreach ($array as $key => $arrayValue) {
                if ($value == $arrayValue) {
                    unset($array[$key]);
                    if ($removeOnlyFirst === true) break;
                }
            }

            return $array;
        };
    }
}
