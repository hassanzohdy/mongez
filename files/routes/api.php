<?php

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'namespace' => 'Api',
], function () {
    // the following routes are for admin and site 
    // login
    Route::post('/login', 'Auth\LoginController@index');

    // logout
    Route::get('/logout', 'Auth\LogoutController@index');
        
    // forget-password
    Route::post('/forget-password', 'Auth\ForgetPasswordController@index');

    // reset-password
    Route::get('/reset-password/{code}', 'Auth\ResetPasswordController@index');
    Route::post('/reset-password/{code}', 'Auth\ResetPasswordController@reset');
    
    // admin
    Route::group([
        'namespace' => 'Admin',
        'prefix' => '/admin',
    ], function () {    
        Route::group([
            'middleware' => ['logged-in'],
        ], function () {            
            // users
            Route::resource('/users', 'Users\UsersController');                 
        });
    });

    // site 
    Route::group([
        'namespace' => 'Site',
    ], function () {    
        // register
        Route::post('/register', 'Account\RegisterController@index');

        // Account
        Route::group([
            'middleware' => ['logged-in'],
        ], function () {
            // account summary
            Route::get('/account', 'Account\AccountController@index');
            
            // user info
            Route::get('/me', 'Account\MeController@index');

            // update settings
            Route::post('/me/update', 'Account\Settings\UpdateSettingsController@index');
            
            // change password
            Route::post('/me/change-password', 'Account\Settings\ChangePasswordController@index');
        });
    });
});

// app routes
require 'app.api.php';