<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Booking;
use App\Cabin;
use Auth;

class BookingHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings          = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
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
}
