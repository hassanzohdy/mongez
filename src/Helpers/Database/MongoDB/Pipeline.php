<?php

namespace HZ\Illuminate\Mongez\Helpers\Database\MongoDB;

use PhpParser\Node\Stmt\TraitUseAdaptation\Alias;

class Pipeline
{
    /**
     * Aggregation Framework Handler
     * 
     * @var Aggregation
     */
    protected $aggregationFramework;

    /**
     * Pipeline name
     */
    protected $name;

    /**
     * Pipeline Data
     */
    protected $data = [];

    /**
     * Matched operators
     * 
     * @const array
     */
    const MATCHING_OPERATOR = [
        '=' => '$eq',
        '<' => '$lt',
        '<=' => '$lte',
        '>' => '$gt',
        '>=' => '$gte',
        '!=' => '$ne',
        '<>' => '$ne',
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(Aggregation $aggregationFramework, string $name)
    {
        $this->name = $name;
        $this->aggregationFramework = $aggregationFramework;
    }

    /**
     * Add data to it
     * 
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function data($key, $value): Pipeline
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Select columns
     * 
     * @param ...mixed $columns
     * @return $this
     */
    public function select(...$columns)
    {
        foreach ($columns as $column) {
            if (is_array($column)) {
                list($column, $alias) = $column;
            } else {
                $keys = explode('.', $column);
                $alias = end($keys); // i.e user.id => will return id only
            }

            if ($this->name == 'group') {
                $this->data($alias, [
                    '$first' => "$$column"
                ]);
            } elseif ($this->name == 'project') {
                $this->data($alias, [
                    $column => "$$column"
                ]);
            }
        }

        return $this;
    }

    /**
     * Unselect the given columns
     * 
     * @param  ...string $columns
     * @return $this
     */
    public function unselect(...$columns)
    {
        foreach ($columns as $column) {
            $this->data($column, 0);
        }

        return $this;
    }

    /**
     * Where clause
     * 
     * @param string $column 
     * @param string $operator|$value 
     * @param mixed $value
     * @return Pipeline 
     */
    public function where()
    {
        $arguments = func_get_args();
        $totalArguments = count($arguments);

        if ($totalArguments == 2) {
            list($column, $value) = $arguments;
            $operator = '=';
        } elseif ($totalArguments == 3) {
            list($column, $operator, $value) = $arguments;
        }

        $this->data($column, [
            static::MATCHING_OPERATOR[$operator] => $value,
        ]);

        return $this;
    }

    /**
     * Where clause
     * 
     * @param string $column 
     * @param string $operator|$value 
     * @param mixed $value
     * @return Pipeline 
     */
    public function orWhere()
    {
        $arguments = func_get_args();
        $totalArguments = count($arguments);

        if ($totalArguments == 2) {
            list($column, $value) = $arguments;
            $operator = '=';
        } elseif ($totalArguments == 3) {
            list($column, $operator, $value) = $arguments;
        }

        $data = [
            $column => [
                static::MATCHING_OPERATOR[$operator] => $value,
            ]
        ];
        
        $this->data('$or', $data);

        return $this;
    }

    /**
     * where in clause
     * 
     * @param  string $column
     * @param  array $array 
     * @return Pipeline      
     */
    public function whereIn($column, $array): Pipeline
    {
        $this->data($column, [
            '$in' => $array,
        ]);

        return $this;
    }

    /**
     * where in clause for array of integers
     * 
     * @param  string $column
     * @param  array $array 
     * @return Pipeline      
     */
    public function whereInInt($column, $array): Pipeline
    {
        return $this->whereIn($column, array_map('intval', $array));
    }

    /**
     * Where between clause
     * 
     * @param  string $column
     * @param  mixed $minValue
     * @param  mixed $maxValue
     * @return Pipeline
     */
    public function whereBetween($column, $minValue, $maxValue): Pipeline
    {
        $this->data($column, [
            static::MATCHING_OPERATOR['>='] => $minValue,
            static::MATCHING_OPERATOR['<='] => $maxValue,
        ]);

        return $this;
    }

    /**
     * Select columns
     * 
     * @param ...mixed $columns
     * @return $this
     */
    public function count(...$columns): Pipeline
    {
        foreach ($columns as $column) {
            if (is_array($column)) {
                list($alias, $column) = $column;
            } else {
                $alias = $column;
            }

            $this->data($alias, [
                '$sum' => 1
            ]);
        }

        return $this;
    }

    /**
     * Select columns
     * 
     * @param ...mixed $columns
     * @return $this
     */
    public function sum(...$columns): Pipeline
    {
        foreach ($columns as $column) {
            if (is_array($column)) {
                list($alias, $column) = $column;
            } else {
                $alias = $column;
            }

            $this->data($alias, [
                '$sum' => "$$column"
            ]);
        }

        return $this;
    }

    /**
     * Return the final name of the pipeline
     * 
     * @return string
     */
    public function getName(): string 
    {
        return '$' . $this->name;
    }

    /**
     * Return the final data of the pipeline
     * 
     * @return array
     */
    public function getData(): array 
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->aggregationFramework, $name], $arguments);
    }
}
