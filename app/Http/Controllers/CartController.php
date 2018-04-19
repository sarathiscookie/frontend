<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CartRequest;
use App\Cabin;
use App\Booking;
use App\MountSchoolBooking;
use App\Country;
use App\Order;
use Carbon\Carbon;
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
        if(isset($request->createBooking) && $request->createBooking === 'createBooking') {
            $available             = 'failure';
            $bedsRequest           = 0;
            $dormsRequest          = 0;
            $requestBedsSumDorms   = 0;
            $sleepsRequest         = 0;
            $eachDepositWithTax    = 0;
            $not_regular_dates     = [];
            $dates_array           = [];
            $availableStatus       = [];
            $clickHere             = '<a href="/inquiry">click here</a>';

            $carts                 = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->where('status', "8")
                ->where('is_delete', 0)
                ->take(5)
                ->get();

            if($carts){
                foreach ($carts as $key => $cart) {
                    $cabin                    = Cabin::where('is_delete', 0)
                        ->where('other_cabin', "0")
                        ->findOrFail($cart->cabin_id);

                    /* Form request begin */
                    $commentsRequest          = $request->guest[$cart->_id]['comments'];
                    if(isset($request->guest[$cart->_id]['halfboard']))
                    {
                        $halfBoard            = $request->guest[$cart->_id]['halfboard'];
                    }
                    else {
                        $halfBoard            = '0';
                    }

                    if ($cabin->sleeping_place === 1) {
                        $sleepsRequest        = (int)$request->guest[$cart->_id]['sleeps'];
                    }
                    else {
                        $bedsRequest          = (int)$request->guest[$cart->_id]['beds'];
                        $dormsRequest         = (int)$request->guest[$cart->_id]['dormitory'];
                        $requestBedsSumDorms  = $bedsRequest + $dormsRequest;
                    }
                    /* Form request end */

                    /* Payment calculation begin */
                    $monthBegin               = $cart->checkin_from->format('Y-m-d');
                    $monthEnd                 = $cart->reserve_to->format('Y-m-d');
                    $d1                       = new DateTime($monthBegin);
                    $d2                       = new DateTime($monthEnd);
                    $dateDifference           = $d2->diff($d1);
                    $guestSleepsTypeCondition = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                    $amount                   = round(($cabin->prepayment_amount * $dateDifference->days) * $guestSleepsTypeCondition, 2);

                    if($amount <= 30) {
                        $serviceTax           = env('SERVICE_TAX_ONE');
                        $eachAmountPercentage = ($serviceTax / 100) * $amount;
                        $eachDepositWithTax   = round($amount + $eachAmountPercentage, 2);
                    }

                    if($amount > 30 && $amount <= 100) {
                        $serviceTax           = env('SERVICE_TAX_TWO');
                        $eachAmountPercentage = ($serviceTax / 100) * $amount;
                        $eachDepositWithTax   = round($amount + $eachAmountPercentage, 2);
                    }

                    if($amount > 100) {
                        $serviceTax           = env('SERVICE_TAX_THREE');
                        $eachAmountPercentage = ($serviceTax / 100) * $amount;
                        $eachDepositWithTax   = round($amount + $eachAmountPercentage, 2);
                    }
                    /* Payment calculation end */

                    // Generate date b/w checking from and to
                    $generateBookingDates     = $this->generateDates($cart->checkin_from->format('Y-m-d'), $cart->reserve_to->format('Y-m-d'));

                    foreach ($generateBookingDates as $generateBookingDate) {

                        $dates                = $generateBookingDate->format('Y-m-d');
                        $day                  = $generateBookingDate->format('D');

                        /* Checking bookings available begins */
                        $mon_day              = ($cabin->mon_day === 1) ? 'Mon' : 0;
                        $tue_day              = ($cabin->tue_day === 1) ? 'Tue' : 0;
                        $wed_day              = ($cabin->wed_day === 1) ? 'Wed' : 0;
                        $thu_day              = ($cabin->thu_day === 1) ? 'Thu' : 0;
                        $fri_day              = ($cabin->fri_day === 1) ? 'Fri' : 0;
                        $sat_day              = ($cabin->sat_day === 1) ? 'Sat' : 0;
                        $sun_day              = ($cabin->sun_day === 1) ? 'Sun' : 0;

                        /* Getting bookings from booking collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
                        $bookings             = Booking::select('beds', 'dormitory', 'sleeps')
                            ->where('is_delete', 0)
                            ->where('cabinname', $cabin->name)
                            ->whereIn('status', ['1', '4', '7', '8'])
                            ->whereRaw(['checkin_from' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->get();

                        /* Getting bookings from mschool collection status 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
                        $msBookings           = MountSchoolBooking::select('beds', 'dormitory', 'sleeps')
                            ->where('is_delete', 0)
                            ->where('cabin_name', $cabin->name)
                            ->whereIn('status', ['1', '4', '7', '8'])
                            ->whereRaw(['check_in' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                            ->get();

                        /* Getting count of sleeps, beds and dorms */
                        if(count($bookings) > 0) {
                            $sleeps        = $bookings->sum('sleeps');
                            $beds          = $bookings->sum('beds');
                            $dorms         = $bookings->sum('dormitory');
                        }
                        else {
                            $dorms         = 0;
                            $beds          = 0;
                            $sleeps        = 0;
                        }

                        if(count($msBookings) > 0) {
                            $msSleeps      = $msBookings->sum('sleeps');
                            $msBeds        = $msBookings->sum('beds');
                            $msDorms       = $msBookings->sum('dormitory');
                        }
                        else {
                            $msSleeps      = 0;
                            $msBeds        = 0;
                            $msDorms       = 0;
                        }

                        /* Taking beds, dorms and sleeps depends up on sleeping_place */
                        if($cabin->sleeping_place != 1) {

                            // Reason for subtraction: Eg cabin->beds = 5 cabin->dorms = 5. If guest added 3 beds and 3 dorms to cart, cart data is treated as booked for 1 hour. When guest edit the cart (eg set beds to 4 and dorms to 4) the availability condition will run and shows not available. Because already 3 beds and 3 dorms in booking cart. So we subtract the particular user's cart->beds and cart->dormitory.
                            $totalBeds     = ($beds + $msBeds) - $cart->beds;
                            $totalDorms    = ($dorms + $msDorms) - $cart->dormitory;

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

                                    $dates_array[]               = $dates;

                                    if(($totalBeds < $cabin->not_regular_beds) || ($totalDorms < $cabin->not_regular_dorms)) {
                                        $not_regular_beds_diff   = $cabin->not_regular_beds - $totalBeds;
                                        $not_regular_dorms_diff  = $cabin->not_regular_dorms - $totalDorms;

                                        /* Available beds and dorms on not regular */
                                        $not_regular_beds_avail  = ($not_regular_beds_diff >= 0) ? $not_regular_beds_diff : 0;
                                        $not_regular_dorms_avail = ($not_regular_dorms_diff >= 0) ? $not_regular_dorms_diff : 0;

                                        if($bedsRequest <= $not_regular_beds_avail) {
                                            $availableStatus[]   = 'available';
                                        }
                                        else {
                                            $availableStatus[]   = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                        }

                                        if($dormsRequest <= $not_regular_dorms_avail) {
                                            $availableStatus[]   = 'available';
                                        }
                                        else {
                                            $availableStatus[]   = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                        }

                                        if($requestBedsSumDorms >= $cabin->not_regular_inquiry_guest) {
                                            $availableStatus[]   = 'notAvailable';

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

                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->not_regular_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                        }
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                    }

                                }
                            }

                            /* Calculating beds & dorms for regular */
                            if($cabin->regular === 1) {

                                if($mon_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[]             = $dates;

                                        if(($totalBeds < $cabin->mon_beds) || ($totalDorms < $cabin->mon_dorms)) {
                                            $mon_beds_diff         = $cabin->mon_beds - $totalBeds;
                                            $mon_dorms_diff        = $cabin->mon_dorms - $totalDorms;

                                            /* Available beds and dorms on regular monday */
                                            $mon_beds_avail        = ($mon_beds_diff >= 0) ? $mon_beds_diff : 0;
                                            $mon_dorms_avail       = ($mon_dorms_diff >= 0) ? $mon_dorms_diff : 0;

                                            if($bedsRequest <= $mon_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            if($dormsRequest <= $mon_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($requestBedsSumDorms >= $cabin->mon_inquiry_guest) {
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

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->mon_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                        }
                                    }
                                }

                                if($tue_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[]             = $dates;

                                        if(($totalBeds < $cabin->tue_beds) || ($totalDorms < $cabin->tue_dorms)) {
                                            $tue_beds_diff         = $cabin->tue_beds - $totalBeds;
                                            $tue_dorms_diff        = $cabin->tue_dorms - $totalDorms;

                                            /* Available beds and dorms on regular tuesday */
                                            $tue_beds_avail        = ($tue_beds_diff >= 0) ? $tue_beds_diff : 0;
                                            $tue_dorms_avail       = ($tue_dorms_diff >= 0) ? $tue_dorms_diff : 0;

                                            if($bedsRequest <= $tue_beds_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            if($dormsRequest <= $tue_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($requestBedsSumDorms >= $cabin->tue_inquiry_guest) {
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

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->tue_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            if($dormsRequest <= $wed_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($requestBedsSumDorms >= $cabin->wed_inquiry_guest) {
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

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->wed_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            if($dormsRequest <= $thu_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($requestBedsSumDorms >= $cabin->thu_inquiry_guest) {
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

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->thu_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            if($dormsRequest <= $fri_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($requestBedsSumDorms >= $cabin->fri_inquiry_guest) {
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

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->fri_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                        }
                                    }
                                }

                                if($sat_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[]             = $dates;

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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            if($dormsRequest <= $sat_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($requestBedsSumDorms >= $cabin->sat_inquiry_guest) {
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

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->sat_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            if($dormsRequest <= $sun_dorms_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($requestBedsSumDorms >= $cabin->sun_inquiry_guest) {
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

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->sun_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                        return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $bedsRequest.' Beds are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                    }

                                    if($dormsRequest <= $normal_dorms_avail) {
                                        $availableStatus[] = 'available';
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $dormsRequest.' Dorms are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                    }

                                    /* Checking requested beds and dorms sum is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                    if($requestBedsSumDorms >= $cabin->inquiry_starts) {
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

                                        return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if sum of beds and dorms is less than '.$cabin->inquiry_starts.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                    }
                                }
                                else {
                                    $availableStatus[] = 'notAvailable';
                                    return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'Beds and Dorms are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                }
                            }

                        }
                        else {
                            $totalSleeps   = ($sleeps + $msSleeps) - $cart->sleeps;

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

                                    $dates_array[]                = $dates;

                                    if(($totalSleeps < $cabin->not_regular_sleeps)) {
                                        $not_regular_sleeps_diff  = $cabin->not_regular_sleeps - $totalSleeps;

                                        /* Available sleeps on not regular */
                                        $not_regular_sleeps_avail = ($not_regular_sleeps_diff >= 0) ? $not_regular_sleeps_diff : 0;

                                        if($sleepsRequest <= $not_regular_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                        }

                                        /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                        if($sleepsRequest >= $cabin->not_regular_inquiry_guest) {
                                            $availableStatus[] = 'notAvailable';

                                            $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                            $request->session()->put('cabin_name', $cabin->name);
                                            $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                            $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                            $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                            $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                            $request->session()->put('beds', 0);
                                            $request->session()->put('dormitory', 0);
                                            $request->session()->put('sleeps', $sleepsRequest);
                                            $request->session()->put('guests', $sleepsRequest);

                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->not_regular_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                        }
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($sleepsRequest >= $cabin->mon_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';

                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                                $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                                $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->mon_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                        }
                                    }
                                }

                                if($tue_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[]             = $dates;

                                        if(($totalSleeps < $cabin->tue_sleeps)) {
                                            $tue_sleeps_diff       = $cabin->tue_sleeps - $totalSleeps;

                                            /* Available sleeps on regular tuesday */
                                            $tue_sleeps_avail      = ($tue_sleeps_diff >= 0) ? $tue_sleeps_diff : 0;

                                            if($sleepsRequest <= $tue_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($sleepsRequest >= $cabin->tue_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';

                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                                $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                                $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->tue_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($sleepsRequest >= $cabin->wed_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';

                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                                $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                                $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->wed_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                        }
                                    }
                                }

                                if($thu_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[]             = $dates;

                                        if(($totalSleeps < $cabin->thu_sleeps)) {
                                            $thu_sleeps_diff       = $cabin->thu_sleeps - $totalSleeps;

                                            /* Available sleeps on regular thursday */
                                            $thu_sleeps_avail      = ($thu_sleeps_diff >= 0) ? $thu_sleeps_diff : 0;

                                            if($sleepsRequest <= $thu_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($sleepsRequest >= $cabin->thu_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';

                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                                $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                                $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->thu_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }

                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                        }
                                    }
                                }

                                if($fri_day === $day) {

                                    if(!in_array($dates, $dates_array)) {

                                        $dates_array[]             = $dates;

                                        if(($totalSleeps < $cabin->fri_sleeps)) {
                                            $fri_sleeps_diff       = $cabin->fri_sleeps - $totalSleeps;

                                            /* Available sleeps on regular friday */
                                            $fri_sleeps_avail      = ($fri_sleeps_diff >= 0) ? $fri_sleeps_diff : 0;

                                            if($sleepsRequest <= $fri_sleeps_avail) {
                                                $availableStatus[] = 'available';
                                            }
                                            else {
                                                $availableStatus[] = 'notAvailable';
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($sleepsRequest >= $cabin->fri_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';

                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                                $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                                $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->fri_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($sleepsRequest >= $cabin->sat_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';

                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                                $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                                $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->sat_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
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
                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                            }

                                            /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                            if($sleepsRequest >= $cabin->sun_inquiry_guest) {
                                                $availableStatus[] = 'notAvailable';

                                                $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                                $request->session()->put('cabin_name', $cabin->name);
                                                $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                                $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                                $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                                $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                                $request->session()->put('beds', 0);
                                                $request->session()->put('dormitory', 0);
                                                $request->session()->put('sleeps', $sleepsRequest);
                                                $request->session()->put('guests', $sleepsRequest);

                                                return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->sun_inquiry_guest.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                            }
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                        }
                                    }
                                }

                            }

                            /* Calculating sleeps for normal */
                            if(!in_array($dates, $dates_array)) {

                                if(($totalSleeps < $cabin->sleeps)) {
                                    $normal_sleeps_diff    = $cabin->sleeps - $totalSleeps;

                                    /* Available sleeps on normal */
                                    $normal_sleeps_avail   = ($normal_sleeps_diff >= 0) ? $normal_sleeps_diff : 0;

                                    if($sleepsRequest <= $normal_sleeps_avail) {
                                        $availableStatus[] = 'available';
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', $sleepsRequest.' Sleeps are not available on '.$generateBookingDate->format("jS F"))->withInput();
                                    }

                                    /* Checking requested sleeps is greater or equal to inquiry. Cabin inquiry guest is greater than 0 */
                                    if($sleepsRequest >= $cabin->inquiry_starts) {
                                        $availableStatus[] = 'notAvailable';

                                        $request->session()->put('cabin_id', new \MongoDB\BSON\ObjectID($cabin->_id));
                                        $request->session()->put('cabin_name', $cabin->name);
                                        $request->session()->put('sleeping_place', $cabin->sleeping_place);
                                        $request->session()->put('checkin_from', $cart->checkin_from->format('d.m.y'));
                                        $request->session()->put('reserve_to', $cart->reserve_to->format('d.m.y'));
                                        $request->session()->put('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id));
                                        $request->session()->put('beds', 0);
                                        $request->session()->put('dormitory', 0);
                                        $request->session()->put('sleeps', $sleepsRequest);
                                        $request->session()->put('guests', $sleepsRequest);


                                        return redirect()->back()->with('notAvailable.'.$cart->_id.'.status', 'On '.$generateBookingDate->format("jS F").' booking is possible if no of sleeps is less than '.$cabin->inquiry_starts.'. But you can send enquiry. Please '.$clickHere.' for inquiry')->withInput();
                                    }
                                }
                                else {
                                    $availableStatus[] = 'notAvailable';
                                    return redirect()->back()->with('notAvailable.'.$cart->_id.'.status',  'Sleeps are already filled on '.$generateBookingDate->format("jS F"))->withInput();
                                }

                            }

                        }
                        /* Checking bookings available ends */
                    }

                    if(!in_array('notAvailable', $availableStatus)) {
                        $available                         = 'success';
                        $booking                           = Booking::find($cart->_id);
                        $booking->beds                     = ($cabin->sleeping_place != 1) ? $bedsRequest : 0;
                        $booking->dormitory                = ($cabin->sleeping_place != 1) ? $dormsRequest : 0;
                        $booking->sleeps                   = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                        $booking->guests                   = ($cabin->sleeping_place === 1) ? $sleepsRequest : $requestBedsSumDorms;
                        $booking->halfboard                = $halfBoard;
                        $booking->comments                 = $commentsRequest;
                        $booking->prepayment_amount        = $amount;
                        $booking->total_prepayment_amount  = $eachDepositWithTax; // Total prepayment amount is not the exact figure.
                        $booking->updated_at               = Carbon::now();
                        $booking->save();
                    }

                }

                return redirect()->route('payment')->with('availableStatus', $available);
            }

        }
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
