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
        /*$monthBegin           = date("Y-m-d");
        $monthEnd             = date("Y-m-t 23:59:59");*/

        $monthBegin              = date("2018-02-02");
        $monthEnd                = date("Y-m-t 23:59:59");

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
            $mon_day     = ($cabin->mon_day === 1) ? 'Mon' : 0;
            $tue_day     = ($cabin->tue_day === 1) ? 'Tue' : 0;
            $wed_day     = ($cabin->wed_day === 1) ? 'Wed' : 0;
            $thu_day     = ($cabin->thu_day === 1) ? 'Thu' : 0;
            $fri_day     = ($cabin->fri_day === 1) ? 'Fri' : 0;
            $sat_day     = ($cabin->sat_day === 1) ? 'Sat' : 0;
            $sun_day     = ($cabin->sun_day === 1) ? 'Sun' : 0;

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

                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                print_r(' sleeps '. $sleeps .' msSleeps '. $msSleeps);
            }

            /* Taking beds, dorms and sleeps depends up on sleeping_place */
            /* >= 75% are booked Orange, 100% is red, < 75% are green*/
            if($cabin->sleeping_place != 1) {
                $totalBeds       = $beds + $msBeds;
                $totalDorms      = $dorms + $msDorms;

                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);

                /* Calculating beds & dorms for not regular */
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
                            $not_regular_beds_diff              = $cabin->not_regular_beds - $totalBeds;
                            $not_regular_dorms_diff             = $cabin->not_regular_dorms - $totalDorms;

                            $not_regular_beds_avail             = ($not_regular_beds_diff >= 0) ? $not_regular_beds_diff : 0;
                            $not_regular_dorms_avail            = ($not_regular_dorms_diff >= 0) ? $not_regular_dorms_diff : 0;

                            /* Available beds and dorms */
                            $not_regular_bed_dorms_available    = $not_regular_beds_avail + $not_regular_dorms_avail;

                            /* Sum of not regular cabins beds and dorms */
                            $not_regular_cabin_beds_dorms_total = $cabin->not_regular_beds + $cabin->not_regular_dorms;

                            /* Already Filled beds and dorms */
                            $not_regular_bed_dorms_filled       = $not_regular_cabin_beds_dorms_total - $not_regular_bed_dorms_available;

                            /* Percentage calculation */
                            $not_regular_percentage             = ($not_regular_bed_dorms_filled / $not_regular_cabin_beds_dorms_total) * 100;

                            if($not_regular_percentage > 75) {
                                $orangeDates[]     = $dates;
                                //print_r(' orange_dates '. $dates);
                            }
                            else {
                                $available_dates[] = $dates;
                                //print_r(' available_dates '. $dates);
                            }
                            print_r('not_regular_data ----');
                            print_r(' not_rgl_dates: ' . $dates . ' not_regular_beds_diff: '. $not_regular_beds_diff. ' not_regular_beds_avail: '. $not_regular_beds_avail);
                            print_r( ' not_rgl_dates: ' . $dates . ' not_regular_dorms_diff: '. $not_regular_dorms_diff. ' not_regular_dorms_avail: '. $not_regular_dorms_avail);
                            print_r( ' not_regular_sum: ' . $not_regular_bed_dorms_available);
                            print_r(' not_regular_bed_dorms_filled = '. $not_regular_bed_dorms_filled .' not_regular_cabin_beds_dorms_total = '. $not_regular_cabin_beds_dorms_total .' result(not_regular_bed_dorms_filled / not_regular_cabin_beds_dorms_total) * 100 = '. $not_regular_percentage);
                        }
                        else {
                            $not_available_dates[] = $dates;
                            print_r(' not_available_dates '. $dates);
                        }
                    }
                    /*beds 29 dorms 0 msBeds 0 msDorms 9 totalBeds 29 totalDorms 9
                      available_dates 2018-02-10
                      not_rgl_dates: 2018-02-10 not_regular_beds_diff: 11 not_regular_beds_avail: 11
                      not_rgl_dates: 2018-02-10 not_regular_dorms_diff: 23 not_regular_dorms_avail: 23
                      not_regular_sum: 34
                      not_regular_bed_dorms_available 34 not_regular_bed_dorms_filled 38 not_regular_cabin_beds_dorms_total 72 result 52.777777777778
                     */
                }

                /* Calculating beds & dorms for regular */
                if($cabin->regular === 1) {

                    if($mon_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalBeds < $cabin->mon_beds) || ($totalDorms < $cabin->mon_dorms)) {
                                $mon_beds_diff              = $cabin->mon_beds - $totalBeds;
                                $mon_dorms_diff             = $cabin->mon_dorms - $totalDorms;

                                $mon_beds_avail             = ($mon_beds_diff >= 0) ? $mon_beds_diff : 0;
                                $mon_dorms_avail            = ($mon_dorms_diff >= 0) ? $mon_dorms_diff : 0;

                                /* Available beds and dorms */
                                $mon_bed_dorms_available    = $mon_beds_avail + $mon_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $mon_cabin_beds_dorms_total = $cabin->mon_beds + $cabin->mon_dorms;

                                /* Already Filled beds and dorms */
                                $mon_bed_dorms_filled       = $mon_cabin_beds_dorms_total - $mon_bed_dorms_available;

                                /* Percentage calculation */
                                $mon_percentage             = ($mon_bed_dorms_filled / $mon_cabin_beds_dorms_total) * 100;

                                if($mon_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                print_r('mon_regular_data ----');
                                print_r(' mon_dates: ' . $dates . ' mon_beds_diff: '. $mon_beds_diff. ' mon_beds_avail: '. $mon_beds_avail);
                                print_r( ' mon_dates: ' . $dates . ' mon_dorms_diff: '. $mon_dorms_diff. ' mon_dorms_avail: '. $mon_dorms_avail);
                                print_r( ' mon_sum: ' . $mon_bed_dorms_available);
                                print_r(' mon_bed_dorms_filled = '. $mon_bed_dorms_filled .' mon_cabin_beds_dorms_total = '. $mon_cabin_beds_dorms_total .' result(mon_bed_dorms_filled / mon_cabin_beds_dorms_total) * 100 = '. $mon_percentage);
                            }
                            else {
                                $not_available_dates[] = $dates;
                                print_r(' not_available_dates '. $dates);
                            }
                        }
                    }

                    if($tue_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalBeds < $cabin->tue_beds) || ($totalDorms < $cabin->tue_dorms)) {
                                $tue_beds_diff              = $cabin->tue_beds - $totalBeds;
                                $tue_dorms_diff             = $cabin->tue_dorms - $totalDorms;

                                $tue_beds_avail             = ($tue_beds_diff >= 0) ? $tue_beds_diff : 0;
                                $tue_dorms_avail            = ($tue_dorms_diff >= 0) ? $tue_dorms_diff : 0;

                                /* Available beds and dorms */
                                $tue_bed_dorms_available    = $tue_beds_avail + $tue_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $tue_cabin_beds_dorms_total = $cabin->tue_beds + $cabin->tue_dorms;

                                /* Already Filled beds and dorms */
                                $tue_bed_dorms_filled       = $tue_cabin_beds_dorms_total - $tue_bed_dorms_available;

                                /* Percentage calculation */
                                $tue_percentage             = ($tue_bed_dorms_filled / $tue_cabin_beds_dorms_total) * 100;

                                if($tue_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                print_r('tue_regular_data ----');
                                print_r(' tue_dates: ' . $dates . ' tue_beds_diff: '. $tue_beds_diff. ' tue_beds_avail: '. $tue_beds_avail);
                                print_r( ' tue_dates: ' . $dates . ' tue_dorms_diff: '. $tue_dorms_diff. ' tue_dorms_avail: '. $tue_dorms_avail);
                                print_r( ' tue_sum: ' . $tue_bed_dorms_available);
                                print_r(' tue_bed_dorms_filled = '. $tue_bed_dorms_filled .' tue_cabin_beds_dorms_total = '. $tue_cabin_beds_dorms_total .' result(tue_bed_dorms_filled / tue_cabin_beds_dorms_total) * 100 = '. $tue_percentage);
                            }
                            else {
                                $not_available_dates[] = $dates;
                                print_r(' not_available_dates '. $dates);
                            }
                        }
                    }

                    if($wed_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalBeds < $cabin->wed_beds) || ($totalDorms < $cabin->wed_dorms)) {
                                $wed_beds_diff              = $cabin->wed_beds - $totalBeds;
                                $wed_dorms_diff             = $cabin->wed_dorms - $totalDorms;

                                $wed_beds_avail             = ($wed_beds_diff >= 0) ? $wed_beds_diff : 0;
                                $wed_dorms_avail            = ($wed_dorms_diff >= 0) ? $wed_dorms_diff : 0;

                                /* Available beds and dorms */
                                $wed_bed_dorms_available    = $wed_beds_avail + $wed_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $wed_cabin_beds_dorms_total = $cabin->wed_beds + $cabin->wed_dorms;

                                /* Already Filled beds and dorms */
                                $wed_bed_dorms_filled       = $wed_cabin_beds_dorms_total - $wed_bed_dorms_available;

                                /* Percentage calculation */
                                $wed_percentage             = ($wed_bed_dorms_filled / $wed_cabin_beds_dorms_total) * 100;

                                if($wed_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                print_r('wed_regular_data ----');
                                print_r(' wed_dates: ' . $dates . ' wed_beds_diff: '. $wed_beds_diff. ' wed_beds_avail: '. $wed_beds_avail);
                                print_r( ' wed_dates: ' . $dates . ' wed_dorms_diff: '. $wed_dorms_diff. ' wed_dorms_avail: '. $wed_dorms_avail);
                                print_r( ' wed_sum: ' . $wed_bed_dorms_available);
                                print_r(' wed_bed_dorms_filled = '. $wed_bed_dorms_filled .' wed_cabin_beds_dorms_total = '. $wed_cabin_beds_dorms_total .' result(wed_bed_dorms_filled / wed_cabin_beds_dorms_total) * 100 = '. $wed_percentage);
                            }
                            else {
                                $not_available_dates[] = $dates;
                                print_r(' not_available_dates '. $dates);
                            }
                        }
                    }

                    if($thu_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalBeds < $cabin->thu_beds) || ($totalDorms < $cabin->thu_dorms)) {
                                $thu_beds_diff              = $cabin->thu_beds - $totalBeds;
                                $thu_dorms_diff             = $cabin->thu_dorms - $totalDorms;

                                $thu_beds_avail             = ($thu_beds_diff >= 0) ? $thu_beds_diff : 0;
                                $thu_dorms_avail            = ($thu_dorms_diff >= 0) ? $thu_dorms_diff : 0;

                                /* Available beds and dorms */
                                $thu_bed_dorms_available    = $thu_beds_avail + $thu_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $thu_cabin_beds_dorms_total = $cabin->thu_beds + $cabin->thu_dorms;

                                /* Already Filled beds and dorms */
                                $thu_bed_dorms_filled       = $thu_cabin_beds_dorms_total - $thu_bed_dorms_available;

                                /* Percentage calculation */
                                $thu_percentage             = ($thu_bed_dorms_filled / $thu_cabin_beds_dorms_total) * 100;

                                if($thu_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                print_r('thu_regular_data ----');
                                print_r(' thu_dates: ' . $dates . ' thu_beds_diff: '. $thu_beds_diff. ' thu_beds_avail: '. $thu_beds_avail);
                                print_r( ' thu_dates: ' . $dates . ' thu_dorms_diff: '. $thu_dorms_diff. ' thu_dorms_avail: '. $thu_dorms_avail);
                                print_r( ' thu_sum: ' . $thu_bed_dorms_available);
                                print_r(' thu_bed_dorms_filled = '. $thu_bed_dorms_filled .' thu_cabin_beds_dorms_total = '. $thu_cabin_beds_dorms_total .' result(thu_bed_dorms_filled / thu_cabin_beds_dorms_total) * 100 = '. $thu_percentage);
                            }
                            else {
                                $not_available_dates[] = $dates;
                                print_r(' not_available_dates '. $dates);
                            }
                        }
                    }

                    if($fri_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalBeds < $cabin->fri_beds) || ($totalDorms < $cabin->fri_dorms)) {
                                $fri_beds_diff              = $cabin->fri_beds - $totalBeds;
                                $fri_dorms_diff             = $cabin->fri_dorms - $totalDorms;

                                $fri_beds_avail             = ($fri_beds_diff >= 0) ? $fri_beds_diff : 0;
                                $fri_dorms_avail            = ($fri_dorms_diff >= 0) ? $fri_dorms_diff : 0;

                                /* Available beds and dorms */
                                $fri_bed_dorms_available    = $fri_beds_avail + $fri_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $fri_cabin_beds_dorms_total = $cabin->fri_beds + $cabin->fri_dorms;

                                /* Already Filled beds and dorms */
                                $fri_bed_dorms_filled       = $fri_cabin_beds_dorms_total - $fri_bed_dorms_available;

                                /* Percentage calculation */
                                $fri_percentage             = ($fri_bed_dorms_filled / $fri_cabin_beds_dorms_total) * 100;

                                if($fri_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                print_r('fri_regular_data ----');
                                print_r(' fri_dates: ' . $dates . ' fri_beds_diff: '. $fri_beds_diff. ' fri_beds_avail: '. $fri_beds_avail);
                                print_r( ' fri_dates: ' . $dates . ' fri_dorms_diff: '. $fri_dorms_diff. ' fri_dorms_avail: '. $fri_dorms_avail);
                                print_r( ' fri_sum: ' . $fri_bed_dorms_available);
                                print_r(' fri_bed_dorms_filled = '. $fri_bed_dorms_filled .' fri_cabin_beds_dorms_total = '. $fri_cabin_beds_dorms_total .' result(fri_bed_dorms_filled / fri_cabin_beds_dorms_total) * 100 = '. $fri_percentage);
                            }
                            else {
                                $not_available_dates[] = $dates;
                                print_r(' not_available_dates '. $dates);
                            }
                        }
                    }

                    if($sat_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalBeds < $cabin->sat_beds) || ($totalDorms < $cabin->sat_dorms)) {
                                $sat_beds_diff              = $cabin->sat_beds - $totalBeds;
                                $sat_dorms_diff             = $cabin->sat_dorms - $totalDorms;

                                $sat_beds_avail             = ($sat_beds_diff >= 0) ? $sat_beds_diff : 0;
                                $sat_dorms_avail            = ($sat_dorms_diff >= 0) ? $sat_dorms_diff : 0;

                                /* Available beds and dorms */
                                $sat_bed_dorms_available    = $sat_beds_avail + $sat_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $sat_cabin_beds_dorms_total = $cabin->sat_beds + $cabin->sat_dorms;

                                /* Already Filled beds and dorms */
                                $sat_bed_dorms_filled       = $sat_cabin_beds_dorms_total - $sat_bed_dorms_available;

                                /* Percentage calculation */
                                $sat_percentage             = ($sat_bed_dorms_filled / $sat_cabin_beds_dorms_total) * 100;

                                if($sat_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                print_r('sat_regular_data ----');
                                print_r(' sat_dates: ' . $dates . ' sat_beds_diff: '. $sat_beds_diff. ' sat_beds_avail: '. $sat_beds_avail);
                                print_r( ' sat_dates: ' . $dates . ' sat_dorms_diff: '. $sat_dorms_diff. ' sat_dorms_avail: '. $sat_dorms_avail);
                                print_r( ' sat_sum: ' . $sat_bed_dorms_available);
                                print_r(' sat_bed_dorms_filled = '. $sat_bed_dorms_filled .' sat_cabin_beds_dorms_total = '. $sat_cabin_beds_dorms_total .' result(sat_bed_dorms_filled / sat_cabin_beds_dorms_total) * 100 = '. $sat_percentage);
                            }
                            else {
                                $not_available_dates[] = $dates;
                                print_r(' not_available_dates '. $dates);
                            }
                        }
                    }

                    if($sun_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalBeds < $cabin->sun_beds) || ($totalDorms < $cabin->sun_dorms)) {
                                $sun_beds_diff              = $cabin->sun_beds - $totalBeds;
                                $sun_dorms_diff             = $cabin->sun_dorms - $totalDorms;

                                $sun_beds_avail             = ($sun_beds_diff >= 0) ? $sun_beds_diff : 0;
                                $sun_dorms_avail            = ($sun_dorms_diff >= 0) ? $sun_dorms_diff : 0;

                                /* Available beds and dorms */
                                $sun_bed_dorms_available    = $sun_beds_avail + $sun_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $sun_cabin_beds_dorms_total = $cabin->sun_beds + $cabin->sun_dorms;

                                /* Already Filled beds and dorms */
                                $sun_bed_dorms_filled       = $sun_cabin_beds_dorms_total - $sun_bed_dorms_available;

                                /* Percentage calculation */
                                $sun_percentage             = ($sun_bed_dorms_filled / $sun_cabin_beds_dorms_total) * 100;

                                if($sun_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                print_r('sun_regular_data ----');
                                print_r(' sun_dates: ' . $dates . ' sun_beds_diff: '. $sun_beds_diff. ' sun_beds_avail: '. $sun_beds_avail);
                                print_r( ' sun_dates: ' . $dates . ' sun_dorms_diff: '. $sun_dorms_diff. ' sun_dorms_avail: '. $sun_dorms_avail);
                                print_r( ' sun_sum: ' . $sun_bed_dorms_available);
                                print_r(' sun_bed_dorms_filled = '. $sun_bed_dorms_filled .' sun_cabin_beds_dorms_total = '. $sun_cabin_beds_dorms_total .' result(sun_bed_dorms_filled / sun_cabin_beds_dorms_total) * 100 = '. $sun_percentage);
                            }
                            else {
                                $not_available_dates[] = $dates;
                                print_r(' not_available_dates '. $dates);
                            }
                        }
                    }

                }

                /* Calculating beds & dorms for normal */
                if(!in_array($dates, $dates_array)) {

                    if(($totalBeds < $cabin->beds) || ($totalDorms < $cabin->dormitory)) {

                        $normal_beds_diff              = $cabin->beds - $totalBeds;
                        $normal_dorms_diff             = $cabin->dormitory - $totalDorms;

                        $normal_beds_avail             = ($normal_beds_diff >= 0) ? $normal_beds_diff : 0;
                        $normal_dorms_avail            = ($normal_dorms_diff >= 0) ? $normal_dorms_diff : 0;

                        /* Available beds and dorms */
                        $normal_bed_dorms_available    = $normal_beds_avail + $normal_dorms_avail;

                        /* Sum of normal cabins beds and dorms */
                        $normal_cabin_beds_dorms_total = $cabin->beds + $cabin->dormitory;

                        /* Already Filled beds and dorms */
                        $normal_bed_dorms_filled       = $normal_cabin_beds_dorms_total - $normal_bed_dorms_available;

                        /* Percentage calculation */
                        $normal_percentage             = ($normal_bed_dorms_filled / $normal_cabin_beds_dorms_total) * 100;

                        if($normal_percentage > 75) {
                            $orangeDates[]     = $dates;
                        }
                        else {
                            $available_dates[] = $dates;
                        }
                        print_r('normal_data ----');
                        print_r(' normal_dates: ' . $dates . ' normal_beds_diff: '. $normal_beds_diff. ' normal_beds_avail: '. $normal_beds_avail);
                        print_r( ' normal_dates: ' . $dates . ' normal_dorms_diff: '. $normal_dorms_diff. ' normal_dorms_avail: '. $normal_dorms_avail);
                        print_r( ' normal_sum: ' . $normal_bed_dorms_available);
                        print_r(' normal_bed_dorms_filled = '. $normal_bed_dorms_filled .' normal_cabin_beds_dorms_total = '. $normal_cabin_beds_dorms_total .' result(normal_bed_dorms_filled / normal_cabin_beds_dorms_total) * 100 = '. $normal_percentage);

                    }
                    else {
                        $not_available_dates[] = $dates;
                        print_r(' not_available_dates '. $dates);
                    }

                    /* beds 38 dorms 1 msBeds 0 msDorms 0 sleeps 39 msSleeps 0 totalBeds 38 totalDorms 1
                       normal_dates: 2018-02-17 normal_beds_diff: 2 normal_beds_avail: 2
                       normal_dates: 2018-02-17 normal_dorms_diff: 39 normal_dorms_avail: 39
                       normal_sum: 41
                       normal_bed_dorms_filled = 39 normal_cabin_beds_dorms_total = 80 result(normal_bed_dorms_filled / normal_cabin_beds_dorms_total) * 100 = 48.75
                    */
                }

            }
            else {
                $totalSleeps     = $sleeps + $msSleeps;
                //print_r(' totalSleeps: '. $totalSleeps);
            }



            /* Checking bookings available ends */
        }

        return response()->json(['holidayDates' => $holidayDates, 'greenDates' => $available_dates, 'orangeDates' => $orangeDates, 'redDates' => $not_available_dates], 200);

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
