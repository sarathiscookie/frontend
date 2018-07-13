<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BookingHistoryRequest;
use App\Http\Requests\PaymentRequest;
use App\Region;
use App\Ordernumber;
use App\Season;
use App\Booking;
use App\MountSchoolBooking;
use App\Cabin;
use App\Country;
use App\Order;
use App\Userlist;
use Auth;
use PDF;
use DateTime;
use DatePeriod;
use DateInterval;
use Mail;
use App\Mail\SendVoucher;
use Validator;
use App\Http\Controllers\PaymentController;

class BookingHistoryController extends Controller
{
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('is_delete', 0)
            ->whereIn('status', ['1', '2', '3', '4', '5', '7']) /* 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 7=> Inquiry */
            ->orderBy('bookingdate', 'desc')
            ->simplePaginate(10);

        return view('bookingHistory', ['bookings' => $bookings]);
    }

    /**
     * Return cabin details when injection occur.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function cabin($name)
    {
        $cabin     = '';
        if($name) {
            $cabin = Cabin::select('name', 'region', 'sleeping_place')
                ->where('is_delete', 0)
                ->where('other_cabin', "0")
                ->where('name', $name)
                ->first();
        }
        return $cabin;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $booking           = Booking::where('status', '1')
            ->where('payment_status', '1')
            ->where('is_delete', 0)
            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->find($id);

        if(!empty($booking)) {

            if(empty($booking->order_id)) {
                /* Generate order number begin */
                $orderNumber   = Ordernumber::first();
                if( !empty ($orderNumber->number) ) {
                    $order_num = (int)$orderNumber->number + 1;
                }
                else {
                    $order_num = 100000;
                }
                $order_number  = 'ORDER'.'-'.date('y').'-'.$order_num;
                /* Generate order number end */

                /* Creating new order: This is for old system. In old system we don't have orders table. */
                $order                    = new Order;
                $order->order_id          = $order_number;
                $order->auth_user         = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                $order->old_order_comment = 'Booking from old website';
                $order->order_delete      = 0;
                $order->save();

                if(!empty($order)) {
                    /* Updating order number in booking collection */
                    $booking->order_id   = new \MongoDB\BSON\ObjectID($order->_id);
                    $booking->save();

                    /* Updating order number in ordernumber collection */
                    $orderNumber->number = $order_num;
                    $orderNumber->save();
                }
                else {
                    return redirect()->back();
                }
            }

            $cabinDetails = Cabin::select('name', 'region', 'prepayment_amount', 'sleeping_place', 'halfboard', 'halfboard_price')
                ->where('is_delete', 0)
                ->where('other_cabin', "0")
                ->where('name', $booking->cabinname)
                ->first();

            $country      = Country::select('name')
                ->where('is_delete', 0)
                ->get();

            return view('bookingHistoryEdit', ['booking' => $booking, 'cabinDetails' => $cabinDetails, 'country' => $country]);
        }
        else {
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\BookingHistoryRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BookingHistoryRequest $request, $id)
    {
        if($request->has('updateBooking') && $request->updateBooking === 'updateBooking') {

            $monthBegin                   = DateTime::createFromFormat('d.m.y', $request->dateFrom)->format('Y-m-d');
            $monthEnd                     = DateTime::createFromFormat('d.m.y', $request->dateTo)->format('Y-m-d');

            $d1                           = new DateTime($monthBegin);
            $d2                           = new DateTime($monthEnd);
            $new_date_diff                = $d2->diff($d1);
            $requestBedsSumDorms          = 0;
            $bookingSleeps                = 0;
            $bookingBeds                  = 0;
            $bookingDorms                 = 0;
            $bedsSumDorms                 = 0;
            $holiday_prepare              = [];
            $not_regular_dates            = [];
            $dates_array                  = [];
            $availableStatus              = [];
            $sleepsRequest                = (int)$request->sleeps;
            $bedsRequest                  = (int)$request->beds;
            $dormsRequest                 = (int)$request->dormitory;

            if($monthBegin < $monthEnd) {
                if($new_date_diff->days <= 60) {

                    $user                 = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);

                    $booking              = Booking::select('invoice_number', 'cabinname', 'beds', 'dormitory', 'sleeps','prepayment_amount', 'checkin_from', 'reserve_to', 'order_id')
                        ->where('status', '1')
                        ->where('payment_status', '1')
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find($id);

                    if(!empty($booking)) {

                        $cabin                    = Cabin::where('is_delete', 0)
                            ->where('other_cabin', "0")
                            ->where('name', $booking->cabinname)
                            ->first();

                        $order                    = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))->find($booking->order_id);

                        $orderNumber              = Ordernumber::first();

                        /* Form request begin */
                        if ($cabin->sleeping_place === 1) {
                            $bookingSleeps        = (int)$booking->sleeps;
                        }
                        else {
                            $bookingBeds          = (int)$booking->beds;
                            $bookingDorms         = (int)$booking->dormitory;
                            $requestBedsSumDorms  = $bedsRequest + $dormsRequest;
                            $bedsSumDorms         = $bookingBeds + $bookingDorms;
                        }
                        /* Form request end */

                        /* Payment calculation begin */
                        // Old Data
                        $old_sleeps_sum           = ($cabin->sleeping_place === 1) ? $bookingSleeps : $bedsSumDorms;
                        $old_amount               = round($booking->prepayment_amount, 2);
                        $old_checking_from        = $booking->checkin_from->format('Y-m-d');
                        $old_reserve_to           = $booking->reserve_to->format('Y-m-d');
                        $old_date_one             = new DateTime($old_checking_from);
                        $old_date_two             = new DateTime($old_reserve_to);
                        $old_date_diff            = $old_date_two->diff($old_date_one);

                        // New data
                        $new_sleeps_sum           = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                        $new_amount               = round(($cabin->prepayment_amount * $new_date_diff->days) * $new_sleeps_sum, 2);
                        $new_old_amount_diff      = round($new_amount - $old_amount, 2);
                        $total                    = ($new_amount > $old_amount) ? $new_old_amount_diff : $new_amount;
                        $amount                   = ($new_amount > $old_amount) ? $total : $old_amount - $total;
                        /* Payment calculation end */

                        $seasons                  = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id))->get();

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
                                    return redirect()->back()->with('bookingAvailableStatus', __('searchDetails.notSeasonTime'));
                                }

                                $prepareArray       = [$dates => $day];
                                $array_unique       = array_unique($holiday_prepare);
                                $array_intersect    = array_intersect($prepareArray, $array_unique);

                                foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                                    if((strtotime($array_intersect_key) >= strtotime($monthBegin)) && (strtotime($array_intersect_key) < strtotime($monthEnd))) {
                                        return redirect()->back()->with('bookingAvailableStatus', __('searchDetails.holidayIncludedAlert'));
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

                            /* Getting bookings from booking collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart, 9=> Old (Booking updated) */
                            $bookings  = Booking::select('beds', 'dormitory', 'sleeps')
                                ->where('is_delete', 0)
                                ->where('cabinname', $cabin->name)
                                ->whereIn('status', ['1', '4', '5', '8'])
                                ->whereRaw(['checkin_from' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                                ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                                ->get();

                            /* Getting bookings from mschool collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart, 9=> Old (Booking updated) */
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
                                /* Condition to check any changes applied in date and beds or dorms by guest */
                                if( ($total === $old_amount) && ($old_checking_from === $monthBegin) && ($old_reserve_to === $monthEnd) && ($bedsRequest === $bookingBeds) && ($dormsRequest === $bookingDorms) ) {
                                    return redirect()->back()->with('updateBookingFailedStatus', __('bookingHistory.errorThree'));
                                }
                                else {
                                    /*Availability checking of beds dorms begin*/
                                    $totalBeds           = ($beds + $msBeds) - $bookingBeds;
                                    $totalDorms          = ($dorms + $msDorms) - $bookingDorms;
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
                                                    return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                }

                                                if($dormsRequest <= $not_regular_dorms_avail) {
                                                    $availableStatus[] = 'available';
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                }

                                                /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                if($cabin->not_regular_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->not_regular_inquiry_guest) {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->not_regular_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                }
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
                                            }
                                        }
                                    }

                                    /* Calculating beds & dorms for regular */
                                    if($cabin->regular === 1) {

                                        if($mon_day === $day) {
                                            if(!in_array($dates, $dates_array)) {

                                                $dates_array[] = $dates;

                                                if(($totalBeds < $cabin->mon_beds) || ($totalDorms < $cabin->mon_dorms)) {
                                                    $mon_beds_diff              = $cabin->mon_beds - $totalBeds;
                                                    $mon_dorms_diff             = $cabin->mon_dorms - $totalDorms;

                                                    /* Available beds and dorms on regular monday */
                                                    $mon_beds_avail             = ($mon_beds_diff >= 0) ? $mon_beds_diff : 0;
                                                    $mon_dorms_avail            = ($mon_dorms_diff >= 0) ? $mon_dorms_diff : 0;

                                                    if($bedsRequest <= $mon_beds_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    if($dormsRequest <= $mon_dorms_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->mon_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->mon_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->mon_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
                                                }
                                            }
                                        }

                                        if($tue_day === $day) {
                                            if(!in_array($dates, $dates_array)) {

                                                $dates_array[] = $dates;

                                                if(($totalBeds < $cabin->tue_beds) || ($totalDorms < $cabin->tue_dorms)) {
                                                    $tue_beds_diff              = $cabin->tue_beds - $totalBeds;
                                                    $tue_dorms_diff             = $cabin->tue_dorms - $totalDorms;

                                                    /* Available beds and dorms on regular tuesday */
                                                    $tue_beds_avail             = ($tue_beds_diff >= 0) ? $tue_beds_diff : 0;
                                                    $tue_dorms_avail            = ($tue_dorms_diff >= 0) ? $tue_dorms_diff : 0;

                                                    if($bedsRequest <= $tue_beds_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    if($dormsRequest <= $tue_dorms_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->tue_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->tue_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->tue_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
                                                }
                                            }
                                        }

                                        if($wed_day === $day) {

                                            if(!in_array($dates, $dates_array)) {

                                                $dates_array[] = $dates;

                                                if(($totalBeds < $cabin->wed_beds) || ($totalDorms < $cabin->wed_dorms)) {
                                                    $wed_beds_diff              = $cabin->wed_beds - $totalBeds;
                                                    $wed_dorms_diff             = $cabin->wed_dorms - $totalDorms;

                                                    /* Available beds and dorms on regular wednesday */
                                                    $wed_beds_avail             = ($wed_beds_diff >= 0) ? $wed_beds_diff : 0;
                                                    $wed_dorms_avail            = ($wed_dorms_diff >= 0) ? $wed_dorms_diff : 0;

                                                    if($bedsRequest <= $wed_beds_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    if($dormsRequest <= $wed_dorms_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->wed_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->wed_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->wed_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
                                                }
                                            }
                                        }

                                        if($thu_day === $day) {

                                            if(!in_array($dates, $dates_array)) {

                                                $dates_array[] = $dates;

                                                if(($totalBeds < $cabin->thu_beds) || ($totalDorms < $cabin->thu_dorms)) {
                                                    $thu_beds_diff              = $cabin->thu_beds - $totalBeds;
                                                    $thu_dorms_diff             = $cabin->thu_dorms - $totalDorms;

                                                    /* Available beds and dorms on regular thursday */
                                                    $thu_beds_avail             = ($thu_beds_diff >= 0) ? $thu_beds_diff : 0;
                                                    $thu_dorms_avail            = ($thu_dorms_diff >= 0) ? $thu_dorms_diff : 0;

                                                    if($bedsRequest <= $thu_beds_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    if($dormsRequest <= $thu_dorms_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->thu_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->thu_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->thu_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
                                                }
                                            }
                                        }

                                        if($fri_day === $day) {

                                            if(!in_array($dates, $dates_array)) {

                                                $dates_array[] = $dates;

                                                if(($totalBeds < $cabin->fri_beds) || ($totalDorms < $cabin->fri_dorms)) {
                                                    $fri_beds_diff         = $cabin->fri_beds - $totalBeds;
                                                    $fri_dorms_diff        = $cabin->fri_dorms - $totalDorms;

                                                    /* Available beds and dorms on regular friday */
                                                    $fri_beds_avail        = ($fri_beds_diff >= 0) ? $fri_beds_diff : 0;
                                                    $fri_dorms_avail       = ($fri_dorms_diff >= 0) ? $fri_dorms_diff : 0;

                                                    if($bedsRequest <= $fri_beds_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    if($dormsRequest <= $fri_dorms_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->fri_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->fri_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus',  __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m"). __("bookingHistory.bookingLimitReachedOne").($cabin->fri_inquiry_guest - 1). __("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
                                                }
                                            }
                                        }

                                        if($sat_day === $day) {

                                            if(!in_array($dates, $dates_array)) {

                                                $dates_array[] = $dates;

                                                if(($totalBeds < $cabin->sat_beds) || ($totalDorms < $cabin->sat_dorms)) {
                                                    $sat_beds_diff         = $cabin->sat_beds - $totalBeds;
                                                    $sat_dorms_diff        = $cabin->sat_dorms - $totalDorms;

                                                    /* Available beds and dorms on regular saturday */
                                                    $sat_beds_avail        = ($sat_beds_diff >= 0) ? $sat_beds_diff : 0;
                                                    $sat_dorms_avail       = ($sat_dorms_diff >= 0) ? $sat_dorms_diff : 0;

                                                    if($bedsRequest <= $sat_beds_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    if($dormsRequest <= $sat_dorms_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->sat_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->sat_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus',  __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m"). __("bookingHistory.bookingLimitReachedOne").($cabin->sat_inquiry_guest - 1). __("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
                                                }
                                            }
                                        }

                                        if($sun_day === $day) {

                                            if(!in_array($dates, $dates_array)) {

                                                $dates_array[] = $dates;

                                                if(($totalBeds < $cabin->sun_beds) || ($totalDorms < $cabin->sun_dorms)) {
                                                    $sun_beds_diff         = $cabin->sun_beds - $totalBeds;
                                                    $sun_dorms_diff        = $cabin->sun_dorms - $totalDorms;

                                                    /* Available beds and dorms on regular sunday */
                                                    $sun_beds_avail        = ($sun_beds_diff >= 0) ? $sun_beds_diff : 0;
                                                    $sun_dorms_avail       = ($sun_dorms_diff >= 0) ? $sun_dorms_diff : 0;

                                                    if($bedsRequest <= $sun_beds_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    if($dormsRequest <= $sun_dorms_avail) {
                                                        $availableStatus[] = 'available';
                                                    }
                                                    else {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->sun_inquiry_guest > 0 && $requestBedsSumDorms >= $cabin->sun_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus',  __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m"). __("bookingHistory.bookingLimitReachedOne").($cabin->sun_inquiry_guest - 1). __("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus',  __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
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
                                                return redirect()->back()->with('bookingAvailableStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                            }

                                            if($dormsRequest <= $normal_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('bookingAvailableStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->inquiry_starts > 0 && $requestBedsSumDorms >= $cabin->inquiry_starts) {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m"). __("bookingHistory.bookingLimitReachedOne").($cabin->inquiry_starts - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('bookingAvailableStatus',  __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
                                        }
                                    }
                                    /*Availability checking of beds dorms end*/
                                }
                            }
                            else {
                                /* Condition to check any changes applied in date and sleeps by guest */
                                if( ($total === $old_amount) && ($old_checking_from === $monthBegin) && ($old_reserve_to === $monthEnd) && ($bookingSleeps === $sleepsRequest) ) {
                                    return redirect()->back()->with('updateBookingFailedStatus', __('bookingHistory.errorThree'));
                                }
                                else {
                                    /*Availability checking of sleeps begin*/
                                    $totalSleeps = ($sleeps + $msSleeps) - $bookingSleeps;

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
                                                    return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                                }

                                                /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                if($cabin->not_regular_inquiry_guest > 0 && $sleepsRequest >= $cabin->not_regular_inquiry_guest) {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->not_regular_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                }
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
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
                                                        return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->mon_inquiry_guest > 0 && $sleepsRequest >= $cabin->mon_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->mon_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
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
                                                        return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->tue_inquiry_guest > 0 && $sleepsRequest >= $cabin->tue_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->tue_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
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
                                                        return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->wed_inquiry_guest > 0 && $sleepsRequest >= $cabin->wed_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->wed_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
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
                                                        return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->thu_inquiry_guest > 0 && $sleepsRequest >= $cabin->thu_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->thu_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
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
                                                        return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->fri_inquiry_guest > 0 && $sleepsRequest >= $cabin->fri_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->fri_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
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
                                                        return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->sat_inquiry_guest > 0 && $sleepsRequest >= $cabin->sat_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->sat_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
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
                                                        return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                                    }

                                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                                    if($cabin->sun_inquiry_guest > 0 && $sleepsRequest >= $cabin->sun_inquiry_guest) {
                                                        $availableStatus[] = 'notAvailable';
                                                        return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->sun_inquiry_guest - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                                    }
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
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
                                                return redirect()->back()->with('bookingAvailableStatus', $sleepsRequest.__("searchDetails.sleepsNotAvailable").$generateBookingDate->format("d.m"));
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($cabin->inquiry_starts > 0 && $sleepsRequest >= $cabin->inquiry_starts) {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('bookingAvailableStatus', __("bookingHistory.bookingLimitReached").$generateBookingDate->format("d.m").__("bookingHistory.bookingLimitReachedOne").($cabin->inquiry_starts - 1).__("bookingHistory.bookingLimitReachedTwo"));
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('bookingAvailableStatus', __("searchDetails.alreadyFilledSleeps").$generateBookingDate->format("d.m"));
                                        }

                                    }





                                    /*Availability checking of sleeps end*/
                                }
                            }
                            /* Checking bookings available end */
                        }

                        if(!in_array('notAvailable', $availableStatus)) {

                            $available = 'success';

                            /* Create invoice tree structure begin */
                            if(!empty($user->invoice_autonum_tree) ) {
                                $autoNumberTree = (int)$user->invoice_autonum_tree + 1;
                            }
                            else {
                                $autoNumberTree = 1;
                            }
                            /* Create invoice tree structure end */

                            /* Generate order number begin */
                            if(!empty($orderNumber->number) ) {
                                $order_num = (int)$orderNumber->number + 1;
                            }
                            else {
                                $order_num = 100000;
                            }
                            $order_number  = 'ORDER'.'-'.date('y').'-'.$order_num;
                            /* Generate order number end */

                            if ($new_amount <= $old_amount){

                                /* Update status of old booking begin */
                                $booking->booking_update       = date('Y-m-d H:i:s');
                                $booking->status               = "9"; //9 => Old (Booking Updated)
                                $booking->is_delete            = 1;
                                $booking->save();
                                /* Update status of old booking end */

                                /* Update status of old orders begin */
                                if(!empty($order)) {
                                    $order->order_update_date  = date('Y-m-d H:i:s');
                                    $order->save();
                                }
                                /* Update status of old orders end */

                                /* Create new order begin */
                                $newOrder                       = new Order;
                                $newOrder->order_id             = $order_number;
                                $newOrder->auth_user            = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                                $newOrder->order_payment_type   = 'Used previous booking amount';
                                $newOrder->order_payment_method = 4; //4 => Fully paid using previous voucher amount
                                $newOrder->order_amount         = ($new_amount === 0) ? $old_amount : $new_amount;
                                $newOrder->order_total_amount   = ($new_amount === 0) ? $old_amount : $new_amount;
                                $newOrder->old_order_id         = new \MongoDB\BSON\ObjectID($order->_id);
                                $newOrder->order_delete         = 0;
                                $newOrder->save();
                                /* Create new order end */

                                /* Create new booking begin */
                                $invoice_explode                     = explode('-', $booking->invoice_number); // Exploding auto number and ignoring last element

                                $newBooking                          = new Booking;
                                $newBooking->cabinname               = $cabin->name;
                                $newBooking->cabin_id                = new \MongoDB\BSON\ObjectID($cabin->_id);
                                $newBooking->checkin_from            = $this->getDateUtc($request->dateFrom);
                                $newBooking->reserve_to              = $this->getDateUtc($request->dateTo);
                                $newBooking->user                    = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                                $newBooking->beds                    = $bedsRequest;
                                $newBooking->dormitory               = $dormsRequest;
                                $newBooking->invoice_number          = $invoice_explode[0].'-'.$invoice_explode[1].'-'.$invoice_explode[2].'-'.$autoNumberTree;
                                $newBooking->sleeps                  = $new_sleeps_sum;
                                $newBooking->guests                  = $new_sleeps_sum;
                                $newBooking->halfboard               = (isset($request->halfboard)) ? $request->halfboard : '0';
                                $newBooking->comments                = $request->comments;
                                $newBooking->prepayment_amount       = ($new_amount === 0) ? $old_amount : $new_amount;
                                $newBooking->total_prepayment_amount = ($new_amount === 0) ? $old_amount : $new_amount;
                                $newBooking->payment_type            = 'Used previous booking amount';
                                $newBooking->moneybalance_used       = 0;
                                $newBooking->bookingdate             = date('Y-m-d H:i:s');
                                $newBooking->status                  = '1';
                                $newBooking->payment_status          = '1';
                                $newBooking->reservation_cancel      = $cabin->reservation_cancel;
                                $newBooking->money_refunded          = round($amount, 2);
                                $newBooking->old_booking_id          = new \MongoDB\BSON\ObjectID($booking->_id);
                                $newBooking->order_id                = new \MongoDB\BSON\ObjectID($newOrder->_id);
                                $newBooking->is_delete               = 0;
                                $newBooking->save();
                                /* Create new booking end */

                                /* Updating money balance and invoice_autonum tree */
                                $user->money_balance         = round(Auth::user()->money_balance + $amount, 2);
                                $user->invoice_autonum_tree  = $autoNumberTree;
                                $user->save();

                                /* Updating order number in ordernumber collection */
                                $orderNumber->number         = $order_num;
                                $orderNumber->save();

                                /* Send email with voucher */
                                Mail::to($user->usrEmail)->send(new SendVoucher($newBooking));

                                //dd('Dont need to go payment page. Just update booking, update money balance, send email'.'New amount: '.$new_amount.' Old: '.$old_amount.' Total: '.$total. ' Amount ' .$amount);
                                return redirect()->route('booking.history')->with('updateBookingSuccessStatus', __('bookingHistory.updateBookingSuccessTwo'));
                            }
                            else {
                                $request->session()->put('bookingIdRequest', $booking->_id);
                                $request->session()->put('dateFromRequest', $request->dateFrom);
                                $request->session()->put('dateToRequest', $request->dateTo);
                                $request->session()->put('bedRequest', $bedsRequest);
                                $request->session()->put('dormRequest', $dormsRequest);
                                $request->session()->put('sleepRequest', $new_sleeps_sum);

                                if(isset($request->halfboard)) {
                                    $request->session()->put('halfBoardRequest', $request->halfboard);
                                }
                                else {
                                    $request->session()->put('halfBoardRequest', '0');
                                }

                                $request->session()->put('commentsRequest', $request->comments);
                                $request->session()->put('sleepingPlaceRequest', $request->sleeping_place);
                                $request->session()->put('prepaymentAmountRequest', $amount);
                                $request->session()->put('availableStatus', $available);
                                //dd('----Redirect to payment gateway-----'.'New amount: '.$new_amount.' Old: '.$old_amount.' Total: '.$total. ' Amount ' .$amount); //Higher - Amount: 83.04 Old: 55.36 Total: 27.68. Lower - Amount: 27.68 Old: 55.36 Total: 27.68

                                return redirect()->route('booking.history.payment', $request->updateBooking)->with('editAvailableStatus', $available);

                            }
                        }
                    }
                    else {
                        return redirect()->back()->with('updateBookingFailedStatus', __('bookingHistory.errorTwo'));
                    }
                }
                else {
                    return redirect()->back()->with('updateBookingFailedStatus', __("searchDetails.sixtyDaysExceed"));
                }
            }
            else {
                return redirect()->back()->with('updateBookingFailedStatus', __("searchDetails.dateGreater"));
            }
        }
        else {
            return redirect()->back()->with('updateBookingFailedStatus', __('bookingHistory.errorTwo'));
        }
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
     * Display the specified resource.
     *
     * @param  string  $editBooking
     * @return \Illuminate\Http\Response
     */
    public function show($editBooking)
    {
        if($editBooking !== null && $editBooking === 'updateBooking' && session()->has('availableStatus') && session()->get('availableStatus') === 'success') {
            $bookingData            = Booking::where('status', '1')
                ->where('payment_status', '1')
                ->where('is_delete', 0)
                ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->find(session()->get('bookingIdRequest'));

            if(!empty($bookingData)) {
                $sum_prepayment_amount = session()->get('prepaymentAmountRequest');
                $serviceTax            = (new PaymentController)->serviceFees($sum_prepayment_amount, $paymentMethod = null);
                $percentage            = ($serviceTax / 100) * $sum_prepayment_amount;
                $prepay_service_total  = $sum_prepayment_amount + $percentage;

                /* Condition to check pay by bill possible begin */
                // Pay by bill radio button in payment page will show when there is two weeks diff b/w current date and checking from date.
                $checkingFrom          = DateTime::createFromFormat('d.m.y', session()->get('dateFromRequest'))->format('Y-m-d');
                $currentDate           = date('Y-m-d');
                $d1                    = new DateTime($currentDate);
                $d2                    = new DateTime($checkingFrom);
                $dateDifference        = $d2->diff($d1);
                if($dateDifference->days > 14) {
                    $payByBillPossible = 'yes';
                }
                else {
                    $payByBillPossible = 'no';
                }
                /* Condition to check pay by bill possible end */

                /* Get order number */
                $orderNumber           = Ordernumber::first();
                if( !empty ($orderNumber->number) ) {
                    $order_num         = (int)$orderNumber->number + 1;
                }
                else {
                    $order_num         = 100000;
                }
                $order_number          = 'ORDER'.'-'.date('y').'-'.$order_num;

                return view('payment', ['moneyBalance' => Auth::user()->money_balance, 'sumPrepaymentAmount' => $sum_prepayment_amount, 'prepayServiceTotal' => $prepay_service_total, 'serviceTax' => $serviceTax, 'payByBillPossible' => $payByBillPossible, 'order_number' => $order_number, 'editBooking' => $editBooking, 'availableStatus' => session()->get('availableStatus')]);
            }
            else {
                return redirect()->back()->with('updateBookingFailedStatus', __('bookingHistory.errorTwo'));
            }
        }
        else {
            return redirect()->back()->with('updateBookingFailedStatus', __('bookingHistory.errorTwo'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PaymentRequest $request)
    {
        if( isset($request->updateBookingPayment) && $request->updateBookingPayment === 'updateBookingPayment' && $request->session()->has('availableStatus') && $request->session()->get('availableStatus') === 'success' && $request->session()->has('prepaymentAmountRequest') ) {

            $bookingOld                = Booking::where('status', '1')
                ->where('payment_status', '1')
                ->where('is_delete', 0)
                ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->find(session()->get('bookingIdRequest'));

            if(!empty($bookingOld)) {

                $user                  = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                $order                 = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))->find($bookingOld->order_id);
                $cabin                 = Cabin::where('is_delete', 0)->where('other_cabin', "0")->where('name', $bookingOld->cabinname)->first();
                $invoice_explode       = explode('-', $bookingOld->invoice_number); // Exploding auto number and ignoring last element
                $sum_prepayment_amount = session()->get('prepaymentAmountRequest');

                // Pay by bill condition works if there is two weeks diff b/w current date and checking from date.
                $checkingFrom          = DateTime::createFromFormat('d.m.y', session()->get('dateFromRequest'))->format('Y-m-d');
                $currentDate           = date('Y-m-d');
                $d1                    = new DateTime($currentDate);
                $d2                    = new DateTime($checkingFrom);
                $dateDifference        = $d2->diff($d1);
                if($dateDifference->days > 14) {
                    $payByBillPossible = 'yes';
                }
                else {
                    $payByBillPossible = 'no';
                }

                /* Create invoice tree structure begin */
                if(!empty($user->invoice_autonum_tree) ) {
                    $autoNumberTree = (int)$user->invoice_autonum_tree + 1;
                }
                else {
                    $autoNumberTree = 1;
                }
                /* Create invoice tree structure end */

                /* Generate order number begin */
                $orderNumber           = Ordernumber::first();
                if(!empty($orderNumber->number) ) {
                    $order_num         = (int)$orderNumber->number + 1;
                }
                else {
                    $order_num         = 100000;
                }
                $order_number          = 'ORDER'.'-'.date('y').'-'.$order_num;
                /* Generate order number end */

                $total_prepayment_amount = round($sum_prepayment_amount, 2);

                if($request->has('moneyBalance') && $request->moneyBalance === '1') {
                    if($user->money_balance >= $total_prepayment_amount) {
                        /* How much money user have in their account after used money balance */
                        $afterRedeemAmount = $user->money_balance - $total_prepayment_amount;

                        /* Update status of old booking begin */
                        $bookingOld->booking_update = date('Y-m-d H:i:s');
                        $bookingOld->status         = "9"; //9 => Old (Booking Updated)
                        $bookingOld->is_delete      = 1;
                        $bookingOld->save();
                        /* Update status of old booking end */

                        /* Update status of old orders begin */
                        if(!empty($order)) {
                            $order->order_update_date  = date('Y-m-d H:i:s');
                            $order->save();
                        }
                        /* Update status of old orders end */

                        /* Create new order begin */
                        $newOrder                                = new Order;
                        $newOrder->order_id                      = $order_number;
                        $newOrder->auth_user                     = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                        $newOrder->order_amount                  = $total_prepayment_amount;
                        $newOrder->order_total_amount            = $total_prepayment_amount;
                        $newOrder->order_money_balance_used      = $total_prepayment_amount;
                        $newOrder->order_money_balance_used_date = date('Y-m-d H:i:s');
                        $newOrder->old_order_id                  = new \MongoDB\BSON\ObjectID($order->_id);
                        $newOrder->order_payment_method          = 1; // 1 => Fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                        $newOrder->order_delete                  = 0;
                        $newOrder->save();
                        /* Create new order end */

                        if($newOrder) {
                            /* Create new booking begin */
                            $newBooking                          = new Booking;
                            $newBooking->cabinname               = $cabin->name;
                            $newBooking->cabin_id                = new \MongoDB\BSON\ObjectID($cabin->_id);
                            $newBooking->checkin_from            = $this->getDateUtc(session()->get('dateFromRequest'));
                            $newBooking->reserve_to              = $this->getDateUtc(session()->get('dateToRequest'));
                            $newBooking->user                    = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                            $newBooking->beds                    = (int)session()->get('bedRequest');
                            $newBooking->dormitory               = (int)session()->get('dormRequest');
                            $newBooking->invoice_number          = $invoice_explode[0].'-'.$invoice_explode[1].'-'.$invoice_explode[2].'-'.$autoNumberTree;
                            $newBooking->sleeps                  = (int)session()->get('sleepRequest');
                            $newBooking->guests                  = (int)session()->get('sleepRequest'); // Sleeps and guest are same count
                            $newBooking->halfboard               = session()->get('halfBoardRequest');
                            $newBooking->comments                = session()->get('commentsRequest');
                            $newBooking->prepayment_amount       = round($total_prepayment_amount + $bookingOld->prepayment_amount, 2);
                            $newBooking->total_prepayment_amount = round($total_prepayment_amount + $bookingOld->total_prepayment_amount, 2);
                            $newBooking->new_amount              = $total_prepayment_amount;
                            $newBooking->moneybalance_used       = $total_prepayment_amount;
                            $newBooking->bookingdate             = date('Y-m-d H:i:s');
                            $newBooking->status                  = '1';
                            $newBooking->payment_status          = '1';
                            $newBooking->reservation_cancel      = $cabin->reservation_cancel;
                            $newBooking->old_booking_id          = new \MongoDB\BSON\ObjectID($bookingOld->_id);
                            $newBooking->order_id                = new \MongoDB\BSON\ObjectID($newOrder->_id);
                            $newBooking->is_delete               = 0;
                            $newBooking->save();
                            /* Create new booking end */

                            /* Updating money balance and invoice_autonum tree */
                            $user->money_balance         = round($afterRedeemAmount, 2);
                            $user->invoice_autonum_tree  = $autoNumberTree;
                            $user->save();

                            /* Updating order number in ordernumber collection */
                            $orderNumber->number         = $order_num;
                            $orderNumber->save();

                            /* Send email with voucher */
                            Mail::to($user->usrEmail)->send(new SendVoucher($newBooking));

                            return redirect()->route('booking.history.payment.success')->with('editBookingSuccessStatus', __('payment.bookingSuccessStatus'));
                        }
                        else {
                            return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                        }
                    }
                    else {
                        if(isset($request->payment)) {
                            /* How much money user have in their account after used money balance */
                            $afterRedeemAmount = $total_prepayment_amount - $user->money_balance;
                            $percentage        = ((new PaymentController)->serviceFees($afterRedeemAmount, $request->payment) / 100) * $afterRedeemAmount;
                            $total             = round($afterRedeemAmount + $percentage, 2);

                            // Function call for payment gateway section
                            $paymentGateway    = (new PaymentController)->paymentGateway($request->all(), $request->ip(), $total, $order_number);

                            if ($paymentGateway["status"] === "REDIRECT") { // If card is 3d secure return status is REDIRECT and "redirect url" will return.

                                /* Storing new order details */
                                $newOrder                                = Order::firstOrCreate(['old_order_id' => new \MongoDB\BSON\ObjectID($order->_id)]);
                                $newOrder->order_id                      = $order_number;
                                $newOrder->auth_user                     = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                                $newOrder->order_status                  = "REDIRECT";
                                $newOrder->txid                          = $paymentGateway["txid"];
                                $newOrder->userid                        = $paymentGateway["userid"];
                                $newOrder->order_payment_type            = $request->payment;
                                $newOrder->order_payment_method          = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                                $newOrder->order_amount                  = round($afterRedeemAmount, 2);
                                $newOrder->order_total_amount            = $total;
                                $newOrder->order_money_balance_used      = round($user->money_balance, 2);
                                $newOrder->order_money_balance_used_date = date('Y-m-d H:i:s');
                                $newOrder->old_order_id                  = new \MongoDB\BSON\ObjectID($order->_id);
                                $newOrder->order_delete                  = 0;
                                $newOrder->save();
                                /* Storing new order details end */

                                if($newOrder) {
                                    /* Create new booking begin */
                                    $newBooking                          = Booking::firstOrCreate(['old_booking_id' => new \MongoDB\BSON\ObjectID($bookingOld->_id)]);
                                    $newBooking->cabinname               = $cabin->name;
                                    $newBooking->cabin_id                = new \MongoDB\BSON\ObjectID($cabin->_id);
                                    $newBooking->checkin_from            = $this->getDateUtc(session()->get('dateFromRequest'));
                                    $newBooking->reserve_to              = $this->getDateUtc(session()->get('dateToRequest'));
                                    $newBooking->user                    = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                                    $newBooking->beds                    = (int)session()->get('bedRequest');
                                    $newBooking->dormitory               = (int)session()->get('dormRequest');
                                    $newBooking->invoice_number          = $invoice_explode[0].'-'.$invoice_explode[1].'-'.$invoice_explode[2].'-'.$autoNumberTree;
                                    $newBooking->sleeps                  = (int)session()->get('sleepRequest');
                                    $newBooking->guests                  = (int)session()->get('sleepRequest'); // Sleeps and guest are same count
                                    $newBooking->halfboard               = session()->get('halfBoardRequest');
                                    $newBooking->comments                = session()->get('commentsRequest');
                                    $newBooking->prepayment_amount       = round($total_prepayment_amount + $bookingOld->prepayment_amount, 2);
                                    $newBooking->total_prepayment_amount = round($total_prepayment_amount + $bookingOld->total_prepayment_amount, 2);
                                    $newBooking->new_amount              = $total_prepayment_amount;
                                    $newBooking->moneybalance_used       = round($user->money_balance, 2);
                                    $newBooking->bookingdate             = date('Y-m-d H:i:s');
                                    $newBooking->status                  = '10'; // 10=> Temporary (This status is using in edit booking section)
                                    $newBooking->reservation_cancel      = $cabin->reservation_cancel;
                                    $newBooking->payment_type            = $request->payment;
                                    $newBooking->txid                    = $paymentGateway["txid"];
                                    $newBooking->userid                  = $paymentGateway["userid"];
                                    $newBooking->old_booking_id          = new \MongoDB\BSON\ObjectID($bookingOld->_id);
                                    $newBooking->order_id                = new \MongoDB\BSON\ObjectID($newOrder->_id);
                                    $newBooking->is_delete               = 0;
                                    $newBooking->save();
                                    /* Create new booking end */

                                    /* Storing new userid, old userid and updating invoice auto number tree in user collection */
                                    $user->userid               = $paymentGateway["userid"];
                                    $user->invoice_autonum_tree = $autoNumberTree;
                                    $user->save();

                                    /* Updating order number in ordernumber collection */
                                    $orderNumber->number        = $order_num;
                                    $orderNumber->save();

                                    $request->session()->flash('updateBookingPayment', $request->updateBookingPayment);
                                    $request->session()->flash('newBooking', $newBooking->_id);
                                    $request->session()->flash('editTxId', $paymentGateway["txid"]);
                                    $request->session()->flash('editUserId', $paymentGateway["userid"]);
                                    $request->session()->flash('editBookingSuccessStatus', __('payment.bookingSuccessStatus'));

                                    return redirect()->away($paymentGateway["redirecturl"]);
                                }
                                else {
                                    return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                }
                            }
                            elseif ($paymentGateway["status"] === "APPROVED") { // If card is not 3d secure and prepayment(PayByBill) return status is APPROVED and "redirect url" will not return. We manually redirect to success page.

                                /* Storing new order details */
                                $newOrder                                = Order::firstOrCreate(['old_order_id' => new \MongoDB\BSON\ObjectID($order->_id)]);;
                                $newOrder->order_id                      = $order_number;
                                $newOrder->auth_user                     = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                                $newOrder->order_status                  = "APPROVED";
                                $newOrder->txid                          = $paymentGateway["txid"];
                                $newOrder->userid                        = $paymentGateway["userid"];
                                $newOrder->order_payment_type            = $request->payment;
                                $newOrder->order_payment_method          = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                                $newOrder->order_amount                  = round($afterRedeemAmount, 2);
                                $newOrder->order_total_amount            = $total;
                                $newOrder->order_money_balance_used      = round($user->money_balance, 2);
                                $newOrder->order_money_balance_used_date = date('Y-m-d H:i:s');
                                $newOrder->old_order_id                  = new \MongoDB\BSON\ObjectID($order->_id);
                                $newOrder->order_delete                  = 0;

                                /* If guest paid using payByBill we need to store bank details. Condition begin */
                                if($request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                                    $newOrder->clearing_bankaccount       = $paymentGateway["clearing_bankaccount"];
                                    $newOrder->clearing_bankcode          = $paymentGateway["clearing_bankcode"];
                                    $newOrder->clearing_bankcountry       = $paymentGateway["clearing_bankcountry"];
                                    $newOrder->clearing_bankname          = $paymentGateway["clearing_bankname"];
                                    $newOrder->clearing_bankaccountholder = $paymentGateway["clearing_bankaccountholder"];
                                    $newOrder->clearing_bankiban          = $paymentGateway["clearing_bankiban"];
                                    $newOrder->clearing_bankbic           = $paymentGateway["clearing_bankbic"];
                                }
                                /* If guest paid using payByBill we need to store bank details. Condition end */

                                $newOrder->save();

                                /* Storing new order details end */

                                if($newOrder) {
                                    /* Create new booking begin */
                                    $newBooking                          = Booking::firstOrCreate(['old_booking_id' => new \MongoDB\BSON\ObjectID($bookingOld->_id)]);
                                    $newBooking->cabinname               = $cabin->name;
                                    $newBooking->cabin_id                = new \MongoDB\BSON\ObjectID($cabin->_id);
                                    $newBooking->checkin_from            = $this->getDateUtc(session()->get('dateFromRequest'));
                                    $newBooking->reserve_to              = $this->getDateUtc(session()->get('dateToRequest'));
                                    $newBooking->user                    = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                                    $newBooking->beds                    = (int)session()->get('bedRequest');
                                    $newBooking->dormitory               = (int)session()->get('dormRequest');
                                    $newBooking->invoice_number          = $invoice_explode[0].'-'.$invoice_explode[1].'-'.$invoice_explode[2].'-'.$autoNumberTree;
                                    $newBooking->sleeps                  = (int)session()->get('sleepRequest');
                                    $newBooking->guests                  = (int)session()->get('sleepRequest'); // Sleeps and guest are same count
                                    $newBooking->halfboard               = session()->get('halfBoardRequest');
                                    $newBooking->comments                = session()->get('commentsRequest');
                                    $newBooking->prepayment_amount       = round($total_prepayment_amount + $bookingOld->prepayment_amount, 2);
                                    $newBooking->total_prepayment_amount = round($total_prepayment_amount + $bookingOld->total_prepayment_amount, 2);
                                    $newBooking->new_amount              = $total_prepayment_amount;
                                    $newBooking->moneybalance_used       = round($user->money_balance, 2);
                                    $newBooking->bookingdate             = date('Y-m-d H:i:s');
                                    $newBooking->status                  = '10'; // 10=> Temporary (This status is using in edit booking section)
                                    $newBooking->reservation_cancel      = $cabin->reservation_cancel;
                                    $newBooking->payment_type            = $request->payment;
                                    $newBooking->txid                    = $paymentGateway["txid"];
                                    $newBooking->userid                  = $paymentGateway["userid"];
                                    $newBooking->old_booking_id          = new \MongoDB\BSON\ObjectID($bookingOld->_id);
                                    $newBooking->order_id                = new \MongoDB\BSON\ObjectID($newOrder->_id);
                                    $newBooking->is_delete               = 0;
                                    $newBooking->save();
                                    /* Create new booking end */

                                    /* Storing new userid, old userid and updating invoice auto number tree in user collection */
                                    $user->userid               = $paymentGateway["userid"];
                                    $user->invoice_autonum_tree = $autoNumberTree;
                                    $user->save();

                                    /* Updating order number in ordernumber collection */
                                    $orderNumber->number        = $order_num;
                                    $orderNumber->save();

                                    /* If guest paid using payByBill it will redirect to bank details listing page. Condition begin*/
                                    if($request->payment === 'payByBill') {
                                        if($payByBillPossible === 'yes') {
                                            $request->session()->flash('updateBookingPayment', $request->updateBookingPayment);
                                            $request->session()->flash('newBooking', $newBooking->_id);
                                            $request->session()->flash('editTxId', $paymentGateway["txid"]);
                                            $request->session()->flash('editUserId', $paymentGateway["userid"]);
                                            $request->session()->flash('editPayByBillPossible', $payByBillPossible);
                                            $request->session()->flash('editBookingSuccessStatusPrepayment', __('payment.bookingSuccessStatus'));

                                            return redirect()->route('booking.history.payment.prepayment')->with('editBookOrder', $newOrder);
                                        }
                                        else {
                                            return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                        }
                                    }
                                    /* If guest paid using payByBill it will redirect to bank details listing page. Condition end*/

                                    $request->session()->flash('editTxId', $paymentGateway["txid"]);
                                    $request->session()->flash('editUserId', $paymentGateway["userid"]);
                                    $request->session()->flash('updateBookingPayment', $request->updateBookingPayment);
                                    $request->session()->flash('newBooking', $newBooking->_id);

                                    return redirect()->route('booking.history.payment.success')->with('editBookingSuccessStatus', __('payment.bookingSuccessStatus'));
                                }
                                else {
                                    return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                }
                            }
                            else {
                                return redirect()->route('payment.error')->with('bookingErrorStatus', __('payment.bookingErrorStatus'));
                            }
                        }
                        else {
                            $validator = Validator::make($request->all(), [
                                'payment' => 'required'
                            ]);

                            if ($validator->fails()) {
                                return redirect()->back()->withErrors($validator)->withInput();
                            }
                        }
                    }
                }
                else {
                    if(isset($request->payment)) {
                        $percentage     = ((new PaymentController)->serviceFees($total_prepayment_amount, $request->payment) / 100) * $total_prepayment_amount;
                        $total          = round($total_prepayment_amount + $percentage, 2);

                        // Function call for payment gateway section
                        $paymentGateway = (new PaymentController)->paymentGateway($request->all(), $request->ip(), $total, $order_number);
                        if ($paymentGateway["status"] === "REDIRECT") { // If card is 3d secure return status is REDIRECT and "redirect url" will return.

                            /* Storing new order details */
                            $newOrder                                = Order::firstOrCreate(['old_order_id' => new \MongoDB\BSON\ObjectID($order->_id)]);
                            $newOrder->order_id                      = $order_number;
                            $newOrder->auth_user                     = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                            $newOrder->order_status                  = "REDIRECT";
                            $newOrder->txid                          = $paymentGateway["txid"];
                            $newOrder->userid                        = $paymentGateway["userid"];
                            $newOrder->order_payment_type            = $request->payment;
                            $newOrder->order_payment_method          = 3; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $newOrder->order_amount                  = $total_prepayment_amount;
                            $newOrder->order_total_amount            = $total;
                            $newOrder->old_order_id                  = new \MongoDB\BSON\ObjectID($order->_id);
                            $newOrder->order_delete                  = 0;
                            $newOrder->save();
                            /* Storing new order details end */

                            if($newOrder) {
                                /* Create new booking begin */
                                $newBooking                          = Booking::firstOrCreate(['old_booking_id' => new \MongoDB\BSON\ObjectID($bookingOld->_id)]);
                                $newBooking->cabinname               = $cabin->name;
                                $newBooking->cabin_id                = new \MongoDB\BSON\ObjectID($cabin->_id);
                                $newBooking->checkin_from            = $this->getDateUtc(session()->get('dateFromRequest'));
                                $newBooking->reserve_to              = $this->getDateUtc(session()->get('dateToRequest'));
                                $newBooking->user                    = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                                $newBooking->beds                    = (int)session()->get('bedRequest');
                                $newBooking->dormitory               = (int)session()->get('dormRequest');
                                $newBooking->invoice_number          = $invoice_explode[0].'-'.$invoice_explode[1].'-'.$invoice_explode[2].'-'.$autoNumberTree;
                                $newBooking->sleeps                  = (int)session()->get('sleepRequest');
                                $newBooking->guests                  = (int)session()->get('sleepRequest'); // Sleeps and guest are same count
                                $newBooking->halfboard               = session()->get('halfBoardRequest');
                                $newBooking->comments                = session()->get('commentsRequest');
                                $newBooking->prepayment_amount       = round($total_prepayment_amount + $bookingOld->prepayment_amount, 2);
                                $newBooking->total_prepayment_amount = round($total_prepayment_amount + $bookingOld->total_prepayment_amount, 2);
                                $newBooking->new_amount              = $total_prepayment_amount;
                                $newBooking->moneybalance_used       = 0;
                                $newBooking->bookingdate             = date('Y-m-d H:i:s');
                                $newBooking->status                  = '10'; // 10=> Temporary (This status is using in edit booking section)
                                $newBooking->reservation_cancel      = $cabin->reservation_cancel;
                                $newBooking->payment_type            = $request->payment;
                                $newBooking->txid                    = $paymentGateway["txid"];
                                $newBooking->userid                  = $paymentGateway["userid"];
                                $newBooking->old_booking_id          = new \MongoDB\BSON\ObjectID($bookingOld->_id);
                                $newBooking->order_id                = new \MongoDB\BSON\ObjectID($newOrder->_id);
                                $newBooking->is_delete               = 0;
                                $newBooking->save();
                                /* Create new booking end */

                                /* Storing new userid, old userid and updating invoice auto number tree in user collection */
                                $user->userid               = $paymentGateway["userid"];
                                $user->invoice_autonum_tree = $autoNumberTree;
                                $user->save();

                                /* Updating order number in ordernumber collection */
                                $orderNumber->number        = $order_num;
                                $orderNumber->save();

                                $request->session()->flash('updateBookingPayment', $request->updateBookingPayment);
                                $request->session()->flash('newBooking', $newBooking->_id);
                                $request->session()->flash('editTxId', $paymentGateway["txid"]);
                                $request->session()->flash('editUserId', $paymentGateway["userid"]);
                                $request->session()->flash('editBookingSuccessStatus', __('payment.bookingSuccessStatus'));

                                return redirect()->away($paymentGateway["redirecturl"]);
                            }
                            else {
                                return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                            }
                        }
                        elseif ($paymentGateway["status"] === "APPROVED") { // If card is not 3d secure and prepayment(PayByBill) return status is APPROVED and "redirect url" will not return. We manually redirect to success page.

                            /* Storing new order details */
                            $newOrder                                = Order::firstOrCreate(['old_order_id' => new \MongoDB\BSON\ObjectID($order->_id)]);
                            $newOrder->order_id                      = $order_number;
                            $newOrder->auth_user                     = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                            $newOrder->order_status                  = "APPROVED";
                            $newOrder->txid                          = $paymentGateway["txid"];
                            $newOrder->userid                        = $paymentGateway["userid"];
                            $newOrder->order_payment_type            = $request->payment;
                            $newOrder->order_payment_method          = 3; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $newOrder->order_amount                  = $total_prepayment_amount;
                            $newOrder->order_total_amount            = $total;
                            $newOrder->old_order_id                  = new \MongoDB\BSON\ObjectID($order->_id);
                            $newOrder->order_delete                  = 0;

                            /* If guest paid using payByBill we need to store bank details. Condition begin */
                            if($request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                                $newOrder->clearing_bankaccount       = $paymentGateway["clearing_bankaccount"];
                                $newOrder->clearing_bankcode          = $paymentGateway["clearing_bankcode"];
                                $newOrder->clearing_bankcountry       = $paymentGateway["clearing_bankcountry"];
                                $newOrder->clearing_bankname          = $paymentGateway["clearing_bankname"];
                                $newOrder->clearing_bankaccountholder = $paymentGateway["clearing_bankaccountholder"];
                                $newOrder->clearing_bankiban          = $paymentGateway["clearing_bankiban"];
                                $newOrder->clearing_bankbic           = $paymentGateway["clearing_bankbic"];
                            }
                            /* If guest paid using payByBill we need to store bank details. Condition end */

                            $newOrder->save();

                            /* Storing new order details end */

                            if($newOrder) {
                                /* Create new booking begin */
                                $newBooking                          = Booking::firstOrCreate(['old_booking_id' => new \MongoDB\BSON\ObjectID($bookingOld->_id)]);
                                $newBooking->cabinname               = $cabin->name;
                                $newBooking->cabin_id                = new \MongoDB\BSON\ObjectID($cabin->_id);
                                $newBooking->checkin_from            = $this->getDateUtc(session()->get('dateFromRequest'));
                                $newBooking->reserve_to              = $this->getDateUtc(session()->get('dateToRequest'));
                                $newBooking->user                    = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                                $newBooking->beds                    = (int)session()->get('bedRequest');
                                $newBooking->dormitory               = (int)session()->get('dormRequest');
                                $newBooking->invoice_number          = $invoice_explode[0].'-'.$invoice_explode[1].'-'.$invoice_explode[2].'-'.$autoNumberTree;
                                $newBooking->sleeps                  = (int)session()->get('sleepRequest');
                                $newBooking->guests                  = (int)session()->get('sleepRequest'); // Sleeps and guest are same count
                                $newBooking->halfboard               = session()->get('halfBoardRequest');
                                $newBooking->comments                = session()->get('commentsRequest');
                                $newBooking->prepayment_amount       = round($total_prepayment_amount + $bookingOld->prepayment_amount, 2);;
                                $newBooking->total_prepayment_amount = round($total_prepayment_amount + $bookingOld->total_prepayment_amount, 2);;
                                $newBooking->new_amount              = $total_prepayment_amount;
                                $newBooking->moneybalance_used       = 0;
                                $newBooking->bookingdate             = date('Y-m-d H:i:s');
                                $newBooking->status                  = '10'; // 10=> Temporary (This status is using in edit booking section)
                                $newBooking->reservation_cancel      = $cabin->reservation_cancel;
                                $newBooking->payment_type            = $request->payment;
                                $newBooking->txid                    = $paymentGateway["txid"];
                                $newBooking->userid                  = $paymentGateway["userid"];
                                $newBooking->old_booking_id          = new \MongoDB\BSON\ObjectID($bookingOld->_id);
                                $newBooking->order_id                = new \MongoDB\BSON\ObjectID($newOrder->_id);
                                $newBooking->is_delete               = 0;
                                $newBooking->save();
                                /* Create new booking end */

                                /* Storing new userid, old userid and updating invoice auto number tree in user collection */
                                $user->userid               = $paymentGateway["userid"];
                                $user->invoice_autonum_tree = $autoNumberTree;
                                $user->save();

                                /* Updating order number in ordernumber collection */
                                $orderNumber->number        = $order_num;
                                $orderNumber->save();

                                /* If guest paid using payByBill it will redirect to bank details listing page. Condition begin*/
                                if($request->payment === 'payByBill') {
                                    if($payByBillPossible === 'yes') {
                                        $request->session()->flash('updateBookingPayment', $request->updateBookingPayment);
                                        $request->session()->flash('newBooking', $newBooking->_id);
                                        $request->session()->flash('editTxId', $paymentGateway["txid"]);
                                        $request->session()->flash('editUserId', $paymentGateway["userid"]);
                                        $request->session()->flash('editPayByBillPossible', $payByBillPossible);
                                        $request->session()->flash('editBookingSuccessStatusPrepayment', __('payment.bookingSuccessStatus'));

                                        return redirect()->route('booking.history.payment.prepayment')->with('editBookOrder', $newOrder);
                                    }
                                    else {
                                        return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                    }
                                }
                                /* If guest paid using payByBill it will redirect to bank details listing page. Condition end*/

                                $request->session()->flash('editTxId', $paymentGateway["txid"]);
                                $request->session()->flash('editUserId', $paymentGateway["userid"]);
                                $request->session()->flash('updateBookingPayment', $request->updateBookingPayment);
                                $request->session()->flash('newBooking', $newBooking->_id);

                                return redirect()->route('booking.history.payment.success')->with('editBookingSuccessStatus', __('payment.bookingSuccessStatus'));
                            }
                            else {
                                return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                            }
                        }
                        else {
                            return redirect()->route('payment.error')->with('bookingErrorStatus', __('payment.bookingErrorStatus'));
                        }
                    }
                    else {
                        $validator = Validator::make($request->all(), [
                            'payment' => 'required'
                        ]);

                        if ($validator->fails()) {
                            return redirect()->back()->withErrors($validator)->withInput();
                        }
                    }
                }
            }
            else {
                abort(404);
            }
        }
        else {
            return redirect()->route('booking.history')->with('updateBookingFailedStatus', __('bookingHistory.errorTwo'));
        }
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function success()
    {
        if(session()->has('editBookingSuccessStatus')) {
            if(session()->has('editTxId') && session()->has('editUserId')) {

                if( session()->has('updateBookingPayment') && session()->get('updateBookingPayment') === 'updateBookingPayment' && session()->has('availableStatus') && session()->get('availableStatus') === 'success' ) {

                    $bookingOld = Booking::where('status', '1')
                        ->where('payment_status', '1')
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find(session()->get('bookingIdRequest'));

                    if(!empty($bookingOld)) {
                        /* Update status of old booking */
                        $bookingOld->booking_update = date('Y-m-d H:i:s');
                        $bookingOld->status         = "9"; //9 => Old (Booking Updated)
                        $bookingOld->is_delete      = 1;
                        $bookingOld->save();

                        /* Update status of old orders */
                        $order  = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->find($bookingOld->order_id);

                        if(!empty($order)) {
                            $order->order_update_date  = date('Y-m-d H:i:s');
                            $order->save();
                        }

                        /* Update new booking status and payment status */
                        $newBooking = Booking::where('status', '10')
                            ->where('userid', session()->get('userid'))
                            ->where('txid', session()->get('txid'))
                            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->find(session()->get('newBooking'));

                        $newBooking->status         = '1';
                        $newBooking->payment_status = '1';
                        $newBooking->save();

                        /* Update money balance */
                        $newOrder = Order::where('userid', session()->get('userid'))
                            ->where('txid', session()->get('txid'))
                            ->where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->first();

                        if(!empty($newOrder) && $newOrder->order_payment_method === 2) { // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $user = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                            $user->money_balance = 0.00;
                            $user->save();
                        }
                    }
                }
                else {
                    abort(404);
                }
            }

            /* Delete sessions */
            session()->forget('bookingIdRequest');
            session()->forget('dateFromRequest');
            session()->forget('dateToRequest');
            session()->forget('bedRequest');
            session()->forget('dormRequest');
            session()->forget('sleepRequest');
            session()->forget('halfBoardRequest');
            session()->forget('commentsRequest');
            session()->forget('sleepingPlaceRequest');
            session()->forget('prepaymentAmountRequest');
            session()->forget('availableStatus');

            return view('paymentSuccess');
        }
        else {
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function prepayment()
    {
        if(session()->has('editBookingSuccessStatusPrepayment') && session()->has('editBookOrder')) {
            if(session()->has('txid') && session()->has('userid') && session()->has('editPayByBillPossible') && session()->get('editPayByBillPossible') === 'yes') {

                if( session()->has('updateBookingPayment') && session()->get('updateBookingPayment') === 'updateBookingPayment' && session()->has('availableStatus') && session()->get('availableStatus') === 'success' ) {

                    $bookingOld = Booking::where('status', '1')
                        ->where('payment_status', '1')
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find(session()->get('bookingIdRequest'));

                    if(!empty($bookingOld)) {

                        /* Update status of old booking */
                        $bookingOld->booking_update = date('Y-m-d H:i:s');
                        $bookingOld->status         = "9"; //9 => Old (Booking Updated)
                        $bookingOld->is_delete      = 1;
                        $bookingOld->save();

                        /* Update status of old orders */
                        $order  = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->find($bookingOld->order_id);

                        if(!empty($order)) {
                            $order->order_update_date  = date('Y-m-d H:i:s');
                            $order->save();
                        }

                        /* Update new booking status and payment status */
                        $newBooking = Booking::where('status', '10')
                            ->where('userid', session()->get('userid'))
                            ->where('txid', session()->get('txid'))
                            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->find(session()->get('newBooking'));

                        $newBooking->status         = '5';
                        $newBooking->payment_status = '3';
                        $newBooking->save();

                        /* Update money balance */
                        $newOrder = Order::where('userid', session()->get('userid'))
                            ->where('txid', session()->get('txid'))
                            ->where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->first();

                        if(!empty($newOrder) && $newOrder->order_payment_method === 2) { // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $user = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                            $user->money_balance = 0.00;
                            $user->save();
                        }
                    }
                }
                else {
                    abort(404);
                }
            }

            /* Delete sessions */
            session()->forget('bookingIdRequest');
            session()->forget('dateFromRequest');
            session()->forget('dateToRequest');
            session()->forget('bedRequest');
            session()->forget('dormRequest');
            session()->forget('sleepRequest');
            session()->forget('halfBoardRequest');
            session()->forget('commentsRequest');
            session()->forget('sleepingPlaceRequest');
            session()->forget('prepaymentAmountRequest');
            session()->forget('availableStatus');

            return view('paymentPrepayment');
        }
        else {
            abort(404);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Cancelled booking delete
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyCancelledBooking(Request $request)
    {
        $cart      = Booking::where('status', '2')
            ->where('is_delete', 0)
            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->find($request->delId);

        if(!empty($cart)){
            Booking::destroy($cart->_id);
            return response()->json(['status' => 'success'] ,201);
        }
        else {
            return response()->json(['status' => 'failure'] ,500);
        }
    }


    /**
     * Remove the specified resource from storage.
     * Waiting for payment (prepayment) delete
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyWaitingPrepayBooking(Request $request)
    {
        $cart      = Booking::where('status', '5')
            ->where('payment_status', '3')
            ->where('is_delete', 0)
            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->find($request->delId);

        if(!empty($cart)){
            Booking::destroy($cart->_id);
            return response()->json(['status' => 'success'] ,201);
        }
        else {
            return response()->json(['status' => 'failure'] ,500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Approved inquiry delete
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyApprovedInquiry(Request $request)
    {
        $cart      = Booking::where('status', '5')
            ->where('inquirystatus', 1)
            ->where('typeofbooking', 1)
            ->where('is_delete', 0)
            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->find($request->delId);

        if(!empty($cart)){
            Booking::destroy($cart->_id);
            return response()->json(['status' => 'success'] ,201);
        }
        else {
            return response()->json(['status' => 'failure'] ,500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Waiting inquiry delete
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyWaitingInquiry(Request $request)
    {
        $cart      = Booking::where('status', '7')
            ->where('inquirystatus', 0)
            ->where('typeofbooking', 1)
            ->where('is_delete', 0)
            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->find($request->delId);

        if(!empty($cart)){
            Booking::destroy($cart->_id);
            return response()->json(['status' => 'success'] ,201);
        }
        else {
            return response()->json(['status' => 'failure'] ,500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Rejected inquiry delete
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyRejectedInquiry(Request $request)
    {
        $cart      = Booking::where('status', '7')
            ->where('inquirystatus', 2)
            ->where('typeofbooking', 1)
            ->where('is_delete', 0)
            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->find($request->delId);

        if(!empty($cart)){
            Booking::destroy($cart->_id);
            return response()->json(['status' => 'success'] ,201);
        }
        else {
            return response()->json(['status' => 'failure'] ,500);
        }
    }

    /**
     * Download the booking voucher.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadVoucher(Request $request)
    {
        $cart      = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->whereIn('status', ['1', '3', '4']) /* 1=> Fix, 3=> Completed, 4=> Request (Reservation) */
            ->where('is_delete', 0)
            ->find($request->book_id);

        if(!empty($cart)) {
            /* Generating PDF */
            $cabin = Cabin::select('sleeping_place')
                ->where('name', $cart->cabinname)
                ->where('is_delete', 0)
                ->first();

            /* Date difference */
            $checkin_from = $cart->checkin_from->format('Y-m-d');
            $reserve_to   = $cart->reserve_to->format('Y-m-d');
            $d1           = new DateTime($checkin_from);
            $d2           = new DateTime($reserve_to);
            $dateDiff     = $d2->diff($d1);

            $html  = view('bookingHistoryPDF', ['cart' => $cart, 'sleeping_place' => $cabin, 'dateDifference' => $dateDiff->days]);

            $pdf   = PDF::loadHTML($html);

            return $pdf->download($cart->invoice_number. ".pdf");
        }
        else{
            abort(404);
        }
    }

    /**
     * Download the booking voucher.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cancelBooking(Request $request)
    {
        $cart      = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', '1')
            ->where('is_delete', 0)
            ->find($request->cancelId);

        if(!empty($cart)) {
            $begin              = date('Y-m-d');
            $end                = $cart->checkin_from->format('Y-m-d');
            $d1                 = new DateTime($begin);
            $d2                 = new DateTime($end);
            $dateDifference     = $d2->diff($d1);
            $reservation_cancel = (int)$cart->reservation_cancel;

            if(($begin < $end)) {

                if($reservation_cancel <= $dateDifference->days) {
                    /* Cancelled booking and refund money */
                    $cart->refund_eligible = 1;
                    $cart->cancel_status   = 1;
                    $cart->money_refunded  = (float)$cart->prepayment_amount;

                    $user = Userlist::where('is_delete', 0)
                        ->where('usrActive', '1')
                        ->find(Auth::user()->_id);

                    $total_money_balance   = Auth::user()->money_balance + $cart->money_refunded;

                    $user->money_balance   = round($total_money_balance, 2);
                    $user->save();
                }
                else {
                    /* Cancelled booking and not return money */
                    $cart->refund_eligible = 0;
                    $cart->cancel_status   = 0;
                    $cart->money_refunded  = 0;
                }

                $cart->status      = "2";
                $cart->cancel_date = date('Y-m-d H:i:s');
                $cart->save();

                return response()->json(['status' => 'success'], 201);
            }
            else{
                return response()->json(['status' => 'failure'], 500);
            }
        }
        else{
            return response()->json(['status' => 'failure'], 500);
        }
    }

}
