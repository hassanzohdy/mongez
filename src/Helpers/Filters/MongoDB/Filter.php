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
        'inBool' => 'filterInBool',
        'inBoolean' => 'filterInBool',
        'notInBool' => 'filterNotInBool',
        'inFloat' => 'filterInFloat',
        'notInFloat' => 'filterNotInFloat',
        'int' => 'filterInt',
        'float' => 'filterFloat',
        'bool' => 'filterBoolean',
        'boolean' => 'filterBoolean',
    ];

    /**
     * Filter integer values.
     *
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterInt($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->where($column, (int) $value);
        }
    }

    /**
     * Filter float values.
     *
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterFloat($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->where($column, (float) $value);
        }
    }

    /**
     * Filter boolean values.
     *
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterBoolean($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->where($column, (bool) $value);
        }
    }

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