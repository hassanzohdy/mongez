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
        'users' => App\Modules\Users\Repositories\UsersRepository::class,
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
        Illuminate\Support\Str::class => HZ\Illuminate\Organizer\Macros\Support\Str::class,
        Illuminate\Support\Arr::class => HZ\Illuminate\Organizer\Macros\Support\Arr::class,
        Illuminate\Http\Request::class => HZ\Illuminate\Organizer\Macros\Http\Request::class,
        Illuminate\Support\Collection::class => HZ\Illuminate\Organizer\Macros\Support\Collection::class,
        Illuminate\Database\Query\Builder::class => HZ\Illuminate\Organizer\Macros\Database\Query\Builder::class,
        Illuminate\Database\Schema\Blueprint::class => HZ\Illuminate\Organizer\Macros\Database\Schema\Blueprint::class,
    ],
    'events' => [],
];