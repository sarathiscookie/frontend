<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BookingHistoryRequest;
use App\Region;
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

        $order    = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))->where('order_delete', 0)->first();

        return view('bookingHistory', ['bookings' => $bookings, 'order' => $order]);
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $booking          = Booking::where('status', '1')
            ->where('is_delete', 0)
            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->find($id);

        if(!empty($booking)) {
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
            $sleepsRequest                = 0;
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
                    $booking              = Booking::select('cabinname', 'beds', 'dormitory', 'sleeps','prepayment_amount', 'checkin_from', 'reserve_to')
                        ->where('status', '1')
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find($id);

                    if(!empty($booking)) {

                        $cabin                    = Cabin::where('is_delete', 0)
                            ->where('other_cabin', "0")
                            ->where('name', $booking->cabinname)
                            ->first();

                        /* Form request begin */
                        $commentsRequest          = $request->comments;
                        if($request->has('halfboard'))
                        {
                            $halfBoard            = $request->halfboard;
                        }
                        else {
                            $halfBoard            = '0';
                        }

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
                        $old_amount               = $booking->prepayment_amount;
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


                        if( ($total === $old_amount) && ($old_checking_from === $monthBegin) && ($old_reserve_to === $monthEnd) && ($old_sleeps_sum === $new_sleeps_sum) ) {
                            return redirect()->back()->with('updateBookingFailedStatus', __('bookingHistory.errorThree'));
                        }
                        else {
                            /*Availability checking of beds dorms and sleeps begin*/
                            $seasons                   = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id))->get();

                            $generateBookingDates      = $this->generateDates($monthBegin, $monthEnd);

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

                                /* Getting bookings from booking collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
                                $bookings  = Booking::select('beds', 'dormitory', 'sleeps')
                                    ->where('is_delete', 0)
                                    ->where('cabinname', $cabin->name)
                                    ->whereIn('status', ['1', '4', '5', '8'])
                                    ->whereRaw(['checkin_from' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                                    ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                                    ->get();

                                /* Getting bookings from mschool collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
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
                                                    return redirect()->back()->with('updateBookingFailedStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                                }

                                                if($dormsRequest <= $not_regular_dorms_avail) {
                                                    $availableStatus[] = 'available';
                                                }
                                                else {
                                                    $availableStatus[] = 'notAvailable';
                                                    return redirect()->back()->with('updateBookingFailedStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                                }

                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('updateBookingFailedStatus', __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m"));
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
                                                return redirect()->back()->with('updateBookingFailedStatus', $bedsRequest.__("searchDetails.bedsNotAvailable").$generateBookingDate->format("d.m"));
                                            }

                                            if($dormsRequest <= $normal_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('updateBookingFailedStatus', $dormsRequest.__("searchDetails.dormsNotAvailable").$generateBookingDate->format("d.m"));
                                            }

                                            print_r(' ----normal_data---- '.' totalBeds '. $totalBeds .' totalDorms '. $totalDorms.' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms.' normal_dates: ' . $dates . ' normal_beds_diff: '. $normal_beds_diff. ' normal_beds_avail: '. $normal_beds_avail.' normal_dates: ' . $dates . ' normal_dorms_diff: '. $normal_dorms_diff. ' normal_dorms_avail: '. $normal_dorms_avail);
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return response()->json(['error' =>  __("searchDetails.alreadyFilledBedsDorms").$generateBookingDate->format("d.m")], 422);
                                        }
                                    }

                                }
                                else {
                                    $totalSleeps = ($sleeps + $msSleeps) - $bookingSleeps;
                                    dd($totalSleeps);
                                }
                                /* Checking bookings available end */
                            }
                            /*Availability checking of beds dorms and sleeps end*/

                            /*if ($new_amount <= $old_amount){
                                dd('Dont need to go payment page. Just update booking, update money balance, send email');
                                // After payment use redirection "return redirect()->route('booking.history')->with('updateBookingSuccessStatus', __('bookingHistory.updateBookingSuccessTwo'))";
                            }
                            else {
                                dd('----Redirect to payment gateway-----'.'New amount: '.$new_amount.' Old: '.$old_amount.' Total: '.$total. ' Amount ' .$amount); //Higher - Amount: 83.04 Old: 55.36 Total: 27.68. Lower - Amount: 27.68 Old: 55.36 Total: 27.68
                                // After payment use redirection "return redirect()->route('booking.history')->with('updateBookingSuccessStatus', __('bookingHistory.updateBookingSuccessTwo'))";
                            }*/
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
            ->whereIn('status', ['1', '3', '4'])
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
