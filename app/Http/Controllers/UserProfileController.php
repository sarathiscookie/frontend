<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Country;
use App\Userlist;
use Auth;
use \App\Http\Requests\UserProfileRequest;

class UserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $country  = Country::select('name')->get();


        return view('userProfile', ['country' => $country]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\UserProfileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserProfileRequest $request)
    {
        if($request->has('updateUserProfile')) {
            $user               = Userlist::where('is_delete', 0)
                ->where('usrActive', '1')
                ->find(Auth::user()->_id);

            if(!empty($user)) {
                $user->salutation   = $request->salutation;
                $user->company      = $request->company;
                $user->usrFirstname = $request->firstName;
                $user->usrLastname  = $request->lastName;
                $user->usrCountry   = $request->country;
                $user->usrAddress   = $request->street;
                $user->usrCity      = $request->city;
                $user->usrZip       = $request->zipcode;
                $user->usrMobile    = $request->mobile;
                $user->usrTelephone = $request->phone;
                $user->save();

                return redirect()->back()->with('successStatus', __('userProfile.successStatus'));
            }
            else {
                return redirect()->back()->with('failedStatus', __('userProfile.failedStatus'));
            }
        }
        else {
            return redirect()->back()->with('failedStatus', __('userProfile.failedStatus'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
