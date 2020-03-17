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

Route::get('/', function () {
    return [
        'app' => 'Note App API',
        'version' => '1.0.0',
    ];
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'API\AuthController@login');
    Route::post('register', 'API\AuthController@register');

    // Route::post('verify', 'Auth\VerificationController@index');
    // Route::post('resend_code', 'Auth\VerificationController@resend_code');

    Route::post('password/email', 'Auth\ResetPasswordController@sendEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@resetPassword');

    Route::post('refresh', 'API\AuthController@refresh');
});

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::group(['prefix' => 'admin', 'middleware' => ['role:admin']], function () { });

    Route::group(['middleware' => ['role:user']], function () {
        Route::apiResource('notes', 'API\NotesController')->except('show', 'update', 'destroy');

        Route::group(['prefix' => 'notes'], function () {
            Route::put('{uuid}', 'API\NotesController@update');

            Route::delete('{uuid}', 'API\NotesController@destroy');
        });
    });
});
