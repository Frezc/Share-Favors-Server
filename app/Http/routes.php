<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/



Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function () {
    return 'ok';
});

Route::post('auth', 'AuthenticateController@auth');

Route::post('register', 'AuthenticateController@register');

//需要验证token的api
Route::group(['middleware' => 'jwt.auth'], function() {
    
});

//刷新token的api
Route::get('refreshToken', 'AuthenticateController@refreshToken');


// Route::get('respository','');
// Route::get('respository/{id}','');
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});

Route::group(['middleware' => 'throttle:30'], function () {
    Route::get('sendVerifiedEmail', 'SendEmailController@sendMail');
});

Route::resource('respository', 'RespositoryController', ['except' => [
     'edit'
]]);
