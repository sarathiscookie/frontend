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
        $carts   = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->take(5)
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
        if(isset($request->createBooking) && $request->createBooking === 'createBooking'){

            $sleepsRequest                = 0;
            $bedsRequest                  = 0;
            $dormsRequest                 = 0;
            $requestBedsSumDorms          = 0;
            $commentsRequest              = '';
            $not_regular_dates            = [];
            $clickHere                    = '<a href="/inquiry">click here</a>';

            $carts                        = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->where('status', "8")
                ->where('is_delete', 0)
                ->take(5)
                ->get();

            if($carts){
                foreach ($carts as $key => $cart) {

                    $commentsRequest         = $request->guest[$cart->_id]['comments'];

                    if ((int)$request->guest[$cart->_id]['sleeping_place'] === 1) {
                        $sleepsRequest       = (int)$request->guest[$cart->_id]['sleeps'];
                    }
                    else {
                        $bedsRequest         = (int)$request->guest[$cart->_id]['beds'];
                        $dormsRequest        = (int)$request->guest[$cart->_id]['dormitory'];
                        $requestBedsSumDorms = $bedsRequest + $dormsRequest;
                    }

                    $cabin                   = Cabin::select('_id', 'name', 'mon_day', 'tue_day', 'wed_day', 'thu_day', 'fri_day', 'sat_day', 'sun_day', 'not_regular', 'not_regular_date', 'regular', 'sleeping_place', 'not_regular_inquiry_guest', 'not_regular_beds', 'not_regular_dorms', 'not_regular_sleeps')
                        ->where('is_delete', 0)
                        ->where('other_cabin', "0")
                        ->findOrFail($cart->cabin_id);

                    $country                 = Country::select('name')
                        ->where('is_delete', 0)
                        ->get();

                    // Generate date b/w checking from and to
                    $generateBookingDates = $this->generateDates($cart->checkin_from->format('Y-m-d'), $cart->reserve_to->format('Y-m-d'));

                    foreach ($generateBookingDates as $generateBookingDate) {

                        $dates            = $generateBookingDate->format('Y-m-d');
                        $day              = $generateBookingDate->format('D');

                        /* Checking bookings available begins */
                        $mon_day          = ($cabin->mon_day === 1) ? 'Mon' : 0;
                        $tue_day          = ($cabin->tue_day === 1) ? 'Tue' : 0;
                        $wed_day          = ($cabin->wed_day === 1) ? 'Wed' : 0;
                        $thu_day          = ($cabin->thu_day === 1) ? 'Thu' : 0;
                        $fri_day          = ($cabin->fri_day === 1) ? 'Fri' : 0;
                        $sat_day          = ($cabin->sat_day === 1) ? 'Sat' : 0;
                        $sun_day          = ($cabin->sun_day === 1) ? 'Sun' : 0;

                        /* Getting bookings from booking collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
                        $bookings  = Booking::select('beds', 'dormitory', 'sleeps')
                            ->where('is_delete', 0)
                            ->where('cabinname', $cabin->name)
                            ->whereIn('status', ['1', '4', '7', '8'])
                            ->whereRaw(['checkin_from' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->get();

                        /* Getting bookings from mschool collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
                        $msBookings  = MountSchoolBooking::select('beds', 'dormitory', 'sleeps')
                            ->where('is_delete', 0)
                            ->where('cabin_name', $cabin->name)
                            ->whereIn('status', ['1', '4', '7', '8'])
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

                            $totalBeds     = $beds + $msBeds;
                            $totalDorms    = $dorms + $msDorms;

                            /* Calculating beds & dorms for not regular */
                            if($cabin->not_regular === 1) {
                                $not_regular_date_explode = explode(" - ", $cabin->not_regular_date);
                                $not_regular_date_begin   = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[0])->format('Y-m-d');
                                $not_regular_date_end     = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[1])->format('Y-m-d 23:59:59'); //To get the end date. We need to add time.
                                $generateNotRegularDates  = $this->generateDates($not_regular_date_begin, $not_regular_date_end);

                                foreach($generateNotRegularDates as $generateNotRegularDate) {
                                    $not_regular_dates[]  = $generateNotRegularDate->format('Y-m-d');
                                }

                                if(in_array($dates, $not_regular_dates)) {

                                    $dates_array[] = $dates;

                                    if($requestBedsSumDorms >= $cabin->not_regular_inquiry_guest) {
                                        $availableStatus[] = 'notAvailable';

                                        $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                        $request->session()->put('cabin_name', $cabin->name);
                                        $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                        $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                        $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                        $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                        $request->session()->put('beds', $bedsRequest);
                                        $request->session()->put('dormitory', $dormsRequest);
                                        $request->session()->put('sleeps', $requestBedsSumDorms);
                                        $request->session()->put('guests', $requestBedsSumDorms);

                                        return redirect()->back()->with('notAvailable', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->not_regular_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry');
                                    }
                                    else {

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
                                                return redirect()->back()->with('notAvailable', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"));
                                            }

                                            if($dormsRequest <= $not_regular_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"));
                                            }
                                        }

                                    }

                                }
                            }
                            /* Calculating beds & dorms for regular */
                            /*if($cabin->regular === 1) {

                            }*/
                            /* Calculating beds & dorms for normal */
                            /*if(!in_array($dates, $dates_array)) {

                            }*/
                        }
                        else {
                            $totalSleeps  = $sleeps + $msSleeps;
                        }

                        /* Checking bookings available ends */
                    }


                }


                //dd($commentsRequest);// for testing purpose

            }


            // Checking beds, dorms, sleeps availability
            // Not need to check date in season
            // Update guest, beds, dorms, sleeps, halfboard, comments, total prepayment amount, prepayment amount, order_id
            // Redirect to payment choosing page
        }
        else {
            return redirect()->back();
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
