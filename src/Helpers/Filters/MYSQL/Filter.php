<?php

namespace HZ\Illuminate\Mongez\Helpers\Filters\MYSQL;

class Filter
{
    /**
     * Query Builder Object
     * This property is set from the FilterManager 
     *
     * @var \Illuminate\Database\Query\Builder
     */
    public $query;

    /**
     * available filters
     * 
     * @const array  
     */
    const SQL_FILTER_MAP = [
        '=' => 'basicFilter',
        '>' => 'basicFilter',
        '<' => 'basicFilter',
        '>=' => 'basicFilter',
        '<=' => 'basicFilter',
        'like' => 'filterLike',
        'filterIn'   => 'filterIn',
        'filterNotInInt' => 'filterNotInInt',
        'filterNotIn' => 'filterNotIn'
    ];

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
        $this->query->where(function () use ($columns, $value) {
            $iterate = 0;
            foreach ($columns as $key => $column) {
                if ($iterate > 0) {
                    $this->query->orWhereLike($column, $value);
                }
                $this->query->whereLike($column, $value);
                $iterate++;
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
     * Filter IN array.
     *
     * @param array $columns
     * @param string $value     
     * @return void
     */
    public function filterInInt($columns, $value)
    {
        foreach ($columns as $column) {
            $this->query->whereIn($column, (int) $value);
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
        return static::SQL_FILTER_MAP;
    }
}
