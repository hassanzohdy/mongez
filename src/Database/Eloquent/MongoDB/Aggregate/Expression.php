<?php

namespace HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Aggregate;

class Expression
{
    /**
     * Operator
     * 
     * @var string
     */
    protected string $operator = '';

    /**
     * Column | Columns
     * 
     * @var int|float|string|string[]|Expression
     */
    protected $column;

    /**
     * return as
     * 
     * @var string
     */
    protected string $returnAs = '';

    /**
     * Constructor
     * 
     * @param string $operator
     * @param int|float|string|string[]|Expression $column
     * @param string $returnAs
     */
    public function __construct(string $operator, $column, string $returnAs = '')
    {
        $this->operator($operator);
        $this->column($column);
        $this->returnAs($returnAs);
    }

    /**
     * Set operator
     * 
     * @param string $operator
     * @return Expression
     */
    public function operator(string $operator): Expression
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * Set column
     * 
     * @param string|string[] $column
     * @return Expression
     */
    public function column($column): Expression
    {
        $this->column = $column;
        return $this;
    }

    /**
     * Set returnAs
     * 
     * @param string $returnAs
     * @return Expression
     */
    public function returnAs(string $returnAs): Expression
    {
        $this->returnAs = $returnAs;
        return $this;
    }

    /**
     * Prase expression
     * 
     * @return array
     */
    public function parse(): array
    {
        $returnAs = $this->returnAs ?: $this->column;

        $column = $this->column;

        if (is_string($column)) {
            $column = '$' . $column;
        } elseif (is_array($column)) {
            $column = array_map(function ($column) {
                return '$' . $column;
            }, $column);
        } elseif ($column instanceof Expression) {
            $column = $column->parse();
        }

        return [
            $returnAs => [
                $this->operator => $column,
            ]
        ];
    }
}
