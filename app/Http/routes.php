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

Route::get('testEmail', function () {
	return view('emails.reminder', ['email' => '504021398@qq.com', 'code' => 'FFefFf']);
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

Route::get('user/{id}', 'AuthenticateController@getUserinfo');
Route::get('user/{userId}/repository', 'AuthenticateController@showUserRepo');
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

Route::group(['middleware' => 'throttle:2'], function () {
    Route::get('sendVerifiedEmail', 'SendEmailController@sendMail');
});
Route::post('repository', 'RepositoryController@create');
Route::get('repository/{id}', 'RepositoryController@show');
Route::delete('repository/{id}', 'RepositoryController@destroy');
// Route::resource('repository', 'RepositoryController', ['except' => [
//      'edit'
// ]]);
Route::delete('repository/{repoId}/list', 'RepositoryController@deleteItems');
Route::post('repository/{repoId}/list','RepositoryController@addItems');
Route::put('repository/{repoId}/list', 'RepositoryController@updateItems');

Route::post('repository/{repoId}/tag', 'RepositoryController@addTags');
Route::delete('repository/{repoId}/tag', 'RepositoryController@delTags');