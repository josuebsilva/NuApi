<?php
use Illuminate\Support\Facades\Route;
/*
 * NuAPI
 *
 * Copyright 2023 Josué Barbosa
 * https://jedibit.com.br
 * https://stimper.com.br
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */


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

Route::group(['as' => 'api.', 'middleware' => []], function () {
    //Region


    Route::group(['prefix' => 'auth','as' => 'auth.', 'middleware' => []], function () {
        Route::post('/login', 'Api\AuthController@login')->name('authUser');
        Route::post('/signup', 'Api\AuthController@signup')->name('signupUser');
        Route::post('/forgot', 'Api\AuthController@forgot')->name('forgot');
    });

    Route::group(['prefix' => 'users','as' => 'users.', 'middleware' => ['api.req']], function () {
        Route::put('/update', 'Api\AuthController@update')->name('update');
        Route::put('/update-avatar', 'Api\AuthController@updateAvatar')->name('updateAvatar');
        Route::get('/info', 'Api\AuthController@getInfo')->name('getInfo');
        Route::delete('/delete-account', 'Api\UsersController@deleteUser')->name('deleteUser');
    });

    Route::group(['prefix' => 'banks','as' => 'banks.', 'middleware' => ['api.req']], function () {
        Route::post('/nubank/token', 'Api\Connections\Banks\NubankController@getToken')->name('getToken');
        Route::put('/nubank/sync', 'Api\Connections\Banks\NubankController@sync')->name('sync');
        Route::post('/nubank/extract', 'Api\Connections\Banks\NubankController@getExtract')->name('getExtract');
        Route::put('/nubank/certificate', 'Api\Connections\Banks\NubankController@updateCertificate')->name('updateCertificate');
    });
});
