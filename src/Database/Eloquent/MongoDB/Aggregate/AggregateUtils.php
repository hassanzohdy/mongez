<?php

namespace HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Aggregate;

class AggregateUtils
{
    /**
     * Aggregate framework
     * 
     * @var Aggregate
     */
    protected $aggregate;

    /**
     * Data that will be mapped
     * 
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     * 
     * @param Aggregate $aggregate
     */
    public function __construct(Aggregate $aggregate)
    {
        $this->aggregate = $aggregate;
    }

    /**
     * Map the data into proper mongodb aggregate framework format
     * 
     * @return array
     */
    public function map(): array
    {
        $data = $this->data;
        $this->data = [];
        return $data;
    }

    /**
     * Alias to map method
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->map();
    }

    /**
     * Get sum operator
     * 
     * @param  string $column
     * @param  string $alias
     * @return AggregateUtils
     */
    public function sum(string $column, string $alias = null): AggregateUtils
    {
        $returnAs = $alias ?? $column;

        $this->data[] = [
            $returnAs => [
                '$sum' => '$' . $column,
            ],
        ];

        return $this;
    }

    /**
     * Get avg operator
     * 
     * @param  string $column
     * @param  string $alias
     * @return AggregateUtils
     */
    public function avg(string $column, string $alias = null): AggregateUtils
    {
        $returnAs = $alias ?? $column;

        $this->data[] = [
            $returnAs => [
                '$avg' => '$' . $column,
            ],
        ];

        return $this;
    }

    /**
     * Get max operator
     * 
     * @param  string $column
     * @param  string $alias
     * @return AggregateUtils
     */
    public function max(string $column, string $alias = null): AggregateUtils
    {
        $returnAs = $alias ?? $column;

        $this->data[] = [
            $returnAs => [
                '$max' => '$' . $column,
            ],
        ];

        return $this;
    }

    /**
     * Get min operator
     * 
     * @param  string $column
     * @param  string $alias
     * @return AggregateUtils
     */
    public function min(string $column, string $alias = null): AggregateUtils
    {
        $returnAs = $alias ?? $column;

        $this->data[] = [
            $returnAs => [
                '$min' => '$' . $column,
            ],
        ];

        return $this;
    }

    /**
     * Get count operator
     * 
     * @param  string $column
     * @param  string $alias
     * @return AggregateUtils
     */
    public function count(string $column, string $alias = null): AggregateUtils
    {
        $returnAs = $alias ?? $column;

        $this->data[] = [
            $returnAs => [
                '$sum' => 1,
            ],
        ];

        return $this;
    }
}
