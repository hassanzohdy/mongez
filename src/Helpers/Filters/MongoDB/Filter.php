<?php
namespace HZ\Illuminate\Mongez\Helpers\Filters\MongoDB;

use HZ\Illuminate\Mongez\Helpers\Filters\MYSQL\Filter as MYSQLFilter;

class Filter extends MYSQLFilter
{
    /**
     * Query Builder Object
     * This property is set from the FilterManager 
     *
     * @var \Illuminate\Database\Query\Builder
     */
    public $query;

    /**
     * {@inheritDoc}
     */
    const NO_SQL_FILTER_MAP = [
        'inInt' => 'filterInInt',
        'notInInt' => 'filterNotInInt',
        'inBool' => 'filterInBool',
        // 'notInBool' => [],
        // 'inFloat' => [],
        // 'notInFloat' => [],
        // 'int' => [],
        // 'bool' => [],
        // 'float' => [],
    ];

    /**
     * Get all available filters map 
     * 
     * @return array 
     */
    public function filterMap()
    {
        return array_merge(static::NO_SQL_FILTER_MAP, parent::filterMap());
    }
}