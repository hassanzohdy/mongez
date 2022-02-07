<?php

namespace HZ\Illuminate\Mongez\Database\Filters;

class Filter
{
    /**
     * available filters
     * 
     * @const array  
     */
    const FILTER_MAP = [
        '=' => 'basicFilter',
        '>' => 'basicFilter',
        '<' => 'basicFilter',
        '>=' => 'basicFilter',
        '<=' => 'basicFilter',
        'like' => 'filterLike',
        'in'   => 'filterIn',
        'notIn' => 'filterNotIn',
        'inInt' => 'filterInInt',
        'notInInt' => 'filterNotInInt',
        'null' => 'filterNull',
        'notNull' => 'filterNotNull',
    ];

    /**
     * Query Builder Object
     * This property is set from the FilterManager 
     *
     * @var \Illuminate\Database\Query\Builder
     */
    protected $query;

    /**
     * Set the query builder object
     * 
     * @param  \Illuminate\Database\Query\Builder $query
     * @return void
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Filter by columns 
     * 
     * @param array $columns
     * @param string $value
     * @param string $operator
     * @return void 
     */
    public function basicFilter($columns, $value, $operator = '=')
    {
        foreach ($columns as $column) {
            $this->query->where($column, $operator, $value);
        }
    }

    /**
     * Filter Like.
     *
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterLike($columns, $value)
    {
        $this->query->where(function ($query) use ($columns, $value) {
            foreach ($columns as $index => $column) {
                if ($index > 0) {
                    $query->orWhereLike($column, $value);
                } else {
                    $query->whereLike($column, $value);
                }
            }
        });
    }

    /**
     * Filter IN array.
     * 
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterIn($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->whereIn($column, (array) $value);
        }
    }

    /**
     * Filter null
     * 
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterNull($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->whereNull($column);
        }
    }

    /**
     * Filter not null
     * 
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterNotNull($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->whereNotNull($column);
        }
    }

    /**
     * Filter IN array.
     *
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterInInt($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->whereIn($column, array_map('intval', (array) $value));
        }
    }

    /**
     * Filter NOT IN array.
     *
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterNotInInt($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->whereNotIn($column, array_map('intval', (array) $value));
        }
    }

    /**
     * Filter NOT IN array.
     *
     * @param array $columns
     * @param string $value
     * @return void
     */
    public function filterNotIn($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->whereNotIn($column, array_map('intval', (array) $value));
        }
    }

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
