<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    protected $redirectTo = '/home';

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
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $authUser = User::where('usrEmail', $request->email)
            ->whereIn('usrlId', [1, 2, 5, 6])
            ->first();

        if($authUser) {
            $password = md5('aFGQ475SDsdfsaf2342'. $request->password. $authUser->usrPasswordSalt);
            $user     = User::where('usrEmail', $request->email)
                ->where('usrPassword', $password)
                ->where('usrActive', '1')
                ->where('usrEmailConfirmed', '1')
                ->where('is_delete', 0)
                ->where('usrlId', 2)
                ->first();

            if ($user) {
                $this->guard()->login($user, $request->has('remember'));

                return true;
            }
            else {
                return false;
            }
        }

        return false;
    }
}
