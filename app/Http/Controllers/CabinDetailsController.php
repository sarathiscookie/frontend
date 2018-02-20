<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cabin;
use App\User;
use App\Season;

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
     * Array for reservation cancel.
     *
     * @return array
     */
    public function reservationCancel()
    {
        $array = array(
            '1'  => __("cabinDetails.cancelDeadlineBegin").' 1 '.__("cabinDetails.cancelDeadlineEnd"),
            '2'  => __("cabinDetails.cancelDeadlineBegin").' 2 '.__("cabinDetails.cancelDeadlineEnd"),
            '3'  => __("cabinDetails.cancelDeadlineBegin").' 3 '.__("cabinDetails.cancelDeadlineEnd"),
            '4'  => __("cabinDetails.cancelDeadlineBegin").' 4 '.__("cabinDetails.cancelDeadlineEnd"),
            '5'  => __("cabinDetails.cancelDeadlineBegin").' 5 '. __("cabinDetails.cancelDeadlineEnd"),
            '6'  => __("cabinDetails.cancelDeadlineBegin").' 6 '. __("cabinDetails.cancelDeadlineEnd"),
            '7'  => __("cabinDetails.cancelDeadlineBegin").' 7 '. __("cabinDetails.cancelDeadlineEnd"),
            '8'  => __("cabinDetails.cancelDeadlineBegin").' 8 '. __("cabinDetails.cancelDeadlineEnd"),
            '9'  => __("cabinDetails.cancelDeadlineBegin").' 9 '. __("cabinDetails.cancelDeadlineEnd"),
            '10' => __("cabinDetails.cancelDeadlineBegin").' 10 '. __("cabinDetails.cancelDeadlineEnd"),
            '14' => __("cabinDetails.cancelDeadlineBegin").' 14 '. __("cabinDetails.cancelDeadlineEnd"),
            '15' => __("cabinDetails.cancelDeadlineBegin").' 15 '. __("cabinDetails.cancelDeadlineEnd"),
            '20' => __("cabinDetails.cancelDeadlineBegin").' 20 '. __("cabinDetails.cancelDeadlineEnd"),
            '30' => __("cabinDetails.cancelDeadlineBegin").' 30 '. __("cabinDetails.cancelDeadlineEnd"),
            '60' => __("cabinDetails.cancelDeadlineBegin").' 60 '. __("cabinDetails.cancelDeadlineEnd"),
            '90' => __("cabinDetails.cancelDeadlineBegin").' 90 '. __("cabinDetails.cancelDeadlineEnd"),
            '180' => __("cabinDetails.cancelDeadlineBegin").' 180 '. __("cabinDetails.cancelDeadlineEnd"),
            '365' => __("cabinDetails.cancelDeadlineBegin").' 365 '. __("cabinDetails.cancelDeadlineEnd"),
        );

        return $array;
    }

    /**
     * Get list of cabin when injection occurs.
     *
     * @return array
     */
    public function cabins()
    {
        $neighbourCabins     = Cabin::select('_id', 'name')
            ->where('is_delete', 0)
            ->get();

        if(count($neighbourCabins) > 0) {
            return $neighbourCabins;
        }
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

        if(count($user) > 0){
            return $user;
        }
    }

    /**
     * Get the season start and end date when an injection occurs.
     *
     * @param  string  $cabinId
     * @return \Illuminate\Http\Response
     */
    public function seasons($cabinId)
    {
        $seasons = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($cabinId))->get();

        if(count($seasons) > 0){
            return $seasons;
        }
    }
}
