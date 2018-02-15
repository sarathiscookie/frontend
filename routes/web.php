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

Auth::routes();

/*Route::get('/home', 'HomeController@index')->name('home');*/

/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
|
| Route for index page
|
*/

/* Welcome */
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
Route::match(['get', 'post'], '/search', 'SearchController@index')->name('search');

/* Get cabin name when searching cabin name */
Route::get('/search/cabin/{name}', 'SearchController@cabinName')->name('search.cabin.name');

/*
|--------------------------------------------------------------------------
| Calendar booking availability
|--------------------------------------------------------------------------
|
| Route for calendar booking availability check
|
*/

/* Get dates when page loads */
/*Route::post('/calendar', 'CalendarController@calendar')->name('calendar');*/

/* Get dates when page loads */
Route::post('/calendar/ajax', 'CalendarController@calendarAvailability')->name('calendar');


/*
|--------------------------------------------------------------------------
| User activation
|--------------------------------------------------------------------------
|
| Route for user email verification and account activation
|
*/

/* Verify user */
Route::get('/user/verify/{token}', 'Auth\RegisterController@verifyUser');


Route::group(['middleware' => ['auth']], function () {
    /*
    |--------------------------------------------------------------------------
    | Cabin
    |--------------------------------------------------------------------------
    |
    | Route for cabin individual list
    |
    */

    /* Cabin individual list */
    Route::get('/cabin', 'CabinController@index')->name('cabin');
});




