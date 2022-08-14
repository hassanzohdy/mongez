<?php


namespace HZ\Illuminate\Mongez\Database\Eloquent\MongoDB;

use DateTimeInterface;
use MongoDB\BSON\UTCDateTime;

class Pipeline
{
    /**
     * Pipeline name without the `$` sign
     * 
     * @var string
     */
    public $name;
    /**
     * Aggregation Framework Handler
     * 
     * @var Aggregation
     */
    protected $aggregationFramework;


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
     * Sum the given column name 
     * Please note this method MUST BE CALLED directly after the group by method 
     * 
     * @param string|array $columns
     * @return float   
     */
    public function sum($columns)
    {
        if ($this->name !== 'group') {
            // throw new Exception('Sum Method Must be called directly after the groupBy Method');
            return $this->groupBy()->sum($columns);
        }

        if (is_string($columns)) {
            $columns = [
                $columns => $columns,
            ];
        }

        foreach ($columns as $column => $alias) {
            $this->data($alias, ['$sum' => '$' . $column]);
        }

        return $this;
    }

    /**
     * Add data to it
     * 
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function data($key, $value = null): Pipeline
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            if (isset($this->data[$key])) {
                if (!is_array($value)) {
                    $value = [$value => '$' . $value];
                }

                $this->data[$key] = array_merge((array) $this->data[$key], $value);
            } else {
                $this->data[$key] =  $value;
            }
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
        if (!in_array($this->name, ['group', 'project'])) {
            return $this->aggregationFramework->select(...$columns);
        }

        foreach ($columns as $column) {
            if (is_array($column)) {
                list($column, $alias) = $column;
            } else {
                $keys = explode('.', $column);
                $alias = end($keys); // i.e user.id => will return id only
            }

            if ($this->name == 'group') {
                $this->data($alias, [
                    '$last' => "$$column"
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

        if ($value instanceof DateTimeInterface) {
            $value = new UTCDateTime($value->format('Uv'));
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
        if ($minValue instanceof DateTimeInterface) {
            $minValue = new UTCDateTime($minValue->format('Uv'));
        }

        if ($maxValue instanceof DateTimeInterface) {
            $maxValue = new UTCDateTime($maxValue->format('Uv'));
        }

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
                list($column, $alias) = $column;
            } else {
                $alias = $column;
            }

            $this->data($alias, [
                '$sum' => 1
            ]);
        }

        return $this;
    }

    // /**
    //  * Unwind the given column
    //  * 
    //  * @param string $column
    //  * @return $this
    //  */
    // public function unwind($column)
    // {
    //     if ($this->name !== 'unwind') {
    //         return $this->aggregationFramework->unwind($column);
    //     }

    //     $this->data = $column;

    //     return $this;
    // }

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
     * @return array|string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function limit($number)
    {
        $this->data((int) $number);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function skip($number)
    {
        $this->data((int)$number);
        return $this;
    }

    /**
     * Unwind the given column
     * 
     * @param string $column
     * @return $this
     */
    public function join($from, $localField, $foreignField, $as = null)
    {
        if (!$as) $as = $from;

        $this->data([
            'from' => $from,
            'localField' => $localField,
            'foreignField' => $foreignField,
            'as' => $as
        ]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unwind($column, $includeArrayIndex, $preserveNullAndEmptyArrays)
    {
        if ($this->name !== 'unwind') {
            return $this->aggregationFramework->unwind($column);
        }

        $data = [
            'path' => '$' . $column,
            'includeArrayIndex' => $includeArrayIndex,
            'preserveNullAndEmptyArrays' => $preserveNullAndEmptyArrays
        ];

        if (!$includeArrayIndex) unset($data['includeArrayIndex']);
        $this->data($data);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->aggregationFramework, $name], $arguments);
    }
}
