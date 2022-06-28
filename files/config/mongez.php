<?php

use HZ\Illuminate\Mongez\Testing\Rules\EqualRule;
use HZ\Illuminate\Mongez\Testing\Rules\IsArrayRule;
use HZ\Illuminate\Mongez\Testing\Rules\IsBooleanRule;
use HZ\Illuminate\Mongez\Testing\Rules\IsBoolRule;
use HZ\Illuminate\Mongez\Testing\Rules\IsEmailRule;
use HZ\Illuminate\Mongez\Testing\Rules\IsFloatRule;
use HZ\Illuminate\Mongez\Testing\Rules\IsIntRule;
use HZ\Illuminate\Mongez\Testing\Rules\IsObjectRule;
use HZ\Illuminate\Mongez\Testing\Rules\IsUrlRule;
use HZ\Illuminate\Mongez\Testing\Rules\LengthRule;
use HZ\Illuminate\Mongez\Testing\Rules\MaxLengthRule;
use HZ\Illuminate\Mongez\Testing\Rules\MaxRule;
use HZ\Illuminate\Mongez\Testing\Rules\MinLengthRule;
use HZ\Illuminate\Mongez\Testing\Rules\MinRule;
use HZ\Illuminate\Mongez\Testing\Units\ArrayOfUnit;
use HZ\Illuminate\Mongez\Testing\Units\ArrayUnit;
use HZ\Illuminate\Mongez\Testing\Units\BooleanUnit;
use HZ\Illuminate\Mongez\Testing\Units\BoolUnit;
use HZ\Illuminate\Mongez\Testing\Units\DateUnit;
use HZ\Illuminate\Mongez\Testing\Units\EmailUnit;
use HZ\Illuminate\Mongez\Testing\Units\ErrorKeyValueUnit;
use HZ\Illuminate\Mongez\Testing\Units\FloatUnit;
use HZ\Illuminate\Mongez\Testing\Units\IdUnit;
use HZ\Illuminate\Mongez\Testing\Units\IntUnit;
use HZ\Illuminate\Mongez\Testing\Units\ObjectUnit;
use HZ\Illuminate\Mongez\Testing\Units\PaginationInfoUnit;
use HZ\Illuminate\Mongez\Testing\Units\StringUnit;
use HZ\Illuminate\Mongez\Testing\Units\UrlUnit;
use HZ\Illuminate\Mongez\Testing\Units\ErrorsListUnit;

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
        "mongodb" => [
            "model" => [
                "autoIncrement" => null, // will be auto generated randomly if null
                "initialValue" => null, // will be auto generated randomly if null
            ]
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
    | Validation
    |--------------------------------------------------------------------------
    |
    | You can define your custom validation rules in the `rules` array by defining the rule name as array key
    | and the value will be the rule class.
    | If you want to specify which method to be called on validation, you can define the method name as array [class, methodName].
    | Default method name is `passed`
    |
    */
    'validation' => [
        'rules' => [
            'localized' => Localized::class,
        ]
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
    | The following options are applied on any request related to the RestfulApiController or the /admin requests in general
    | 
    | patchable options: if set to true, then a PATCH request handler method
    | will be invoked from RestfulApiController and the main repository manager
    |
    | returnOn options: single-record | all-records | none
    | 
    */
    'admin' => [
        'patchable' => true,
        'returnOn' => [
            'store' => 'single-record',
            'update' => 'single-record',
            'patch' => 'single-record',
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
    | The `key` will set the name of object key that will hold the input name, defaults to `key`
    | The `value` will set the name of object key that will hold the error message itself, defaults to `value`
    |
    */
    'response' => [
        'errors' => [
            'strategy' => 'array',
            'key' => 'key',
            'value' => 'value',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Postman generator settings
    |--------------------------------------------------------------------------
    | Variables are specific to postman collection
    | 
    |
    */
    'postman' => [
        'variables' => [
            'baseUrl' => env('APP_URL', 'http://localhost'),
            'apiKey' => env('API_KEY', ''),
            'token' => '',
        ]
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
    | Unit Testing Configurations
    |--------------------------------------------------------------------------
    |
    | The headers array will be sent with each request, except the access token request, it has its own headers
    |
    | If The response.rootKey is set, then the response schema will append it to each key
    | For example `response.rootKey = data`, then it will assume that the entire response is sent in the `data` key thus
    | it will be prefixed for each key in the response schema units 
    |
    | The `accessToken.route` will be used when `sAuthenticated` property set to true in any `ApiTestCase`
    | This will generate a new access token from the given route and store its value 
    | to all other requests that need an access token
    |
    | The `tokenResponseKey` is a dot notation key to get the access token from the access token response
    |
    | `units` are list of units that can be used when creating a new response schema validation
    | This can be useful if you're using aliases instead of calling the unit class directly
    |
    */
    'testing' => [
        'headers' => [
            'os' => 'ios',
        ],
        'response' => [],
        'accessToken' => [
            'route' => '/login/guests',
            'tokenResponseKey' => 'data.authorization.accessToken',
            'headers' => [
                'Authorization' => 'key xxx',
            ]
        ],
        'units' => [
            EmailUnit::NAME => EmailUnit::class,
            BooleanUnit::NAME => BooleanUnit::class,
            BoolUnit::NAME => BoolUnit::class,
            IdUnit::NAME => IdUnit::class,
            StringUnit::NAME => StringUnit::class,
            UrlUnit::NAME => UrlUnit::class,
            IntUnit::NAME => IntUnit::class,
            FloatUnit::NAME => FloatUnit::class,
            ArrayOfUnit::NAME => ArrayOfUnit::class,
            ArrayUnit::NAME => ArrayUnit::class,
            ObjectUnit::NAME => ObjectUnit::class,
            PaginationInfoUnit::NAME => PaginationInfoUnit::class,
            ErrorKeyValueUnit::NAME => ErrorKeyValueUnit::class,
            ErrorsListUnit::NAME => ErrorsListUnit::class,
            DateUnit::NAME => DateUnit::class,
        ],
        'rules' => [
            EqualRule::NAME => EqualRule::class,
            IsUrlRule::NAME => IsUrlRule::class,
            IsFloatRule::NAME => IsFloatRule::class,
            IsIntRule::NAME => IsIntRule::class,
            IsBoolRule::NAME => IsBoolRule::class,
            IsBooleanRule::NAME => IsBooleanRule::class,
            IsEmailRule::NAME => IsEmailRule::class,
            IsArrayRule::NAME => IsArrayRule::class,
            IsObjectRule::NAME => IsObjectRule::class,
            MinRule::NAME => MinRule::class,
            MaxRule::NAME => MaxRule::class,
            LengthRule::NAME => LengthRule::class,
            MaxLengthRule::NAME => MaxLengthRule::class,
            MinLengthRule::NAME => MinLengthRule::class,
        ],
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
    | 
    | `withServivce` If set to true, then `Services` directory will be created with `ModuleService` class 
    | It can be overriden by passing --with-service option when using engez:module command
    | @default: true
    |
    | `full` if set to true, then `Events` `Mail` `views` directories will be created as empty directories
    | @default: false
    */
    'console' => [
        'builder' => [
            'withService' => true,
            'full' => false,
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
        Illuminate\Routing\Router::class => HZ\Illuminate\Mongez\Macros\Routing\Router::class,
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
        HZ\Illuminate\Mongez\Database\Filters\MYSQLFilter::class,
        HZ\Illuminate\Mongez\Database\Filters\MongoDBFilter::class,
    ]
];
