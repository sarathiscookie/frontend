<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cabin;
use App\Country;
use App\Booking;
use App\Userlist;
use App\PrivateMessage;
use App\Http\Requests\InquiryRequest;
use Carbon\Carbon;
use DateTime;
use DatePeriod;
use DateInterval;
use Auth;
use Validator;

class InquiryController extends Controller
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
    public function getDateUtc($date)
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
        /* Condition to check session contains inquiry details. If session has inquiry then only guest can send inquiry */
        if(session()->has('cabin_id') && session()->has('cabin_name') && session()->has('checkin_from') && session()->has('reserve_to')) {
            $cabinDetails = Cabin::select('name', 'region', 'prepayment_amount', 'sleeping_place', 'halfboard', 'halfboard_price')
                ->where('is_delete', 0)
                ->where('other_cabin', "0")
                ->findOrFail(session()->get('cabin_id'));

            $country = Country::select('name')
                ->where('is_delete', 0)
                ->get();

            return view('inquiry', ['cabinDetails' => $cabinDetails, 'country' => $country]);
        }
        else{
            /* After sent inquiry details session data will delete and guest can't send same inquiry again. If guest tried to send same inquiry again page will redirect in to search. */
            return redirect()->route('search');
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
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\InquiryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InquiryRequest $request)
    {
        /* Condition to check session contains inquiry details. If session has inquiry then only guest can send inquiry */
        if($request->has('inquirySend') && $request->session()->has('cabin_id') && $request->session()->has('cabin_name') && $request->session()->has('checkin_from') && $request->session()->has('reserve_to')) {

            $monthBegin                  = DateTime::createFromFormat('d.m.y', $request->session()->get('checkin_from'))->format('Y-m-d');
            $monthEnd                    = DateTime::createFromFormat('d.m.y', $request->session()->get('reserve_to'))->format('Y-m-d');

            $bedsRequest                 = (int)$request->beds;
            $dormsRequest                = (int)$request->dormitory;
            $sleepsRequest               = (int)$request->sleeps;
            $requestBedsSumDorms         = $bedsRequest + $dormsRequest;
            $invoiceNumber               = '';
            $serviceTax                  = 0;

            $available                   = 'failure';

            if($monthBegin < $monthEnd) {
                $not_regular_dates       = [];
                $dates_array             = [];
                $availableStatus         = [];

                $cabin                   = Cabin::where('is_delete', 0)
                    ->where('other_cabin', '0')
                    ->findOrFail($request->session()->get('cabin_id'));

                $generateBookingDates    = $this->generateDates($monthBegin, $monthEnd);

                foreach ($generateBookingDates as $generateBookingDate) {

                    $dates               = $generateBookingDate->format('Y-m-d');
                    $day                 = $generateBookingDate->format('D');

                    /* Checking requested inquiry count is greater than or equal to cabin contingent inquiry count begins */
                    $mon_day             = ($cabin->mon_day === 1) ? 'Mon' : 0;
                    $tue_day             = ($cabin->tue_day === 1) ? 'Tue' : 0;
                    $wed_day             = ($cabin->wed_day === 1) ? 'Wed' : 0;
                    $thu_day             = ($cabin->thu_day === 1) ? 'Thu' : 0;
                    $fri_day             = ($cabin->fri_day === 1) ? 'Fri' : 0;
                    $sat_day             = ($cabin->sat_day === 1) ? 'Sat' : 0;
                    $sun_day             = ($cabin->sun_day === 1) ? 'Sun' : 0;

                    /* Taking beds, dorms and sleeps depends up on sleeping_place */
                    if($cabin->sleeping_place != 1) {

                        /*  Checking requested sum of beds and dorms is greater or equal to not regular inquiry */
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

                                if($requestBedsSumDorms >= $cabin->not_regular_inquiry_guest) {
                                    $availableStatus[] = 'possible';
                                }
                                else {
                                    $availableStatus[] = 'notPossible';
                                    return redirect()->back()->with('error', 'You can select greater than values from select box');
                                }
                            }
                        }

                        /*  Checking requested sum of beds and dorms is greater or equal to regular inquiry */
                        if($cabin->regular === 1) {

                            if($mon_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($requestBedsSumDorms >= $cabin->mon_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($tue_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($requestBedsSumDorms >= $cabin->tue_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($wed_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($requestBedsSumDorms >= $cabin->wed_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($thu_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($requestBedsSumDorms >= $cabin->thu_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($fri_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($requestBedsSumDorms >= $cabin->fri_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($sat_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($requestBedsSumDorms >= $cabin->sat_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($sun_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($requestBedsSumDorms >= $cabin->sun_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }
                        }

                        /*  Checking requested sum of beds and dorms is greater or equal to normal inquiry */
                        if(!in_array($dates, $dates_array)) {

                            if($requestBedsSumDorms >= $cabin->inquiry_starts) {
                                $availableStatus[] = 'possible';
                            }
                            else {
                                $availableStatus[] = 'notPossible';
                                return redirect()->back()->with('error', 'You can select greater than values from select box');
                            }
                        }

                    }
                    else {

                        /*  Checking requested sleeps is greater or equal to not regular inquiry */
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

                                if($sleepsRequest >= $cabin->not_regular_sleeps) {
                                    $availableStatus[] = 'possible';
                                }
                                else {
                                    $availableStatus[] = 'notPossible';
                                    return redirect()->back()->with('error', 'You can select greater than values from select box');
                                }
                            }
                        }

                        /* Calculating sleeps for regular */
                        if($cabin->regular === 1) {

                            if($mon_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($sleepsRequest >= $cabin->mon_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($tue_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($sleepsRequest >= $cabin->tue_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($wed_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($sleepsRequest >= $cabin->wed_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($thu_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($sleepsRequest >= $cabin->thu_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($fri_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($sleepsRequest >= $cabin->fri_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($sat_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($sleepsRequest >= $cabin->sat_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }

                            if($sun_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($sleepsRequest >= $cabin->sun_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', 'You can select greater than values from select box');
                                    }
                                }
                            }
                        }

                        /* Calculating sleeps for normal */
                        if(!in_array($dates, $dates_array)) {

                            if($sleepsRequest >= $cabin->inquiry_starts) {
                                $availableStatus[] = 'possible';
                            }
                            else {
                                $availableStatus[] = 'notPossible';
                                return redirect()->back()->with('error', 'You can select greater than values from select box');
                            }

                        }

                    }

                    /* Checking bookings available ends */
                }

                if(!in_array('notPossible', $availableStatus)) {
                    $available = 'success';

                    /* Calculation prepayment and total prepayment amount begin */
                    $d1                       = new DateTime($monthBegin);
                    $d2                       = new DateTime($monthEnd);
                    $dateDifference           = $d2->diff($d1);
                    $guestSleepsTypeCondition = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                    $amount                   = ($cabin->prepayment_amount * $dateDifference->days) * $guestSleepsTypeCondition;
                    $sumPrepaymentAmount      = $amount;

                    if($sumPrepaymentAmount <= 30) {
                        $serviceTax = env('SERVICE_TAX_ONE');
                    }

                    if($sumPrepaymentAmount > 30 && $sumPrepaymentAmount <= 100) {
                        $serviceTax = env('SERVICE_TAX_TWO');
                    }

                    if($sumPrepaymentAmount > 100) {
                        $serviceTax = env('SERVICE_TAX_THREE');
                    }

                    $sumPrepaymentAmountPercentage   = ($serviceTax / 100) * $sumPrepaymentAmount;
                    $sumPrepaymentAmountServiceTotal = $sumPrepaymentAmount + $sumPrepaymentAmountPercentage;
                    /* Calculation prepayment and total prepayment amount end */

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

                    /* Inquiry booking */
                    $inquiry                          = new Booking;
                    $inquiry->cabinname               = $request->session()->get('cabin_name');
                    $inquiry->cabin_id                = new \MongoDB\BSON\ObjectID($request->session()->get('cabin_id'));
                    $inquiry->user                    = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                    $inquiry->bookingdate             = Carbon::now();
                    $inquiry->checkin_from            = $this->getDateUtc($request->session()->get('checkin_from'));
                    $inquiry->reserve_to              = $this->getDateUtc($request->session()->get('reserve_to'));
                    $inquiry->beds                    = $bedsRequest;
                    $inquiry->dormitory               = $dormsRequest;
                    $inquiry->sleeps                  = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                    $inquiry->guests                  = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                    $inquiry->halfboard               = ($request->halfboard === '1') ? $request->halfboard : '0';
                    $inquiry->invoice_number          = $invoiceNumber;
                    $inquiry->typeofbooking           = 1;  // 1 = Inquiry
                    $inquiry->read                    = 0;
                    $inquiry->status                  = '7';
                    $inquiry->inquirystatus           = 0; //0 = waiting, 1 = Approved, 2 = Rejected
                    $inquiry->prepayment_amount       = round($sumPrepaymentAmount, 2);
                    $inquiry->total_prepayment_amount = round($sumPrepaymentAmountServiceTotal, 2);
                    $inquiry->payment_status          = "0";
                    $inquiry->is_delete               = 0;
                    $inquiry->save();

                    /*If inquiry saved then update cabin invoice auto generation number, delete session, update user details and store message*/
                    if(!empty($inquiry)) {
                        /* Update cabin invoice_autonum begin */
                        Cabin::where('is_delete', 0)
                            ->where('other_cabin', "0")
                            ->where('name', $request->session()->get('cabin_name'))
                            ->where('_id', new \MongoDB\BSON\ObjectID($request->session()->get('cabin_id')))
                            ->update(['invoice_autonum' => $autoNumber]);

                        /* Delete session details */
                        $request->session()->forget('cabin_id');
                        $request->session()->forget('cabin_name');
                        $request->session()->forget('sleeping_place');
                        $request->session()->forget('checkin_from');
                        $request->session()->forget('reserve_to');
                        $request->session()->forget('user');
                        $request->session()->forget('beds');
                        $request->session()->forget('dormitory');
                        $request->session()->forget('sleeps');
                        $request->session()->forget('guests');

                        /* Store user details begin */
                        $userDetails               = Userlist::where('usrActive', '1')->where('is_delete', 0)->find(Auth::user()->_id);
                        $userDetails->usrAddress   = $request->street;
                        $userDetails->usrCity      = $request->city;
                        $userDetails->usrCountry   = $request->country;
                        $userDetails->usrZip       = $request->zipcode;
                        $userDetails->usrMobile    = $request->mobile;
                        $userDetails->usrTelephone = $request->phone;
                        $userDetails->save();

                        /* Store message */
                        $privateMessage              = new PrivateMessage;
                        $privateMessage->sender_id   = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                        $privateMessage->receiver_id = new \MongoDB\BSON\ObjectID($cabin->cabin_owner);
                        $privateMessage->booking_id  = new \MongoDB\BSON\ObjectID($inquiry->_id);
                        $privateMessage->subject     = $invoiceNumber;
                        $privateMessage->text        = $request->comments;
                        $privateMessage->read        = 0;
                        $privateMessage->save();
                    }
                    else {
                        abort(404);
                    }

                }
            }
            else {
                return redirect()->back()->with('error', 'Arrival date should be less than departure date.');
            }

            if($available === 'success') {
                return redirect()->route('booking.history')->with('response', $available);
            }
            else {
                return redirect()->back()->with('error', 'Change a few things up and try submitting again');
            }

        }
        else{
            /* After sent inquiry details session data will delete and guest can't send same inquiry again. If guest tried to send same inquiry again page will redirect in to search. */
            return redirect()->route('search');
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
