# Resources

When building an API, we want layer to transform between the model and JSON response, Resource allows you to control the returned data from module model to API.

# What you find in module resource file

```php
namespace App\Modules\ModuleName\Resources;

use HZ\Illuminate\Mongez\Managers\Resources\JsonResourceManager;

class ModuleName extends JsonResourceManager 
{
    /**
     * Data that must be returned
     * 
     * @const array
     */
    const DATA = [];
}
```

In `DATA` constant mongez auto fills it with all entered command option data.

```php
    /**
     * Data that should be returned if exists
     * 
     * @const array
     */
    const WHEN_AVAILABLE = [];
```

In `WHEN_AVAILABLE` set data that returned if has value.


```php
    /**
     * Set that columns that will be formatted as dates
     * it could be numeric array or associated array to set the date format for certain columns
     * 
     * @const array
     */
    const DATES = [];
```

In `DATES` set all you dates data.

```php
    /**
     * Data that has multiple values based on locale codes
     * Mostly this is used with mongodb driver
     * 
     * @const array
     */
    const LOCALIZED = [];
```

In `LOCALIZED` set all you data that returned based on language.


```php
    /**
     * List of assets that will have a full url if available
     */
    const ASSETS = [];
```
In `ASSETS` set all your assets generate full url.


```php
    /**
     * Data that will be returned as a resources
     * 
     * i.e [city => CityResource::class],
     * @const array
     */
    const RESOURCES = [];
```

In `RESOURCES` set all keys with the resource name.


```php
    /**
     * Data that will be returned as a collection of resources
     * 
     * i.e [cities => CityResource::class],
     * @const array
     */
    const COLLECTABLE = []
```

In `COLLECTABLE` set all keys with the resource name and return with collection of resource.

```php
    /**
     * List of keys that will be unset before sending
     * 
     * @var array
     */
    protected static $disabledKeys = [];
```

In `disabledKeys` set all data that will be removed.

```php
    /**
     * List of keys that will be taken only
     * 
     * @var array
     */
    protected static $allowedKeys = [];
```

In `allowedKeys` set all data that will be allowed to return.

### Functions

```php
    /**
     * Extend data
     * 
     * @param  \Request $request
     * @return array
     */
    protected function extend($request)
    { 
    }
```

If you want to extra option not exist in default resource you can implement your own.     