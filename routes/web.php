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
    \Illuminate\Support\Facades\Cache::put('a', 1);
    $a = \Illuminate\Support\Facades\Cache::get('a');
    \Illuminate\Support\Facades\Log::info($a);
    return view('ball/index');
});


Route::get('/test', 'TestController@test');

