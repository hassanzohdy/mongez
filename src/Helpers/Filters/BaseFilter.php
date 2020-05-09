<?php
namespace HZ\Illuminate\Mongez\Helpers\Filters;

class BaseFilter 
{
    /**
     * Get all available filters map 
     * 
     * @return array 
     */
    public function filterMap()
    {
        return static::FILTER_MAP;
    }
}