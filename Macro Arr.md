# Macro Arr

This macro class extends the `Illuminate\Support\Arr` class.

- [Macro Arr](#macro-arr)
- [Adding the macro](#adding-the-macro)
- [Available methods](#available-methods)
  - [Remove](#remove)
    - [Method Syntax](#method-syntax)
    - [Example](#example)


# Adding the macro
By default, the macro is enabled once you installed the package successfully.

To enable/disable it go to `config/mongez.php` and add or remove the macro in the `macros` section.

```php
    'macros' => [
        Illuminate\Support\Arr::class => HZ\Illuminate\Mongez\Macros\Support\Arr::class,
    ],
```

# Available methods
- [remove](#remove)
  
## Remove
Remove value from the given array

if the `$removeFirstOnly` argument is set to true, it will remove the first matching value only, otherwise `default` it will remove all values from the array.

### Method Syntax
`Arr::remove(mixed $value, array $array, bool $removeFirstOnly = false): array`

### Example

```php

use Illuminate\Support\Arr;

$numbers = [1, 2, 3, 4, 6, 1, 2];

$numbers = Arr::remove(1, $numbers);

dd($numbers); // [2, 3, 4, 6, 2]

// remove the first matching value only
$numbers = Arr::remove(2, $numbers, true);

dd($numbers); // [3, 4, 6, 2]

```