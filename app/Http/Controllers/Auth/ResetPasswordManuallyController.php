<?php

namespace App\Http\Controllers\Auth;

use App\PasswordReset;
use Illuminate\Http\Request;
use App\Http\Requests\ResetPasswordManuallyRequest;
use App\Http\Controllers\Controller;
use App\User;
use Mail;
use Validator;
use App\Mail\PasswordResetEmail;

class ResetPasswordManuallyController extends Controller
{
    /**
     * Show form to check email already exist.
     *
     * @return \Illuminate\Http\Response
     */
    public function showForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send password token to user.
     *
     * @param  \App\Http\Requests\ResetPasswordManuallyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function sendPasswordResetToken(ResetPasswordManuallyRequest $request)
    {
        $user = User::where('usrEmail', $request->email)
            ->where('usrActive', '1')
            ->where('is_delete', 0)
            ->where('usrlId', 2)
            ->first();

        if( !empty($user) ) {
            /* Delete old token requested by user */
            PasswordReset::where('email', $user->usrEmail)->delete();

            /* Generate token and store in to table */
            $passwordReset         = new PasswordReset;
            $passwordReset->email  = $user->usrEmail;
            $passwordReset->token  = str_random(60);
            $passwordReset->save();

            Mail::to($passwordReset->email)->send(new PasswordResetEmail($passwordReset));

            return redirect()->back()->with('status', __('passwords.sent'));
        }
        else {
            return redirect()->back()->withErrors(['user' => __('passwords.user')]);
        }
    }

    /**
     * Show password reset form.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function showPasswordResetForm($token)
    {
        $tokenData = PasswordReset::where('token', $token)->first();

        if( !empty($tokenData) ) {
            return view('auth.passwords.resetManually', ['token' => $tokenData->token]);
        }
        else {
            return redirect()->to('/reset/password')->with('failedStatus', __('passwords.token'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::where('usrEmail', $request->email)
            ->where('usrActive', '1')
            ->where('is_delete', 0)
            ->where('usrlId', 2)
            ->first();

        if( !empty($user) ) {
            $verifiedUser = PasswordReset::where('token', $request->token)->where('email', $request->email)->first();
            if(!empty($verifiedUser)) {
                $password       = md5(env('MD5_Key'). $request->password . $user->usrPasswordSalt);
                $user->password = $password;
                $user->save();

                return redirect()->back()->with('passwordResetSuccess', __('passwords.reset'));
            }
            else {
                return redirect()->back()->withErrors(['user' => __('passwords.user')]);
            }
        }
        else {
            return redirect()->back()->withErrors(['user' => __('passwords.user')]);
        }
    }
}
