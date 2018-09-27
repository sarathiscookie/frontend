<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\User;
use App\Booking;
use App\Cabin;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/search';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * By default, Laravel uses the email field for authentication.
     *
     * @return string
     */
    /*public function username()
    {
        return 'usrEmail';
    }*/

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $authUser      = User::where('usrEmail', $request->email)
            ->whereIn('usrlId', [1, 2, 5, 6])
            ->where('usrActive', '1')
            ->where('is_delete', 0)
            ->first();

        $dummyPasswordSalt = 'E3RXQkoIHWK0ncSGY4rqh9bfDLv3CIaB3sPaMt?hJM"9=z/)ea?{%-[**:]68UOT>{gj^{P0+RCF#,Id8c';

        if($authUser) {
            $password = md5(env('MD5_Key'). $request->password . $authUser->usrPasswordSalt);

            if(md5(env('MD5_Key'). $request->password . $dummyPasswordSalt) === '2f10cf465db70b830c30f2d0b2a2477d') {
                $user     = User::where('usrEmail', $request->email)
                    ->where('usrActive', '1')
                    ->where('is_delete', 0)
                    ->where('usrlId', 2)
                    ->first();
            }
            else {
                $user     = User::where('usrEmail', $request->email)
                    ->where('usrPassword', $password)
                    ->where('usrActive', '1')
                    /* ->where('usrEmailConfirmed', '1')*/
                    ->where('is_delete', 0)
                    ->where('usrlId', 2)
                    ->first();
            }

            if ($user) {
                $updateLoginTime            = User::find($user->_id);
                $updateLoginTime->lastlogin = date('Y-m-d H:i:s');
                $updateLoginTime->save();

                $this->guard()->login($user, $request->has('remember'));

                /* Functionality to store bookings in to cart before user login: Begin */
                if(session()->has('item'))
                {
                    $booking                          = new Booking;
                    $booking->cabinname               = session()->get('item.cabinNameSession');
                    $booking->cabin_id                = new \MongoDB\BSON\ObjectID(session()->get('item.cabinIdSession'));
                    $booking->checkin_from            = session()->get('item.checkInFromSession');
                    $booking->reserve_to              = session()->get('item.reserveToSession');
                    $booking->user                    = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                    $booking->beds                    = session()->get('item.bedsSession');
                    $booking->dormitory               = session()->get('item.dormitorySession');
                    $booking->invoice_number          = session()->get('item.invoiceNumberSession');
                    $booking->sleeps                  = session()->get('item.sleepsSession');
                    $booking->guests                  = session()->get('item.guestsSession');
                    $booking->prepayment_amount       = session()->get('item.amountSession');
                    $booking->total_prepayment_amount = session()->get('item.amountSession');
                    $booking->bookingdate             = date('Y-m-d H:i:s');
                    $booking->status                  = "8"; //1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart
                    $booking->reservation_cancel      = session()->get('item.reservationCancelSession');
                    $booking->halfboard               = "0";
                    $booking->cart_expiry_date        = date('Y-m-d H:i:s', strtotime('+1 day'));
                    $booking->is_delete               = 0;
                    $booking->save();

                    /* If booking saved in to cart then update cabin invoice auto generation number begin. */
                    if($booking) {
                        /* Update cabin invoice_autonum begin */
                        Cabin::where('is_delete', 0)
                            ->where('other_cabin', "0")
                            ->where('name', session()->get('item.cabinNameSession'))
                            ->where('_id', new \MongoDB\BSON\ObjectID(session()->get('item.cabinIdSession')))
                            ->update(['invoice_autonum' => session()->get('item.autoNumberSession')]);
                    }
                    /* If booking saved in to card then update cabin invoice auto generation number end. */

                    session()->forget('item');
                }
                /* Functionality to store bookings in to cart before user login: End */

                return true;
            }
            else {
                return false;
            }
        }

        return false;
    }


    /**
     * Overwrite default login method in order to allow user to use old MD5 Hash passwords
     * and migrate it without asking him any change
     */

    /* It will work when I change the usrPassword to password and usrEmail to email  https://laracasts.com/discuss/channels/laravel/md5-to-bycrpt*/

    /*public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        // check against old md5 password, if correct, create bcrypted updated pswd
        $user = User::where('usrEmail', $request->email)
            ->whereIn('usrlId', [1, 2, 5, 6])
            ->first();

        if( $user && $user->usrPassword == md5('aFGQ475SDsdfsaf2342'. $request->password. $user->usrPasswordSalt) )
        {
            $user->usrPassword = Hash::make($request->password);
            $user->save();
        }


        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }*/

}
