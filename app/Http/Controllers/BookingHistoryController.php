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
            $dateDifference               = $d2->diff($d1);
            $sleepsRequest                = 0;
            $requestBedsSumDorms          = 0;
            $sleeps                       = 0;
            $bedsSumDorms                 = 0;
            if($monthBegin < $monthEnd) {
                if($dateDifference->days <= 60) {
                    $booking              = Booking::select('cabinname', 'beds', 'dormitory', 'sleeps','prepayment_amount', 'checkin_from', 'reserve_to')
                        ->where('status', '1')
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find($id);

                    if(!empty($booking)) {

                        $cabin                    = Cabin::select('prepayment_amount', 'sleeping_place')
                            ->where('is_delete', 0)
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
                            $sleepsRequest        = (int)$request->sleeps;
                            $sleeps               = (int)$booking->sleeps;
                        }
                        else {
                            $bedsRequest          = (int)$request->beds;
                            $beds                 = (int)$booking->beds;
                            $dormsRequest         = (int)$request->dormitory;
                            $dorms                = (int)$booking->dormitory;
                            $requestBedsSumDorms  = $bedsRequest + $dormsRequest;
                            $bedsSumDorms         = $beds + $dorms;
                        }
                        /* Form request end */

                        /* Payment calculation begin */
                        $guestSleepsTypeCondition = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                        $sleepsTypeCondition      = ($cabin->sleeping_place === 1) ? $sleeps : $bedsSumDorms;
                        $amount                   = round(($cabin->prepayment_amount * $dateDifference->days) * $guestSleepsTypeCondition, 2);
                        $amountDifference         = round($amount - $booking->prepayment_amount , 2);

                        if($amountDifference > 0 && $guestSleepsTypeCondition > $sleepsTypeCondition) {
                            dd('redirect to payment page'. $amountDifference);
                            // availability checking of beds dorms and sleeps
                            //redirect to payment
                            // After payment use redirection "return redirect()->route('booking.history')->with('updateBookingSuccessStatus', __('bookingHistory.updateBookingSuccessTwo'))";
                        }
                        else {
                            return redirect()->back()->with('updateBookingFailedStatus', __('bookingHistory.errorThree'));
                        }
                        /* Payment calculation end */
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
