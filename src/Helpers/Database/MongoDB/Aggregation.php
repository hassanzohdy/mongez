<?php
namespace HZ\Illuminate\Mongez\Helpers\Database\MongoDB;

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

        foreach ($columns as $column) {
            list($name) = explode('.', $column);

            $columnsList[$name] = "$$column";
        }

        return $this->pipeline('group')->data('_id', $columnsList);
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
     * Select items
     * 
     * @param array ...$columns
     * @return Pipeline
     */
    public function select(...$columns): Pipeline
    {
        return $this->project()->data('_id', 0)->select(...$columns);
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
        $pipeline = new Pipeline($this, $pipelineName);

        $this->pipelines[] = $pipeline;

        return $pipeline;
    }

    /**
     * Get the results
     * 
     * @return array 
     */
    public function get()
    {
        $pipelines = [];

        foreach ($this->pipelines as $pipeline) {
            $pipelines[] = [
                $pipeline->getName() => $pipeline->getData(),
            ];
        }

        // \File::putJson(base_path('pp.json'), $pipelines);
        // pred($pipelines);

        return $this->query->raw(function ($query) use ($pipelines) {
            return $query->aggregate($pipelines);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function __call($name, $arguments)
    {
        // for all where clause
        return call_user_func_array([$this->pipeline('match'), $name], $arguments);
    }
}