# Macros

Macros are used to extend `append` methods to existing classes on the runtime without modifying the original class.

All macros should be placed in `app/Macros` directory.

> Read more about [Macros](https://www.larashout.com/laravel-macros-extending-laravels-core-classes) to see how helpful it is.

- [Macros](#macros)
- [Creating new macro](#creating-new-macro)
- [Creating Collection class](#creating-collection-class)
- [Available Macros](#available-macros)

# Creating new macro

Let's assume that we want to add the `unshift` method so we can add new element to the collection at the beginning of it.

# Creating Collection class

> Please note that the following example is already implemented in the [Collection macro class](./macros/collection) so you don't need to add it again.
 

Create new `Collection.php` file in the `app/Macros/Support` directory.

> Please note i prefer to make the path of the macros are matched with its original path as the Collection class in laravel path is `Illuminate\Support\Collection`.


```php

class Collection {

}
```

Now we will add our `shift` method.


```php

class Collection {
    /**
     * Add new element to the beginning of the collection
     * 
     * @param  mixed $value
     * @return void
     */
    public function unshift()
    {
        return function ($value) {

        };
    }
}
```

As written above, we won't add the `$value` parameter in the `unshift` method but we will return a `Closure` function that will act exactly as if it is the original method.

> Please note that this is the only way to achieve the macro pattern. 

Now in the body of the `anonymous function` we will create our function implementation.


```php

class Collection {
    /**
     * Add new element to the beginning of the collection
     * 
     * @param  mixed $value
     * @return void
     */
    public function unshift()
    {
        return function ($value) {
            array_unshift($this->items, $value);
        };
    }
}
```

The `items` property is the property that holds all of our collection.

Now we're done here now let's register our macro.

Go to `config/mongez.php` and in the `macors` section add your macro as the key will be the original class and the value will be your `mixin` class as follows:

```php
'macros' => [
    Illuminate\Support\Collection::class => App\Macros\Support\Collection::class
]
```

Now let's see how it works:

```php
$numbers = collect([1, 2, 3]);

$numbers->unshift(0);

// now our collection contains [0, 1, 2, 3]

```

# Available Macros
- [Illuminate\Http\Request](./macro-request) 
- [Illuminate\Support\Collection](./macro-collection) 
- [Illuminate\Support\Str](./macro-str) 
- [Illuminate\Support\Arr](./macro-arr) 
- [Illuminate\Database\Query\Builder](./macro-query) 
- [Illuminate\Database\Schema\Blueprint](./macro-blueprint) 
