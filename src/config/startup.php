<?php

return [
    /**
     * This is mainly used to override the max key length
     * @visit Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes
     */
    'database' => [
        'mysql' => [
            'defaultStringLength' => 191,
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
        'users' => App\Repositories\Users\UsersRepository::class,
        'usersGroups' => App\Repositories\Users\UsersGroupsRepository::class,
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
        Illuminate\Support\Str::class => App\Macros\Support\Str::class,
        Illuminate\Support\Arr::class => App\Macros\Support\Arr::class,
        Illuminate\Support\Collection::class => App\Macros\Support\Collection::class,
        Illuminate\Database\Schema\Blueprint::class => App\Macros\Database\Schema\Blueprint::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */
    
    'aliases' => [
        'Request' => Illuminate\Http\Request::class,
        'Model' => App\Helpers\Database\Eloquent\Model::class,
        'Item' => App\Managers\Item::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Str' => Illuminate\Support\Str::class,
        'Collection' => Illuminate\Support\Collection::class,
        'ApiController' => App\Managers\ApiController::class,
    ],
];