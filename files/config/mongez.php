<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database options
    |--------------------------------------------------------------------------
    | 
    | `prefix` value will be added to every model query, 
    | however, if the model has a `TABLE_PREFIX` constant with a value rather than NULL
    | it will be used instead 
    |
    | `updatesLogModel` if set a model class, any updates that occurs to every model will be stored 
    | in the given model to be logged later. 
    |
    | Please Note this will massively increase the updates log model size as every update is stored before the update happens.
    | Please read the documentation for the column names  
    */
    'database' => [
        'mysql' => [
            'defaultStringLength' => 191,
        ],
        'prefix' => '',
        'updatesLogModel' => HZ\Illuminate\Mongez\Models\UpdateLog::class,
        'onModel' => [
            'update' => [
                // the UpdatedModel::class => update options
            ],
            'delete' => [
                // the DeletedModel::class => searchingColumn
            ],
            'deletePull' => [
                // the DeletedModel::class => searchingColumn
            ],
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
    | The date key provides the date options that can be used for any date column 
    | `format`: the date format that will be returned. 
    | `timestamp`: if set to true, the unix timestamp will be returned as integer.  
    | `human`: if set to true, a human time will be returned i.e 12 minutes ago.  
    | `intl`: Display formatted date in locale text  
    |
    |  Please note that if the timestamp and human time are set to true, the 
    |  date format will be returned as string, otherwise it will be returned as array`object`.   
    |
    */
    'resources' => [
        'assets' => 'url',
        'date' => [
            'format' => 'd-m-Y h:i:s a',
            'timestamp' => true,
            'humanTime' => true,
            'intl' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | General Configurations
    |--------------------------------------------------------------------------
    | 
    | The serialize_precision option if set to -1 will encode the float numbers properly  
    |
    */
    'serialize_precision' => -1,

    /*
    |--------------------------------------------------------------------------
    | Locale Codes List
    |--------------------------------------------------------------------------
    |
    | This will determine all available locale codes in the application
    | It will be used to generate translation files when generating new module
    |
    */
    'localeCodes' => [
        'en',
        'ar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization Mode
    |--------------------------------------------------------------------------
    | 
    | This will determine the type of handing data that has multiple values based on locale code
    | Mainly it will be used with resources when returning the data
    | 
    | Available options: array|object
    */
    'localizationMode' => 'array',

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
    | Repository Options
    |--------------------------------------------------------------------------
    |
    | List of repository options located here
    |
    |--------------------------------------------------------------------------
    | Uploads configurations
    |--------------------------------------------------------------------------
    |
    | Setting the uploads directory will be useful when dealing with git repositories to be ignored.
    | If sets to null, then it won't be used
    |
    | This directory will be created inside local directory path in the `config/filesystem.php`
    |
    | keepUploadsName:
    | If set to true, then all uploads names wil be kept as it is.
    | If set to false, a random generated hashed name will be used instead.
    |
    |--------------------------------------------------------------------------
    | Cache configurations
    |--------------------------------------------------------------------------
    | When enabling caching in repositories, set the driver that will be used
    | Available drivers are the available ones in Laravel config/cache.php drivers list
    |
    |--------------------------------------------------------------------------
    | Pagination configurations
    |--------------------------------------------------------------------------
    | Pagination configurations work with `list` method in any repository.
    |
    | Any value listed below will be applied on all repositories unless repository/method-call override.
    |
    */
    'repository' => [
        'cache' => [
            'driver' => '',
        ],
        'publishedColumn' => 'published',
        'uploads' => [
            'uploadsDirectory' => 'data',
            'keepUploadsName' => true,
        ],
        'pagination' => [
            'paginate' => true,
            'itemsPerPage' => 15,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Options
    |--------------------------------------------------------------------------
    | badRequest Response Map strategy
    |
    | If the response map strategy is set as array, then it will be returned as array of objects 
    | each object looks like [key => input, value => message]
    | However, key and value can be customized as well.
    | 
    | Available Options: `array` | `object`, defaults to `array`
    |
    | The arrayKey will set the name of object key that will hold the input name, defaults to `key`
    | The arrayValue will set the name of object key that will hold the error message itself, defaults to `value`
    |
    */
    'response' => [
        'errors' => [
            'strategy' => 'array',
            'arrayKey' => 'key',
            'arrayValue' => 'value',
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
    | Console Options
    |--------------------------------------------------------------------------
    |
    | List of all console options that may be used when using command line
    |
    | All module builder configurations can be overridden when using the command line 
    |
    | build: this will determine if the module will be created 
    | to be served with the admin api controller + api controller or
    | to be served with the admin view controller + view controller
    | available values: view|api, defaults to api
    */
    'console' => [
        'builder' => [
            'build' => 'api',
            'controller' => [
                // available options are: all | site | admin
                'type' => 'all',
                'auth' => [
                    // auto add auth middleware when generating admin routes
                    'enabled' => true,
                    // middleware name that will be used for authorized requests
                    'middleware' => 'authorized',
                ]
            ],
        ]
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
        Illuminate\Console\Command::class => HZ\Illuminate\Mongez\Macros\Console\Command::class,
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

    /*
    |--------------------------------------------------------------------------
    | Base filters 
    |--------------------------------------------------------------------------
    |
    */
    'filters' => [
        HZ\Illuminate\Mongez\Helpers\Filters\MYSQL\Filter::class,
        HZ\Illuminate\Mongez\Helpers\Filters\MongoDB\Filter::class,
    ]
];
