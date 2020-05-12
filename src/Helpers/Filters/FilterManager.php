<?php
namespace HZ\Illuminate\Mongez\Helpers\Filters;

use App;
use Illuminate\Support\Arr;

class FilterManager
{
    /**
     * Query Builder Object
     *
     * @var \Illuminate\Database\Query\Builder
     */
    public $query;

    /**
     * Sended options to filtered
     * 
     * @var array  
     */
    public $options = [];

    /**
     * All options that enable to filter with
     * 
     * @var array  
     */
    public $filterBy = [];

    /** 
     * Set required data for filters
     * 
     * @param string \Illuminate\Database\Query\Builder
     * @param array  $options
     * @param array  $filterBy
     * @param \Illuminate\Database\Query\Builder $query
    */
    public function __construct($query, $options, $filterBy) 
    {
        $this->query = $query;
        $this->options = $options;
        $this->filterBy = $filterBy;
    }

    /**
     * Merge filter class.
     * 
     * @param string $filterClass
     * @return void
     */
    public function merge(array $filterClasses) 
    {
        foreach($filterClasses as $filterClass) {
            $filterObject = App::make($filterClass);

            $filterObject->query = $this->query;
            $sendedOptions = $this->getRequestedOptions();
            foreach ($sendedOptions as $option) {
                $filterFunction = $option['operator'];
                if (array_key_exists($option['operator'], $filterObject->filterMap())) {
                    $filterFunction = $filterObject->filterMap()[$option['operator']];
                    $filterObject->$filterFunction($option['columns'], $option['value'], $option['operator']);
                }
            }
        }
    }

    /**
     * Remove un sended options
     * 
     * @param array filterByOptions
     * @return array 
     */
    protected function getRequestedOptions()
    { 
        $requestedOptions = [];
        foreach ($this->filterBy as $operator => $columns) {
            $options = [];
            foreach((array)$columns as $key => $column) { 
                $columns = [];
                if (!is_string($key)) $key = $column;
                if (($value = Arr::get($this->options, $key, null)) !== null) {
                    $options['operator'] = $operator;
                    $columns = (array) $column;
                    $options['value'] = $value;
                }
            }

            if (!empty($columns)) $options['columns'] = $columns;
            if (!empty($options)) $requestedOptions[] = $options;
        }
        return $requestedOptions;
    }
}