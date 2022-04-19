<?php

namespace HZ\Illuminate\Mongez\Resources;

use DateTime;
use Carbon\Carbon;
use IntlDateFormatter;
use Illuminate\Support\Arr;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use HZ\Illuminate\Mongez\Helpers\Mongez;
use Illuminate\Http\Resources\Json\JsonResource;
use HZ\Illuminate\Mongez\Translation\Traits\Translatable;
use HZ\Illuminate\Mongez\Traits\WithRepositoryAndService;

abstract class JsonResourceManager extends JsonResource
{
    use WithRepositoryAndService, Translatable;

    /**
     * Data that must be returned
     *
     * @const array
     */
    public const DATA = [];

    /**
     * String Data
     * 
     * @const array
     */
    public const STRING_DATA = [];

    /**
     * Boolean Data
     * 
     * @const array
     */
    public const BOOLEAN_DATA = [];

    /**
     * Integer Data
     * 
     * @const array
     */
    public const INTEGER_DATA = [];

    /**
     * Float Data
     * 
     * @const array
     */
    public const FLOAT_DATA = [];

    /**
     * Object Data
     * 
     * @const array
     */
    public const OBJECT_DATA = [];

    /**
     * Set that columns that will be formatted as dates
     * it could be numeric array or associated array to set the date format for certain columns
     *
     * @const array
     */
    public const DATES = [];

    /**
     * Data that has multiple values based on locale codes
     *
     * @const array
     */
    public const LOCALIZED = [];

    /**
     * List of assets that will have a full url if available
     *
     * @const array
     */
    public const ASSETS = [];

    /**
     * Data that will be returned as a resources
     *
     * i.ie [city => CityResource::class]
     *
     * @const array
     */
    public const RESOURCES = [];

    /**
     * Data that will be returned as a collection of resources
     *
     * i.ie [city => CityResource::class]
     *
     * @const array
     */
    public const COLLECTABLE = [];

    /**
     * Data that should be returned if exists
     *
     * @const array
     */
    public const WHEN_AVAILABLE = [];

    /**
     * Set the float round value
     * Defaults to 2
     * 
     * @const int
     */
    public const FLOAT_ROUND = 2;

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
     * Remove value from being sent to response
     * 
     * @param string $keys
     * @return void
     */
    public function remove(...$keys)
    {
        foreach ($keys as $key) {
            unset($this->data[$key]);
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

    /**
     * Collect mandatory data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    protected function collectData(array $columns): JsonResourceManager
    {
        return $this->setData($columns);
    }

    /**
     * Set the given columns data
     * 
     * @param array $columns
     * @param callable $valueCallback
     * @return $this
     */
    protected function setData(array $columns, callable $valueCallback = null): JsonResourceManager
    {
        foreach ($columns as $column => $outputKey) {
            $column = is_numeric($column) ? $outputKey : $column;

            if ($this->ignoreEmptyColumn($column)) continue;

            $this->set($outputKey, $valueCallback ? $valueCallback($column) : $this->value($column));
        }

        return $this;
    }

    /**
     * if the output key is numeric, then we'll return the column instead
     * 
     * @param  int|string $outputKey
     * @param string $column
     * @return string
     */
    // protected function outputKey($outputKey, string $column): string
    // {
    //     return is_numeric($outputKey) ? $column : $outputKey;
    // }

    /**
     * Collect String Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    protected function collectStringData(array $columns): JsonResourceManager
    {
        return $this->setData($columns, function ($column) {
            return (string) $this->value($column, '');
        });
    }

    /**
     * Collect Integer Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    protected function collectIntegerData(array $columns): JsonResourceManager
    {
        return $this->setData($columns, function ($column) {
            return (int) $this->value($column, 0);
        });
    }

    /**
     * Collect Float Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    protected function collectFloatData(array $columns): JsonResourceManager
    {
        return $this->setData($columns, function ($column) {
            return (float) round(
                (float) $this->value($column, 0),
                static::FLOAT_ROUND
            );
        });
    }

    /**
     * Collect Float Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    protected function collectBooleanData(array $columns): JsonResourceManager
    {
        return $this->setData($columns, function ($column) {
            return (bool) $this->value($column, false);
        });
    }

    /**
     * Collect Object Data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    protected function collectObjectData(array $columns): JsonResourceManager
    {
        return $this->setData($columns, function ($column) {
            return (object) $this->value($column, []);
        });
    }

    /**
     * Collect localized data
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    protected function collectLocalized(array $columns): JsonResourceManager
    {
        return $this->setData($columns, function ($column) {
            return $this->locale($column);
        });
    }

    /**
     * Get localized 
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
     * Collect assets
     *
     * @param array $columns
     * @return JsonResourceManager
     */
    protected function collectAssets(array $columns): JsonResourceManager
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

        foreach ($columns as $column => $outputName) {
            if (is_numeric($column)) {
                $column = $outputName;
            }

            if ($this->ignoreEmptyColumn($column)) continue;

            $asset = Arr::get($resource, $column, '');

            if (!$asset) {
                $this->set($column, '');
                continue;
            }

            if (is_string($asset) && is_json($asset)) {
                $asset = json_decode($asset);
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

                $this->set($outputName, $assets);
            } else {
                $this->set($outputName, call_user_func($this->assetsUrlFunction, $asset));
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
    protected function collectCollectables(array $columns): JsonResourceManager
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
    protected function collectResources(array $columns): JsonResourceManager
    {
        foreach ($columns as $column => $resource) {
            if ($this->ignoreEmptyColumn($column)) continue;

            $this->setResource($column, $resource);
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
     * Set resource value
     * 
     * @param  string $column
     * @param  string $resourceClassName
     * @return $this
     */
    protected function setResource($column, string $resourceClassName): JsonResourceManager
    {
        $resourceData = $this->value($column);

        return $this->set($column, $resourceData === null ? null : new $resourceClassName(new Fluent($resourceData)));
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

            if ($options['humanTime']) {
                $values['humanTime'] = Carbon::createFromTimeStamp($value->getTimestamp())->diffForHumans();
            }

            if ($options['intl']) {
                $values['text'] = $this->getLocalizedDate($value);
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
    protected function getLocalizedDate($date): string
    {
        if (!class_exists(IntlDateFormatter::class)) return '';

        $formatter = new IntlDateFormatter(
            Mongez::getRequestLocaleCode() ?: App::getLocale(),
            IntlDateFormatter::FULL,
            IntlDateFormatter::SHORT,
        );

        return $formatter->format($date);
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
                        }
                    }
                }

                return $resource;
            });
        }

        $this->set($column, $resources);
    }


    /**
     * Extend data with more complex returned values
     *
     * @param  \Request $request
     * @return void
     */
    protected function extend($request)
    {
    }
}
