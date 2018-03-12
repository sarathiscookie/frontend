<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Season;
use App\Cabin;
use App\Booking;
use App\MountSchoolBooking;
use DateTime;
use DatePeriod;
use DateInterval;

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
        return view('cart');
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
        $messages = [
            'dateFrom' => 'Arrival date is required',
            'dateTo'  => 'Departure date is required',
            'persons'  => 'No of persons required',
        ];

        $rules    = [
            'dateFrom'  => 'required',
            'dateTo'  => 'required',
            'persons'  => 'required|not_in:0',
        ];

        Validator::make($request->all(), $rules, $messages)->validate();

        $monthBegin              = DateTime::createFromFormat('d.m.y', $request->dateFrom)->format('Y-m-d');
        $monthEnd                = DateTime::createFromFormat('d.m.y', $request->dateTo)->format('Y-m-d');
        $dateDifference          = date_diff(date_create($monthBegin), date_create($monthEnd));

        if($monthBegin < $monthEnd) {
            if($dateDifference->format("%a") <= 60) {
                $holiday_prepare         = [];
                $holidayDates            = [];
                $not_regular_dates       = [];
                $dates_array             = [];
                $available_dates         = [];
                $not_available_dates     = [];
                $orangeDates             = [];
                $not_season_time         = [];

                $seasons                 = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($request->cabin))->get();

                $cabin                   = Cabin::findOrFail($request->cabin);

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
                            return response()->json(['error' => 'Sorry selected dates are not in a season time.'], 422);
                        }

                        $prepareArray       = [$dates => $day];
                        $array_unique       = array_unique($holiday_prepare);
                        $array_intersect    = array_intersect($prepareArray, $array_unique);

                        foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                            if($monthBegin === $array_intersect_key) {
                                return response()->json(['error' => $array_intersect_values.' is a holiday.'], 422);
                            }

                            if((strtotime($array_intersect_key) > strtotime($monthBegin)) && (strtotime($array_intersect_key) < strtotime($monthEnd))) {
                                return response()->json(['error' => 'Booking not possible because holidays included.'], 422);
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

                    /* Getting bookings from booking collection status is 1=>Fix, 4=>Request, 7=>Inquiry */
                    $bookings  = Booking::select('beds', 'dormitory', 'sleeps')
                        ->where('is_delete', 0)
                        ->where('cabinname', $cabin->name)
                        ->whereIn('status', ['1', '4', '7'])
                        ->whereRaw(['checkin_from' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                        ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                        ->get();

                    /* Getting bookings from mschool collection status is 1=>Fix, 4=>Request, 7=>Inquiry */
                    $msBookings  = MountSchoolBooking::select('beds', 'dormitory', 'sleeps')
                        ->where('is_delete', 0)
                        ->where('cabin_name', $cabin->name)
                        ->whereIn('status', ['1', '4', '7'])
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
                    /* >= 75% are booked Orange, 100% is red, < 75% are green*/
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

                                    /* Available beds and dorms */
                                    $not_regular_bed_dorms_available    = $not_regular_beds_avail + $not_regular_dorms_avail;

                                    if($request->persons < $cabin->not_regular_inquiry_guest) {
                                        if($request->persons <= $not_regular_bed_dorms_available) {
                                            // Query to store details in to cart
                                        }
                                        else {
                                            return response()->json(['error' => 'Rooms not available on '.$generateBookingDate->format('jS F')], 422);
                                        }
                                    }
                                    else {
                                        return response()->json(['error' => 'No of persons reached maximum '.$cabin->not_regular_inquiry_guest.' for this cabin. Please send an inquiry'], 422);
                                    }

                                    /*print_r(' ----not_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' not_rgl_dates: ' . $dates . ' not_regular_beds_diff: '. $not_regular_beds_diff. ' not_regular_beds_avail: '. $not_regular_beds_avail);
                                    print_r( ' not_rgl_dates: ' . $dates . ' not_regular_dorms_diff: '. $not_regular_dorms_diff. ' not_regular_dorms_avail: '. $not_regular_dorms_avail);
                                    print_r( ' not_regular_sum: ' . $not_regular_bed_dorms_available);*/

                                }
                                else {
                                    /*print_r(' ----not_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                    return response()->json(['error' => 'Rooms are already filled on '.$generateBookingDate->format('jS F')], 422);
                                }
                            }
                        }
                    }

                    /* Checking bookings available ends */
                }
                //exit();
            }
            else {
                return response()->json(['error' => 'Quota exceeded! Maximum 60 days you can book'], 422);
            }
        }
        else {
            return response()->json(['error' => 'Arrival date should be less than departure date.'], 422);
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
