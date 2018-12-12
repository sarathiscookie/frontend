<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PaymentRequest;
use App\Cabin;
use App\Country;
use App\Booking;
use App\Userlist;
use App\Order;
use App\Ordernumber;
use App\PrivateMessage;
use App\Http\Requests\InquiryRequest;
use App\Http\Requests\ChatMessageRequest;
use Carbon\Carbon;
use DateTime;
use DatePeriod;
use DateInterval;
use Auth;
use Validator;
use Mail;
use App\Mail\SendVoucher;
use App\Http\Controllers\PaymentController;

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
            $cabinDetails = Cabin::select('_id', 'name', 'region', 'prepayment_amount', 'sleeping_place', 'halfboard', 'halfboard_price')
                ->where('is_delete', 0)
                ->where('other_cabin', "0")
                ->findOrFail(session()->get('cabin_id'));

            $country = Country::select('name')->get();

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
     * Return cabin details when injection occur.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function message($id)
    {
        $readPrivateMessages = PrivateMessage::where('booking_id', new \MongoDB\BSON\ObjectID($id))
            ->latest()
            ->take(10)
            ->get();

        return $readPrivateMessages->reverse();
    }

    /**
     * Update the specified resource in message.
     *
     * @param  \App\Http\Requests\ChatMessageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(ChatMessageRequest $request)
    {
       //dd($request->message);
        /*$request->validate([
            "message"    => "required",
            "message.*"  => "required|string|max:350",
        ]);*/

        $bookingData            = Booking::select('cabinname', 'invoice_number')
            ->where('status', '7')
            ->where('inquirystatus', 0)
            ->where('typeofbooking', 1)
            ->where('is_delete', 0)
            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->find($request->chatBookId);

        if(!empty($bookingData)) {
            $cabin = Cabin::select('cabin_owner')
                ->where('is_delete', 0)
                ->where('other_cabin', "0")
                ->where('name', $bookingData->cabinname)
                ->first();

            if(!empty($cabin)) {

                $user = Userlist::select('_id')
                    ->where('usrActive', '1')
                    ->where('is_delete', 0)
                    ->find(new \MongoDB\BSON\ObjectID($cabin->cabin_owner));

                if(!empty($user)) {
                    $privateMessage              = new PrivateMessage;
                    $privateMessage->sender_id   = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                    $privateMessage->receiver_id = new \MongoDB\BSON\ObjectID($user->_id);
                    $privateMessage->booking_id  = new \MongoDB\BSON\ObjectID($request->chatBookId);
                    $privateMessage->subject     = $bookingData->invoice_number;
                    $privateMessage->text        = $request->message;
                    $privateMessage->read        = 0;
                    $privateMessage->save();

                    return response()->json(['status' => 'success'], 201);
                }
                else {
                    return response()->json(['error' => __('inquiry.msgSendFailed')], 422);
                }
            }
            else {
                return response()->json(['error' => __('inquiry.msgSendFailed')], 422);
            }
        }
        else {
            return response()->json(['error' => __('inquiry.msgSendFailed')], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function showListImageInquiry($id)
    {
        $list_image_name = '';
        if(!empty($id)) {
            $directories = Storage::disk('public')->directories('huetten');
            foreach ($directories as $directory) {
                $files = Storage::disk('public')->files($directory);
                foreach ($files as $file) {
                    $explode_directory = explode('/', $file);
                    if($explode_directory[1] === $id && $explode_directory[2] === 'list.jpg') {
                        $list_image_name = $file;
                    }
                }
            }
        }
        return $list_image_name;
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
                                    return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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

                                if($sleepsRequest >= $cabin->not_regular_inquiry_guest) {
                                    $availableStatus[] = 'possible';
                                }
                                else {
                                    $availableStatus[] = 'notPossible';
                                    return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
                                }
                            }
                        }

                        /*  Checking sleeps is greater or equal to regular inquiry */
                        if($cabin->regular === 1) {

                            if($mon_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if($sleepsRequest >= $cabin->mon_inquiry_guest) {
                                        $availableStatus[] = 'possible';
                                    }
                                    else {
                                        $availableStatus[] = 'notPossible';
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
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
                                        return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
                                    }
                                }
                            }
                        }

                        /* Checking requested sleeps is greater or equal to normal inquiry */
                        if(!in_array($dates, $dates_array)) {

                            if($sleepsRequest >= $cabin->inquiry_starts) {
                                $availableStatus[] = 'possible';
                            }
                            else {
                                $availableStatus[] = 'notPossible';
                                return redirect()->back()->with('error', __("inquiry.alertChooseGreater"));
                            }

                        }

                    }

                    /* Checking inquiry possible ends */
                }

                if(!in_array('notPossible', $availableStatus)) {
                    $available = 'success';

                    /* Calculation prepayment and total prepayment amount begin */
                    $d1                       = new DateTime($monthBegin);
                    $d2                       = new DateTime($monthEnd);
                    $dateDifference           = $d2->diff($d1);
                    $guestSleepsTypeCondition = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                    $amount                   = ($cabin->prepayment_amount * $dateDifference->days) * $guestSleepsTypeCondition;
                    $amountAfterDeductDays    = round($cabin->prepayment_amount * $guestSleepsTypeCondition, 2);

                    if($amountAfterDeductDays <= 30) {
                        $serviceTax = env('SERVICE_TAX_ONE');
                    }

                    if($amountAfterDeductDays > 30 && $amountAfterDeductDays <= 100) {
                        $serviceTax = env('SERVICE_TAX_TWO');
                    }

                    if($amountAfterDeductDays > 100) {
                        $serviceTax = env('SERVICE_TAX_THREE');
                    }

                    $sumPrepaymentAmountPercentage   = ($serviceTax / 100) * $amountAfterDeductDays;
                    $sumPrepaymentAmountServiceTotal = $amount + $sumPrepaymentAmountPercentage;
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
                    $inquiry->reservation_cancel      = $cabin->reservation_cancel;
                    $inquiry->inquirystatus           = 0; //0 = waiting, 1 = Approved, 2 = Rejected
                    $inquiry->prepayment_amount       = round($amount, 2);
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
                return redirect()->back()->with('error', __('searchDetails.dateGreater'));
            }

            if($available === 'success') {
                return redirect()->route('booking.history')->with('response', $available);
            }
            else {
                return redirect()->back()->with('error', __('inquiry.errorTwo'));
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
        if(!empty($id)) {
            $bookingData            = Booking::where('status', '5')
                ->where('inquirystatus', 1)
                ->where('typeofbooking', 1)
                ->where('is_delete', 0)
                ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->find($id);

            if(!empty($bookingData)) {
                $checkingFrom          = $bookingData->checkin_from->format('Y-m-d');

                /* Deduct days from booked amount begin */
                $checkingTo            = $bookingData->reserve_to->format('Y-m-d');
                $dateOne               = new DateTime($checkingFrom);
                $dateTwo               = new DateTime($checkingTo);
                $dateDiff              = $dateTwo->diff($dateOne);
                $amountAfterDeductDays = round($bookingData->prepayment_amount / $dateDiff->days, 2);
                /* Deduct days from booked amount end */

                $sum_prepayment_amount = round($bookingData->prepayment_amount, 2);
                $serviceTax            = (new PaymentController)->serviceFees($amountAfterDeductDays, $paymentMethod = null);
                $percentage            = ($serviceTax / 100) * $amountAfterDeductDays;
                $prepay_service_total  = $sum_prepayment_amount + $percentage;

                /* Condition to check pay by bill possible begin */
                // Pay by bill radio button in payment page will show when there is two weeks diff b/w current date and checking from date.
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

                if(empty($bookingData->order_id)) {

                    if( !empty ($orderNumber->number) ) {
                        $order_num         = (int)$orderNumber->number + 1;
                    }
                    else {
                        $order_num         = 100000;
                    }
                    $order_number          = 'ORDER'.'-'.date('y').'-'.$order_num;

                    /* Creating new order: This is for inquiry system. In inquiry system we don't have orders table. */
                    $order                    = new Order;
                    $order->order_id          = $order_number;
                    $order->auth_user         = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                    $order->old_order_comment = 'Order created for inquiry';
                    $order->order_delete      = 1;
                    $order->save();

                    if(!empty($order)) {
                        /* Updating order number in booking collection */
                        $bookingData->order_id = new \MongoDB\BSON\ObjectID($order->_id);
                        $bookingData->save();

                        /* Updating order number in ordernumber collection */
                        $orderNumber->number   = $order_num;
                        $orderNumber->save();
                    }
                }

                if( !empty ($orderNumber->number) ) {
                    $order_sum = (int)$orderNumber->number + 1;
                }
                else {
                    $order_sum = 100000;
                }

                $order_number_format = 'ORDER'.'-'.date('y').'-'.$order_sum;

                return view('payment', ['moneyBalance' => Auth::user()->money_balance, 'sumPrepaymentAmount' => $sum_prepayment_amount, 'prepayServiceTotal' => $prepay_service_total, 'serviceTax' => $percentage, 'payByBillPossible' => $payByBillPossible, 'order_number' => $order_number_format, 'inquiryPayment' => 'inquiryPayment', 'inquiryPaymentId' => $bookingData->_id, 'amountAfterDeductDays' => $amountAfterDeductDays, 'deductedDays' => $dateDiff->days]);
            }
            else {
                return redirect()->back()->with('inquiryPaymentStatus', __('bookingHistory.errorTwo'));
            }
        }
        else {
            abort(404);
        }
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
     * @param  \App\Http\Requests\PaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(PaymentRequest $request)
    {
        if( isset($request->updateInquiryPayment, $request->inquiryPaymentId) && $request->updateInquiryPayment === 'updateInquiryPayment' ) {
            $bookingData            = Booking::where('status', '5')
                ->where('inquirystatus', 1)
                ->where('typeofbooking', 1)
                ->where('is_delete', 0)
                ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->find($request->inquiryPaymentId);

            if(!empty($bookingData)) {
                $user                  = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                $order                 = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))->find($bookingData->order_id);

                // Pay by bill condition works if there is two weeks diff b/w current date and checking from date.
                $checkingFrom          = $bookingData->checkin_from->format('Y-m-d');

                /* Deduct days from booked amount begin */
                $checkingTo            = $bookingData->reserve_to->format('Y-m-d');
                $dateOne               = new DateTime($checkingFrom);
                $dateTwo               = new DateTime($checkingTo);
                $dateDiff              = $dateTwo->diff($dateOne);
                $deductedDays          = $dateDiff->days;
                $amountAfterDeductDays = round($bookingData->prepayment_amount / $deductedDays, 2);
                /* Deduct days from booked amount end */

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

                $total_prepayment_amount = round($bookingData->prepayment_amount, 2);
                if($request->has('moneyBalance') && $request->moneyBalance === '1') {
                    if($user->money_balance >= $total_prepayment_amount) {
                        /* How much money user have in their account after used money balance */
                        $afterRedeemAmount = $user->money_balance - $total_prepayment_amount;

                        if(!empty($order)) {
                            /* Update status of orders begin */
                            $order->order_amount                  = $total_prepayment_amount;
                            $order->order_total_amount            = $total_prepayment_amount;
                            $order->order_money_balance_used      = $total_prepayment_amount;
                            $order->order_money_balance_used_date = date('Y-m-d H:i:s');
                            $order->order_payment_method          = 1; // 1 => Fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $order->order_delete                  = 0;
                            $order->save();
                            /* Update status of orders end */

                            /* Update status of booking begin */
                            $bookingData->status            = "1"; // 1=> Fix
                            $bookingData->payment_status    = '1';
                            $bookingData->moneybalance_used = $total_prepayment_amount;
                            $bookingData->save();
                            /* Update status of booking end */

                            /* Updating money balance and invoice_autonum tree */
                            $user->money_balance         = round($afterRedeemAmount, 2);
                            $user->save();

                            /* Send email with voucher */
                            Mail::to($user->usrEmail)->send(new SendVoucher($bookingData));

                            return redirect()->route('payment.success')->with('editBookingSuccessStatus', __('payment.bookingSuccessStatus'));
                        }
                        else {
                            return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                        }
                    }
                    else {
                        if(isset($request->payment)) {
                            /* How much money user have in their account after used money balance */
                            $afterRedeemAmount            = $total_prepayment_amount - $user->money_balance;
                            $afterRedeemAmountWithoutDays = round($afterRedeemAmount / $deductedDays, 2);
                            $percentage                   = ((new PaymentController)->serviceFees($afterRedeemAmountWithoutDays, $request->payment) / 100) * $afterRedeemAmountWithoutDays;
                            $total                        = round($afterRedeemAmount + $percentage, 2);

                            // Function call for payment gateway section
                            $paymentGateway               = (new PaymentController)->paymentGateway($request->all(), $request->ip(), $total, $order_number);
                            if ($paymentGateway["status"] === "REDIRECT") {
                                if(!empty($order)) {
                                    /* Update order details */
                                    $order->order_status                  = "REDIRECT";
                                    $order->txid                          = $paymentGateway["txid"];
                                    $order->userid                        = $paymentGateway["userid"];
                                    $order->order_payment_type            = $request->payment;
                                    $order->order_payment_method          = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                                    $order->order_amount                  = round($afterRedeemAmount, 2);
                                    $order->order_total_amount            = $total;
                                    $order->order_money_balance_used      = round($user->money_balance, 2);
                                    $order->order_money_balance_used_date = date('Y-m-d H:i:s');
                                    $order->save();

                                    /* Updating booking details */
                                    $bookingData->payment_type            = $request->payment;
                                    $bookingData->txid                    = $paymentGateway["txid"];
                                    $bookingData->userid                  = $paymentGateway["userid"];
                                    $bookingData->moneybalance_used       = round($user->money_balance, 2);
                                    $bookingData->save();

                                    /* Storing new userid in user collection */
                                    $user->userid                         = $paymentGateway["userid"];
                                    $user->save();

                                    $request->session()->flash('updateInquiryPayment', $request->updateInquiryPayment);
                                    $request->session()->flash('newBooking', $bookingData->_id);
                                    $request->session()->flash('inquiryTxId', $paymentGateway["txid"]);
                                    $request->session()->flash('inquiryUserId', $paymentGateway["userid"]);
                                    $request->session()->flash('inquiryBookingSuccessStatus', __('payment.bookingSuccessStatus'));

                                    return redirect()->away($paymentGateway["redirecturl"]);
                                }
                                else {
                                    return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                }
                            }
                            elseif ($paymentGateway["status"] === "APPROVED") {
                                if(!empty($order)) {
                                    /* Update order details */
                                    $order->order_status                  = "APPROVED";
                                    $order->txid                          = $paymentGateway["txid"];
                                    $order->userid                        = $paymentGateway["userid"];
                                    $order->order_payment_type            = $request->payment;
                                    $order->order_payment_method          = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                                    $order->order_amount                  = round($afterRedeemAmount, 2);
                                    $order->order_total_amount            = $total;
                                    $order->order_money_balance_used      = round($user->money_balance, 2);
                                    $order->order_money_balance_used_date = date('Y-m-d H:i:s');

                                    /* If guest paid using payByBill we need to store bank details. Condition begin */
                                    if($request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                                        $order->clearing_bankaccount       = '';
                                        $order->clearing_bankcode          = '';
                                        $order->clearing_bankcountry       = '';
                                        $order->clearing_bankname          = 'Sparkasse Allgäu';
                                        $order->clearing_bankaccountholder = 'Huetten-Holiday.de GmbH';
                                        $order->clearing_bankiban          = 'DE32733500000515492916';
                                        $order->clearing_bankbic           = 'BYLADEM1ALG';
                                    }
                                    /* If guest paid using payByBill we need to store bank details. Condition end */

                                    $order->save();

                                    /* Updating booking details */
                                    if($request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                                        $bookingData->status         = '5'; //Waiting for payment
                                        $bookingData->payment_status = '3'; //Prepayment
                                    }
                                    else {
                                        $bookingData->status = '11'; // On processing
                                    }

                                    $bookingData->payment_type       = $request->payment;
                                    $bookingData->txid               = $paymentGateway["txid"];
                                    $bookingData->userid             = $paymentGateway["userid"];
                                    $bookingData->moneybalance_used  = round($user->money_balance, 2);
                                    $bookingData->save();

                                    /* Storing new userid and update money balance in user collection */
                                    // 1 => Fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                                    if($order->order_payment_method === 2 && $request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                                        $user->money_balance = 0.00;
                                    }

                                    $user->userid  = $paymentGateway["userid"];
                                    $user->save();

                                    /* If guest paid using payByBill it will redirect to bank details listing page. Condition begin*/
                                    if($request->payment === 'payByBill') {
                                        if($payByBillPossible === 'yes') {
                                            $request->session()->flash('updateInquiryPayment', $request->updateInquiryPayment);
                                            $request->session()->flash('newBooking', $bookingData->_id);
                                            $request->session()->flash('inquiryTxId', $paymentGateway["txid"]);
                                            $request->session()->flash('inquiryUserId', $paymentGateway["userid"]);
                                            $request->session()->flash('inquiryPayByBillPossible', $payByBillPossible);
                                            $request->session()->flash('inquiryBookingSuccessStatusPrepayment', __('payment.bookingSuccessStatus'));

                                            return redirect()->route('payment.prepayment')->with('inquiryBookOrder', $order);
                                        }
                                        else {
                                            return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                        }
                                    }
                                    /* If guest paid using payByBill it will redirect to bank details listing page. Condition end*/

                                    $request->session()->flash('updateInquiryPayment', $request->updateInquiryPayment);
                                    $request->session()->flash('newBooking', $bookingData->_id);
                                    $request->session()->flash('inquiryTxId', $paymentGateway["txid"]);
                                    $request->session()->flash('inquiryUserId', $paymentGateway["userid"]);

                                    return redirect()->route('payment.success')->with('inquiryBookingSuccessStatus', __('payment.bookingSuccessStatus'));
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
                        $percentage     = ((new PaymentController)->serviceFees($amountAfterDeductDays, $request->payment) / 100) * $amountAfterDeductDays;
                        $total          = round($total_prepayment_amount + $percentage, 2);

                        // Function call for payment gateway section
                        $paymentGateway = (new PaymentController)->paymentGateway($request->all(), $request->ip(), $total, $order_number);
                        if ($paymentGateway["status"] === "REDIRECT") {
                            if(!empty($order)) {
                                /* Update order details */
                                $order->order_status                  = "REDIRECT";
                                $order->txid                          = $paymentGateway["txid"];
                                $order->userid                        = $paymentGateway["userid"];
                                $order->order_payment_type            = $request->payment;
                                $order->order_payment_method          = 3; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                                $order->order_amount                  = $total_prepayment_amount;
                                $order->order_total_amount            = $total;
                                $order->save();

                                /* Updating booking details */
                                $bookingData->payment_type            = $request->payment;
                                $bookingData->txid                    = $paymentGateway["txid"];
                                $bookingData->userid                  = $paymentGateway["userid"];
                                $bookingData->moneybalance_used       = 0;
                                $bookingData->save();

                                /* Storing new userid in user collection */
                                $user->userid                         = $paymentGateway["userid"];
                                $user->save();

                                $request->session()->flash('updateInquiryPayment', $request->updateInquiryPayment);
                                $request->session()->flash('newBooking', $bookingData->_id);
                                $request->session()->flash('inquiryTxId', $paymentGateway["txid"]);
                                $request->session()->flash('inquiryUserId', $paymentGateway["userid"]);
                                $request->session()->flash('inquiryBookingSuccessStatus', __('payment.bookingSuccessStatus'));

                                return redirect()->away($paymentGateway["redirecturl"]);
                            }
                            else {
                                return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                            }
                        }
                        elseif ($paymentGateway["status"] === "APPROVED") {
                            if(!empty($order)) {
                                /* Update order details */
                                $order->order_status                  = "APPROVED";
                                $order->txid                          = $paymentGateway["txid"];
                                $order->userid                        = $paymentGateway["userid"];
                                $order->order_payment_type            = $request->payment;
                                $order->order_payment_method          = 3; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                                $order->order_amount                  = $total_prepayment_amount;
                                $order->order_total_amount            = $total;

                                /* If guest paid using payByBill we need to store bank details. Condition begin */
                                if($request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                                    $order->clearing_bankaccount       = '';
                                    $order->clearing_bankcode          = '';
                                    $order->clearing_bankcountry       = '';
                                    $order->clearing_bankname          = 'Sparkasse Allgäu';
                                    $order->clearing_bankaccountholder = 'Huetten-Holiday.de GmbH';
                                    $order->clearing_bankiban          = 'DE32733500000515492916';
                                    $order->clearing_bankbic           = 'BYLADEM1ALG';
                                }
                                /* If guest paid using payByBill we need to store bank details. Condition end */

                                $order->save();

                                /* Updating booking details */
                                if($request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                                    $bookingData->status         = '5'; //Waiting for payment
                                    $bookingData->payment_status = '3'; //Prepayment
                                }
                                else {
                                    $bookingData->status = '11'; // On processing
                                }

                                $bookingData->payment_type       = $request->payment;
                                $bookingData->txid               = $paymentGateway["txid"];
                                $bookingData->userid             = $paymentGateway["userid"];
                                $bookingData->moneybalance_used  = 0;
                                $bookingData->save();

                                /* Storing new userid in user collection */
                                $user->userid = $paymentGateway["userid"];
                                $user->save();

                                /* If guest paid using payByBill it will redirect to bank details listing page. Condition begin*/
                                if($request->payment === 'payByBill') {
                                    if($payByBillPossible === 'yes') {
                                        $request->session()->flash('updateInquiryPayment', $request->updateInquiryPayment);
                                        $request->session()->flash('newBooking', $bookingData->_id);
                                        $request->session()->flash('inquiryTxId', $paymentGateway["txid"]);
                                        $request->session()->flash('inquiryUserId', $paymentGateway["userid"]);
                                        $request->session()->flash('inquiryPayByBillPossible', $payByBillPossible);
                                        $request->session()->flash('inquiryBookingSuccessStatusPrepayment', __('payment.bookingSuccessStatus'));

                                        return redirect()->route('payment.prepayment')->with('inquiryBookOrder', $order);
                                    }
                                    else {
                                        return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                    }
                                }
                                /* If guest paid using payByBill it will redirect to bank details listing page. Condition end*/

                                $request->session()->flash('updateInquiryPayment', $request->updateInquiryPayment);
                                $request->session()->flash('newBooking', $bookingData->_id);
                                $request->session()->flash('inquiryTxId', $paymentGateway["txid"]);
                                $request->session()->flash('inquiryUserId', $paymentGateway["userid"]);

                                return redirect()->route('payment.success')->with('inquiryBookingSuccessStatus', __('payment.bookingSuccessStatus'));
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
            abort(404);
        }
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
