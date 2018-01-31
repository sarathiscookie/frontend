<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SearchRequest;
use App\Cabin;
use App\Country;
use App\Region;
use App\Season;
use DateTime;
use DatePeriod;
use DateInterval;

class SearchController extends Controller
{
    /**
     * Show the facility array when an injection occurs.
     *
     * @return \Illuminate\Http\Response
     */
    public function facility()
    {
        $facilities = array(
            'Wifi'                                      => __("search.interiorWifi"),
            'shower available'                          => __("search.interiorShower"),
            'Food à la carte'                           => __("search.interiorMealCard"),
            'breakfast'                                 => __("search.interiorBreakfast"),
            'TV available'                              => __("search.interiorTv"),
            'washing machine'                           => __("search.interiorWashingMachine"),
            'drying room'                               => __("search.interiorDryingRoom"),
            'Luggage transport from the valley'         => __("search.interiorLuggageTransport"),
            'Accessible by car'                         => __("search.interiorAccessCar"),
            'dogs allowed'                              => __("search.interiorDogsAllowed"),
            'Suitable for wheelchairs'                  => __("search.interiorWheelchairs"),
            'Public telephone available'                => __("search.interiorPublicPhone"),
            'Mobile phone reception'                    => __("search.interiorPhoneReception"),
            'Power supply for own devices'              => __("search.interiorPowerSupply"),
            'Waste bin'                                 => __("search.interiorDustbins"),
            'Hut shop'                                  => __("search.interiorCabinShop"),
            'Advancement possibilities including time'  => __("search.interiorAscentPossibility"),
            'reachable by phone'                        => __("search.interiorAccessibleTelephone"),
            'Smoking (allowed, forbidden)'              => __("search.interiorSmokingAllowed"),
            'smoke detector'                            => __("search.interiorSmokeDetector"),
            'Carbon monoxide detector'                  => __("search.interiorCarbMonoDetector"),
            'Helicopter land available'                 => __("search.interiorHelicopterLand"),
        );

        return $facilities;
    }

    /**
     * Show the open season array when an injection occurs.
     *
     * @return \Illuminate\Http\Response
     */
    public function openSeasons()
    {
        $seasonOpens = array(
            'Open on winter season'                     => __("search.winterSeasonOpen"),
            'Open on summer season'                     => __("search.summerSeasonOpen")
        );

        return $seasonOpens;
    }

    /**
     * To generate date between two dates.
     *
     * @param  string  $now
     * @param  string  $end
     * @return \Illuminate\Http\Response
     */
    protected function generateDates($now, $end){
        $period = new DatePeriod(
            new DateTime($now),
            new DateInterval('P1D'),
            new DateTime($end)
        );

        return $period;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Requests\SearchRequest
     * @return \Illuminate\Http\Response
     */
    public function index(SearchRequest $request)
    {
        $cabin = Cabin::select('_id', 'name', 'region', 'height', 'country', 'interior')
            ->where('is_delete', 0)
            ->where('other_cabin', "0");

        if(isset($request->cabinname)){
            $cabin->where('name', $request->cabinname);
        }

        if(isset($request->country)){
            foreach ($request->country as $land){
                $cabin->where('country', $land);
            }
        }

        if(isset($request->region)){
            foreach ($request->region as $region){
                $cabin->where('region', $region);
            }
        }

        if(isset($request->facility)){
            foreach ($request->facility as $facility){
                $cabin->whereIn('interior', [$facility]);
            }
        }

        $cabinSearchResult = $cabin->simplePaginate(10);

        return view('searchResult', ['cabinSearchResult' => $cabinSearchResult]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cabinName($name)
    {
        $data = Cabin::where('name', 'LIKE', $name.'%')
            ->where('is_delete', 0)
            ->where('other_cabin', "0")
            ->take(10)
            ->get(array('name'));

        return response()->json($data);
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
     * Show the data when page initial loads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function calendar(Request $request)
    {
        $monthBegin        = date("Y-m-d");
        $monthEnd          = date("Y-m-t 23:59:59");

        $holiday_prepare   = [];
        $disableDates      = [];

        $seasons           = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($request->dataId))->get();

        if($seasons) {

            $generateDates = $this->generateDates($monthBegin, $monthEnd);

            foreach ($generateDates as $generateDate) {

                $dates = $generateDate->format('Y-m-d');
                $day   = $generateDate->format('D');

                foreach ($seasons as $season) {

                    if (($season->summerSeasonStatus === 'open') && ($season->summerSeason === 1) && ($dates >= ($season->earliest_summer_open)->format('Y-m-d')) && ($dates < ($season->latest_summer_close)->format('Y-m-d'))) {
                        $holiday_prepare[] = ($season->summer_mon === 1) ? 'Mon' : 0;
                        $holiday_prepare[] = ($season->summer_tue === 1) ? 'Tue' : 0;
                        $holiday_prepare[] = ($season->summer_wed === 1) ? 'Wed' : 0;
                        $holiday_prepare[] = ($season->summer_thu === 1) ? 'Thu' : 0;
                        $holiday_prepare[] = ($season->summer_fri === 1) ? 'Fri' : 0;
                        $holiday_prepare[] = ($season->summer_sat === 1) ? 'Sat' : 0;
                        $holiday_prepare[] = ($season->summer_sun === 1) ? 'Sun' : 0;
                    } elseif (($season->winterSeasonStatus === 'open') && ($season->winterSeason === 1) && ($dates >= ($season->earliest_winter_open)->format('Y-m-d')) && ($dates < ($season->latest_winter_close)->format('Y-m-d'))) {
                        $holiday_prepare[] = ($season->winter_mon === 1) ? 'Mon' : 0;
                        $holiday_prepare[] = ($season->winter_tue === 1) ? 'Tue' : 0;
                        $holiday_prepare[] = ($season->winter_wed === 1) ? 'Wed' : 0;
                        $holiday_prepare[] = ($season->winter_thu === 1) ? 'Thu' : 0;
                        $holiday_prepare[] = ($season->winter_fri === 1) ? 'Fri' : 0;
                        $holiday_prepare[] = ($season->winter_sat === 1) ? 'Sat' : 0;
                        $holiday_prepare[] = ($season->winter_sun === 1) ? 'Sun' : 0;
                    }

                }

                $prepareArray    = [$dates => $day];
                $array_unique    = array_unique($holiday_prepare);
                $array_intersect = array_intersect($prepareArray, $array_unique);

                foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                    $disableDates[] = $array_intersect_key;
                }

            }
        }
        return response()->json(['disableDates' => $disableDates], 200);
    }


    /**
     * Search available data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function calendarAvailability(Request $request)
    {
        $holiday_prepare    = [];
        $disableDates       = [];

        if($request->dateFrom != '') {
            $monthBegin        = $request->dateFrom;
            $monthEnd          = date('Y-m-t 23:59:59', strtotime($request->dateFrom));
            $seasons           = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($request->dataId))->get();

            if($seasons) {

                $generateDates  = $this->generateDates($monthBegin, $monthEnd);

                foreach ($generateDates as $generateDate) {

                    $dates = $generateDate->format('Y-m-d');
                    $day   = $generateDate->format('D');

                    foreach($seasons as $season) {

                        if( ($season->summerSeasonStatus === 'open') && ($season->summerSeason === 1) && ($dates >= ($season->earliest_summer_open)->format('Y-m-d')) && ($dates < ($season->latest_summer_close)->format('Y-m-d')) ) {
                            $holiday_prepare[] = ($season->summer_mon === 1) ? 'Mon' : 0;
                            $holiday_prepare[] = ($season->summer_tue === 1) ? 'Tue' : 0;
                            $holiday_prepare[] = ($season->summer_wed === 1) ? 'Wed' : 0;
                            $holiday_prepare[] = ($season->summer_thu === 1) ? 'Thu' : 0;
                            $holiday_prepare[] = ($season->summer_fri === 1) ? 'Fri' : 0;
                            $holiday_prepare[] = ($season->summer_sat === 1) ? 'Sat' : 0;
                            $holiday_prepare[] = ($season->summer_sun === 1) ? 'Sun' : 0;
                        }
                        elseif( ($season->winterSeasonStatus === 'open') && ($season->winterSeason === 1) && ($dates >= ($season->earliest_winter_open)->format('Y-m-d')) && ($dates < ($season->latest_winter_close)->format('Y-m-d')) ) {
                            $holiday_prepare[] = ($season->winter_mon === 1) ? 'Mon' : 0;
                            $holiday_prepare[] = ($season->winter_tue === 1) ? 'Tue' : 0;
                            $holiday_prepare[] = ($season->winter_wed === 1) ? 'Wed' : 0;
                            $holiday_prepare[] = ($season->winter_thu === 1) ? 'Thu' : 0;
                            $holiday_prepare[] = ($season->winter_fri === 1) ? 'Fri' : 0;
                            $holiday_prepare[] = ($season->winter_sat === 1) ? 'Sat' : 0;
                            $holiday_prepare[] = ($season->winter_sun === 1) ? 'Sun' : 0;
                        }

                    }

                    $prepareArray           = [$dates => $day];
                    $array_unique           = array_unique($holiday_prepare);
                    $array_intersect        = array_intersect($prepareArray,$array_unique);

                    foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                        $disableDates[] = $array_intersect_key;
                    }
                }

            }
        }

        return response()->json(['disableDates' => $disableDates], 201);

    }

    /**
     * Show the countries when an injection occurs.
     *
     * @return \Illuminate\Http\Response
     */
    public function country()
    {
        $country       = Country::select('name')
            ->where('is_delete', 0)
            ->get();

        if(count($country) > 0){
            return $country;
        }
    }

    /**
     * Show the regions when an injection occurs.
     *
     * @return \Illuminate\Http\Response
     */
    public function regions()
    {
        $regions       = Region::select('name')
            ->where('is_delete', 0)
            ->get();

        if(count($regions) > 0){
            return $regions;
        }
    }

    /**
     * Get the count of cabin region wise when an injection occurs.
     *
     * @param  string  $region
     * @return \Illuminate\Http\Response
     */
    public function cabinCount($region)
    {
        $cabin = Cabin::where('is_delete', 0)
            ->where('other_cabin', "0")
            ->where('region', $region)
            ->count();

        if($cabin > 0)
        {
            return $cabin;
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
