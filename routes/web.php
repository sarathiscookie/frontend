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

/*
|--------------------------------------------------------------------------
| Login, Register and Reset password
|--------------------------------------------------------------------------
|
| Route for login, register and reset password
|
*/

/* Default laravel auth route */
Auth::routes();

/* Show form for checking email */
Route::get('/reset/password', 'Auth\ResetPasswordManuallyController@showForm')->name('reset.password.manually');

/* Send token to user */
Route::post('/reset/password', 'Auth\ResetPasswordManuallyController@sendPasswordResetToken')->name('reset.password.manually');

/* Show password reset form */
Route::get('/reset/password/{token}', 'Auth\ResetPasswordManuallyController@showPasswordResetForm')->name('reset.password.form');

/* Reset password */
Route::post('/reset/password/{token}', 'Auth\ResetPasswordManuallyController@resetPassword')->name('reset.password.token');

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
| Footer
|--------------------------------------------------------------------------
|
| Route for footer links
|
*/
/* About page */
Route::get('/about', function(){
    return view('about');
});

/* Contact page */
Route::get('/contact', function(){
    return view('contact');
});

/* Media page */
Route::get('/media', function(){
    return view('media');
});

/* Jobs page */
Route::get('/jobs', function(){
    return view('jobs');
});

/* Jobs: media page */
Route::get('/job/media', function(){
    return view('jobMedia');
});

/* Jobs: php page */
Route::get('/job/programmer', function(){
    return view('jobProgrammer');
});

/* Jobs: trainee page */
Route::get('/job/trainee', function(){
    return view('jobTrainee');
});

/* Terms and Conditions */
Route::get('/terms', function(){
    return view('termsConditions');
});

/* Impress */
Route::get('/impress', function(){
    return view('impress');
});

/* image Rights */
Route::get('/image/rights', function(){
    return view('imageRights');
});

/* Data protection */
Route::get('/data/protection', function(){
    return view('dataProtection');
});

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

    /* Download voucher */
    Route::post('/booking/history/voucher/download', 'BookingHistoryController@downloadVoucher')->name('booking.history.voucher.download');

    /* Delete cancelled booking */
    Route::post('/booking/history/delete/cancelled/booking', 'BookingHistoryController@destroyCancelledBooking')->name('booking.history.delete.cancelled.booking');

    /* Delete waiting prepay booking */
    Route::post('/booking/history/delete/waiting/prepay', 'BookingHistoryController@destroyWaitingPrepayBooking')->name('booking.history.delete.waiting.prepay');

    /* Delete approved inquiry booking */
    Route::post('/booking/history/delete/approved/inquiry', 'BookingHistoryController@destroyApprovedInquiry')->name('booking.history.delete.approved.inquiry');

    /* Delete waiting inquiry booking */
    Route::post('/booking/history/delete/waiting/inquiry', 'BookingHistoryController@destroyWaitingInquiry')->name('booking.history.delete.waiting.inquiry');

    /* Delete rejected inquiry booking */
    Route::post('/booking/history/delete/rejected/inquiry', 'BookingHistoryController@destroyRejectedInquiry')->name('booking.history.delete.rejected.inquiry');

    /* Cancel booking */
    Route::post('/booking/history/cancel', 'BookingHistoryController@cancelBooking')->name('booking.history.cancel');

    /* Inquiry booking payment */
    Route::get('/booking/history/inquiry/{payment}', 'InquiryController@show')->name('booking.history.inquiry');

    /* Choose payment and store data */
    Route::post('/booking/history/inquiry/payment/update', 'InquiryController@update')->name('booking.history.inquiry.payment.update');

    /* Inquiry chat message send */
    Route::post('/booking/history/inquiry/message/send', 'InquiryController@sendMessage')->name('booking.history.inquiry.message.send');

    /* Choose payment and store data */
    Route::post('/booking/history/payment/store', 'BookingHistoryController@store')->name('booking.history.payment.store');

    /* Edit booking history */
    Route::get('/booking/history/edit/{id}', 'BookingHistoryController@edit')->name('edit.booking.history');

    /* Update booking history */
    Route::post('/booking/history/edit/{id}', 'BookingHistoryController@update')->name('update.booking.history');

    /* Edit booking payment */
    Route::get('/booking/history/payment/{editBooking}', 'BookingHistoryController@show')->name('booking.history.payment');

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

    /* Payment success */
    Route::get('/payment/success', 'PaymentController@success')->name('payment.success');

    /* Payment failure */
    Route::get('/payment/error', 'PaymentController@error')->name('payment.error');

    /* Payment prepayment */
    Route::get('/payment/prepayment', 'PaymentController@prepayment')->name('payment.prepayment');

    /* Payment prepayment download */
    Route::post('/payment/prepayment/download', 'PaymentController@download')->name('payment.prepayment.download');

    /* Choose payment and store data */
    Route::post('/payment/store', 'PaymentController@store')->name('payment.store');


    /*
    |--------------------------------------------------------------------------
    | User profile
    |--------------------------------------------------------------------------
    |
    | Route for view user profile, update user profile
    |
    */
    /* View user profile page */
    Route::get('/user/profile', 'UserProfileController@index')->name('user.profile');

    /* Store user profile data */
    Route::post('/user/profile/store', 'UserProfileController@store')->name('user.profile.store');

});

/*
 |--------------------------------------------------------------------------
 | Payment response
 |--------------------------------------------------------------------------
 |
 | Route for getting response from payone
 |
 */
/* Response from payone */
Route::post('/payment/response', 'PaymentController@response')->name('payment.response');



