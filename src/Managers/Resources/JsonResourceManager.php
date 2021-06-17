<?php

namespace HZ\Illuminate\Mongez\Managers\Resources;

use DateTime;
use IntlDateFormatter;
use Illuminate\Support\Arr;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class JsonResourceManager extends JsonResource
{
    /**
     * Data that must be returned
     *
     * @const array
     */
    const DATA = [];

    /**
     * String Data
     * 
     * @const array
     */
    const STRING_DATA = [];

    /**
     * Boolean Data
     * 
     * @const array
     */
    const BOOLEAN_DATA = [];

    /**
     * Integer Data
     * 
     * @const array
     */
    const INTEGER_DATA = [];

    /**
     * Float Data
     * 
     * @const array
     */
    const FLOAT_DATA = [];

    /**
     * Object Data
     * 
     * @const array
     */
    const OBJECT_DATA = [];

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

        $this->collectStringData(static::STRING_DATA);

        $this->collectIntegerData(static::INTEGER_DATA);

        $this->collectFloatData(static::FLOAT_DATA);

        $this->collectBooleanData(static::BOOLEAN_DATA);

        $this->collectObjectData(static::OBJECT_DATA);

        $this->collectLocalized(static::LOCALIZED);

        $this->collectAssets(static::ASSETS);

        $this->collectCollectables(static::COLLECTABLE);

        $this->collectResources(static::RESOURCES);

        $this->collectDates(static::DATES);

        // $this->filterWhenAvailable(static::WHEN_AVAILABLE);

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
            if ($this->ignoreEmptyColumn($column)) continue;

            $value = $this->value($column);

            if (is_float($value)) {
                $value = round($value, 2);
            }

            $this->set($column, $value);
        }

        return $this;
    }

    /**
     * Collect String Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectStringData(array $columns): JsonResourceManager
    {
        foreach ($columns as $column) {
            if ($this->ignoreEmptyColumn($column)) continue;

            $this->set($column, (string) $this->value($column, ''));
        }

        return $this;
    }

    /**
     * Collect Integer Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectIntegerData(array $columns): JsonResourceManager
    {
        foreach ($columns as $column) {
            if ($this->ignoreEmptyColumn($column)) continue;

            $this->set($column, (int) $this->value($column, 0));
        }

        return $this;
    }

    /**
     * Collect Float Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectFloatData(array $columns): JsonResourceManager
    {
        foreach ($columns as $column) {
            if ($this->ignoreEmptyColumn($column)) continue;

            $this->set($column, (float) $this->value($column, 0));
        }

        return $this;
    }

    /**
     * Collect Float Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectBooleanData(array $columns): JsonResourceManager
    {
        foreach ($columns as $column) {
            if ($this->ignoreEmptyColumn($column)) continue;

            $this->set($column, (bool) $this->value($column, false));
        }

        return $this;
    }

    /**
     * Collect Object Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    public function collectObjectData(array $columns): JsonResourceManager
    {
        foreach ($columns as $column) {
            if ($this->ignoreEmptyColumn($column)) continue;

            $this->set($column, (object) $this->value($column, []));
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
            if ($this->ignoreEmptyColumn($column)) continue;

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
            if ($this->ignoreEmptyColumn($column)) continue;

            $collection = $this->value($column);

            if ($collection !== null) {
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
            if ($this->ignoreEmptyColumn($column)) continue;

            $resourceData = $this->value($column);

            $this->set($column, $resourceData === null ? null : new $resource(new Fluent($resourceData)));
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
            $dateOptions = [];
            if (is_string($key)) {
                if (is_string($column)) {
                    $dateOptions['format'] = $column;
                } else {
                    $dateOptions = $column;
                }
                $column = $key;
            }

            if ($this->ignoreEmptyColumn($column)) continue;

            $value = $this->value($column);

            if ($value === null) {
                $this->set($column, null);
                continue;
            }

            $this->setDate($column, $value, $dateOptions);
        }

        return $this;
    }

    /**
     * Determine whether to ignore the empty data for the given column
     * 
     * @param string $column
     * @return bool
     */
    protected function ignoreEmptyColumn(string $column): bool
    {
        $value = $this->value($column);

        if (in_array($value, [0, false], true)) return false;

        return empty($value) && in_array($column, static::WHEN_AVAILABLE);
    }

    /**
     * Get value from resource
     * 
     * @param  string $column
     * @param  mixed $default
     * @return mixed
     */
    protected function value(string $column, $default = null)
    {
        return Arr::get($this->resource, $column, $default);
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
            'intl' => config('mongez.resources.date.intl', true),
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
            $this->set($column, $options['intl'] ? $this->getLocalizedDate($value) : $value->format($options['format']));
        } else {
            $values = [
                'format' => $value->format($options['format']),
            ];

            if ($options['timestamp']) {
                $values['timestamp'] = $value->getTimestamp();
            }

            if ($options['intl']) {
                $values['text'] = $this->getLocalizedDate($value);
            }

            if ($options['humanTime']) {
                $values['humanTime'] = \Carbon\Carbon::createFromTimeStamp($value->getTimestamp())->diffForHumans();
            }

            $this->set($column, $values);
        }
    }

    /**
     * Get a localized date based on current locale code
     * 
     * @param  DateTime $date
     * @return string
     */
    protected function getLocalizedDate($date)
    {
        $formatter = new IntlDateFormatter(
            Mongez::getRequestLocaleCode() ?: App::getLocale(),
            IntlDateFormatter::FULL,
            IntlDateFormatter::SHORT,
        );

        return $formatter->format($date);
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
            $value = $this->value($column);
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
                                // case 'whenAvailable':
                                //     // $resource->filterWhenAvailable($columns);
                                //     break;
                        }
                    }
                }

                return $resource;
            });
        }

        $this->set($column, $resources);
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
        $value = $this->value($column);

        if (empty($value)) return '';

        if (is_string($value) || !Mongez::requestHasLocaleCode()) return $value;

        $localeCode = Mongez::getRequestLocaleCode();

        // get the localization mode
        // it cn be an object or an array of objects
        $localizationMode = config('mognez.localizationMode', 'array');

        // the OR in the following if conditions is used as a fallback for the data that is 
        // not matching the current localization mode 
        // for example, if the data is stored as object and the localization mode is an array
        // in that case it will be rendered as an array 

        if ($localizationMode === 'array' && isset($value[0]) || isset($value[0])) {
            $valuesList = [];
            foreach ($value as $localizedValue) {
                // check if it is an array of values
                if (isset($localizedValue[0])) {
                    foreach ($localizedValue as $subValue) {
                        if ($subValue['localeCode'] === $localeCode) {
                            $valuesList[] = (string) $subValue['text'];
                        }
                    }
                } else {
                    if ($localizedValue['localeCode'] === $localeCode) {
                        return (string) $localizedValue['text'];
                    }
                }
            }
            return $valuesList ?: $value;
        } elseif ($localizationMode === 'object' && isset($value[$localeCode]) || isset($value[$localeCode])) {
            return (string) $value[$localeCode];
        }

        return $value;
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
