<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddToCartRequest;
use App\Season;
use App\Cabin;
use App\Booking;
use App\MountSchoolBooking;
use DateTime;
use DatePeriod;
use DateInterval;

class CookieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\AddToCartRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddToCartRequest $request)
    {
        if(isset($request->cookieToCart) && $request->cookieToCart === 'cookieToCart') {
            $clickHere               = '<a href="/inquiry">'.__("searchDetails.clickHere").'</a>';
            $monthBegin              = DateTime::createFromFormat('d.m.y', $request->dateFrom)->format('Y-m-d');
            $monthEnd                = DateTime::createFromFormat('d.m.y', $request->dateTo)->format('Y-m-d');
            $d1                      = new DateTime($monthBegin);
            $d2                      = new DateTime($monthEnd);
            $dateDifference          = $d2->diff($d1);
            $available               = 'failure';
            $invoiceNumber           = '';
            $bedsRequest             = (int)$request->beds;
            $dormsRequest            = (int)$request->dorms;
            $sleepsRequest           = (int)$request->sleeps;
            $requestBedsSumDorms     = $bedsRequest + $dormsRequest;
            if($monthBegin < $monthEnd) {
                if($dateDifference->days <= 60) {
                    $holiday_prepare          = [];
                    $not_regular_dates        = [];
                    $dates_array              = [];
                    $availableStatus          = [];

                    $seasons                  = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($request->cabin))->get();
                    $cabin                    = Cabin::where('is_delete', 0)
                        ->where('other_cabin', "0")
                        ->findOrFail($request->cabin);

                    /* Payment calculation begin */
                    $guestSleepsTypeCondition = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                    $amount                   = round(($cabin->prepayment_amount * $dateDifference->days) * $guestSleepsTypeCondition, 2);
                    /* Payment calculation end */

                    $generateBookingDates     = $this->generateDates($monthBegin, $monthEnd);

                    foreach ($generateBookingDates as $generateBookingDate) {
                        $dates                 = $generateBookingDate->format('Y-m-d');
                        $day                   = $generateBookingDate->format('D');
                        $bookingDateSeasonType = null;

                        /* Checking season begin */
                        if($seasons) {
                            foreach ($seasons as $season) {

                                if (($season->summerSeasonStatus === 'open') && ($season->summerSeason === 1) && ($dates >= ($season->earliest_summer_open)->format('Y-m-d')) && ($dates < ($season->latest_summer_close)->format('Y-m-d'))) {
                                    $holiday_prepare[]     = ($season->summer_mon === 1) ? 'Mon' : 0;
                                    $holiday_prepare[]     = ($season->summer_tue === 1) ? 'Tue' : 0;
                                    $holiday_prepare[]     = ($season->summer_wed === 1) ? 'Wed' : 0;
                                    $holiday_prepare[]     = ($season->summer_thu === 1) ? 'Thu' : 0;
                                    $holiday_prepare[]     = ($season->summer_fri === 1) ? 'Fri' : 0;
                                    $holiday_prepare[]     = ($season->summer_sat === 1) ? 'Sat' : 0;
                                    $holiday_prepare[]     = ($season->summer_sun === 1) ? 'Sun' : 0;
                                    $bookingDateSeasonType = 'summer';
                                }
                                elseif (($season->winterSeasonStatus === 'open') && ($season->winterSeason === 1) && ($dates >= ($season->earliest_winter_open)->format('Y-m-d')) && ($dates < ($season->latest_winter_close)->format('Y-m-d'))) {
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

                            if (!$bookingDateSeasonType)
                            {
                                return response()->json(['error' => __('searchDetails.notSeasonTime')], 422);
                            }

                            $prepareArray       = [$dates => $day];
                            $array_unique       = array_unique($holiday_prepare);
                            $array_intersect    = array_intersect($prepareArray, $array_unique);

                            foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                                if((strtotime($array_intersect_key) >= strtotime($monthBegin)) && (strtotime($array_intersect_key) < strtotime($monthEnd))) {
                                    return response()->json(['error' => __('searchDetails.holidayIncludedAlert')], 422);
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

                        /* Getting bookings from booking collection status 1=> Fix, 4=> Request (Reservation), 5=> Waiting for payment, 8=> Cart */
                        $bookings  = Booking::select('beds', 'dormitory', 'sleeps')
                            ->where('is_delete', 0)
                            ->where('cabinname', $cabin->name)
                            ->whereIn('status', ['1', '4', '5', '8'])
                            ->whereRaw(['checkin_from' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->get();

                        /* Getting bookings from mschool collection status 1=> Fix, 4=> Request (Reservation), 5=> Waiting for payment, 8=> Cart */
                        $msBookings  = MountSchoolBooking::select('beds', 'dormitory', 'sleeps')
                            ->where('is_delete', 0)
                            ->where('cabin_name', $cabin->name)
                            ->whereIn('status', ['1', '4', '5', '8'])
                            ->whereRaw(['check_in' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
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
                        if($cabin->sleeping_place != 1) {
                            $totalBeds       = $beds + $msBeds;
                            $totalDorms      = $dorms + $msDorms;

                            /* Calculating beds & dorms for not regular */
                            if($cabin->not_regular === 1) {
                                $not_regular_date_explode = explode(" - ", $cabin->not_regular_date);
                                $not_regular_date_begin   = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[0])->format('Y-m-d');
                                $not_regular_date_end     = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[1])->format('Y-m-d 23:59:59'); //To get the end date. We need to add time
                                $generateNotRegularDates  = $this->generateDates($not_regular_date_begin, $not_regular_date_end);

                                foreach($generateNotRegularDates as $generateNotRegularDate) {
                                    $not_regular_dates[]  = $generateNotRegularDate->format('Y-m-d');
                                }

                                if(in_array($dates, $not_regular_dates)) {

                                    $dates_array[] = $dates;

                                    if(($totalBeds < $cabin->not_regular_beds) || ($totalDorms < $cabin->not_regular_dorms)) {
                                        $not_regular_beds_diff              = $cabin->not_regular_beds - $totalBeds;
                                        $not_regular_dorms_diff             = $cabin->not_regular_dorms - $totalDorms;

                                        /* Available beds and dorms on not regular */
                                        $not_regular_beds_avail             = ($not_regular_beds_diff >= 0) ? $not_regular_beds_diff : 0;
                                        $not_regular_dorms_avail            = ($not_regular_dorms_diff >= 0) ? $not_regular_dorms_diff : 0;

                                        if($bedsRequest <= $not_regular_beds_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                        }

                                        if($dormsRequest <= $not_regular_dorms_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                        }

                                        /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                        if($cabin->not_regular_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->not_regular_inquiry_guest) {
                                            $availableStatus[] = 'notAvailable';
                                            $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                            $request->session()->put('cabin_name', $cabin->name);
                                            $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                            $request->session()->put('checkin_from', $request->dateFrom);
                                            $request->session()->put('reserve_to', $request->dateTo);
                                            $request->session()->put('beds', $bedsRequest);
                                            $request->session()->put('dormitory', $dormsRequest);
                                            $request->session()->put('sleeps', $requestBedsSumDorms);
                                            $request->session()->put('guests', $requestBedsSumDorms);
                                            return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->not_regular_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                        }
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return response()->json(['error' => __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                    }
                                }
                            }

                            /* Calculating beds & dorms for regular */
                            if($cabin->regular === 1) {
                                if($mon_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalBeds < $cabin->mon_beds) || ($totalDorms < $cabin->mon_dorms)) {
                                            $mon_beds_diff   = $cabin->mon_beds - $totalBeds;
                                            $mon_dorms_diff  = $cabin->mon_dorms - $totalDorms;

                                            /* Available beds and dorms on regular monday */
                                            $mon_beds_avail  = ($mon_beds_diff >= 0) ? $mon_beds_diff : 0;
                                            $mon_dorms_avail = ($mon_dorms_diff >= 0) ? $mon_dorms_diff : 0;

                                            if($bedsRequest <= $mon_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            if($dormsRequest <= $mon_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->mon_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->mon_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', $bedsRequest);
                                                $request->session()->put('dormitory', $dormsRequest);
                                                $request->session()->put('sleeps', $requestBedsSumDorms);
                                                $request->session()->put('guests', $requestBedsSumDorms);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->mon_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($tue_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalBeds < $cabin->tue_beds) || ($totalDorms < $cabin->tue_dorms)) {
                                            $tue_beds_diff  = $cabin->tue_beds - $totalBeds;
                                            $tue_dorms_diff = $cabin->tue_dorms - $totalDorms;

                                            /* Available beds and dorms on regular tuesday */
                                            $tue_beds_avail  = ($tue_beds_diff >= 0) ? $tue_beds_diff : 0;
                                            $tue_dorms_avail = ($tue_dorms_diff >= 0) ? $tue_dorms_diff : 0;

                                            if($bedsRequest <= $tue_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            if($dormsRequest <= $tue_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->tue_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->tue_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', $bedsRequest);
                                                $request->session()->put('dormitory', $dormsRequest);
                                                $request->session()->put('sleeps', $requestBedsSumDorms);
                                                $request->session()->put('guests', $requestBedsSumDorms);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->tue_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($wed_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalBeds < $cabin->wed_beds) || ($totalDorms < $cabin->wed_dorms)) {
                                            $wed_beds_diff   = $cabin->wed_beds - $totalBeds;
                                            $wed_dorms_diff  = $cabin->wed_dorms - $totalDorms;

                                            /* Available beds and dorms on regular wednesday */
                                            $wed_beds_avail  = ($wed_beds_diff >= 0) ? $wed_beds_diff : 0;
                                            $wed_dorms_avail = ($wed_dorms_diff >= 0) ? $wed_dorms_diff : 0;

                                            if($bedsRequest <= $wed_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            if($dormsRequest <= $wed_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->wed_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->wed_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', $bedsRequest);
                                                $request->session()->put('dormitory', $dormsRequest);
                                                $request->session()->put('sleeps', $requestBedsSumDorms);
                                                $request->session()->put('guests', $requestBedsSumDorms);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->wed_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($thu_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalBeds < $cabin->thu_beds) || ($totalDorms < $cabin->thu_dorms)) {
                                            $thu_beds_diff   = $cabin->thu_beds - $totalBeds;
                                            $thu_dorms_diff  = $cabin->thu_dorms - $totalDorms;

                                            /* Available beds and dorms on regular thursday */
                                            $thu_beds_avail  = ($thu_beds_diff >= 0) ? $thu_beds_diff : 0;
                                            $thu_dorms_avail = ($thu_dorms_diff >= 0) ? $thu_dorms_diff : 0;

                                            if($bedsRequest <= $thu_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            if($dormsRequest <= $thu_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->thu_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->thu_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', $bedsRequest);
                                                $request->session()->put('dormitory', $dormsRequest);
                                                $request->session()->put('sleeps', $requestBedsSumDorms);
                                                $request->session()->put('guests', $requestBedsSumDorms);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->thu_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($fri_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalBeds < $cabin->fri_beds) || ($totalDorms < $cabin->fri_dorms)) {
                                            $fri_beds_diff  = $cabin->fri_beds - $totalBeds;
                                            $fri_dorms_diff = $cabin->fri_dorms - $totalDorms;

                                            /* Available beds and dorms on regular friday */
                                            $fri_beds_avail  = ($fri_beds_diff >= 0) ? $fri_beds_diff : 0;
                                            $fri_dorms_avail = ($fri_dorms_diff >= 0) ? $fri_dorms_diff : 0;

                                            if($bedsRequest <= $fri_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            if($dormsRequest <= $fri_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->fri_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->fri_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', $bedsRequest);
                                                $request->session()->put('dormitory', $dormsRequest);
                                                $request->session()->put('sleeps', $requestBedsSumDorms);
                                                $request->session()->put('guests', $requestBedsSumDorms);
                                                return response()->json(['error' =>  __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m"). __("searchDetails.inquiryAlert1").$cabin->fri_inquiry_guest. __("searchDetails.inquiryAlert2").$clickHere. __("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($sat_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalBeds < $cabin->sat_beds) || ($totalDorms < $cabin->sat_dorms)) {
                                            $sat_beds_diff   = $cabin->sat_beds - $totalBeds;
                                            $sat_dorms_diff  = $cabin->sat_dorms - $totalDorms;

                                            /* Available beds and dorms on regular saturday */
                                            $sat_beds_avail  = ($sat_beds_diff >= 0) ? $sat_beds_diff : 0;
                                            $sat_dorms_avail = ($sat_dorms_diff >= 0) ? $sat_dorms_diff : 0;

                                            if($bedsRequest <= $sat_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            if($dormsRequest <= $sat_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->sat_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->sat_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', $bedsRequest);
                                                $request->session()->put('dormitory', $dormsRequest);
                                                $request->session()->put('sleeps', $requestBedsSumDorms);
                                                $request->session()->put('guests', $requestBedsSumDorms);
                                                return response()->json(['error' =>  __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m"). __("searchDetails.inquiryAlert1").$cabin->sat_inquiry_guest. __("searchDetails.inquiryAlert2").$clickHere. __("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($sun_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalBeds < $cabin->sun_beds) || ($totalDorms < $cabin->sun_dorms)) {
                                            $sun_beds_diff   = $cabin->sun_beds - $totalBeds;
                                            $sun_dorms_diff  = $cabin->sun_dorms - $totalDorms;

                                            /* Available beds and dorms on regular sunday */
                                            $sun_beds_avail  = ($sun_beds_diff >= 0) ? $sun_beds_diff : 0;
                                            $sun_dorms_avail = ($sun_dorms_diff >= 0) ? $sun_dorms_diff : 0;

                                            if($bedsRequest <= $sun_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            if($dormsRequest <= $sun_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->sun_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->sun_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', $bedsRequest);
                                                $request->session()->put('dormitory', $dormsRequest);
                                                $request->session()->put('sleeps', $requestBedsSumDorms);
                                                $request->session()->put('guests', $requestBedsSumDorms);
                                                return response()->json(['error' =>  __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m"). __("searchDetails.inquiryAlert1").$cabin->sun_inquiry_guest. __("searchDetails.inquiryAlert2").$clickHere. __("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' =>  __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }
                            }

                            /* Calculating beds & dorms for normal */
                            if(!in_array($dates, $dates_array)) {
                                if(($totalBeds < $cabin->beds) || ($totalDorms < $cabin->dormitory)) {

                                    $normal_beds_diff              = $cabin->beds - $totalBeds;
                                    $normal_dorms_diff             = $cabin->dormitory - $totalDorms;

                                    /* Available beds and dorms on normal */
                                    $normal_beds_avail             = ($normal_beds_diff >= 0) ? $normal_beds_diff : 0;
                                    $normal_dorms_avail            = ($normal_dorms_diff >= 0) ? $normal_dorms_diff : 0;

                                    if($bedsRequest <= $normal_beds_avail) {
                                        $availableStatus[] = 'available';
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return response()->json(['error' => $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                    }

                                    if($dormsRequest <= $normal_dorms_avail) {
                                        $availableStatus[] = 'available';
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return response()->json(['error' => $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                    }

                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                    if($cabin->inquiry_starts > 0 && $requestBedsSumDorms >= $cabin->inquiry_starts) {
                                        $availableStatus[] = 'notAvailable';
                                        $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                        $request->session()->put('cabin_name', $cabin->name);
                                        $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                        $request->session()->put('checkin_from', $request->dateFrom);
                                        $request->session()->put('reserve_to', $request->dateTo);
                                        $request->session()->put('beds', $bedsRequest);
                                        $request->session()->put('dormitory', $dormsRequest);
                                        $request->session()->put('sleeps', $requestBedsSumDorms);
                                        $request->session()->put('guests', $requestBedsSumDorms);
                                        return response()->json(['error' =>  __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m"). __("searchDetails.inquiryAlert1").$cabin->inquiry_starts. __("searchDetails.inquiryAlert2").$clickHere. __("searchDetails.inquiryAlert3")], 422);
                                    }
                                }
                                else {
                                    $availableStatus[] = 'notAvailable';
                                    return response()->json(['error' =>  __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                }
                            }

                        }
                        else {
                            $totalSleeps     = $sleeps + $msSleeps;

                            /* Calculating sleeps for not regular */
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

                                    if(($totalSleeps < $cabin->not_regular_sleeps)) {
                                        $not_regular_sleeps_diff       = $cabin->not_regular_sleeps - $totalSleeps;

                                        /* Available sleeps on not regular */
                                        $not_regular_sleeps_avail      = ($not_regular_sleeps_diff >= 0) ? $not_regular_sleeps_diff : 0;

                                        if($sleepsRequest <= $not_regular_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                        }

                                        /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                        if($cabin->not_regular_inquiry_guest > 0 && $sleepsRequest >= $cabin->not_regular_inquiry_guest) {
                                            $availableStatus[] = 'notAvailable';
                                            $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                            $request->session()->put('cabin_name', $cabin->name);
                                            $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                            $request->session()->put('checkin_from', $request->dateFrom);
                                            $request->session()->put('reserve_to', $request->dateTo);
                                            $request->session()->put('beds', 0);
                                            $request->session()->put('dormitory', 0);
                                            $request->session()->put('sleeps', $sleepsRequest);
                                            $request->session()->put('guests', $sleepsRequest);
                                            return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->not_regular_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                        }
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                    }
                                }
                            }

                            /* Calculating sleeps for regular */
                            if($cabin->regular === 1) {

                                if($mon_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalSleeps < $cabin->mon_sleeps)) {
                                            $mon_sleeps_diff       = $cabin->mon_sleeps - $totalSleeps;

                                            /* Available sleeps on regular monday */
                                            $mon_sleeps_avail      = ($mon_sleeps_diff >= 0) ? $mon_sleeps_diff : 0;

                                            if($sleepsRequest <= $mon_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->mon_inquiry_guest > 0 && $sleepsRequest >= $cabin->mon_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->mon_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($tue_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalSleeps < $cabin->tue_sleeps)) {
                                            $tue_sleeps_diff       = $cabin->tue_sleeps - $totalSleeps;

                                            /* Available sleeps on regular tuesday */
                                            $tue_sleeps_avail      = ($tue_sleeps_diff >= 0) ? $tue_sleeps_diff : 0;

                                            if($sleepsRequest <= $tue_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->tue_inquiry_guest > 0 && $sleepsRequest >= $cabin->tue_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->tue_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($wed_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalSleeps < $cabin->wed_sleeps)) {
                                            $wed_sleeps_diff       = $cabin->wed_sleeps - $totalSleeps;

                                            /* Available sleeps on regular wednesday */
                                            $wed_sleeps_avail      = ($wed_sleeps_diff >= 0) ? $wed_sleeps_diff : 0;

                                            if($sleepsRequest <= $wed_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->wed_inquiry_guest > 0 && $sleepsRequest >= $cabin->wed_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->wed_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($thu_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalSleeps < $cabin->thu_sleeps)) {
                                            $thu_sleeps_diff       = $cabin->thu_sleeps - $totalSleeps;

                                            /* Available sleeps on regular thursday */
                                            $thu_sleeps_avail      = ($thu_sleeps_diff >= 0) ? $thu_sleeps_diff : 0;

                                            if($sleepsRequest <= $thu_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->thu_inquiry_guest > 0 && $sleepsRequest >= $cabin->thu_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->thu_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($fri_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalSleeps < $cabin->fri_sleeps)) {
                                            $fri_sleeps_diff       = $cabin->fri_sleeps - $totalSleeps;

                                            /* Available sleeps on regular friday */
                                            $fri_sleeps_avail      = ($fri_sleeps_diff >= 0) ? $fri_sleeps_diff : 0;

                                            if($sleepsRequest <= $fri_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->fri_inquiry_guest > 0 && $sleepsRequest >= $cabin->fri_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->fri_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($sat_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalSleeps < $cabin->sat_sleeps)) {
                                            $sat_sleeps_diff       = $cabin->sat_sleeps - $totalSleeps;

                                            /* Available sleeps on regular saturday */
                                            $sat_sleeps_avail      = ($sat_sleeps_diff >= 0) ? $sat_sleeps_diff : 0;

                                            if($sleepsRequest <= $sat_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->sat_inquiry_guest > 0 && $sleepsRequest >= $cabin->sat_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->sat_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                                if($sun_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[] = $dates;

                                        if(($totalSleeps < $cabin->sun_sleeps)) {
                                            $sun_sleeps_diff       = $cabin->sun_sleeps - $totalSleeps;

                                            /* Available sleeps on regular sunday */
                                            $sun_sleeps_avail      = ($sun_sleeps_diff >= 0) ? $sun_sleeps_diff : 0;

                                            if($sleepsRequest <= $sun_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->sun_inquiry_guest > 0 && $sleepsRequest >= $cabin->sun_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';
                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $request->dateFrom);
                                                $request->session()->put('reserve_to', $request->dateTo);
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);
                                                return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->sun_inquiry_guest.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }
                                }

                            }

                            /* Calculating sleeps for normal */
                            if(!in_array($dates, $dates_array)) {

                                if(($totalSleeps < $cabin->sleeps)) {
                                    $normal_sleeps_diff       = $cabin->sleeps - $totalSleeps;

                                    /* Available sleeps on normal */
                                    $normal_sleeps_avail      = ($normal_sleeps_diff >= 0) ? $normal_sleeps_diff : 0;

                                    if($sleepsRequest <= $normal_sleeps_avail) {
                                        $availableStatus[] = 'available';
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return response()->json(['error' => $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m")], 422);
                                    }

                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                    if($cabin->inquiry_starts > 0 && $sleepsRequest >= $cabin->inquiry_starts) {
                                        $availableStatus[] = 'notAvailable';
                                        $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                        $request->session()->put('cabin_name', $cabin->name);
                                        $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                        $request->session()->put('checkin_from', $request->dateFrom);
                                        $request->session()->put('reserve_to', $request->dateTo);
                                        $request->session()->put('beds', 0);
                                        $request->session()->put('dormitory', 0);
                                        $request->session()->put('sleeps', $sleepsRequest);
                                        $request->session()->put('guests', $sleepsRequest);
                                        return response()->json(['error' => __("searchDetails.inquiryAlert").$generateBookingDate->format("d.m").__("searchDetails.inquiryAlert1").$cabin->inquiry_starts.__("searchDetails.inquiryAlert2").$clickHere.__("searchDetails.inquiryAlert3")], 422);
                                    }
                                }
                                else {
                                    $availableStatus[] = 'notAvailable';
                                    return response()->json(['error' => __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m")], 422);
                                }

                            }

                        }
                        /* Checking bookings available ends */
                    }

                    if(!in_array('notAvailable', $availableStatus)) {
                        $available                 = 'success';
                        /* Create invoice number begin */
                        if( !empty ($cabin->invoice_autonum) ) {
                            $autoNumber = (int)$cabin->invoice_autonum + 1;
                        }
                        else {
                            $autoNumber = 100000;
                        }

                        if( !empty ($cabin->invoice_code) ) {
                            $invoiceCode   = $cabin->invoice_code;
                            $invoiceNumber = $invoiceCode . "-" . date("y") . "-" . $autoNumber;
                        }
                        /* Create invoice number end */

                        $bookingSleeps      = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;

                        /* Storing data in to cookie begin*/

                        $item = [
                            'cabinNameSession' => $cabin->name,
                            'cabinIdSession' => $cabin->_id,
                            'checkInFromSession' => $this->getDateUtc($request->dateFrom),
                            'reserveToSession' => $this->getDateUtc($request->dateTo),
                            'bedsSession' => $bedsRequest,
                            'dormitorySession' => $dormsRequest,
                            'invoiceNumberSession' => $invoiceNumber,
                            'sleepsSession' => $bookingSleeps,
                            'guestsSession' => $bookingSleeps,
                            'amountSession' => $amount,
                            'reservationCancelSession' => $cabin->reservation_cancel,
                            'autoNumberSession' => $autoNumber,
                        ];

                        $request->session()->put('item', $item);

                    }

                }
                else {
                    return response()->json(['error' => __("searchDetails.sixtyDaysExceed")], 422);
                }
            }
            else {
                return response()->json(['error' => __("searchDetails.dateGreater")], 422);
            }

            return response()->json(['response' => $available]);

        }
        else {
            return response()->json(['error' => __("searchDetails.failedStatus")], 422);
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
