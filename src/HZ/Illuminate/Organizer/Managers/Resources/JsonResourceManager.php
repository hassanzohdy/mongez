<?php
namespace HZ\Illuminate\Organizer\Managers\Resources;

use DateTime;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class JsonResourceManager extends JsonResource 
{
    /**
     * Request object
     * 
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The data that will be returned
     * 
     * @var array
     */
    protected $data = [];

    /**
     * Data that must be returned
     * 
     * @const array
     */
    const DATA = [];
    
    /**
     * Data that should be returned if exists
     * 
     * @const array
     */
    const WHEN_AVAILABLE = [];
    
    /**
     * Set that columns that will be formatted as dates
     * it could be numeric array or associated array to set the date format for certain columns
     * 
     * @const array
     */
    const DATES = [];
    
    /**
     * Data that has multiple values based on locale codes
     * 
     * @const array
     */
    const LOCALIZED = [];
    
    /**
     * List of assets that will have a full url if available
     * 
     * @cosnt array
     */
    const ASSETS = [];

    /**
     * Data that will be returned as a resources
     * 
     * i.ie [city => CityResource::class]
     * 
     * @const array
     */
    const RESOURCES = [];

    /**
     * Data that will be returned as a collection of resources
     * 
     * i.ie [city => CityResource::class]
     * 
     * @const array
     */
    const COLLECTABLE = [];

    /**
     * Transform the resource into an array.
     *
     * @param   \Illuminate\Http\Request  $request
     * @return  array
     */
    public function toArray($request)
    {
        $this->request = $request;

        $this->data = [];

        foreach (static::DATA as $column) {
            $this->data[$column] = $this->$column ?? null;
        }

        foreach (static::LOCALIZED as $column) {
            $this->data[$column] = $this->locale($column);
        }

        foreach (static::ASSETS as $column) {
            if (! isset($this->$column)) {
                $this->data[$column] = null;
                continue;    
            }

            $asset = $this->$column;
            
            $this->data[$column] =  ! is_array($asset) ? url($asset) : array_map(function ($asset) {
                return url($asset);
            }, $asset);
        }

        foreach (static::COLLECTABLE as $column => $resource) {
            if (isset($this->$column)) {
                $collection = $this->$column;
                $this->collect($column, $resource, $collection);
            } else {
                $this->data[$column] = [];
            }
        }

        foreach (static::RESOURCES as $column => $resource) {
            if (isset($this->$column)) {
                $resourceData = $this->$column;
                $this->data[$column] = new $resource((object) $resourceData);
            } else {
                $this->data[$column] = [];
            }
        }

        foreach (static::DATES as $key => $column) {
            $format = 'd-m-Y h:i:s a';
            if (is_string($key)) {
                $format = $column;
                $column = $key; 
            }

            if (! isset($this->$column)) {
                $this->data[$column] = null;
                continue;
            }

            if ($this->$column instanceof UTCDateTime) {
                $this->$column = $this->$column->toDateTime();
            } elseif (is_int($this->$column)) {
                $this->$column = new DateTime("@{$this->$column}");
            } elseif ($this->$column instanceof Carbon) {
            } elseif (is_array($this->$column) && isset($this->$column['date'])) {
                $this->$column = new DateTime($this->$column['date']);
            }

            $this->data[$column] = [
                'format' => $this->$column->format($format),
                'timestamp' => $this->$column->getTimestamp(),
            ];
        }
        
        foreach (static::WHEN_AVAILABLE as $column) {
            $value = $this->$column ?? null;
            $dataValue = $this->data[$column] ?? null;
            
            if (! $this->isEmptyValue($value) || ! $this->isEmptyValue($dataValue)) {
                $this->data[$column] = $dataValue ?? $value;
            } else {
                unset($this->data[$column]);
            }
        }

        $this->extend($request);

        return $this->data;
    }

    /**
     * Check if the given value is empty
     * Empty value is an empty array or a null value.  
     *
     * @param  mixed $value
     * @return boolean
     */
    protected function isEmptyValue($value): bool 
    {
        return  is_null($value) || is_array($value) && count($value) == 0;
    }

    /**
     * Collect the given items and set it as collection
     * 
     * @param   string $column
     * @param   string $resource
     * @param   mixed $collection
     * @return  void
     */
    protected function collect($column, $resource, $collection) 
    {
        if (is_array($collection)) {
            $collection = collect($collection)->map(function ($item) {
                return (object) $item;
            });
        }

        $this->data[$column] = $resource::collection($collection);
    }

    /**
     * Extend data
     * 
     * @param  \Request $request
     * @return array
     */
    protected function extend($request) {}

    /**
     * Get name 
     * 
     * @return mixed
     */
    protected function locale($column)
    {
        if (empty($this->$column)) return null;

        if ($this->request->locale) {
            return $this->$column[$this->request->locale] ?? $this->$column;
        } else {
            return $this->$column;
        }
    }

    /**
     * Get Resource info
     * 
     * @return array
     */
    public function info()
    {
        return $this->toArray(request());
    }
}