<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Rules\Lowercase;
use Mail;
use App\Mail\VerifyUserEmail;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => [ 'required', 'string', 'email', 'max:255', Rule::unique('user', 'usrEmail')->whereIn('usrlId', [1, 2, 5, 6]), new Lowercase ],
            'password' => 'required|string|min:6|confirmed',
            'dataProtection' => 'required',
            'termsService' => 'required',
        ]);
    }

    /**
     * Generating dynamic salt.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateDynamicSalt() {
        $dynamicSalt = '';
        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }
        return $dynamicSalt = str_ireplace('\\', '@', $dynamicSalt);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Userlist
     */
    protected function create(array $data)
    {
        if(isset($data['newsletter'])){
            $newsletter = (int)$data['newsletter'];
        }
        else {
            $newsletter = 0;
        }

        $generateDynamicSalt = $this->generateDynamicSalt();

        $user =  User::create([
            'usrFirstname' => $data['firstName'],
            'usrLastname' => $data['lastName'],
            'usrEmail' => $data['email'], // later check email capital letter. Store all email in to small letter
            'usrPassword' =>  md5('aFGQ475SDsdfsaf2342'. $data['password'] . $generateDynamicSalt),
            'usrPasswordSalt' => $generateDynamicSalt,
            'usrActive' => '0',
            'usrEmailConfirmed' => '0',
            'usrlId' => 2,
            'usrDatenschutz' => (int)$data['dataProtection'],
            'usrTerms' => (int)$data['termsService'],
            'usrNewsletter' => $newsletter,
            'token' => str_random(60),
            'is_delete' => 0,
        ]);

        Mail::to($data['email'])->send(new VerifyUserEmail($user));

        return $user;
    }

    /**
     * Force user to verify email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function registered(Request $request, $user)
    {
        $this->guard()->logout();
        return redirect('/login')->with('status', 'We sent you an activation code. Check your email and click on the link to verify.');
    }

    /**
     * User email verification and account activation.
     *
     * @param  string $token
     * @return \Illuminate\Http\Response
     */
    public function verifyUser($token)
    {
        $verifyUser = User::where('token', $token)->first();
        if(isset($verifyUser)){
            if($verifyUser->usrEmailConfirmed === '0'){
                $verifyUser->usrEmailConfirmed  = '1';
                $verifyUser->usrActive          = '1';
                $verifyUser->emailConfirmedDate = date('Y-m-d H:i:s');
                $verifyUser->save();
                $status = "Your e-mail is verified. You can now login.";
            }
            else{
                $status = "Your e-mail is already verified. You can now login.";
            }
        }
        else{
            return redirect('/login')->with('warning', "Sorry your email cannot be identified.");
        }

        return redirect('/login')->with('status', $status);
    }
}
