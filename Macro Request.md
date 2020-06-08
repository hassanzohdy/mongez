# Macro Request

This macro class extends the `Illuminate\Http\Request` class.

- [Macro Request](#macro-request)
- [Adding the macro](#adding-the-macro)
- [Available methods](#available-methods)
    - [referer](#referer)
        - [Method Syntax](#method-syntax)
        - [Example](#example)
    - [uri](#uri)
        - [Method Syntax](#method-syntax-1)
        - [Example](#example-1)
    - [authorization](#authorization)
        - [Method Syntax](#method-syntax-2)
        - [Example](#example-2)
    - [authorizationValue](#authorizationvalue)
        - [Method Syntax](#method-syntax-3)
        - [Example](#example-3)


# Adding the macro
By default, the macro is enabled once you installed the package successfully.

To enable/disable it go to `app/mongez.php` and add or remove the macro in the `macros` section.

```php

    'macros' => [
        Illuminate\Http\Request::class => HZ\Illuminate\Mongez\Macros\Http\Request::class,
    ],
```

# Available methods
- [referer](#referer)
- [uri](#uri)
- [authorization](#authorization)
- [authorizationValue](#authorizationValue)
  
## referer
Get request referer.

### Method Syntax
`Http::referer(): string`

### Example

```php
$referer = $request->referer();
```

## uri
Get request uri **without the full url**.

### Method Syntax
`Http::uri(): string`

### Example

```php
$uri = $request->uri(); // for example: /posts
```

## authorization
Get request authorization header content.

### Method Syntax
`Http::authorization(): string`

### Example

```php
$authorization = $request->authorization(); // Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VySWQiOiJiMDhmODZhZi0zNWRhLTQ4ZjItOGZhYi1jZWYzOTA0NjYwYmQifQ.-xN_h82PHVTCMA9vdoHrcZxH-x5mb11y1537t3rGzcM
```

## authorizationValue
Get request authorization header **value only without the key**.

### Method Syntax
`Http::authorizationValue(string|bool $authorizationType = true): string|null`

### Example

Let's take same example for the [authorization](#authorization) method

```php
// get the value only
$authorization = $request->authorizationValue(); // eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VySWQiOiJiMDhmODZhZi0zNWRhLTQ4ZjItOGZhYi1jZWYzOTA0NjYwYmQifQ.-xN_h82PHVTCMA9vdoHrcZxH-x5mb11y1537t3rGzcM
```

If you want to make sure that the authorization value matches certain type of authorization, then you can pass the `type` to the method.

If the key is not matched, then it will return null.

Sometimes the `Authorization` header contains a `key` so you may check if its value is for the key or not.

```php
// the sent authorization header is: key PS42ASHHRT5WQ634GSRRWRTE46GPLHM52


// get the value regardless the type

$authorization = $request->authorizationValue(); // PS42ASHHRT5WQ634GSRRWRTE46GPLHM52

// get the value if the Authorization type is `key`

$authorization = $request->authorizationValue('key'); // PS42ASHHRT5WQ634GSRRWRTE46GPLHM52

// get the value if the Authorization type is `Bearer`


$authorization = $request->authorizationValue('Bearer'); // returns null because the current type is key not Bearer
```