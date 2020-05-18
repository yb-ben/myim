<?php

use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

Auth::routes();

Route::group(['namespace'=>'IM','middleware'=>'auth'],function(){

    Route::get('/home', 'RoomController@index')->name('home');
    Route::get('/IM/create','RoomController@create');
    Route::post('/IM','RoomController@store');
    Route::get('/IM/{roomId}','RoomController@show');
});

Route::group(['namespace'=> 'danmu'],function(){
   Route::get('/index','IndexController@index');
});
