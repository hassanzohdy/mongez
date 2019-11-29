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
Route::post('/login', 'Modules\Users\Controllers\Admin\Auth\LoginController@index')->name('login');
Route::get('/logout', 'Modules\Users\Controllers\Admin\Auth\LogoutController@index')->name('logout');

Route::group([
    'namespace' => 'Modules\Users\Controllers\Admin',
    'middleware' => ['logged-in','check-permission'], // this middleware is used to check if user/admin is logged in
], function () {
    // Restful API CRUD routes
    Route::apiResource('/users/permissions', 'PermissionsController');
    Route::apiResource('/users/groups', 'UsersGroupsController');
    Route::apiResource('/users', 'UsersController');
    // Child API CRUD routes
});