# Macro Collection

This macro class extends the `Illuminate\Support\Collection` class.

- [Macro Collection](#macro-collection)
- [Adding the macro](#adding-the-macro)
- [Available methods](#available-methods)
    - [walk](#walk)
        - [Method Syntax](#method-syntax)
        - [Example](#example)
    - [Unshift](#unshift)
        - [Method Syntax](#method-syntax-1)
        - [Example](#example-1)
    - [Remove](#remove)
        - [Method Syntax](#method-syntax-2)
        - [Example](#example-2)


# Adding the macro
By default, the macro is enabled once you installed the package successfully.

To enable/disable it go to `app/mongez.php` and add or remove the macro in the `macros` section.

```php

    'macros' => [
        Illuminate\Support\Collection::class => HZ\Illuminate\Mongez\Macros\Support\Collection::class,
    ],
```

# Available methods
- [walk](#walk)
- [unshift](#unshift)

## walk
If we want to execute a callback function on the collection **without creating new collection** then we will use this method instead of the `Collection::map()` method


### Method Syntax

`$collectionObject->walk(Closure $anonymous): void`

Let's see how it works in action

### Example
```php

$priceList = collect([10, 20, 30, 40]);

// add 10% taxes to the price
$priceList->walk(function (& $price) {
    $price += $price * 10 / 100;
});

// now use the same collection normally
```

> Please note that this method works only with functions that accept parameters by reference only.

## Unshift
Add one or more value to the beginning of the collection 

### Method Syntax
`$collectionObject->unshift($value, $anotherValue,...): void`

### Example

```php

$priceList = collect([56, 72, 12, 88]);

$priceList->unshift(20); 

dd($priceList); // [20, 56, 72, 12, 88]

$priceList->unshift(2, 5, 10, 15);

dd($priceList); // [2, 5, 10, 15, 20, 56, 72, 12, 88]

```

## Remove
Remove value from the collection

### Method Syntax
`$collectionObject->remove(mixed $value, bool $removeFirstOnly = false): void`

### Example

```php

$numbers = collect([1, 2, 3, 4, 6, 1, 2]);

$numbers->remove(1); 

dd($numbers); // [2, 3, 4, 6, 2]

$numbers->remove(2, true); 

dd($numbers); // [3, 4, 6, 2]

```