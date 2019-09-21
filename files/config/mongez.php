<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database options
    |--------------------------------------------------------------------------
    | 
    | These are the `resource` options that can be used with any `Resource` class
    | The `assets` option defines the generating `url` for any asset, by default is `url()`
    |
    */
    'database' => [
        'mysql' => [
            'defaultStringLength' => 191,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources options
    |--------------------------------------------------------------------------
    | 
    | These are the `resource` options that can be used with any `Resource` class
    | The `assets` option defines the generating `url` for any asset, by default is `url()`
    |
    */
    'resources' => [
        'assets' => 'url',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module builder
    |--------------------------------------------------------------------------
    |
    | Based on the settings that is provided here, the module builder will adjust its settings accordingly.
    | Put your configurations based on your application flow
    | 
    | has-admin: if set to false, then Laravel Mongez will treat the application as a single application with no admin panel 
    | 
    */
    'module-builder' => [
        'has-admin' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination configurations
    |--------------------------------------------------------------------------
    | Pagination configurations work with `list` method in any repository.
    |    
    | Any value listed below will be applied on all repositories unless repository/method-call override.   
    */
    'pagination' => [
        'paginate' => true,
        'itemsPerPage' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin options
    |--------------------------------------------------------------------------
    |
    | The following options are applied on any request related to the AdminApiController or the /admin requests in general
    | 
    | returnOn options: single-record | all-records | none
    | 
    */
    'admin' => [
        'returnOn' => [
            'store' => 'single-record',
            'update' => 'single-record',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Repositories
    |--------------------------------------------------------------------------
    |
    | The repositories section will be mainly used for records retrieval... fetching records from database
    | It will also be responsible for inserting/updating and deleting from database 
    |
    */
    'repositories' => [
        // add your repositories here  
        // 'repo-short-name' => RepositoryClassPath::class,
        // Auto generated repositories here: DO NOT remove this line.   
    ],

    /*
    |--------------------------------------------------------------------------
    | Macroable classes
    |--------------------------------------------------------------------------
    |
    | Here you can set your macros classes that will be used to be 
    | The key will be the original class name that will be extends 
    | The value will be the macro class that will be used to extend the original class 
    |
    */
    'macros' => [
        Illuminate\Support\Str::class => HZ\Illuminate\Mongez\Macros\Support\Str::class,
        Illuminate\Support\Arr::class => HZ\Illuminate\Mongez\Macros\Support\Arr::class,
        Illuminate\Http\Request::class => HZ\Illuminate\Mongez\Macros\Http\Request::class,
        Illuminate\Support\Collection::class => HZ\Illuminate\Mongez\Macros\Support\Collection::class,
        Illuminate\Filesystem\Filesystem::class => HZ\Illuminate\Mongez\Macros\FileSystem\FileSystem::class,
        Illuminate\Database\Query\Builder::class => HZ\Illuminate\Mongez\Macros\Database\Query\Builder::class,
        Illuminate\Database\Schema\Blueprint::class => HZ\Illuminate\Mongez\Macros\Database\Schema\Blueprint::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Events list
    |--------------------------------------------------------------------------
    |
    | Set list of events listeners that will be triggered later from its sources 
    |
    */
    'events' => [],
];