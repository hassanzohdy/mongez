<?php

namespace HZ\Illuminate\Mongez\Macros\Support;

class Arr
{
    /**
     * Remove from array by the given value 
     * 
     * @param  mixed $value
     * @param  array $array
     * @param  bool $removeFirstOnly
     * @return array
     */
    public static function remove()
    {
        return function (array $array, $value, bool $removeFirstOnly = false): array {
            foreach ($array as $key => $arrayValue) {
                if ($value == $arrayValue) {
                    unset($array[$key]);
                    if ($removeFirstOnly === true) break;
                }
            }

            return $array;
        };
    }

    /**
     * Get the all values that are not duplicated in the given arrays
     * 
     * @param  ...$arrays
     * @return array 
     */
    public static function outer()
    {
        return function (...$arrays) {
            $union_array = array_merge(...$arrays);
            $intersect_array = array_intersect(...$arrays);
            return array_diff($union_array, $intersect_array);
        };
    }
}
