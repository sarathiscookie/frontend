<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cabin;
use App\Country;
use App\Booking;
use App\Http\Requests\InquiryRequest;
use Carbon\Carbon;
use DateTime;
use DatePeriod;
use DateInterval;
use Auth;

class InquiryController extends Controller
{

    /**
     * To generate date format as mongo.
     *
     * @param  string  $date
     * @return object
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
        $cabinDetails  = Cabin::select('name', 'region', 'prepayment_amount', 'sleeping_place', 'halfboard', 'halfboard_price')
            ->where('is_delete', 0)
            ->where('other_cabin', "0")
            ->findOrFail(session()->get('cabin_id'));

        $country       = Country::select('name')
            ->where('is_delete', 0)
            ->get();

        return view('inquiry', ['cabinDetails' => $cabinDetails, 'country' => $country]);
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
        dd($request->all());

        /* Inquiry booking begin */
        /*$book                 = new Booking;
        $book->cabinname      = session()->get('cabin_name');
        $book->cabin_id       = new \MongoDB\BSON\ObjectID(session()->get('cabin_id'));
        $book->user           = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
        $book->bookingdate    = Carbon::now();
        $book->checkin_from   = session()->get('checkin_from');
        $book->reserve_to     = session()->get('reserve_to');
        $book->invoice_number = 'SWH-16-333336';
        $book->typeofbooking  = 1;
        $book->read           = 0;
        $book->status         = '7';
        $book->inquirystatus  = 0;
        $book->is_delete      = 0;
        $book->save();*/
        /* Inquiry booking end*/

        return redirect()->back();
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
