<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Country;
use App\Region;

class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $country       = Country::select('name')
            ->get();

        $regions       = Region::select('name')
            ->where('is_delete', 0)
            ->get();

        $facilities = array(
            'Wifi'                                      => __("welcome.interiorWifi"),
            'shower available'                          => __("welcome.interiorShower"),
            'Food à la carte'                           => __("welcome.interiorMealCard"),
            'breakfast'                                 => __("welcome.interiorBreakfast"),
            'TV available'                              => __("welcome.interiorTv"),
            'washing machine'                           => __("welcome.interiorWashingMachine"),
            'drying room'                               => __("welcome.interiorDryingRoom"),
            'Luggage transport from the valley'         => __("welcome.interiorLuggageTransport"),
            'Accessible by car'                         => __("welcome.interiorAccessCar"),
            'dogs allowed'                              => __("welcome.interiorDogsAllowed"),
            'Suitable for wheelchairs'                  => __("welcome.interiorWheelchairs"),
            'Public telephone available'                => __("welcome.interiorPublicPhone"),
            'Mobile phone reception'                    => __("welcome.interiorPhoneReception"),
            'Power supply for own devices'              => __("welcome.interiorPowerSupply"),
            'Waste bin'                                 => __("welcome.interiorDustbins"),
            'Hut shop'                                  => __("welcome.interiorCabinShop"),
            'Advancement possibilities including time'  => __("welcome.interiorAscentPossibility"),
            'reachable by phone'                        => __("welcome.interiorAccessibleTelephone"),
            'Smoking (allowed, forbidden)'              => __("welcome.interiorSmokingAllowed"),
            'smoke detector'                            => __("welcome.interiorSmokeDetector"),
            'Carbon monoxide detector'                  => __("welcome.interiorCarbMonoDetector"),
            'Helicopter land available'                 => __("welcome.interiorHelicopterLand"),
        );

        return view('welcome', ['country' => $country, 'regions' => $regions, 'facilities' => $facilities]);
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
}
