<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SearchRequest;
use App\Cabin;
use App\Country;
use App\Region;
use App\Season;
use App\Booking;
use App\MountSchoolBooking;
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
     * To generate date format as mongo.
     *
     * @param  string  $date
     * @return \Illuminate\Http\Response
     */
    protected function getDateUtc($date)
    {
        $dateFormatChange = DateTime::createFromFormat("d.m.y", $date)->format('Y-m-d');
        $dateTime         = new DateTime($dateFormatChange);
        $timeStamp        = $dateTime->getTimestamp();
        $utcDateTime      = new \MongoDB\BSON\UTCDateTime($timeStamp * 1000);
        return $utcDateTime;
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
        $monthBegin           = date("Y-m-d");
        $monthEnd             = date("Y-m-t 23:59:59");

        /*$monthBegin              = date("2018-02-02");
        $monthEnd                = date("2018-02-06");*/

        $holiday_prepare         = [];
        $holidayDates            = [];
        $not_regular_dates       = [];
        $dates_array             = [];
        $available_dates         = [];
        $not_available_dates     = [];

        $dorms                   = 0;
        $beds                    = 0;
        $sleeps                  = 0;

        $msSleeps                = 0;
        $msBeds                  = 0;
        $msDorms                 = 0;

        $seasons                 = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($request->dataId))->get();
        $cabin                   = Cabin::findOrFail($request->dataId);

        $generateBookingDates    = $this->generateDates($monthBegin, $monthEnd);

        foreach ($generateBookingDates as $generateBookingDate) {

            $dates         = $generateBookingDate->format('Y-m-d');
            $day           = $generateBookingDate->format('D');

            /* Checking season begin */
            if($seasons) {
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

                $prepareArray       = [$dates => $day];
                $array_unique       = array_unique($holiday_prepare);
                $array_intersect    = array_intersect($prepareArray, $array_unique);

                foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                    $holidayDates[] = $array_intersect_key;
                }
            }
            /* Checking season end */

            /* Checking bookings available begins */
            $session_mon_day     = ($cabin->mon_day === 1) ? 'Mon' : 0;
            $session_tue_day     = ($cabin->tue_day === 1) ? 'Tue' : 0;
            $session_wed_day     = ($cabin->wed_day === 1) ? 'Wed' : 0;
            $session_thu_day     = ($cabin->thu_day === 1) ? 'Thu' : 0;
            $session_fri_day     = ($cabin->fri_day === 1) ? 'Fri' : 0;
            $session_sat_day     = ($cabin->sat_day === 1) ? 'Sat' : 0;
            $session_sun_day     = ($cabin->sun_day === 1) ? 'Sun' : 0;

            /* Getting bookings from booking collection status is 1=>Fix, 4=>Request, 7=>Inquiry */
            $bookings  = Booking::select('beds', 'dormitory', 'sleeps')
                ->where('is_delete', 0)
                ->where('cabinname', $cabin->name)
                ->whereIn('status', ['1', '4', '7'])
                ->whereRaw(['checkin_from' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                ->get();

            /* Getting bookings from mschool collection status is 1=>Fix, 4=>Request, 7=>Inquiry */
            $msBookings  = MountSchoolBooking::select('beds', 'dormitory', 'sleeps')
                ->where('is_delete', 0)
                ->where('cabin_name', $cabin->name)
                ->whereIn('status', ['1', '4', '7'])
                ->whereRaw(['check_in' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                ->get();

            /* Getting count of sleeps, beds and dorms */
            if(count($bookings) > 0 || count($msBookings) > 0) {
                $sleeps          = $bookings->sum('sleeps');
                $beds            = $bookings->sum('beds');
                $dorms           = $bookings->sum('dormitory');
                $msSleeps        = $msBookings->sum('sleeps');
                $msBeds          = $msBookings->sum('beds');
                $msDorms         = $msBookings->sum('dormitory');

                //print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                //print_r(' sleeps '. $sleeps .' msSleeps '. $msSleeps);
            }

            /* Taking beds, dorms and sleeps depends up on sleeping_place */
            /* >= 75% are booked Orange, 100% is red, < 75% are green*/
            if($cabin->sleeping_place != 1) {
                $totalBeds       = $beds + $msBeds;
                $totalDorms      = $dorms + $msDorms;

                //print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);

                /* Calculating beds & dorms of regular and not regular booking */
                if($cabin->not_regular === 1) {
                    $not_regular_date_explode = explode(" - ", $cabin->not_regular_date);
                    $not_regular_date_begin   = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[0])->format('Y-m-d');
                    $not_regular_date_end     = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[1])->format('Y-m-d 23:59:59'); //To get the end date we need to add time
                    $generateNotRegularDates  = $this->generateDates($not_regular_date_begin, $not_regular_date_end);

                    foreach($generateNotRegularDates as $generateNotRegularDate) {
                        $not_regular_dates[]  = $generateNotRegularDate->format('Y-m-d');
                    }

                    if(in_array($dates, $not_regular_dates)) {

                        $dates_array[] = $dates;

                        if(($totalBeds < $cabin->not_regular_beds) || ($totalDorms < $cabin->not_regular_dorms)) {
                            $not_regular_beds_diff      = $cabin->not_regular_beds - $totalBeds;
                            $not_regular_dorms_diff     = $cabin->not_regular_dorms - $totalDorms;

                            $not_regular_beds_avail     = ($not_regular_beds_diff >= 0) ? $not_regular_beds_diff : 0;
                            $not_regular_dorms_avail    = ($not_regular_dorms_diff >= 0) ? $not_regular_dorms_diff : 0;

                            $not_regular_beds_dorms_sum = $not_regular_beds_avail + $not_regular_dorms_avail;

                            if($not_regular_beds_dorms_sum > 0) {
                                $available_dates[] = $dates;
                                //print_r(' available_dates '. $dates);
                            }

                            /*print_r(' not_rgl_dates: ' . $dates . ' not_regular_beds_diff: '. $not_regular_beds_diff. ' not_regular_beds_avail: '. $not_regular_beds_avail);
                            print_r( ' not_rgl_dates: ' . $dates . ' not_regular_dorms_diff: '. $not_regular_dorms_diff. ' not_regular_dorms_avail: '. $not_regular_dorms_avail);
                            print_r( ' not_regular_sum: ' . $not_regular_beds_dorms_sum);*/
                        }
                        else {
                            $not_available_dates[] = $dates;
                            //print_r(' not_available_dates '. $dates);
                        }
                    }
                }

                if($cabin->regular === 1) {
                    //print_r(' regular '. $cabin->regular);
                }

                /* Calculating beds & dorms of normal booking */
            }
            else {
                $totalSleeps     = $sleeps + $msSleeps;
                //print_r(' totalSleeps: '. $totalSleeps);
            }



            /* Checking bookings available ends */
        }


        /*$greenDates  = ["2018-02-05", "2018-02-08", "2018-02-11"];
        $yellowDates = ["2018-02-06", "2018-02-09", "2018-02-12"];
        $redDates    = ["2018-02-07", "2018-02-10", "2018-02-13"];

        return response()->json(['holidayDates' => $holidayDates, 'greenDates' => $greenDates, 'yellowDates' => $yellowDates, 'redDates' => $redDates], 200);*/
        $yellowDates = ["2018-02-28"];
        return response()->json(['holidayDates' => $holidayDates, 'greenDates' => $available_dates, 'yellowDates' => $yellowDates, 'redDates' => $not_available_dates], 200);

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
        $holidayDates       = [];

        if($request->dateFrom != '') {
            $monthBegin        = $request->dateFrom;
            $monthEnd          = date('Y-m-t 23:59:59', strtotime($request->dateFrom));
            $seasons           = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($request->dataId))->get();

            $generateDates  = $this->generateDates($monthBegin, $monthEnd);

            foreach ($generateDates as $generateDate) {

                $dates = $generateDate->format('Y-m-d');
                $day   = $generateDate->format('D');

                if($seasons) {
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
                        $holidayDates[] = $array_intersect_key;
                    }
                }

            }
        }

        $greenDates  = ["2018-03-03", "2018-03-04", "2018-03-05"];
        $yellowDates = ["2018-03-06", "2018-03-09", "2018-03-12"];
        $redDates    = ["2018-03-07", "2018-03-10", "2018-03-13"];

        return response()->json(['holidayDates' => $holidayDates, 'greenDates' => $greenDates, 'yellowDates' => $yellowDates, 'redDates' => $redDates], 200);
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
