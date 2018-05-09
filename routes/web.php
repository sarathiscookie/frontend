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
| Home
|--------------------------------------------------------------------------
|
| Route for index page
|
*/

/* Home */
Route::get('/', 'HomeController@index');

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
| Cabin
|--------------------------------------------------------------------------
|
| Route for cabin details
|
*/

/* Cabin details */
Route::get('/cabin/details/{id}', 'CabinDetailsController@index')->name('cabin.details');

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
    | Home
    |--------------------------------------------------------------------------
    |
    | Route for show home
    |
    */
    /* Home */
    Route::get('/home', 'HomeController@index');

    /*
    |--------------------------------------------------------------------------
    | Cabin List: Add to cart
    |--------------------------------------------------------------------------
    |
    | Route for add cabin to cart
    |
    */

    /* Add to cart */
    Route::post('/add/to/cart', 'SearchController@store')->name('add.to.cart');

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    |
    | Route for list, store, delete cart
    |
    */

    /* Cart listing */
    Route::get('/cart', 'CartController@index')->name('cart');

    /* Cart Store */
    Route::post('/cart/store', 'CartController@store')->name('cart.store');

    /* Delete cart */
    Route::get('/cart/delete/{cabinId}/{cartId}', 'CartController@destroy')->name('cart.destroy');

    /*
    |--------------------------------------------------------------------------
    | Inquiry
    |--------------------------------------------------------------------------
    |
    | Route for view and send inquiry
    |
    */
    /* View inquiry page */
    Route::get('/inquiry', 'InquiryController@index')->name('inquiry');

    /* Send and store inquiry */
    Route::post('/inquiry/send', 'InquiryController@store')->name('inquiry.store');

    /*
    |--------------------------------------------------------------------------
    | Booking History
    |--------------------------------------------------------------------------
    |
    | Route for view booking history
    |
    */
    /* View booking history */
    Route::get('/booking/history', 'BookingHistoryController@index')->name('booking.history');

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    |
    | Route for view payment type, redeem amount, payment gateway functionality
    |
    */
    /* View payment page */
    Route::get('/payment', 'PaymentController@index')->name('payment');

    /* Choose payment and store data */
    Route::post('/payment/store', 'PaymentController@store')->name('payment.store');

    /* Response from payone */
    Route::get('/payment/response', 'PaymentController@response')->name('payment.response');

    /* Payment success */
    Route::get('/payment/success', 'PaymentController@success')->name('payment.success');

    /* Payment failure */
    Route::get('/payment/error', 'PaymentController@error')->name('payment.error');
});




