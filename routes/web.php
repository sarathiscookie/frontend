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
| Route for search cabins
|
*/

/* Search cabins */
Route::match(['get', 'post'], '/search', 'SearchController@index')->name('search');

/* Get cabin name while searching */
Route::get('/search/cabin/{name}', 'SearchController@cabinName')->name('search.cabin.name');

/*
|--------------------------------------------------------------------------
| Calendar
|--------------------------------------------------------------------------
|
| Route for calendar booking availability check
|
*/

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

/*
|--------------------------------------------------------------------------
| Middleware group for authentication
|--------------------------------------------------------------------------
|
| Route for after authentication
|
*/
Route::group(['middleware' => ['auth']], function () {
    /*
     |--------------------------------------------------------------------------
     | Cabin
     |--------------------------------------------------------------------------
     |
     | Route for cabin details
     |
     */

    /* Cabin details */
    Route::get('/cabin/details/{id}', 'CabinDetailsController@index')->name('cabin.details');




});




