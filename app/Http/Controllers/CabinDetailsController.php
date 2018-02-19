<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cabin;
use App\User;

class CabinDetailsController extends Controller
{
    /**
     * Array for payment type.
     *
     * @return array
     */
    public function paymentType()
    {
        $array = array(
            '0' => __("cabinDetails.cabinBoxLabelPayTypeCash"),
            '1' => __("cabinDetails.cabinBoxLabelPayTypeDebit"),
            '2' => __("cabinDetails.cabinBoxLabelPayTypeCredit"),
        );

        return $array;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $cabin_id = preg_replace(sprintf('/%s/', env('MD5_Key')), '', base64_decode($id));

        $cabinDetails = Cabin::where('is_delete', 0)
            ->where('other_cabin', "0")
            ->find($cabin_id);

        return view('cabinDetails', ['cabinDetails' => $cabinDetails]);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

    /**
     * Get the user details when an injection occurs.
     *
     * @param  string  $userId
     * @return \Illuminate\Http\Response
     */
    public function userDetails($userId)
    {
        $user = User::where('usrActive', '1')
            ->where('is_delete', 0)
            ->where('usrlId', 5)
            ->find($userId);

        return $user;
    }
}
