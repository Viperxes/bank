<?php

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

Route::get('/accounts/{account}/balance', 'AccountController@getBalance');
Route::put('/accounts/{account}/deposit', 'AccountController@deposit');
Route::put('/accounts/{account}/withdraw', 'AccountController@withdraw');
Route::post('/accounts/transfer', 'AccountController@transfer');
Route::get('/accounts/{account}/transactions', 'AccountController@getTransactions');

Route::apiResource('accounts', 'AccountController');