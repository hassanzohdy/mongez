# Macro Str

This macro class extends the `Illuminate\Support\Str` class.

- [Macro Str](#macro-str)
- [Adding the macro](#adding-the-macro)
- [Available methods](#available-methods)
    - [RemoveFirst](#removefirst)
        - [Method Syntax](#method-syntax)
        - [Example](#example)


# Adding the macro
By default, the macro is enabled once you installed the package successfully.

To enable/disable it go to `app/mongez.php` and add or remove the macro in the `macros` section.

```php

    'macros' => [
        Illuminate\Support\Str::class => HZ\Illuminate\Mongez\Macros\Support\Str::class,
    ],
```

# Available methods
- [removeFirst](#removeFirst)
  
## RemoveFirst
Remove the first occurrence of the given needle from the given string

### Method Syntax
`Arr::remove(string $needle, string $string): string`

### Example

```php

use Illuminate\Support\Str;

$myString = 'Hello World!!';

echo Str::removeFirst('Hello', $myString); // World!

```