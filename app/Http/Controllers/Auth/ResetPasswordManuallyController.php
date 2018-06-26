<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Requests\ResetPasswordManuallyRequest;
use App\Http\Controllers\Controller;
use App\User;

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
            dd('send email token');
        }
        else {
            return redirect()->back()->withErrors(['user' => __('passwords.user')]);
        }
    }
}
