<?php

/*
|--------------------------------------------------------------------------
| Users Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your admin "back office/dashboard" application.
| Please note that this file is auto imported in the main routes file, so it will inherit the main "prefix"
| and "namespace", so don't edit it to add for example "admin" as a prefix. 
*/
Route::post('/login', 'Modules\Users\Controllers\Admin\Auth\LoginController@index');
Route::get('/logout', 'Modules\Users\Controllers\Admin\Auth\LogoutController@index');
Route::group([
    'namespace' => 'Modules\Users\Controllers\Admin',
    'middleware' => ['logged-in'], // this middleware is used to check if user/admin is logged in
], function () {
    // Restful API CRUD routes 
    Route::apiResource('/users', 'UsersController');
});