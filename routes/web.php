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
    return view('welcome');
});

Auth::routes();
Route::get('/activate/{code}','Auth\RegisterController@activate');


Route::resource('file', 'HomeController', [
    'except' => ['create']
]);

Route::get('download/{file}', 'HomeController@download')->name('download');
Route::get('api/files', 'HomeController@apiFiles')->name('api.files');
Route::get('api/description/{file}', 'HomeController@fullDescription')->name('api.fullDescription');
