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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'prefix'     => 'socket',
    'namespace'  => 'Socket',
], function () {
    //工单
    Route::any('ball/open', 'BallController@open');
    Route::any('ball/close', 'BallController@close');
    Route::any('ball/move', 'BallController@move');
    Route::any('ball/setName', 'BallController@setName');


    Route::any('ball/video', 'VideoController@index');
});

