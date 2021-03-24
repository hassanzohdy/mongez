<?php

namespace HZ\Illuminate\Mongez\Managers\Resources;

use DateTime;
use Illuminate\Support\Arr;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Fluent;
use Illuminate\Database\Eloquent\Model;
use HZ\Illuminate\Mongez\Helpers\Mongez;
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
     * @const array
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
     * Assets function for generating full url
     *
     * @var string
     */
    protected $assetsUrlFunction;

    /**
     * List of keys that will be unset before sending
     *
     * @var array
     */
    protected static $disabledKeys = [];

    /**
     * List of keys that will be taken only
     *
     * @var array
     */
    protected static $allowedKeys = [];

    /**
     * Disable the given list of keys
     *
     * @param ...$keys
     * @return void
     */
    public static function disable(...$keys)
    {
        static::$disabledKeys = array_merge(static::$disabledKeys, $keys);
    }

    /**
     * Disable the given list of keys
     *
     * @param ...$keys
     * @return void
     */
    public static function only(...$keys)
    {
        static::$allowedKeys = array_merge(static::$allowedKeys, $keys);
    }

    /**
     * Transform the resource into an array.
     *
     * @param   \Illuminate\Http\Request  $request
     * @return  array
     */
    public function toArray($request)
    {
        $this->request = $request;

        if (!$this->assetsUrlFunction) {
            $this->assetsUrlFunction = static::assetsFunction();
        }

        $this->collectData(static::DATA);

        $this->collectLocalized(static::LOCALIZED);

        $this->collectAssets(static::ASSETS);

        $this->collectCollectables(static::COLLECTABLE);

        $this->collectResources(static::RESOURCES);

        $this->collectDates(static::DATES);

        $this->filterWhenAvailable(static::WHEN_AVAILABLE);

        $this->extend($request);

        // unset all data from the resource
        if (!empty(static::$disabledKeys)) {
            foreach (static::$disabledKeys as $key) {
                unset($this->data[$key]);
            }
        }

        if (!empty(static::$allowedKeys)) {
            foreach (array_keys($this->data) as $key) {
                if (!in_array($key, static::$allowedKeys)) {
                    unset($this->data[$key]);
                }
            }
        }

        if (isset($this->data['accessTokens'])) {
            $token = $this->data['accessTokens'][0]['token'];
            unset($this->data['accessTokens']);
            $this->data['accessToken'] = $token;
        }

        return $this->data;
    }

    /**
     * Get assets function name
     *
     * @return string
     */
    public static function assetsFunction(): string
    {
        return config('mongez.resources.assets', 'url');
    }

    /**
     * Get the full url for the given asset path
     *
     * @param string $path
     * @return string
     */
    public static function assetsUrl(string $path): string
    {
        return call_user_func(static::assetsFunction(), $path);
    }

    /**
     * Collect mandatory data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectData(array $columns): JsonResourceManager
    {
        foreach ($columns as $column) {
            $value = $this->resource->$column ?? null;

            if (is_float($value)) {
                $value = round($value, 2);
            }

            $this->set($column, $value);
        }

        return $this;
    }

    /**
     * Collect localized data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectLocalized(array $columns): JsonResourceManager
    {
        foreach ($columns as $column) {
            $this->set($column, $this->locale($column));
        }

        return $this;
    }

    /**
     * Collect assets
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectAssets(array $columns): JsonResourceManager
    {
        if ($this->resource instanceof Model) {
            if (method_exists($this->resource, 'info')) {
                $resource = $this->resource->info();
            } else {
                $resource = $this->resource->getAttributes();
            }
        } elseif ($this->resource instanceof Fluent) {
            $resource = $this->resource->toArray();
        } else {
            $resource = (array) $this->resource;
        }

        foreach ($columns as $column) {
            $asset = Arr::get($resource, $column, null);
            if (!$asset) {
                $this->set($column, null);
                continue;
            }

            if (is_array($asset)) {
                $assets = [];
                // the key here is very important
                // as it might be an associated key not index
                // i.e image in two or more locales, one image for each
                // locale code
                foreach ($asset as $key => $assetPath) {
                    $assets[$key] = call_user_func($this->assetsUrlFunction, $assetPath);
                }
                $this->set($column, $assets);
            } else {
                $this->set($column, call_user_func($this->assetsUrlFunction, $asset));
            }
        }

        return $this;
    }

    /**
     * Collect Collectable data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectCollectables(array $columns): JsonResourceManager
    {
        foreach ($columns as $column => $resource) {
            if (isset($this->$column)) {
                $collection = $this->$column;
                $this->collect($column, $resource, $collection);
            } else {
                $this->set($column, []);
            }
        }

        return $this;
    }

    /**
     * Collect resources data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectResources(array $columns): JsonResourceManager
    {
        foreach ($columns as $column => $resource) {
            if (!empty($this->$column)) {
                $resourceData = $this->$column;
                $this->set($column, new $resource(new Fluent($resourceData)));
            } else {
                $this->set($column, null);
            }
        }

        return $this;
    }

    /**
     * Collect dates
     *
     * @param array $columns
     * @return JsonResourceManage
     */
    public function collectDates(array $columns): JsonResourceManager
    {
        if (empty($columns)) return $this;

        foreach ($columns as $key => $column) {
            if (is_string($key)) {
                $format = $column;
                $column = $key;
            }

            if (!isset($this->$column)) {
                $this->set($column, null);
                continue;
            }

            $value = $this->$column;

            $this->setDate($column, $value);
        }

        return $this;
    }

    /**
     * Set date
     * 
     * @param string $column
     * @param mixed $value
     * @return void
     */
    protected function setDate($column, $value, $options = [])
    {
        $options = array_merge([
            'format' =>  config('mongez.resources.date.format', 'd-m-Y h:i:s a'),
            'timestamp' => config('mongez.resources.date.timestamp', true),
            'humanTime' => config('mongez.resources.date.humanTime', true),
        ], $options);


        if ($value instanceof UTCDateTime) {
            $value = $value->toDateTime();
            $timezone = new \DateTimeZone(config('app.timezone'));
            $value->setTimezone($timezone);
        } elseif (is_numeric($value)) {
            $value = new DateTime("@{$value}");
        } elseif (is_array($value) && isset($value['date'])) {
            $value = new DateTime($value['date']);
        } elseif (is_string($value)) {
            $value = new DateTime($value);
        } elseif (!$value instanceof DateTime) {
            return;
        }

        if (!$options['humanTime'] && !$options['timestamp']) {
            $this->set($column, $value->format($options['format']));
        } else {
            $values = [
                'format' => $value->format($options['format']),
            ];

            if ($options['timestamp']) {
                $values['timestamp'] = $value->getTimestamp();
            }

            if ($options['humanTime']) {
                $values['humanTime'] = \Carbon\Carbon::createFromTimeStamp($value->getTimestamp())->diffForHumans();
            }

            $this->set($column, $values);
        }
    }

    /**
     * Filter when available data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function filterWhenAvailable(array $columns): JsonResourceManager
    {
        foreach ($columns as $column) {
            $value = $this->$column ?? null;
            $dataValue = $this->data[$column] ?? null;

            if (
                !$this->isEmptyValue($value) ||
                !is_subclass_of($dataValue, self::class) &&
                !$this->isEmptyValue($dataValue)
            ) {
                $this->set($column, $dataValue ?? $value);
            } else {
                unset($this->data[$column]);
            }
        }

        return $this;
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
                if (!is_array($item) && !is_string($item)) {
                    return [];
                }

                return new Fluent($item);
            });
        }

        $resourceDetails = null;

        if (is_array($resource) && isset($resource['resource'])) {
            $resourceDetails = $resource;
            $resource = $resourceDetails['resource'];
        }

        $resources = $resource::collection($collection);
        if ($resourceDetails) {
            $resources->collection = $resources->collection->map(function (JsonResourceManager $resource) use ($resourceDetails) {
                if (!empty($resourceDetails['append'])) {
                    foreach ((array) $resourceDetails['append'] as $type => $columns) {
                        switch ($type) {
                            case 'data':
                                $resource->collectData($columns);
                                break;
                            case 'dates':
                                $resource->collectDates($columns);
                                break;
                            case 'assets':
                                $resource->collectAssets($columns);
                                break;
                            case 'whenAvailable':
                                $resource->filterWhenAvailable($columns);
                                break;
                        }
                    }
                }

                return $resource;
            });
        }

        $this->data[$column] = $resources;
    }

    /**
     * Extend data
     *
     * @param  \Request $request
     * @return array
     */
    protected function extend($request)
    {
    }

    /**
     * Get name
     *
     * @return mixed
     */
    protected function locale($column)
    {
        $value = $this->$column;

        if (empty($value) || !Mongez::requestHasLocaleCode()) return null;

        if (is_string($value)) return $value;

        $localeCode = Mongez::getRequestLocaleCode();

        // get the localization mode
        // it cn be an object or an array of objects
        $localizationMode = config('mognez.localizationMode', 'array');

        // the OR in the following if conditions is used as a fallback for the data that is 
        // not matching the current localization mode 
        // for example, if the data is stored as object and the localization mode is an array
        // in that case it will be rendered as an array 

        if ($localizationMode === 'array' && isset($value[0]) || isset($value[0])) {
            foreach ($value as $localizedValue) {
                if ($localizedValue['localeCode'] === $localeCode) {
                    return $localizedValue['text'];
                }
            }
        } elseif ($localizationMode === 'object' && isset($value[$localeCode]) || isset($value[$localeCode])) {
            return $value[$localeCode];
        }

        return null;
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
    /**
     * Set more data from outside the resource
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function set(string $key, $value)
    {
        Arr::set($this->data, $key, $value);

        return $this;
    }

    /**
     * Append the given key from the resource to the data array
     *
     * @param  string $key
     * @return $this
     */
    public function append(string $key)
    {
        return $this->set($key, $this->$key);
    }

    /**
     * Collect resources from array
     *
     * @param array $collection
     * @return mixed
     */
    public static function collectArray($collection)
    {
        return static::collection(collect($collection)->map(function ($resource) {
            return new Fluent($resource);
        }));
    }
}
