<?php

return [
    /**
     * This is mainly used to override the max key length
     * @visit https://laravel-news.com/laravel-5-4-key-too-long-error
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
        // add your repositories here
        
        // 'repo-short-name' => RepositoryClassPath::class,
        // for example
        // 'users' => App\Repositories\Users\UsersRepository::class,
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
        Illuminate\Support\Str::class => HZ\Laravel\Organizer\App\Macros\Support\Str::class,
        Illuminate\Support\Arr::class => HZ\Laravel\Organizer\App\Macros\Support\Arr::class,
        Illuminate\Support\Collection::class => HZ\Laravel\Organizer\App\Macros\Support\Collection::class,
        Illuminate\Database\Schema\Blueprint::class => HZ\Laravel\Organizer\App\Macros\Database\Schema\Blueprint::class,
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
        'Arr' => Illuminate\Support\Arr::class,
        'Str' => Illuminate\Support\Str::class,
        'Request' => Illuminate\Http\Request::class,
        'Collection' => Illuminate\Support\Collection::class,
        'Item' => HZ\Laravel\Organizer\App\Managers\Item::class,
        'ApiController' => HZ\Laravel\Organizer\App\Managers\ApiController::class,
        'Model' => HZ\Laravel\Organizer\App\Helpers\Database\Eloquent\Model::class,
        'RepositoryTrait' => HZ\Laravel\Organizer\App\Traits\RepositoryTrait::class,
    ],
];