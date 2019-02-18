<?php
namespace HZ\Laravel\Organizer\Managers\Resources;

use DateTimeZone;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class JsonResourceManager extends JsonResource 
{
    /**
     * Request object
     * 
     * @var \Request
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
     */
    const ASSETS = [];

    /**
     * Data that will be returned as a resources
     * 
     * i.ie [city => CityResource::class],
     * @const array
     */
    const RESOURCES = [];

    /**
     * Data that will be returned as a collection of resources
     * 
     * i.ie [city => CityResource::class],
     * @const array
     */
    const COLLECTABLE = [];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->request = $request;

        $this->data = [];

        foreach (static::DATA as $column) {
            $this->data[$column] = $this->$column ?? null;
        }

        foreach (static::WHEN_AVAILABLE as $column) {
            if (isset($this->$column)) {
                $this->data[$column] = $this->$column;
            }
        }

        foreach (static::LOCALIZED as $column) {
            $this->data[$column] = $this->locale($column);
        }

        foreach (static::ASSETS as $column) {
            $this->data[$column] = $column ? url($this->$column) : null;
        }

        foreach (static::COLLECTABLE as $column => $resource) {
            if (isset($this->$column)) {
                $collection = $this->$column;
                $this->collect($column, $resource, $collection);
            }
        }

        foreach (static::RESOURCES as $column => $resource) {
            if ($column == 'orders') dd($this->$column);
            if (isset($this->$column)) {
                $resourceData = $this->$column;
                $this->data[$column] = new $resource((object) $resourceData);
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
            }

            if ($timezone = config('app.timezone')) {
                $timezone = new DateTimeZone($timezone);
                $this->$column->setTimeZone($timezone);
            }

            $this->data[$column] = [
                'format' => $this->$column->format($format),
                'timestamp' => $this->$column->getTimestamp(),
            ];
        }

        $this->extend($request);

        return $this->data;
    }

    /**
     * Collect the given items and set it as collection
     * 
     * @param  string $column
     * @param  string $resource
     * @param  mixed $collection
     * @return void
     */
    protected function collect($column, $resource, $collection) 
    {
        if (is_array($collection)) {
            $collection = collect($collection)->map(function ($item) {
                return (object) $item;
            })->sortByDesc('id');
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
     * Get item info
     * 
     * @return array
     */
    public function info() 
    {
        return $this->toArray(request());
    }
}