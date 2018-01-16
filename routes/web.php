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

Route::get('/cabins', function(){
   return view('cabins');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
|
| Route for index page
|
*/

/* List country */
Route::get('/', 'WelcomeController@index');

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
|
| Route for search cabin
|
*/

/* Search cabin */
Route::post('/search', 'SearchController@index')->name('search');

/* Get cabin name when searching cabin name */
Route::get('/search/cabin/{name}', 'SearchController@cabinName')->name('search.cabin.name');









