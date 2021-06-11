<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'prefix' => 'v1'
], function () {
    Route::group([
        'prefix' => 'user'
    ], function () {
        Route::post('register', 'Api\Auth\User\RegisterController@register')
            ->name('user.register');
        Route::post('login', 'Api\Auth\User\LoginController@login')
            ->name('user.login');
    });
    Route::group([
        'prefix' => 'shopkeeper'
    ], function () {
        Route::post('register', 'Api\Auth\Shopkeeper\RegisterController@register')
            ->name('shopkeeper.register');
        Route::post('login', 'Api\Auth\Shopkeeper\LoginController@login')
            ->name('shopkeeper.login');
    });
});

Route::group([
    'prefix' => 'v1',
    'middleware' => 'auth:api'
], function () {
    Route::post('transaction', 'Api\TransactionController@transaction')
        ->name('transaction');
});
