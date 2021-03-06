<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('ball/index');
});

Route::get('/media', function () {
    return view('video/media');
});

Route::get('/mediaShow', function () {
    return view('video/media_show');
});

Route::get('/test', 'TestController@test');

