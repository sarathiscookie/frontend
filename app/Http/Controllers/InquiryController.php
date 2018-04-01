<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cabin;
use App\Country;
use App\Booking;
use App\MountSchoolBooking;
use App\Season;
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

        //dd($request->all());
        /*
  "halfboard" => "1"
  "comments" => "test"*/

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

        $monthBegin              = DateTime::createFromFormat('d.m.y', session()->get('checkin_from'))->format('Y-m-d');
        $monthEnd                = DateTime::createFromFormat('d.m.y', session()->get('reserve_to'))->format('Y-m-d');
        $d1                      = new DateTime($monthBegin);
        $d2                      = new DateTime($monthEnd);
        $dateDifference          = $d2->diff($d1);

        $available               = 'failure';

        if($monthBegin < $monthEnd) {
            if($dateDifference->days <= 60) {
                $not_regular_dates       = [];
                $dates_array             = [];
                $availableStatus         = [];

                $seasons                 = Season::where('cabin_id', new \MongoDB\BSON\ObjectID(session()->get('cabin_id')))->get();
                $cabin                   = Cabin::where('is_delete', 0)
                    ->where('other_cabin', "0")
                    ->findOrFail(session()->get('cabin_id'));

                $generateBookingDates    = $this->generateDates($monthBegin, $monthEnd);

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
                            return redirect()->back()->with('error', 'Sorry selected dates are not in a season time.');
                        }

                        $prepareArray       = [$dates => $day];
                        $array_unique       = array_unique($holiday_prepare);
                        $array_intersect    = array_intersect($prepareArray, $array_unique);

                        foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                            if((strtotime($array_intersect_key) >= strtotime($monthBegin)) && (strtotime($array_intersect_key) < strtotime($monthEnd))) {
                                return redirect()->back()->with('error', 'Booking not possible because holidays included.');
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
                            $not_regular_date_end     = DateTime::createFromFormat('d.m.y', $not_regular_date_explode[1])->format('Y-m-d 23:59:59'); //To get the end date we need to add time
                            $generateNotRegularDates  = $this->generateDates($not_regular_date_begin, $not_regular_date_end);

                            foreach($generateNotRegularDates as $generateNotRegularDate) {
                                $not_regular_dates[]  = $generateNotRegularDate->format('Y-m-d');
                            }

                            if(in_array($dates, $not_regular_dates)) {

                                $dates_array[] = $dates;

                                if(($totalBeds < $cabin->not_regular_beds) || ($totalDorms < $cabin->not_regular_dorms)) {
                                    $not_regular_beds_diff              = $cabin->not_regular_beds - $totalBeds;
                                    $not_regular_dorms_diff             = $cabin->not_regular_dorms - $totalDorms;

                                    $not_regular_beds_avail             = ($not_regular_beds_diff >= 0) ? $not_regular_beds_diff : 0;
                                    $not_regular_dorms_avail            = ($not_regular_dorms_diff >= 0) ? $not_regular_dorms_diff : 0;

                                    if($request->beds <= $not_regular_beds_avail) {
                                        $availableStatus[] = 'available';
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                    }

                                    if($request->dormitory <= $not_regular_dorms_avail) {
                                        $availableStatus[] = 'available';
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                    }

                                    /*print_r(' ----not_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' not_rgl_dates: ' . $dates . ' not_regular_beds_diff: '. $not_regular_beds_diff. ' not_regular_beds_avail: '. $not_regular_beds_avail);
                                    print_r( ' not_rgl_dates: ' . $dates . ' not_regular_dorms_diff: '. $not_regular_dorms_diff. ' not_regular_dorms_avail: '. $not_regular_dorms_avail);*/


                                }
                                else {
                                    /*print_r(' ----not_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                    $availableStatus[] = 'notAvailable';
                                    //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                    return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
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

                                        $mon_beds_avail             = ($mon_beds_diff >= 0) ? $mon_beds_diff : 0;
                                        $mon_dorms_avail            = ($mon_dorms_diff >= 0) ? $mon_dorms_diff : 0;

                                        if($request->beds <= $mon_beds_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        if($request->dormitory <= $mon_dorms_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        /*print_r(' ----mon_regular_data---- ');
                                        print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                        print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                        print_r(' mon_dates: ' . $dates . ' mon_beds_diff: '. $mon_beds_diff. ' mon_beds_avail: '. $mon_beds_avail);
                                        print_r( ' mon_dates: ' . $dates . ' mon_dorms_diff: '. $mon_dorms_diff. ' mon_dorms_avail: '. $mon_dorms_avail);
                                        */

                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----mon_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                    }
                                }
                            }

                            if($tue_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalBeds < $cabin->tue_beds) || ($totalDorms < $cabin->tue_dorms)) {
                                        $tue_beds_diff              = $cabin->tue_beds - $totalBeds;
                                        $tue_dorms_diff             = $cabin->tue_dorms - $totalDorms;

                                        $tue_beds_avail             = ($tue_beds_diff >= 0) ? $tue_beds_diff : 0;
                                        $tue_dorms_avail            = ($tue_dorms_diff >= 0) ? $tue_dorms_diff : 0;

                                        if($request->beds <= $tue_beds_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        if($request->dormitory <= $tue_dorms_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        /*print_r(' ----tue_regular_data---- ');
                                        print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                        print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                        print_r(' tue_dates: ' . $dates . ' tue_beds_diff: '. $tue_beds_diff. ' tue_beds_avail: '. $tue_beds_avail);
                                        print_r( ' tue_dates: ' . $dates . ' tue_dorms_diff: '. $tue_dorms_diff. ' tue_dorms_avail: '. $tue_dorms_avail);
                                        */
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----tue_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                    }
                                }
                            }

                            if($wed_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalBeds < $cabin->wed_beds) || ($totalDorms < $cabin->wed_dorms)) {
                                        $wed_beds_diff              = $cabin->wed_beds - $totalBeds;
                                        $wed_dorms_diff             = $cabin->wed_dorms - $totalDorms;

                                        $wed_beds_avail             = ($wed_beds_diff >= 0) ? $wed_beds_diff : 0;
                                        $wed_dorms_avail            = ($wed_dorms_diff >= 0) ? $wed_dorms_diff : 0;

                                        if($request->beds <= $wed_beds_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        if($request->dormitory <= $wed_dorms_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        /*print_r(' ----wed_regular_data---- ');
                                        print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                        print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                        print_r(' wed_dates: ' . $dates . ' wed_beds_diff: '. $wed_beds_diff. ' wed_beds_avail: '. $wed_beds_avail);
                                        print_r( ' wed_dates: ' . $dates . ' wed_dorms_diff: '. $wed_dorms_diff. ' wed_dorms_avail: '. $wed_dorms_avail);
                                        */
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----wed_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                    }
                                }
                            }

                            if($thu_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalBeds < $cabin->thu_beds) || ($totalDorms < $cabin->thu_dorms)) {
                                        $thu_beds_diff              = $cabin->thu_beds - $totalBeds;
                                        $thu_dorms_diff             = $cabin->thu_dorms - $totalDorms;

                                        $thu_beds_avail             = ($thu_beds_diff >= 0) ? $thu_beds_diff : 0;
                                        $thu_dorms_avail            = ($thu_dorms_diff >= 0) ? $thu_dorms_diff : 0;

                                        if($request->beds <= $thu_beds_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        if($request->dormitory <= $thu_dorms_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        /*print_r(' ----thu_regular_data---- ');
                                        print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                        print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                        print_r(' thu_dates: ' . $dates . ' thu_beds_diff: '. $thu_beds_diff. ' thu_beds_avail: '. $thu_beds_avail);
                                        print_r( ' thu_dates: ' . $dates . ' thu_dorms_diff: '. $thu_dorms_diff. ' thu_dorms_avail: '. $thu_dorms_avail);
                                        */
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----thu_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                    }
                                }
                            }

                            if($fri_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalBeds < $cabin->fri_beds) || ($totalDorms < $cabin->fri_dorms)) {
                                        $fri_beds_diff              = $cabin->fri_beds - $totalBeds;
                                        $fri_dorms_diff             = $cabin->fri_dorms - $totalDorms;

                                        $fri_beds_avail             = ($fri_beds_diff >= 0) ? $fri_beds_diff : 0;
                                        $fri_dorms_avail            = ($fri_dorms_diff >= 0) ? $fri_dorms_diff : 0;

                                        if($request->beds <= $fri_beds_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        if($request->dormitory <= $fri_dorms_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        /*print_r(' ----fri_regular_data---- ');
                                        print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                        print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                        print_r(' fri_dates: ' . $dates . ' fri_beds_diff: '. $fri_beds_diff. ' fri_beds_avail: '. $fri_beds_avail);
                                        print_r( ' fri_dates: ' . $dates . ' fri_dorms_diff: '. $fri_dorms_diff. ' fri_dorms_avail: '. $fri_dorms_avail);
                                        */
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                        /*print_r(' ----fri_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                    }
                                }
                            }

                            if($sat_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalBeds < $cabin->sat_beds) || ($totalDorms < $cabin->sat_dorms)) {
                                        $sat_beds_diff              = $cabin->sat_beds - $totalBeds;
                                        $sat_dorms_diff             = $cabin->sat_dorms - $totalDorms;

                                        $sat_beds_avail             = ($sat_beds_diff >= 0) ? $sat_beds_diff : 0;
                                        $sat_dorms_avail            = ($sat_dorms_diff >= 0) ? $sat_dorms_diff : 0;

                                        if($request->beds <= $sat_beds_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        if($request->dormitory <= $sat_dorms_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        /*print_r(' ----sat_regular_data---- ');
                                        print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                        print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                        print_r(' sat_dates: ' . $dates . ' sat_beds_diff: '. $sat_beds_diff. ' sat_beds_avail: '. $sat_beds_avail);
                                        print_r( ' sat_dates: ' . $dates . ' sat_dorms_diff: '. $sat_dorms_diff. ' sat_dorms_avail: '. $sat_dorms_avail);
                                        */
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                        /*print_r(' ----sat_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                    }
                                }
                            }

                            if($sun_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalBeds < $cabin->sun_beds) || ($totalDorms < $cabin->sun_dorms)) {
                                        $sun_beds_diff              = $cabin->sun_beds - $totalBeds;
                                        $sun_dorms_diff             = $cabin->sun_dorms - $totalDorms;

                                        $sun_beds_avail             = ($sun_beds_diff >= 0) ? $sun_beds_diff : 0;
                                        $sun_dorms_avail            = ($sun_dorms_diff >= 0) ? $sun_dorms_diff : 0;

                                        if($request->beds <= $sun_beds_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        if($request->dormitory <= $sun_dorms_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }

                                        /*print_r(' ----sun_regular_data---- ');
                                        print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                        print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                        print_r(' sun_dates: ' . $dates . ' sun_beds_diff: '. $sun_beds_diff. ' sun_beds_avail: '. $sun_beds_avail);
                                        print_r( ' sun_dates: ' . $dates . ' sun_dorms_diff: '. $sun_dorms_diff. ' sun_dorms_avail: '. $sun_dorms_avail);
                                        */
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                        /*print_r(' ----sun_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                    }
                                }
                            }
                        }

                        /* Calculating beds & dorms for normal */
                        if(!in_array($dates, $dates_array)) {

                            if(($totalBeds < $cabin->beds) || ($totalDorms < $cabin->dormitory)) {

                                $normal_beds_diff              = $cabin->beds - $totalBeds;
                                $normal_dorms_diff             = $cabin->dormitory - $totalDorms;

                                $normal_beds_avail             = ($normal_beds_diff >= 0) ? $normal_beds_diff : 0;
                                $normal_dorms_avail            = ($normal_dorms_diff >= 0) ? $normal_dorms_diff : 0;

                                if($request->beds <= $normal_beds_avail) {
                                    $availableStatus[] = 'available';
                                }
                                else {
                                    $availableStatus[] = 'notAvailable';
                                    //print_r('Beds not available');
                                    //return response()->json(['error' => 'Beds not available on '.$generateBookingDate->format("jS F")], 422);
                                    return redirect()->back()->with('error', $request->beds.' beds are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                }

                                if($request->dormitory <= $normal_dorms_avail) {
                                    $availableStatus[] = 'available';
                                }
                                else {
                                    $availableStatus[] = 'notAvailable';
                                    //print_r('Dorms not available');
                                    //return response()->json(['error' => 'Dorms not available on '.$generateBookingDate->format("jS F")], 422);
                                    return redirect()->back()->with('error', $request->dormitory.' dorms not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                }

                                /*print_r(' ----normal_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' normal_dates: ' . $dates . ' normal_beds_diff: '. $normal_beds_diff. ' normal_beds_avail: '. $normal_beds_avail);
                                print_r( ' normal_dates: ' . $dates . ' normal_dorms_diff: '. $normal_dorms_diff. ' normal_dorms_avail: '. $normal_dorms_avail);*/
                            }
                            else {
                                $availableStatus[] = 'notAvailable';
                                /*print_r(' ----normal_data---- ');
                                print_r(' not_available_dates '. $dates);*/
                                //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                            }
                        }

                    }
                    else {
                        $totalSleeps = $sleeps + $msSleeps;

                        /* Calculating beds & dorms for not regular */
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

                                    /* Available sleeps */
                                    $not_regular_sleeps_avail      = ($not_regular_sleeps_diff >= 0) ? $not_regular_sleeps_diff : 0;

                                    if($request->sleeps <= $not_regular_sleeps_avail) {
                                        $availableStatus[] = 'available';
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                    }

                                    /*print_r(' ----not_regular_data---- ');
                                    print_r(' Ms sleeps '.$msSleeps.' Sleeps '.$sleeps.' Total sleeps '. $totalSleeps.' not_rgl_dates: ' . $dates . ' not_regular_sleeps_avail: '. $not_regular_sleeps_avail);*/
                                }
                                else {
                                    $availableStatus[] = 'notAvailable';
                                    //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                    return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                    /*print_r(' ----not_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                }
                            }
                        }

                        /* Calculating beds & dorms for regular */
                        if($cabin->regular === 1) {

                            if($mon_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalSleeps < $cabin->mon_sleeps)) {
                                        $mon_sleeps_diff       = $cabin->mon_sleeps - $totalSleeps;

                                        /* Available sleeps */
                                        $mon_sleeps_avail      = ($mon_sleeps_diff >= 0) ? $mon_sleeps_diff : 0;

                                        if($request->sleeps <= $mon_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }
                                        /*print_r(' ----mon_regular_data---- ');
                                        print_r(' mon_dates: ' . $dates . ' mon_sleeps_avail: '. $mon_sleeps_avail);
                                        print_r(' mon_sleeps_filled = '. $mon_sleeps_filled .' mon_cabin_sleeps_total = '. $cabin->mon_sleeps .' result(mon_sleeps_filled / mon_cabin_sleeps) * 100 = '. $mon_sleeps_percentage);*/
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----mon_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                    }
                                }
                            }

                            if($tue_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalSleeps < $cabin->tue_sleeps)) {
                                        $tue_sleeps_diff       = $cabin->tue_sleeps - $totalSleeps;

                                        /* Available sleeps */
                                        $tue_sleeps_avail      = ($tue_sleeps_diff >= 0) ? $tue_sleeps_diff : 0;

                                        if($request->sleeps <= $tue_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }
                                        /*print_r(' ----tue_regular_data---- ');
                                        print_r(' tue_dates: ' . $dates . ' tue_sleeps_avail: '. $tue_sleeps_avail);
                                        print_r(' tue_sleeps_filled = '. $tue_sleeps_filled .' tue_cabin_sleeps_total = '. $cabin->tue_sleeps .' result(tue_sleeps_filled / tue_cabin_sleeps) * 100 = '. $tue_sleeps_percentage);*/
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                                        /*print_r(' ----tue_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                    }
                                }
                            }

                            if($wed_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalSleeps < $cabin->wed_sleeps)) {
                                        $wed_sleeps_diff       = $cabin->wed_sleeps - $totalSleeps;

                                        /* Available sleeps */
                                        $wed_sleeps_avail      = ($wed_sleeps_diff >= 0) ? $wed_sleeps_diff : 0;

                                        if($request->sleeps <= $wed_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }
                                        /*print_r(' ----wed_regular_data---- ');
                                        print_r(' wed_dates: ' . $dates . ' wed_sleeps_avail: '. $wed_sleeps_avail);
                                        print_r(' wed_sleeps_filled = '. $wed_sleeps_filled .' wed_cabin_sleeps_total = '. $cabin->wed_sleeps .' result(wed_sleeps_filled / wed_cabin_sleeps) * 100 = '. $wed_sleeps_percentage);*/
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----wed_regular_data---- ');
                                       print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));

                                    }
                                }
                            }

                            if($thu_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalSleeps < $cabin->thu_sleeps)) {
                                        $thu_sleeps_diff       = $cabin->thu_sleeps - $totalSleeps;

                                        /* Available sleeps */
                                        $thu_sleeps_avail      = ($thu_sleeps_diff >= 0) ? $thu_sleeps_diff : 0;

                                        if($request->sleeps <= $thu_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }
                                        /*print_r(' ----thu_regular_data---- ');
                                        print_r(' thu_dates: ' . $dates . ' thu_sleeps_avail: '. $thu_sleeps_avail);
                                        print_r(' thu_sleeps_filled = '. $thu_sleeps_filled .' thu_cabin_sleeps_total = '. $cabin->thu_sleeps .' result(thu_sleeps_filled / thu_cabin_sleeps) * 100 = '. $thu_sleeps_percentage);*/
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----thu_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));

                                    }
                                }
                            }

                            if($fri_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalSleeps < $cabin->fri_sleeps)) {
                                        $fri_sleeps_diff       = $cabin->fri_sleeps - $totalSleeps;

                                        /* Available sleeps */
                                        $fri_sleeps_avail      = ($fri_sleeps_diff >= 0) ? $fri_sleeps_diff : 0;

                                        if($request->sleeps <= $fri_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }
                                        /*print_r(' ----fri_regular_data---- ');
                                        print_r(' fri_dates: ' . $dates . ' fri_sleeps_avail: '. $fri_sleeps_avail);
                                        print_r(' fri_sleeps_filled = '. $fri_sleeps_filled .' fri_cabin_sleeps_total = '. $cabin->fri_sleeps .' result(fri_sleeps_filled / fri_cabin_sleeps) * 100 = '. $fri_sleeps_percentage);*/
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----fri_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));

                                    }
                                }
                            }

                            if($sat_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalSleeps < $cabin->sat_sleeps)) {
                                        $sat_sleeps_diff       = $cabin->sat_sleeps - $totalSleeps;

                                        /* Available sleeps */
                                        $sat_sleeps_avail      = ($sat_sleeps_diff >= 0) ? $sat_sleeps_diff : 0;

                                        if($request->sleeps <= $sat_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }
                                        /*print_r(' ----sat_regular_data---- ');
                                        print_r(' sat_dates: ' . $dates . ' sat_sleeps_avail: '. $sat_sleeps_avail);
                                        print_r(' sat_sleeps_filled = '. $sat_sleeps_filled .' sat_cabin_sleeps_total = '. $cabin->sat_sleeps .' result(sat_sleeps_filled / sat_cabin_sleeps) * 100 = '. $sat_sleeps_percentage);*/
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----sat_regular_data---- ');
                                        print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));

                                    }
                                }
                            }

                            if($sun_day === $day) {

                                if(!in_array($dates, $dates_array)) {

                                    $dates_array[] = $dates;

                                    if(($totalSleeps < $cabin->sun_sleeps)) {
                                        $sun_sleeps_diff       = $cabin->sun_sleeps - $totalSleeps;

                                        /* Available sleeps */
                                        $sun_sleeps_avail      = ($sun_sleeps_diff >= 0) ? $sun_sleeps_diff : 0;

                                        if($request->sleeps <= $sun_sleeps_avail) {
                                            $availableStatus[] = 'available';
                                        }
                                        else {
                                            $availableStatus[] = 'notAvailable';
                                            //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                            return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                        }
                                        /*print_r(' ----sun_regular_data---- ');
                                        print_r(' sun_dates: ' . $dates . ' sun_sleeps_avail: '. $sun_sleeps_avail);
                                        print_r(' sun_sleeps_filled = '. $sun_sleeps_filled .' sun_cabin_sleeps_total = '. $cabin->sun_sleeps .' result(sun_sleeps_filled / sun_cabin_sleeps) * 100 = '. $sun_sleeps_percentage);*/
                                    }
                                    else {
                                        $availableStatus[] = 'notAvailable';
                                        /*print_r(' ----sun_regular_data---- ');
                                       print_r(' not_available_dates '. $dates);*/
                                        //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                        return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));

                                    }
                                }
                            }
                        }

                        /* Calculating beds & dorms for normal */
                        if(!in_array($dates, $dates_array)) {

                            if(($totalSleeps < $cabin->sleeps)) {
                                $normal_sleeps_diff       = $cabin->sleeps - $totalSleeps;

                                /* Available sleeps */
                                $normal_sleeps_avail      = ($normal_sleeps_diff >= 0) ? $normal_sleeps_diff : 0;

                                if($request->sleeps <= $normal_sleeps_avail) {
                                    $availableStatus[] = 'available';
                                }
                                else {
                                    $availableStatus[] = 'notAvailable';
                                    //return response()->json(['error' => 'Sleeps not available on '.$generateBookingDate->format("jS F")], 422);
                                    return redirect()->back()->with('error', $request->sleeps.' sleeps are not available on '.$generateBookingDate->format("jS F"). ' try other digits');
                                }

                                /*print_r(' ----normal_regular_data---- ');
                                print_r(' normal_dates: ' . $dates . ' normal_sleeps_avail: '. $normal_sleeps_avail);
                                print_r(' normal_sleeps_filled = '. $normal_sleeps_filled .' normal_cabin_sleeps_total = '. $cabin->sleeps .' result(normal_sleeps_filled / normal_cabin_sleeps) * 100 = '. $normal_sleeps_percentage);*/
                            }
                            else {
                                $availableStatus[] = 'notAvailable';
                                /*print_r(' ----normal_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
                                //return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format("jS F")], 422);
                                return redirect()->back()->with('error', 'Sorry! Rooms are already filled on '.$generateBookingDate->format("jS F"));
                            }

                        }

                    }

                    /* Checking bookings available ends */
                }

                if(!in_array('notAvailable', $availableStatus)) {
                    $available = 'success';

                    // store user data
                    // store booking
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

                }
            }
            else {
                return response()->json(['error' => 'Quota exceeded! Maximum 60 days you can book'], 422);
            }
        }
        else {
            return response()->json(['error' => 'Arrival date should be less than departure date.'], 422);
        }

        return response()->json(['response' => $available]);

        //return redirect()->route('booking.history');
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
