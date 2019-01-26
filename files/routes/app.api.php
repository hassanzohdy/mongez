<?php
use App\Models\Airport\Airport;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your admin application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'namespace' => 'Api\Admin',
    'prefix' => '/admin',
    'middleware' => ['logged-in'],
], function () {
    // admin routes    
});


Route::group([
    'namespace' => 'Api\Site',
], function () {
    // normal routes
    
    // Authorized routes
    Route::group([
        'middleware' => ['logged-in'],
    ], function () {
    });    
});