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

Route::group(['namespace' => 'App\Api\v1\Controllers'], function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('users', ['uses' => 'UserController@index']);
    });
});

use App\Http\Controllers\Auth\Api\ApiAuthController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [ApiAuthController::class, 'login']);
    Route::get('logout', [ApiAuthController::class, 'logout']);
    Route::get('refresh', [ApiAuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});