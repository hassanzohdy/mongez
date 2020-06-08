# Macro Query Builder

This macro class extends the `Illuminate\Database\Query\Builder` class and the methods can be used also in Models.

- [Macro Query Builder](#macro-query-builder)
- [Adding the macro](#adding-the-macro)
- [Available methods](#available-methods)
  - [whereLike](#wherelike)
    - [Method Syntax](#method-syntax)
    - [Example](#example)
  - [getNextId](#getnextid)
    - [Method Syntax](#method-syntax-1)
    - [Example](#example-1)


# Adding the macro
By default, the macro is enabled once you installed the package successfully.

To enable/disable it go to `app/mongez.php` and add or remove the macro in the `macros` section.

```php
    'macros' => [
        Illuminate\Database\Query\Builder::class => HZ\Illuminate\Mongez\Macros\Database\Query\Builder::class,
    ],
```

# Available methods
- [whereLike()](#whereLike)
- [getNextId()](#getNextId)


## whereLike 
This method is used to filter records using the `WHERE LIKE` clause.

### Method Syntax
`whereLike(string $column, $value): this`

### Example

```php

$users = DB::table('users')->whereLike('email', $request->email)->get();

```

Also this could be used in models.

```php

$users = User::whereLike('email', $request->email)->get();

```

## getNextId
Sometimes you want to get the last id of the table before inserting new record to the table.

So in a simple way, you can do it using the query builder.

### Method Syntax
`getNextId(): int`

### Example

```php

$nextId = DB::table('users')->getNextId();

```

Or using `Model` to get the next id of it.

```php
$nextId = User::getNextId();
```