<?php
namespace HZ\Illuminate\Mongez\Helpers\Database\MongoDB;

use Illuminate\Support\Str;

class Aggregation
{
    // TODO: Sort
    // TODO: Limit
    // TODO: Skip
    // TODO: Join
    // TODO: Unwind
    // TODO: GeoNear
    
    /**
     * Query Builder
     */
    protected $query;

    /**
     * Pipelines list
     * 
     * @var array
     */
    protected $pipelines = [];

    /**
     * Current Pipeline
     * 
     * @var Pipeline
     */
    protected $currentPipeline;

    /**
     * Constructor
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Group By the given column
     * 
     * @param ...string $columns
     * @return Pipeline
     */
    public function groupBy(...$columns): Pipeline 
    {
        $columnsList = [];

        if (count($columns) == 1 && $columns[0] === null) {
            $columnsList = null;
        } else {
            foreach ($columns as $column) {
                list($name) = explode('.', $column);
    
                $columnsList[$name] = "$$column";
            }    
        }

        return $this->pipeline('group')->data('_id', $columnsList);
    }

    /**
     * unwind clause 
     * 
     * @param string $column 
     * @return Pipeline 
     */
    public function unwind($column)
    {
        return $this->pipeline('unwind')->unwind($column);
    }

    /**
     * Group By day 
     * 
     * @param string $column
     * @return Pipeline
     */
    public function groupByDay($column): Pipeline
    {
        return $this->pipeline('group')->data('_id', [
            'day' => ['$dayOfMonth' => '$' . $column],
        ]);
    }

    /**
     * Group By full date 
     * 
     * @param string $column
     * @return Pipeline
     */
    public function groupByDate($column): Pipeline
    {
        return $this->pipeline('group')->data('_id', [
            'day' => ['$dayOfMonth' => '$' . $column],
            'month' => ['$month' => '$' . $column],
            'year' => ['$year' => '$' . $column],
        ]);
    }

    /**
     * Group By month
     * 
     * @param string $column
     * @return Pipeline
     */
    public function groupByMonth($column): Pipeline
    {
        return $this->pipeline('group')->data('_id', [
            'month' => ['$month' => '$' . $column],
        ]);
    }

    /**
     * Group By week
     * 
     * @param string $column
     * @return Pipeline
     */
    public function groupByWeek($column): Pipeline
    {
        return $this->pipeline('group')->data('_id', [
            'week' => ['$week' => '$' . $column],
        ]);
    }

    /**
     * Group By year
     * 
     * @param string $column
     * @return Pipeline
     */
    public function groupByYear($column): Pipeline
    {
        return $this->pipeline('group')->data('_id', [
            'year' => ['$year' => '$' . $column],
        ]);
    }

    /**
     * Where clause 
     * 
     * @param string $column 
     * @param string $operator|$value 
     * @param mixed $value
     * @return Pipeline 
     */
    public function where(...$args)
    {
        return $this->pipeline('match')->where(...$args);
    }

    /**
     * Order returned records
     *
     * @param array $columns
     * @return Pipeline
     */
    public function orderBy($column, $order = 'asc')
    {
        $pipeline = $this->currentPipeline->name == 'sort' ? $this->currentPipeline : $this->pipeline('sort');

        $columnsList = [];
        
        $columnsList[$column] = strtolower($order) == 'asc' ? 1 : -1;

        $pipeline->data($columnsList);

        return $this;
    }

    /**
     * Unwind the field list
     *
     * @param string  $path
     * @param string  $includeArrayIndex
     * @param boolean $preserveNullAndEmptyArrays
     * 
     * @return Pipeline
     */
    public function unwind(string $path, $includeArrayIndex = null, bool $preserveNullAndEmptyArrays = false)
    {
        return $this->pipeline('unwind')->unwind($path, $includeArrayIndex, $preserveNullAndEmptyArrays);
    }

    /**
     * Extract the field list
     *
     * @param string  $path
     * @param string  $includeArrayIndex
     * @param boolean $preserveNullAndEmptyArrays
     * 
     * @return Pipeline
     */
    public function extract($path, $includeArrayIndex = null, bool $preserveNullAndEmptyArrays = false)
    {
        return $this->pipeline('unwind')->unwind($path, $includeArrayIndex, $preserveNullAndEmptyArrays);
    }

    /**
     * Join
     *
     * @param string $from
     * @param string $localField
     * @param string $foreignField
     * @param string $as
     * 
     * @return void
     */
    public function join($from, $localField, $foreignField, $as = null)
    {
        return $this->pipeline('join')->join($from, $localField, $foreignField, $as);
    }

    /**
     * Limit number of records
     *
     * @param int $number
     * @return Pipeline
     */
    public function limit($number, $offset = null)
    {
        if ($offset) {
            $this->offset($offset);
        }

        return $this->pipeline('limit')->limit($number) ;
    }

    /**
     * Skip number of records
     *
     * @param int $number
     * @return Pipeline
     */
    public function skip($number)
    {
        return $this->pipeline('skip')->skip($number);
    }

    /**
     * Offset number of records
     * 
     * @param int $number
     * @return $this
     */
    public function offset($offset)
    {
        return $this->skip($offset);
    }

    /**
     * Select items
     * 
     * @param array ...$columns
     * @return Pipeline
     */
    public function select(...$columns): Pipeline
    {
        return $this->project()->select(...$columns);
    }

    /**
     * Select items
     * 
     * @param array ...$columns
     * @return Pipeline
     */
    public function project(): Pipeline
    {
        return $this->pipeline('project');
    }

    /**
     * Create new pipeline
     * 
     * @param  string $pipelineName
     * @return Pipeline
     */
    public function pipeline(string $pipelineName): Pipeline 
    {
        $this->currentPipeline = new Pipeline($this, $pipelineName);

        $this->pipelines[] = $this->currentPipeline;

        return $this->currentPipeline;
    }

    /**
     * Get the results
     * 
     * @return mixed 
     */
    public function get()
    {
        $pipelines = [];

        foreach ($this->pipelines as $pipeline) {
            $pipelines[] = [
                $pipeline->getName() => $pipeline->getData(),
            ];
        }
        
        // pre($pipelines);

        return iterator_to_array($this->query->raw(function ($query) use ($pipelines) {
            $options = [
                'typeMap' => ['root' => 'array', 'document' => 'array'],
            ];  
            return $query->aggregate($pipelines, $options);
        }));
    }

    /**
     * {@inheritDoc}
     */
    public function __call($name, $arguments)
    {
        // for all where clause
        if (Str::startsWith($name, 'where')) {
            return call_user_func_array([$this->pipeline('match'), $name], $arguments);
        }

        return call_user_func_array([$this->currentPipeline, $name], $arguments);
    }
}