<?php

/*
|--------------------------------------------------------------------------
| Localization Site Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your main "front office" application.
| Please note that this file is auto imported in the main routes file, so it will inherit the main "prefix"
| and "namespace", so don't edit it to add for example "api" as a prefix. 
*/
Route::group([
    'namespace' => 'Modules\Localization\Controllers\Site',
], function () {
    // list records
    Route::get('/regions', 'RegionsController@index');
    Route::get('/cities', 'CitiesController@index');
    Route::get('/countries', 'CountriesController@index');
    Route::get('/currencies', 'CurrenciesController@index');
    // one record 
    Route::get('/regions/{id}', 'RegionsController@show');
    Route::get('/cities/{id}', 'CitiesController@show');
    Route::get('/countries/{id}', 'CountriesController@show');
    Route::get('/currencies/{id}', 'CurrenciesController@show');
    // Child routes
});