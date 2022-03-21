<?php

namespace HZ\Illuminate\Mongez\Database\Filters;

class MongoDBFilter extends Filter
{
    /**
     * {@inheritDoc}
     */
    const NO_SQL_FILTER_MAP = [
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
        if ($value === 'false') {
            $value = false;
        }

        foreach ($columns as $column) {
            $this->query->where($column, (bool) $value);
        }
    }

    /**
     * Filter in boolean boolean values.
     *
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterInBoolean($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->whereIn($column, array_map('boolval', (array) $value));
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
