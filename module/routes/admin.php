<?php

/*
|--------------------------------------------------------------------------
| ModuleName Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your admin "back office/dashboard" application.
| Please note that this file is auto imported in the main routes file, so it will inherit the main "prefix"
| and "namespace", so don't edit it to add for example "admin" as a prefix. 
*/
Route::group([
    'namespace' => 'Modules\ModuleName\Controllers\Admin',
    'middleware' => ['logged-in'], // this middleware is used to check if user/admin is logged in
], function () {
    // Restful API CRUD routes 
    Route::apiResource('/route-prefix', 'ControllerName');
});