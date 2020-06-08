# Configurations

Run the following command to create the `config/mongez.php` file.

`php artisan vendor:publish --provider="HZ\Illuminate\Mongez\Providers\MongezServiceProvider"`

> Each part of the package has its own configurations, so you'll find the corresponding documentation based on its location of the wiki pages.

## Auth config

in the `config/auth.php` add the following code 

```php
return [
    
    'guards' => [
        // .. at the bottom of the guards key
        // add the admin guard info
        'admin' => [
            'driver' => 'session',
            'provider' => 'users',
            'repository' => 'users',
        ],
        
         // it maybe customers in the front site instead of users
        'site' => [
            'driver' => 'session',
            'provider' => 'users',
            'repository' => 'users',
        ],        
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            // modify the model to the user model
            'model' => App\Modules\Users\Models\User::class,
        ],
];
```

We didn't change anything but we add only the `admin` and `site` keys to the `guards` array, so we can determine which provider to be used when we're in the admin or the front office site.


## Filesystem
If you don't have sensitive files, you may change the path of the `local` storage from `storage_path('app')` to `public_path()`  

`config/filesystem.php`

```php

    // change this 
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        ...
    ],

    // to 
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => public_path(),
        ],
        ...
    ],
```

# ENV
As the package is for APIs, we should've at least an `API key` for non authorized requests, which means the requests that the user is not logged in.

In your `.env` file, add `API_KEY` with a `32` string length of random letters and numbers.

This will make sure that any request for a `non authorized` user must send a `Authorization: key API_KEY` with each request.

For example:

`.env`

```
API_KEY=QA34GH9ZXDRFF5TY7UJMHL9YXWZ15V4A
```