<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cabin;
use App\User;
use App\Season;
use App\Booking;
use App\MountSchoolBooking;
use DateTime;
use DatePeriod;
use DateInterval;

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
     * An injection occurs interior name will return.
     *
     * @param  string  $interior
     * @return array
     */
    public function interiorLabel($interior = null)
    {
        $facilities = array(
            'Wifi'                                      => __("cabinDetails.interiorWifi"),
            'shower available'                          => __("cabinDetails.interiorShower"),
            'Food à la carte'                           => __("cabinDetails.interiorMealCard"),
            'breakfast'                                 => __("cabinDetails.interiorBreakfast"),
            'TV available'                              => __("cabinDetails.interiorTv"),
            'washing machine'                           => __("cabinDetails.interiorWashingMachine"),
            'drying room'                               => __("cabinDetails.interiorDryingRoom"),
            'Luggage transport from the valley'         => __("cabinDetails.interiorLuggageTransport"),
            'Accessible by car'                         => __("cabinDetails.interiorAccessCar"),
            'dogs allowed'                              => __("cabinDetails.interiorDogsAllowed"),
            'Suitable for wheelchairs'                  => __("cabinDetails.interiorWheelchairs"),
            'Public telephone available'                => __("cabinDetails.interiorPublicPhone"),
            'Mobile phone reception'                    => __("cabinDetails.interiorPhoneReception"),
            'Power supply for own devices'              => __("cabinDetails.interiorPowerSupply"),
            'Waste bin'                                 => __("cabinDetails.interiorDustbins"),
            'Hut shop'                                  => __("cabinDetails.interiorCabinShop"),
            'Advancement possibilities including time'  => __("cabinDetails.interiorAscentPossibility"),
            'reachable by phone'                        => __("cabinDetails.interiorAccessibleTelephone"),
            'Smoking (allowed, forbidden)'              => __("cabinDetails.interiorSmokingAllowed"),
            'smoke detector'                            => __("cabinDetails.interiorSmokeDetector"),
            'Carbon monoxide detector'                  => __("cabinDetails.interiorCarbMonoDetector"),
            'Helicopter land available'                 => __("cabinDetails.interiorHelicopterLand")
        );

        if($interior != null) {
            if(array_key_exists($interior, $facilities)) {
                return $facilities[$interior];
            }
        }
        else {
            return $facilities;
        }
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

    /**
     * Get list of cabin when injection occurs.
     *
     * @param  string  $neighbour
     * @return array
     */
    public function neighbourCabins($neighbour)
    {
        $neighbourCabins     = Cabin::select('name')
            ->where('is_delete', 0)
            ->find($neighbour);

        if(count($neighbourCabins) > 0) {
            return $neighbourCabins->name;
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

        $cabin                   = Cabin::where('is_delete', 0)
            ->where('other_cabin', "0")
            ->findOrFail($cabin_id);

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
                return '<span class="label label-info pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Closed </span>';
            }

            $prepareArray       = [$dayBegin => $day];
            $array_unique       = array_unique($holiday_prepare);
            $array_intersect    = array_intersect($prepareArray, $array_unique);

            foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                if($dayBegin === $array_intersect_key) {
                    return '<span class="label label-info pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Closed </span>';
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

        /* Getting bookings from booking collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
        $dateTime    = new DateTime($dayBegin);
        $timeStamp   = $dateTime->getTimestamp();
        $utcDateTime = new \MongoDB\BSON\UTCDateTime($timeStamp * 1000);

        $bookings    = Booking::select('beds', 'dormitory', 'sleeps')
            ->where('is_delete', 0)
            ->where('cabinname', $cabin->name)
            ->whereIn('status', ['1', '4', '7', '8'])
            ->whereRaw(['checkin_from' => array('$lte' => $utcDateTime)])
            ->whereRaw(['reserve_to' => array('$gt' => $utcDateTime)])
            ->get();

        /* Getting bookings from mschool collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
        $msBookings  = MountSchoolBooking::select('beds', 'dormitory', 'sleeps')
            ->where('is_delete', 0)
            ->where('cabin_name', $cabin->name)
            ->whereIn('status', ['1', '4', '7', '8'])
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
                            return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited </span>';
                        }
                        else {
                            return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                        }
                    }
                    else {
                        return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place: <br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited </span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }

                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }

                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                        return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                    }
                    else {
                        return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                    }
                }
                else {
                    return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
                }
            }

        }
        else {
            $totalSleeps = $sleeps + $msSleeps;

            /* Calculating sleeps for not regular */
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
                            return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                        }
                        else {
                            return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                        }
                    }
                    else {
                        return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
                    }
                }
            }

            /* Calculating sleeps for regular */
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
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
                                return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                            }
                            else {
                                return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                            }
                        }
                        else {
                            return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
                        }
                    }
                }


            }

            /* Calculating sleeps for normal */
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
                        return '<span class="label label-warning pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Limited</span>';
                    }
                    else {
                        return '<span class="label label-success pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Available</span>';
                    }
                }
                else {
                    return '<span class="label label-danger pull-left label-cabin-details">Tomorrow sleeping place:<br class="s-p-a-br-cabin-details"/> Booked out</span>';
                }

            }
        }
        /* Checking bookings available end */
    }
}
