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
     * @param  string  $name
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

    /**
     * Show the next day booking availability when an injection occurs.
     *
     * @param  string  $cabin_id
     * @return \Illuminate\Http\Response
     */
    public function bookingPossibleNextDays($cabin_id)
    {
        $dayBegin                = date("Y-m-d", strtotime(' +1 day'));
        $day                     = date("D", strtotime(' +1 day'));
        $holiday_prepare         = [];
        $not_regular_dates       = [];
        $dates_array             = [];
        $bookingDateSeasonType   = null;

        $seasons                 = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($cabin_id))->get();

        $cabin                   = Cabin::findOrFail($cabin_id);

        /* Checking season begin */
        if($seasons) {
            foreach ($seasons as $season) {

                if (($season->summerSeasonStatus === 'open') && ($season->summerSeason === 1) && ($dayBegin >= ($season->earliest_summer_open)->format('Y-m-d')) && ($dayBegin < ($season->latest_summer_close)->format('Y-m-d'))) {
                    $holiday_prepare[]     = ($season->summer_mon === 1) ? 'Mon' : 0;
                    $holiday_prepare[]     = ($season->summer_tue === 1) ? 'Tue' : 0;
                    $holiday_prepare[]     = ($season->summer_wed === 1) ? 'Wed' : 0;
                    $holiday_prepare[]     = ($season->summer_thu === 1) ? 'Thu' : 0;
                    $holiday_prepare[]     = ($season->summer_fri === 1) ? 'Fri' : 0;
                    $holiday_prepare[]     = ($season->summer_sat === 1) ? 'Sat' : 0;
                    $holiday_prepare[]     = ($season->summer_sun === 1) ? 'Sun' : 0;
                    $bookingDateSeasonType = 'summer';
                }
                elseif (($season->winterSeasonStatus === 'open') && ($season->winterSeason === 1) && ($dayBegin >= ($season->earliest_winter_open)->format('Y-m-d')) && ($dayBegin < ($season->latest_winter_close)->format('Y-m-d'))) {
                    $holiday_prepare[]     = ($season->winter_mon === 1) ? 'Mon' : 0;
                    $holiday_prepare[]     = ($season->winter_tue === 1) ? 'Tue' : 0;
                    $holiday_prepare[]     = ($season->winter_wed === 1) ? 'Wed' : 0;
                    $holiday_prepare[]     = ($season->winter_thu === 1) ? 'Thu' : 0;
                    $holiday_prepare[]     = ($season->winter_fri === 1) ? 'Fri' : 0;
                    $holiday_prepare[]     = ($season->winter_sat === 1) ? 'Sat' : 0;
                    $holiday_prepare[]     = ($season->winter_sun === 1) ? 'Sun' : 0;
                    $bookingDateSeasonType = 'winter';
                }

            }

            if (!$bookingDateSeasonType) {
                return '<span class="label label-info pull-left">Tomorrow cabin closed</span>';
            }

            $prepareArray       = [$dayBegin => $day];
            $array_unique       = array_unique($holiday_prepare);
            $array_intersect    = array_intersect($prepareArray, $array_unique);

            foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                if($dayBegin === $array_intersect_key) {
                    return '<span class="label label-info pull-left">Tomorrow cabin closed</span>';
                }
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
        $dateTime    = new DateTime($dayBegin);
        $timeStamp   = $dateTime->getTimestamp();
        $utcDateTime = new \MongoDB\BSON\UTCDateTime($timeStamp * 1000);

        $bookings    = Booking::select('beds', 'dormitory', 'sleeps')
            ->where('is_delete', 0)
            ->where('cabinname', $cabin->name)
            ->whereIn('status', ['1', '4', '7'])
            ->whereRaw(['checkin_from' => array('$lte' => $utcDateTime)])
            ->whereRaw(['reserve_to' => array('$gt' => $utcDateTime)])
            ->get();

        /* Getting bookings from mschool collection status is 1=>Fix, 4=>Request, 7=>Inquiry */
        $msBookings  = MountSchoolBooking::select('beds', 'dormitory', 'sleeps')
            ->where('is_delete', 0)
            ->where('cabin_name', $cabin->name)
            ->whereIn('status', ['1', '4', '7'])
            ->whereRaw(['check_in' => array('$lte' => $utcDateTime)])
            ->whereRaw(['reserve_to' => array('$gt' => $utcDateTime)])
            ->get();

        /* Getting count of sleeps, beds and dorms */
        if(count($bookings) > 0) {
            $sleeps          = $bookings->sum('sleeps');
            $beds            = $bookings->sum('beds');
            $dorms           = $bookings->sum('dormitory');
        }
        else {
            $dorms           = 0;
            $beds            = 0;
            $sleeps          = 0;
        }

        if(count($msBookings) > 0) {
            $msSleeps        = $msBookings->sum('sleeps');
            $msBeds          = $msBookings->sum('beds');
            $msDorms         = $msBookings->sum('dormitory');
        }
        else {
            $msSleeps        = 0;
            $msBeds          = 0;
            $msDorms         = 0;
        }

        /* Taking beds, dorms and sleeps depends up on sleeping_place */
        /* >= 75% are booked Orange, 100% is red, < 75% are green*/

        if($cabin->sleeping_place != 1) {
            $totalBeds       = $beds + $msBeds;
            $totalDorms      = $dorms + $msDorms;

            /* Calculating beds & dorms for not regular */
            if($cabin->not_regular === 1) {
                $not_regular_date_explode = explode(" - ", $cabin->not_regular_date);
                $not_regular_date_begin   = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[0])->format('Y-m-d');
                $not_regular_date_end     = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[1])->format('Y-m-d 23:59:59'); //To get the end date we need to add time
                $generateNotRegularDates  = $this->generateDates($not_regular_date_begin, $not_regular_date_end);

                foreach($generateNotRegularDates as $generateNotRegularDate) {
                    $not_regular_dates[]  = $generateNotRegularDate->format('Y-m-d');
                }

                if(in_array($dayBegin, $not_regular_dates)) {

                    $dates_array[] = $dayBegin;

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
                            return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                        }
                        else {
                            return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                        }
                    }
                    else {
                        return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                    }
                }
            }

            /* Calculating beds & dorms for regular */

            if($cabin->regular === 1) {

                if($mon_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

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
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }

                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($tue_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

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
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }

                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($wed_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

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
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($thu_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

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
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($fri_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

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
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($sat_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

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
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($sun_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

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
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

            }

            /* Calculating beds & dorms for normal */
            if(!in_array($dayBegin, $dates_array)) {

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
                        return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                    }
                    else {
                        return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                    }
                }
                else {
                    return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                }
            }

        }
        else {
            $totalSleeps = $sleeps + $msSleeps;

            /* Calculating beds & dorms for not regular */
            if($cabin->not_regular === 1) {
                $not_regular_date_explode = explode(" - ", $cabin->not_regular_date);
                $not_regular_date_begin   = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[0])->format('Y-m-d');
                $not_regular_date_end     = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[1])->format('Y-m-d 23:59:59'); //To get the end date we need to add time
                $generateNotRegularDates  = $this->generateDates($not_regular_date_begin, $not_regular_date_end);

                foreach($generateNotRegularDates as $generateNotRegularDate) {
                    $not_regular_dates[]  = $generateNotRegularDate->format('Y-m-d');
                }

                if(in_array($dayBegin, $not_regular_dates)) {

                    $dates_array[] = $dayBegin;

                    if(($totalSleeps < $cabin->not_regular_sleeps)) {
                        $not_regular_sleeps_diff       = $cabin->not_regular_sleeps - $totalSleeps;

                        /* Available beds and dorms */
                        $not_regular_sleeps_avail      = ($not_regular_sleeps_diff >= 0) ? $not_regular_sleeps_diff : 0;

                        /* Already Filled beds and dorms */
                        $not_regular_sleeps_filled     = $cabin->not_regular_sleeps - $not_regular_sleeps_avail;

                        /* Percentage calculation */
                        $not_regular_sleeps_percentage = ($not_regular_sleeps_filled / $cabin->not_regular_sleeps) * 100;

                        if($not_regular_sleeps_percentage > 75) {
                            return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                        }
                        else {
                            return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                        }
                    }
                    else {
                        return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                    }
                }
            }

            /* Calculating beds & dorms for regular */
            if($cabin->regular === 1) {

                if($mon_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

                        if(($totalSleeps < $cabin->mon_sleeps)) {
                            $mon_sleeps_diff       = $cabin->mon_sleeps - $totalSleeps;

                            /* Available beds and dorms */
                            $mon_sleeps_avail      = ($mon_sleeps_diff >= 0) ? $mon_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $mon_sleeps_filled     = $cabin->mon_sleeps - $mon_sleeps_avail;

                            /* Percentage calculation */
                            $mon_sleeps_percentage = ($mon_sleeps_filled / $cabin->mon_sleeps) * 100;

                            if($mon_sleeps_percentage > 75) {
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($tue_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

                        if(($totalSleeps < $cabin->tue_sleeps)) {
                            $tue_sleeps_diff       = $cabin->tue_sleeps - $totalSleeps;

                            /* Available beds and dorms */
                            $tue_sleeps_avail      = ($tue_sleeps_diff >= 0) ? $tue_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $tue_sleeps_filled     = $cabin->tue_sleeps - $tue_sleeps_avail;

                            /* Percentage calculation */
                            $tue_sleeps_percentage = ($tue_sleeps_filled / $cabin->tue_sleeps) * 100;

                            if($tue_sleeps_percentage > 75) {
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($wed_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

                        if(($totalSleeps < $cabin->wed_sleeps)) {
                            $wed_sleeps_diff       = $cabin->wed_sleeps - $totalSleeps;

                            /* Available beds and dorms */
                            $wed_sleeps_avail      = ($wed_sleeps_diff >= 0) ? $wed_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $wed_sleeps_filled     = $cabin->wed_sleeps - $wed_sleeps_avail;

                            /* Percentage calculation */
                            $wed_sleeps_percentage = ($wed_sleeps_filled / $cabin->wed_sleeps) * 100;

                            if($wed_sleeps_percentage > 75) {
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($thu_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

                        if(($totalSleeps < $cabin->thu_sleeps)) {
                            $thu_sleeps_diff       = $cabin->thu_sleeps - $totalSleeps;

                            /* Available beds and dorms */
                            $thu_sleeps_avail      = ($thu_sleeps_diff >= 0) ? $thu_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $thu_sleeps_filled     = $cabin->thu_sleeps - $thu_sleeps_avail;

                            /* Percentage calculation */
                            $thu_sleeps_percentage = ($thu_sleeps_filled / $cabin->thu_sleeps) * 100;

                            if($thu_sleeps_percentage > 75) {
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($fri_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

                        if(($totalSleeps < $cabin->fri_sleeps)) {
                            $fri_sleeps_diff       = $cabin->fri_sleeps - $totalSleeps;

                            /* Available beds and dorms */
                            $fri_sleeps_avail      = ($fri_sleeps_diff >= 0) ? $fri_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $fri_sleeps_filled     = $cabin->fri_sleeps - $fri_sleeps_avail;

                            /* Percentage calculation */
                            $fri_sleeps_percentage = ($fri_sleeps_filled / $cabin->fri_sleeps) * 100;

                            if($fri_sleeps_percentage > 75) {
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($sat_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

                        if(($totalSleeps < $cabin->sat_sleeps)) {
                            $sat_sleeps_diff       = $cabin->sat_sleeps - $totalSleeps;

                            /* Available beds and dorms */
                            $sat_sleeps_avail      = ($sat_sleeps_diff >= 0) ? $sat_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $sat_sleeps_filled     = $cabin->sat_sleeps - $sat_sleeps_avail;

                            /* Percentage calculation */
                            $sat_sleeps_percentage = ($sat_sleeps_filled / $cabin->sat_sleeps) * 100;

                            if($sat_sleeps_percentage > 75) {
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }

                if($sun_day === $day) {

                    if(!in_array($dayBegin, $dates_array)) {

                        $dates_array[] = $dayBegin;

                        if(($totalSleeps < $cabin->sun_sleeps)) {
                            $sun_sleeps_diff       = $cabin->sun_sleeps - $totalSleeps;

                            /* Available beds and dorms */
                            $sun_sleeps_avail      = ($sun_sleeps_diff >= 0) ? $sun_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $sun_sleeps_filled     = $cabin->sun_sleeps - $sun_sleeps_avail;

                            /* Percentage calculation */
                            $sun_sleeps_percentage = ($sun_sleeps_filled / $cabin->sun_sleeps) * 100;

                            if($sun_sleeps_percentage > 75) {
                                return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                        }
                    }
                }


            }

            /* Calculating beds & dorms for normal */
            if(!in_array($dayBegin, $dates_array)) {

                if(($totalSleeps < $cabin->sleeps)) {
                    $normal_sleeps_diff       = $cabin->sleeps - $totalSleeps;

                    /* Available beds and dorms */
                    $normal_sleeps_avail      = ($normal_sleeps_diff >= 0) ? $normal_sleeps_diff : 0;

                    /* Already Filled beds and dorms */
                    $normal_sleeps_filled     = $cabin->sleeps - $normal_sleeps_avail;

                    /* Percentage calculation */
                    $normal_sleeps_percentage = ($normal_sleeps_filled / $cabin->sleeps) * 100;

                    if($normal_sleeps_percentage > 75) {
                        return '<span class="label label-warning pull-left">Tomorrow sleeping place limited</span>';
                    }
                    else {
                        return '<span class="label label-success pull-left">Tomorrow sleeping place available</span>';
                    }
                }
                else {
                    return '<span class="label label-danger pull-left">Tomorrow sleeping place booked out</span>';
                }

            }
        }
        /* Checking bookings available end */
    }

}
