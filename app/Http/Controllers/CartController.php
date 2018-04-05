<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CartRequest;
use App\Season;
use App\Cabin;
use App\Booking;
use App\MountSchoolBooking;
use App\Userlist;
use App\Country;
use DateTime;
use DatePeriod;
use DateInterval;
use Auth;

class CartController extends Controller
{
    /**
     * To generate date between two dates.
     *
     * @param  string  $now
     * @param  string  $end
     * @return object
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
     * @return object
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
        $carts   = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->get();

        $country  = Country::select('name')
            ->where('is_delete', 0)
            ->get();

        return view('cart', ['carts' => $carts, 'country' => $country]);
    }

    /**
     * Return cabin details when injection occur.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function cabin($id)
    {
        $cabin = '';
        if($id) {
            $cabin = Cabin::select('name', 'region', 'prepayment_amount', 'sleeping_place', 'halfboard', 'halfboard_price')
                ->where('is_delete', 0)
                ->where('other_cabin', "0")
                ->findOrFail($id);
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
     * @param  \App\Http\Requests\CartRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CartRequest $request)
    {
        dd($request->all());
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
     * @param  string  $cabinId
     * @param  string  $cartId
     * @return \Illuminate\Http\Response
     */
    public function destroy($cabinId, $cartId)
    {
        $booking = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('cabin_id', new \MongoDB\BSON\ObjectID($cabinId))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->findOrFail($cartId);

        $booking->delete();

        return redirect()->back()->with('deletedBooking', 'Booking deleted from your cart');
    }
}
