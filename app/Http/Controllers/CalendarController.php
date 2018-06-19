<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CalendarRequest;
use App\Season;
use App\Booking;
use App\MountSchoolBooking;
use App\Cabin;
use DateTime;
use DatePeriod;
use DateInterval;

class CalendarController extends Controller
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
        //
    }

    /**
     * Show the data when page initial loads.
     *
     * @param  string  $cabinId
     * @param  string  $checkingFrom
     * @return array
     */

    public function calendar($cabinId, $checkingFrom = null)
    {
        // $checkingFrom: In booking history edit booking section, If guest clicked on calendar we will pass previous voucher arrival year and month to generate and show whole month availability in calendar.
        // If there is no $checkingFrom, current month availability will show in calendar. $checkingFrom value is getting from booking history booking edit page. Other calendar listing page doesn't have $checkingFrom values.
        if($checkingFrom === null) {
            $monthBegin          = date("Y-m-d", strtotime(' +1 day'));
        }
        else{
            $monthBegin          = date("$checkingFrom-01");
        }

        $monthEnd                = date("Y-m-t 23:59:59", strtotime($monthBegin));

        $holiday_prepare         = [];
        $holidayDates            = [];
        $not_regular_dates       = [];
        $dates_array             = [];
        $available_dates         = [];
        $not_available_dates     = [];
        $orangeDates             = [];
        $not_season_time         = [];

        $seasons                 = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($cabinId))->get();

        $cabin                   = Cabin::where('is_delete', 0)
            ->where('other_cabin', "0")
            ->findOrFail($cabinId);

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
                    $not_season_time[] = $dates;
                }

                $prepareArray       = [$dates => $day];
                $array_unique       = array_unique($holiday_prepare);
                $array_intersect    = array_intersect($prepareArray, $array_unique);

                foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                    $holidayDates[] = $array_intersect_key;
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

                            /* Sum of not regular cabins beds and dorms */
                            $not_regular_cabin_beds_dorms_total = $cabin->not_regular_beds + $cabin->not_regular_dorms;

                            /* Already Filled beds and dorms */
                            $not_regular_bed_dorms_filled       = $not_regular_cabin_beds_dorms_total - $not_regular_bed_dorms_available;

                            /* Percentage calculation */
                            $not_regular_percentage             = ($not_regular_bed_dorms_filled / $not_regular_cabin_beds_dorms_total) * 100;

                            if($not_regular_percentage > 75) {
                                $orangeDates[]     = $dates;
                                //print_r(' orange_dates '. $dates);
                            }
                            else {
                                $available_dates[] = $dates;
                                //print_r(' available_dates '. $dates);
                            }
                            /*print_r(' ----not_regular_data---- ');
                            print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                            print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                            print_r(' not_rgl_dates: ' . $dates . ' not_regular_beds_diff: '. $not_regular_beds_diff. ' not_regular_beds_avail: '. $not_regular_beds_avail);
                            print_r( ' not_rgl_dates: ' . $dates . ' not_regular_dorms_diff: '. $not_regular_dorms_diff. ' not_regular_dorms_avail: '. $not_regular_dorms_avail);
                            print_r( ' not_regular_sum: ' . $not_regular_bed_dorms_available);
                            print_r(' not_regular_bed_dorms_filled = '. $not_regular_bed_dorms_filled .' not_regular_cabin_beds_dorms_total = '. $not_regular_cabin_beds_dorms_total .' result(not_regular_bed_dorms_filled / not_regular_cabin_beds_dorms_total) * 100 = '. $not_regular_percentage);*/
                        }
                        else {
                            $not_available_dates[] = $dates;
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

                            if(($totalBeds < $cabin->mon_beds) || ($totalDorms < $cabin->mon_dorms)) {
                                $mon_beds_diff              = $cabin->mon_beds - $totalBeds;
                                $mon_dorms_diff             = $cabin->mon_dorms - $totalDorms;

                                $mon_beds_avail             = ($mon_beds_diff >= 0) ? $mon_beds_diff : 0;
                                $mon_dorms_avail            = ($mon_dorms_diff >= 0) ? $mon_dorms_diff : 0;

                                /* Available beds and dorms */
                                $mon_bed_dorms_available    = $mon_beds_avail + $mon_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $mon_cabin_beds_dorms_total = $cabin->mon_beds + $cabin->mon_dorms;

                                /* Already Filled beds and dorms */
                                $mon_bed_dorms_filled       = $mon_cabin_beds_dorms_total - $mon_bed_dorms_available;

                                /* Percentage calculation */
                                $mon_percentage             = ($mon_bed_dorms_filled / $mon_cabin_beds_dorms_total) * 100;

                                if($mon_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                /*print_r(' ----mon_regular_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' mon_dates: ' . $dates . ' mon_beds_diff: '. $mon_beds_diff. ' mon_beds_avail: '. $mon_beds_avail);
                                print_r( ' mon_dates: ' . $dates . ' mon_dorms_diff: '. $mon_dorms_diff. ' mon_dorms_avail: '. $mon_dorms_avail);
                                print_r( ' mon_sum: ' . $mon_bed_dorms_available);
                                print_r(' mon_bed_dorms_filled = '. $mon_bed_dorms_filled .' mon_cabin_beds_dorms_total = '. $mon_cabin_beds_dorms_total .' result(mon_bed_dorms_filled / mon_cabin_beds_dorms_total) * 100 = '. $mon_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----mon_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
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

                                /* Available beds and dorms */
                                $tue_bed_dorms_available    = $tue_beds_avail + $tue_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $tue_cabin_beds_dorms_total = $cabin->tue_beds + $cabin->tue_dorms;

                                /* Already Filled beds and dorms */
                                $tue_bed_dorms_filled       = $tue_cabin_beds_dorms_total - $tue_bed_dorms_available;

                                /* Percentage calculation */
                                $tue_percentage             = ($tue_bed_dorms_filled / $tue_cabin_beds_dorms_total) * 100;

                                if($tue_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                /*print_r(' ----tue_regular_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' tue_dates: ' . $dates . ' tue_beds_diff: '. $tue_beds_diff. ' tue_beds_avail: '. $tue_beds_avail);
                                print_r( ' tue_dates: ' . $dates . ' tue_dorms_diff: '. $tue_dorms_diff. ' tue_dorms_avail: '. $tue_dorms_avail);
                                print_r( ' tue_sum: ' . $tue_bed_dorms_available);
                                print_r(' tue_bed_dorms_filled = '. $tue_bed_dorms_filled .' tue_cabin_beds_dorms_total = '. $tue_cabin_beds_dorms_total .' result(tue_bed_dorms_filled / tue_cabin_beds_dorms_total) * 100 = '. $tue_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----tue_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
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

                                /* Available beds and dorms */
                                $wed_bed_dorms_available    = $wed_beds_avail + $wed_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $wed_cabin_beds_dorms_total = $cabin->wed_beds + $cabin->wed_dorms;

                                /* Already Filled beds and dorms */
                                $wed_bed_dorms_filled       = $wed_cabin_beds_dorms_total - $wed_bed_dorms_available;

                                /* Percentage calculation */
                                $wed_percentage             = ($wed_bed_dorms_filled / $wed_cabin_beds_dorms_total) * 100;

                                if($wed_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                /*print_r(' ----wed_regular_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' wed_dates: ' . $dates . ' wed_beds_diff: '. $wed_beds_diff. ' wed_beds_avail: '. $wed_beds_avail);
                                print_r( ' wed_dates: ' . $dates . ' wed_dorms_diff: '. $wed_dorms_diff. ' wed_dorms_avail: '. $wed_dorms_avail);
                                print_r( ' wed_sum: ' . $wed_bed_dorms_available);
                                print_r(' wed_bed_dorms_filled = '. $wed_bed_dorms_filled .' wed_cabin_beds_dorms_total = '. $wed_cabin_beds_dorms_total .' result(wed_bed_dorms_filled / wed_cabin_beds_dorms_total) * 100 = '. $wed_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----wed_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
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

                                /* Available beds and dorms */
                                $thu_bed_dorms_available    = $thu_beds_avail + $thu_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $thu_cabin_beds_dorms_total = $cabin->thu_beds + $cabin->thu_dorms;

                                /* Already Filled beds and dorms */
                                $thu_bed_dorms_filled       = $thu_cabin_beds_dorms_total - $thu_bed_dorms_available;

                                /* Percentage calculation */
                                $thu_percentage             = ($thu_bed_dorms_filled / $thu_cabin_beds_dorms_total) * 100;

                                if($thu_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                /*print_r(' ----thu_regular_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' thu_dates: ' . $dates . ' thu_beds_diff: '. $thu_beds_diff. ' thu_beds_avail: '. $thu_beds_avail);
                                print_r( ' thu_dates: ' . $dates . ' thu_dorms_diff: '. $thu_dorms_diff. ' thu_dorms_avail: '. $thu_dorms_avail);
                                print_r( ' thu_sum: ' . $thu_bed_dorms_available);
                                print_r(' thu_bed_dorms_filled = '. $thu_bed_dorms_filled .' thu_cabin_beds_dorms_total = '. $thu_cabin_beds_dorms_total .' result(thu_bed_dorms_filled / thu_cabin_beds_dorms_total) * 100 = '. $thu_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----thu_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
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

                                /* Available beds and dorms */
                                $fri_bed_dorms_available    = $fri_beds_avail + $fri_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $fri_cabin_beds_dorms_total = $cabin->fri_beds + $cabin->fri_dorms;

                                /* Already Filled beds and dorms */
                                $fri_bed_dorms_filled       = $fri_cabin_beds_dorms_total - $fri_bed_dorms_available;

                                /* Percentage calculation */
                                $fri_percentage             = ($fri_bed_dorms_filled / $fri_cabin_beds_dorms_total) * 100;

                                if($fri_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                /*print_r(' ----fri_regular_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' fri_dates: ' . $dates . ' fri_beds_diff: '. $fri_beds_diff. ' fri_beds_avail: '. $fri_beds_avail);
                                print_r( ' fri_dates: ' . $dates . ' fri_dorms_diff: '. $fri_dorms_diff. ' fri_dorms_avail: '. $fri_dorms_avail);
                                print_r( ' fri_sum: ' . $fri_bed_dorms_available);
                                print_r(' fri_bed_dorms_filled = '. $fri_bed_dorms_filled .' fri_cabin_beds_dorms_total = '. $fri_cabin_beds_dorms_total .' result(fri_bed_dorms_filled / fri_cabin_beds_dorms_total) * 100 = '. $fri_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
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

                                /* Available beds and dorms */
                                $sat_bed_dorms_available    = $sat_beds_avail + $sat_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $sat_cabin_beds_dorms_total = $cabin->sat_beds + $cabin->sat_dorms;

                                /* Already Filled beds and dorms */
                                $sat_bed_dorms_filled       = $sat_cabin_beds_dorms_total - $sat_bed_dorms_available;

                                /* Percentage calculation */
                                $sat_percentage             = ($sat_bed_dorms_filled / $sat_cabin_beds_dorms_total) * 100;

                                if($sat_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                /*print_r(' ----sat_regular_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' sat_dates: ' . $dates . ' sat_beds_diff: '. $sat_beds_diff. ' sat_beds_avail: '. $sat_beds_avail);
                                print_r( ' sat_dates: ' . $dates . ' sat_dorms_diff: '. $sat_dorms_diff. ' sat_dorms_avail: '. $sat_dorms_avail);
                                print_r( ' sat_sum: ' . $sat_bed_dorms_available);
                                print_r(' sat_bed_dorms_filled = '. $sat_bed_dorms_filled .' sat_cabin_beds_dorms_total = '. $sat_cabin_beds_dorms_total .' result(sat_bed_dorms_filled / sat_cabin_beds_dorms_total) * 100 = '. $sat_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
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

                                /* Available beds and dorms */
                                $sun_bed_dorms_available    = $sun_beds_avail + $sun_dorms_avail;

                                /* Sum of normal cabins beds and dorms */
                                $sun_cabin_beds_dorms_total = $cabin->sun_beds + $cabin->sun_dorms;

                                /* Already Filled beds and dorms */
                                $sun_bed_dorms_filled       = $sun_cabin_beds_dorms_total - $sun_bed_dorms_available;

                                /* Percentage calculation */
                                $sun_percentage             = ($sun_bed_dorms_filled / $sun_cabin_beds_dorms_total) * 100;

                                if($sun_percentage > 75) {
                                    $orangeDates[]          = $dates;
                                }
                                else {
                                    $available_dates[]      = $dates;
                                }
                                /*print_r(' ----sun_regular_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' sun_dates: ' . $dates . ' sun_beds_diff: '. $sun_beds_diff. ' sun_beds_avail: '. $sun_beds_avail);
                                print_r( ' sun_dates: ' . $dates . ' sun_dorms_diff: '. $sun_dorms_diff. ' sun_dorms_avail: '. $sun_dorms_avail);
                                print_r( ' sun_sum: ' . $sun_bed_dorms_available);
                                print_r(' sun_bed_dorms_filled = '. $sun_bed_dorms_filled .' sun_cabin_beds_dorms_total = '. $sun_cabin_beds_dorms_total .' result(sun_bed_dorms_filled / sun_cabin_beds_dorms_total) * 100 = '. $sun_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
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

                        /* Available beds and dorms */
                        $normal_bed_dorms_available    = $normal_beds_avail + $normal_dorms_avail;

                        /* Sum of normal cabins beds and dorms */
                        $normal_cabin_beds_dorms_total = $cabin->beds + $cabin->dormitory;

                        /* Already Filled beds and dorms */
                        $normal_bed_dorms_filled       = $normal_cabin_beds_dorms_total - $normal_bed_dorms_available;

                        /* Percentage calculation */
                        $normal_percentage             = ($normal_bed_dorms_filled / $normal_cabin_beds_dorms_total) * 100;

                        if($normal_percentage > 75) {
                            $orangeDates[]     = $dates;
                        }
                        else {
                            $available_dates[] = $dates;
                        }
                        /*print_r(' ----normal_data---- ');
                        print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                        print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                        print_r(' normal_dates: ' . $dates . ' normal_beds_diff: '. $normal_beds_diff. ' normal_beds_avail: '. $normal_beds_avail);
                        print_r( ' normal_dates: ' . $dates . ' normal_dorms_diff: '. $normal_dorms_diff. ' normal_dorms_avail: '. $normal_dorms_avail);
                        print_r( ' normal_sum: ' . $normal_bed_dorms_available);
                        print_r(' normal_bed_dorms_filled = '. $normal_bed_dorms_filled .' normal_cabin_beds_dorms_total = '. $normal_cabin_beds_dorms_total .' result(normal_bed_dorms_filled / normal_cabin_beds_dorms_total) * 100 = '. $normal_percentage);*/

                    }
                    else {
                        $not_available_dates[] = $dates;
                        /*print_r(' ----normal_data---- ');
                        print_r(' not_available_dates '. $dates);*/
                    }
                }
            }
            else {
                $totalSleeps = $sleeps + $msSleeps;

                /*print_r(' totalSleeps '. $totalSleeps);
                print_r(' sleeps '. $sleeps .' msSleeps '. $msSleeps);*/

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

                            /* Available beds and dorms */
                            $not_regular_sleeps_avail      = ($not_regular_sleeps_diff >= 0) ? $not_regular_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $not_regular_sleeps_filled     = $cabin->not_regular_sleeps - $not_regular_sleeps_avail;

                            /* Percentage calculation */
                            $not_regular_sleeps_percentage = ($not_regular_sleeps_filled / $cabin->not_regular_sleeps) * 100;

                            if($not_regular_sleeps_percentage > 75) {
                                $orangeDates[]     = $dates;
                                //print_r(' orange_dates '. $dates);
                            }
                            else {
                                $available_dates[] = $dates;
                                //print_r(' available_dates '. $dates);
                            }
                            /*print_r(' ----not_regular_data---- ');
                            print_r(' not_rgl_dates: ' . $dates . ' not_regular_sleeps_avail: '. $not_regular_sleeps_avail);
                            print_r(' not_regular_sleeps_filled = '. $not_regular_sleeps_filled .' not_regular_cabin_sleeps_total = '. $cabin->not_regular_sleeps .' result(not_regular_sleeps_filled / not_regular_cabin_sleeps) * 100 = '. $not_regular_sleeps_percentage);*/
                        }
                        else {
                            $not_available_dates[] = $dates;
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

                                /* Available beds and dorms */
                                $mon_sleeps_avail      = ($mon_sleeps_diff >= 0) ? $mon_sleeps_diff : 0;

                                /* Already Filled beds and dorms */
                                $mon_sleeps_filled     = $cabin->mon_sleeps - $mon_sleeps_avail;

                                /* Percentage calculation */
                                $mon_sleeps_percentage = ($mon_sleeps_filled / $cabin->mon_sleeps) * 100;

                                if($mon_sleeps_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                }
                                else {
                                    $available_dates[] = $dates;
                                }
                                /*print_r(' ----mon_regular_data---- ');
                                print_r(' mon_dates: ' . $dates . ' mon_sleeps_avail: '. $mon_sleeps_avail);
                                print_r(' mon_sleeps_filled = '. $mon_sleeps_filled .' mon_cabin_sleeps_total = '. $cabin->mon_sleeps .' result(mon_sleeps_filled / mon_cabin_sleeps) * 100 = '. $mon_sleeps_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----mon_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
                            }
                        }
                    }

                    if($tue_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalSleeps < $cabin->tue_sleeps)) {
                                $tue_sleeps_diff       = $cabin->tue_sleeps - $totalSleeps;

                                /* Available beds and dorms */
                                $tue_sleeps_avail      = ($tue_sleeps_diff >= 0) ? $tue_sleeps_diff : 0;

                                /* Already Filled beds and dorms */
                                $tue_sleeps_filled     = $cabin->tue_sleeps - $tue_sleeps_avail;

                                /* Percentage calculation */
                                $tue_sleeps_percentage = ($tue_sleeps_filled / $cabin->tue_sleeps) * 100;

                                if($tue_sleeps_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                }
                                else {
                                    $available_dates[] = $dates;
                                }
                                /*print_r(' ----tue_regular_data---- ');
                                print_r(' tue_dates: ' . $dates . ' tue_sleeps_avail: '. $tue_sleeps_avail);
                                print_r(' tue_sleeps_filled = '. $tue_sleeps_filled .' tue_cabin_sleeps_total = '. $cabin->tue_sleeps .' result(tue_sleeps_filled / tue_cabin_sleeps) * 100 = '. $tue_sleeps_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
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

                                /* Available beds and dorms */
                                $wed_sleeps_avail      = ($wed_sleeps_diff >= 0) ? $wed_sleeps_diff : 0;

                                /* Already Filled beds and dorms */
                                $wed_sleeps_filled     = $cabin->wed_sleeps - $wed_sleeps_avail;

                                /* Percentage calculation */
                                $wed_sleeps_percentage = ($wed_sleeps_filled / $cabin->wed_sleeps) * 100;

                                if($wed_sleeps_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                }
                                else {
                                    $available_dates[] = $dates;
                                }
                                /*print_r(' ----wed_regular_data---- ');
                                print_r(' wed_dates: ' . $dates . ' wed_sleeps_avail: '. $wed_sleeps_avail);
                                print_r(' wed_sleeps_filled = '. $wed_sleeps_filled .' wed_cabin_sleeps_total = '. $cabin->wed_sleeps .' result(wed_sleeps_filled / wed_cabin_sleeps) * 100 = '. $wed_sleeps_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----wed_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
                            }
                        }
                    }

                    if($thu_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalSleeps < $cabin->thu_sleeps)) {
                                $thu_sleeps_diff       = $cabin->thu_sleeps - $totalSleeps;

                                /* Available beds and dorms */
                                $thu_sleeps_avail      = ($thu_sleeps_diff >= 0) ? $thu_sleeps_diff : 0;

                                /* Already Filled beds and dorms */
                                $thu_sleeps_filled     = $cabin->thu_sleeps - $thu_sleeps_avail;

                                /* Percentage calculation */
                                $thu_sleeps_percentage = ($thu_sleeps_filled / $cabin->thu_sleeps) * 100;

                                if($thu_sleeps_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                }
                                else {
                                    $available_dates[] = $dates;
                                }
                                /*print_r(' ----thu_regular_data---- ');
                                print_r(' thu_dates: ' . $dates . ' thu_sleeps_avail: '. $thu_sleeps_avail);
                                print_r(' thu_sleeps_filled = '. $thu_sleeps_filled .' thu_cabin_sleeps_total = '. $cabin->thu_sleeps .' result(thu_sleeps_filled / thu_cabin_sleeps) * 100 = '. $thu_sleeps_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----thu_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
                            }
                        }
                    }

                    if($fri_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalSleeps < $cabin->fri_sleeps)) {
                                $fri_sleeps_diff       = $cabin->fri_sleeps - $totalSleeps;

                                /* Available beds and dorms */
                                $fri_sleeps_avail      = ($fri_sleeps_diff >= 0) ? $fri_sleeps_diff : 0;

                                /* Already Filled beds and dorms */
                                $fri_sleeps_filled     = $cabin->fri_sleeps - $fri_sleeps_avail;

                                /* Percentage calculation */
                                $fri_sleeps_percentage = ($fri_sleeps_filled / $cabin->fri_sleeps) * 100;

                                if($fri_sleeps_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                }
                                else {
                                    $available_dates[] = $dates;
                                }
                                /*print_r(' ----fri_regular_data---- ');
                                print_r(' fri_dates: ' . $dates . ' fri_sleeps_avail: '. $fri_sleeps_avail);
                                print_r(' fri_sleeps_filled = '. $fri_sleeps_filled .' fri_cabin_sleeps_total = '. $cabin->fri_sleeps .' result(fri_sleeps_filled / fri_cabin_sleeps) * 100 = '. $fri_sleeps_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----fri_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
                            }
                        }
                    }

                    if($sat_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalSleeps < $cabin->sat_sleeps)) {
                                $sat_sleeps_diff       = $cabin->sat_sleeps - $totalSleeps;

                                /* Available beds and dorms */
                                $sat_sleeps_avail      = ($sat_sleeps_diff >= 0) ? $sat_sleeps_diff : 0;

                                /* Already Filled beds and dorms */
                                $sat_sleeps_filled     = $cabin->sat_sleeps - $sat_sleeps_avail;

                                /* Percentage calculation */
                                $sat_sleeps_percentage = ($sat_sleeps_filled / $cabin->sat_sleeps) * 100;

                                if($sat_sleeps_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                }
                                else {
                                    $available_dates[] = $dates;
                                }
                                /*print_r(' ----sat_regular_data---- ');
                                print_r(' sat_dates: ' . $dates . ' sat_sleeps_avail: '. $sat_sleeps_avail);
                                print_r(' sat_sleeps_filled = '. $sat_sleeps_filled .' sat_cabin_sleeps_total = '. $cabin->sat_sleeps .' result(sat_sleeps_filled / sat_cabin_sleeps) * 100 = '. $sat_sleeps_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----sat_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
                            }
                        }
                    }

                    if($sun_day === $day) {

                        if(!in_array($dates, $dates_array)) {

                            $dates_array[] = $dates;

                            if(($totalSleeps < $cabin->sun_sleeps)) {
                                $sun_sleeps_diff       = $cabin->sun_sleeps - $totalSleeps;

                                /* Available beds and dorms */
                                $sun_sleeps_avail      = ($sun_sleeps_diff >= 0) ? $sun_sleeps_diff : 0;

                                /* Already Filled beds and dorms */
                                $sun_sleeps_filled     = $cabin->sun_sleeps - $sun_sleeps_avail;

                                /* Percentage calculation */
                                $sun_sleeps_percentage = ($sun_sleeps_filled / $cabin->sun_sleeps) * 100;

                                if($sun_sleeps_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                }
                                else {
                                    $available_dates[] = $dates;
                                }
                                /*print_r(' ----sun_regular_data---- ');
                                print_r(' sun_dates: ' . $dates . ' sun_sleeps_avail: '. $sun_sleeps_avail);
                                print_r(' sun_sleeps_filled = '. $sun_sleeps_filled .' sun_cabin_sleeps_total = '. $cabin->sun_sleeps .' result(sun_sleeps_filled / sun_cabin_sleeps) * 100 = '. $sun_sleeps_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
                                /*print_r(' ----sun_regular_data---- ');
                                print_r(' not_available_dates '. $dates);*/
                            }
                        }
                    }
                }

                /* Calculating beds & dorms for normal */
                if(!in_array($dates, $dates_array)) {

                    if(($totalSleeps < $cabin->sleeps)) {
                        $normal_sleeps_diff       = $cabin->sleeps - $totalSleeps;

                        /* Available beds and dorms */
                        $normal_sleeps_avail      = ($normal_sleeps_diff >= 0) ? $normal_sleeps_diff : 0;

                        /* Already Filled beds and dorms */
                        $normal_sleeps_filled     = $cabin->sleeps - $normal_sleeps_avail;

                        /* Percentage calculation */
                        $normal_sleeps_percentage = ($normal_sleeps_filled / $cabin->sleeps) * 100;

                        if($normal_sleeps_percentage > 75) {
                            $orangeDates[]     = $dates;
                        }
                        else {
                            $available_dates[] = $dates;
                        }
                        /*print_r(' ----normal_regular_data---- ');
                        print_r(' normal_dates: ' . $dates . ' normal_sleeps_avail: '. $normal_sleeps_avail);
                        print_r(' normal_sleeps_filled = '. $normal_sleeps_filled .' normal_cabin_sleeps_total = '. $cabin->sleeps .' result(normal_sleeps_filled / normal_cabin_sleeps) * 100 = '. $normal_sleeps_percentage);*/
                    }
                    else {
                        $not_available_dates[] = $dates;
                        /*print_r(' ----normal_regular_data---- ');
                        print_r(' not_available_dates '. $dates);*/
                    }

                }

            }
            /* Checking bookings available ends */
        }

        return array(json_encode($holidayDates), json_encode($available_dates), json_encode($orangeDates), json_encode($not_available_dates), json_encode($not_season_time));
    }

    /**
     * Search available data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function calendarAvailability(Request $request)
    {
        if($request->dateFrom != '') {

            $holiday_prepare         = [];
            $holidayDates            = [];
            $not_regular_dates       = [];
            $dates_array             = [];
            $available_dates         = [];
            $not_available_dates     = [];
            $orangeDates             = [];
            $not_season_time         = [];

            $monthBegin              = $request->dateFrom;

            $monthEnd                = date('Y-m-t 23:59:59', strtotime($request->dateFrom));

            $seasons                 = Season::where('cabin_id', new \MongoDB\BSON\ObjectID($request->dataId))->get();

            $cabin                   = Cabin::where('is_delete', 0)
                ->where('other_cabin', "0")
                ->findOrFail($request->dataId);

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
                        $not_season_time[] = $dates;
                    }

                    $prepareArray       = [$dates => $day];
                    $array_unique       = array_unique($holiday_prepare);
                    $array_intersect    = array_intersect($prepareArray, $array_unique);

                    foreach ($array_intersect as $array_intersect_key => $array_intersect_values) {
                        $holidayDates[] = $array_intersect_key;
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

                /* Getting bookings from booking collection status is 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
                $bookings  = Booking::select('beds', 'dormitory', 'sleeps')
                    ->where('is_delete', 0)
                    ->where('cabinname', $cabin->name)
                    ->whereIn('status', ['1', '4', '7', '8'])
                    ->whereRaw(['checkin_from' => array('$lte' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                    ->whereRaw(['reserve_to' => array('$gt' => $this->getDateUtc($generateBookingDate->format('d.m.y')))])
                    ->get();

                /* Getting bookings from mschool collection status is 1=> Fix, 2=> Cancel, 3=> Completed, 4=> Request (Reservation), 5=> Waiting for payment, 6=> Expired, 7=> Inquiry, 8=> Cart */
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

                                /* Sum of not regular cabins beds and dorms */
                                $not_regular_cabin_beds_dorms_total = $cabin->not_regular_beds + $cabin->not_regular_dorms;

                                /* Already Filled beds and dorms */
                                $not_regular_bed_dorms_filled       = $not_regular_cabin_beds_dorms_total - $not_regular_bed_dorms_available;

                                /* Percentage calculation */
                                $not_regular_percentage             = ($not_regular_bed_dorms_filled / $not_regular_cabin_beds_dorms_total) * 100;

                                if($not_regular_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                    //print_r(' orange_dates '. $dates);
                                }
                                else {
                                    $available_dates[] = $dates;
                                    //print_r(' available_dates '. $dates);
                                }
                                /*print_r(' ----not_regular_data---- ');
                                print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                print_r(' not_rgl_dates: ' . $dates . ' not_regular_beds_diff: '. $not_regular_beds_diff. ' not_regular_beds_avail: '. $not_regular_beds_avail);
                                print_r( ' not_rgl_dates: ' . $dates . ' not_regular_dorms_diff: '. $not_regular_dorms_diff. ' not_regular_dorms_avail: '. $not_regular_dorms_avail);
                                print_r( ' not_regular_sum: ' . $not_regular_bed_dorms_available);
                                print_r(' not_regular_bed_dorms_filled = '. $not_regular_bed_dorms_filled .' not_regular_cabin_beds_dorms_total = '. $not_regular_cabin_beds_dorms_total .' result(not_regular_bed_dorms_filled / not_regular_cabin_beds_dorms_total) * 100 = '. $not_regular_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
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

                                if(($totalBeds < $cabin->mon_beds) || ($totalDorms < $cabin->mon_dorms)) {
                                    $mon_beds_diff              = $cabin->mon_beds - $totalBeds;
                                    $mon_dorms_diff             = $cabin->mon_dorms - $totalDorms;

                                    $mon_beds_avail             = ($mon_beds_diff >= 0) ? $mon_beds_diff : 0;
                                    $mon_dorms_avail            = ($mon_dorms_diff >= 0) ? $mon_dorms_diff : 0;

                                    /* Available beds and dorms */
                                    $mon_bed_dorms_available    = $mon_beds_avail + $mon_dorms_avail;

                                    /* Sum of normal cabins beds and dorms */
                                    $mon_cabin_beds_dorms_total = $cabin->mon_beds + $cabin->mon_dorms;

                                    /* Already Filled beds and dorms */
                                    $mon_bed_dorms_filled       = $mon_cabin_beds_dorms_total - $mon_bed_dorms_available;

                                    /* Percentage calculation */
                                    $mon_percentage             = ($mon_bed_dorms_filled / $mon_cabin_beds_dorms_total) * 100;

                                    if($mon_percentage > 75) {
                                        $orangeDates[]          = $dates;
                                    }
                                    else {
                                        $available_dates[]      = $dates;
                                    }
                                    /*print_r(' ----mon_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' mon_dates: ' . $dates . ' mon_beds_diff: '. $mon_beds_diff. ' mon_beds_avail: '. $mon_beds_avail);
                                    print_r( ' mon_dates: ' . $dates . ' mon_dorms_diff: '. $mon_dorms_diff. ' mon_dorms_avail: '. $mon_dorms_avail);
                                    print_r( ' mon_sum: ' . $mon_bed_dorms_available);
                                    print_r(' mon_bed_dorms_filled = '. $mon_bed_dorms_filled .' mon_cabin_beds_dorms_total = '. $mon_cabin_beds_dorms_total .' result(mon_bed_dorms_filled / mon_cabin_beds_dorms_total) * 100 = '. $mon_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----mon_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
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

                                    /* Available beds and dorms */
                                    $tue_bed_dorms_available    = $tue_beds_avail + $tue_dorms_avail;

                                    /* Sum of normal cabins beds and dorms */
                                    $tue_cabin_beds_dorms_total = $cabin->tue_beds + $cabin->tue_dorms;

                                    /* Already Filled beds and dorms */
                                    $tue_bed_dorms_filled       = $tue_cabin_beds_dorms_total - $tue_bed_dorms_available;

                                    /* Percentage calculation */
                                    $tue_percentage             = ($tue_bed_dorms_filled / $tue_cabin_beds_dorms_total) * 100;

                                    if($tue_percentage > 75) {
                                        $orangeDates[]          = $dates;
                                    }
                                    else {
                                        $available_dates[]      = $dates;
                                    }
                                    /*print_r(' ----tue_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' tue_dates: ' . $dates . ' tue_beds_diff: '. $tue_beds_diff. ' tue_beds_avail: '. $tue_beds_avail);
                                    print_r( ' tue_dates: ' . $dates . ' tue_dorms_diff: '. $tue_dorms_diff. ' tue_dorms_avail: '. $tue_dorms_avail);
                                    print_r( ' tue_sum: ' . $tue_bed_dorms_available);
                                    print_r(' tue_bed_dorms_filled = '. $tue_bed_dorms_filled .' tue_cabin_beds_dorms_total = '. $tue_cabin_beds_dorms_total .' result(tue_bed_dorms_filled / tue_cabin_beds_dorms_total) * 100 = '. $tue_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----tue_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
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

                                    /* Available beds and dorms */
                                    $wed_bed_dorms_available    = $wed_beds_avail + $wed_dorms_avail;

                                    /* Sum of normal cabins beds and dorms */
                                    $wed_cabin_beds_dorms_total = $cabin->wed_beds + $cabin->wed_dorms;

                                    /* Already Filled beds and dorms */
                                    $wed_bed_dorms_filled       = $wed_cabin_beds_dorms_total - $wed_bed_dorms_available;

                                    /* Percentage calculation */
                                    $wed_percentage             = ($wed_bed_dorms_filled / $wed_cabin_beds_dorms_total) * 100;

                                    if($wed_percentage > 75) {
                                        $orangeDates[]          = $dates;
                                    }
                                    else {
                                        $available_dates[]      = $dates;
                                    }
                                    /*print_r(' ----wed_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' wed_dates: ' . $dates . ' wed_beds_diff: '. $wed_beds_diff. ' wed_beds_avail: '. $wed_beds_avail);
                                    print_r( ' wed_dates: ' . $dates . ' wed_dorms_diff: '. $wed_dorms_diff. ' wed_dorms_avail: '. $wed_dorms_avail);
                                    print_r( ' wed_sum: ' . $wed_bed_dorms_available);
                                    print_r(' wed_bed_dorms_filled = '. $wed_bed_dorms_filled .' wed_cabin_beds_dorms_total = '. $wed_cabin_beds_dorms_total .' result(wed_bed_dorms_filled / wed_cabin_beds_dorms_total) * 100 = '. $wed_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----wed_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
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

                                    /* Available beds and dorms */
                                    $thu_bed_dorms_available    = $thu_beds_avail + $thu_dorms_avail;

                                    /* Sum of normal cabins beds and dorms */
                                    $thu_cabin_beds_dorms_total = $cabin->thu_beds + $cabin->thu_dorms;

                                    /* Already Filled beds and dorms */
                                    $thu_bed_dorms_filled       = $thu_cabin_beds_dorms_total - $thu_bed_dorms_available;

                                    /* Percentage calculation */
                                    $thu_percentage             = ($thu_bed_dorms_filled / $thu_cabin_beds_dorms_total) * 100;

                                    if($thu_percentage > 75) {
                                        $orangeDates[]          = $dates;
                                    }
                                    else {
                                        $available_dates[]      = $dates;
                                    }
                                    /*print_r(' ----thu_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' thu_dates: ' . $dates . ' thu_beds_diff: '. $thu_beds_diff. ' thu_beds_avail: '. $thu_beds_avail);
                                    print_r( ' thu_dates: ' . $dates . ' thu_dorms_diff: '. $thu_dorms_diff. ' thu_dorms_avail: '. $thu_dorms_avail);
                                    print_r( ' thu_sum: ' . $thu_bed_dorms_available);
                                    print_r(' thu_bed_dorms_filled = '. $thu_bed_dorms_filled .' thu_cabin_beds_dorms_total = '. $thu_cabin_beds_dorms_total .' result(thu_bed_dorms_filled / thu_cabin_beds_dorms_total) * 100 = '. $thu_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----thu_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
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

                                    /* Available beds and dorms */
                                    $fri_bed_dorms_available    = $fri_beds_avail + $fri_dorms_avail;

                                    /* Sum of normal cabins beds and dorms */
                                    $fri_cabin_beds_dorms_total = $cabin->fri_beds + $cabin->fri_dorms;

                                    /* Already Filled beds and dorms */
                                    $fri_bed_dorms_filled       = $fri_cabin_beds_dorms_total - $fri_bed_dorms_available;

                                    /* Percentage calculation */
                                    $fri_percentage             = ($fri_bed_dorms_filled / $fri_cabin_beds_dorms_total) * 100;

                                    if($fri_percentage > 75) {
                                        $orangeDates[]          = $dates;
                                    }
                                    else {
                                        $available_dates[]      = $dates;
                                    }
                                    /*print_r(' ----fri_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' fri_dates: ' . $dates . ' fri_beds_diff: '. $fri_beds_diff. ' fri_beds_avail: '. $fri_beds_avail);
                                    print_r( ' fri_dates: ' . $dates . ' fri_dorms_diff: '. $fri_dorms_diff. ' fri_dorms_avail: '. $fri_dorms_avail);
                                    print_r( ' fri_sum: ' . $fri_bed_dorms_available);
                                    print_r(' fri_bed_dorms_filled = '. $fri_bed_dorms_filled .' fri_cabin_beds_dorms_total = '. $fri_cabin_beds_dorms_total .' result(fri_bed_dorms_filled / fri_cabin_beds_dorms_total) * 100 = '. $fri_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
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

                                    /* Available beds and dorms */
                                    $sat_bed_dorms_available    = $sat_beds_avail + $sat_dorms_avail;

                                    /* Sum of normal cabins beds and dorms */
                                    $sat_cabin_beds_dorms_total = $cabin->sat_beds + $cabin->sat_dorms;

                                    /* Already Filled beds and dorms */
                                    $sat_bed_dorms_filled       = $sat_cabin_beds_dorms_total - $sat_bed_dorms_available;

                                    /* Percentage calculation */
                                    $sat_percentage             = ($sat_bed_dorms_filled / $sat_cabin_beds_dorms_total) * 100;

                                    if($sat_percentage > 75) {
                                        $orangeDates[]          = $dates;
                                    }
                                    else {
                                        $available_dates[]      = $dates;
                                    }
                                    /*print_r(' ----sat_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' sat_dates: ' . $dates . ' sat_beds_diff: '. $sat_beds_diff. ' sat_beds_avail: '. $sat_beds_avail);
                                    print_r( ' sat_dates: ' . $dates . ' sat_dorms_diff: '. $sat_dorms_diff. ' sat_dorms_avail: '. $sat_dorms_avail);
                                    print_r( ' sat_sum: ' . $sat_bed_dorms_available);
                                    print_r(' sat_bed_dorms_filled = '. $sat_bed_dorms_filled .' sat_cabin_beds_dorms_total = '. $sat_cabin_beds_dorms_total .' result(sat_bed_dorms_filled / sat_cabin_beds_dorms_total) * 100 = '. $sat_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
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

                                    /* Available beds and dorms */
                                    $sun_bed_dorms_available    = $sun_beds_avail + $sun_dorms_avail;

                                    /* Sum of normal cabins beds and dorms */
                                    $sun_cabin_beds_dorms_total = $cabin->sun_beds + $cabin->sun_dorms;

                                    /* Already Filled beds and dorms */
                                    $sun_bed_dorms_filled       = $sun_cabin_beds_dorms_total - $sun_bed_dorms_available;

                                    /* Percentage calculation */
                                    $sun_percentage             = ($sun_bed_dorms_filled / $sun_cabin_beds_dorms_total) * 100;

                                    if($sun_percentage > 75) {
                                        $orangeDates[]          = $dates;
                                    }
                                    else {
                                        $available_dates[]      = $dates;
                                    }
                                    /*print_r(' ----sun_regular_data---- ');
                                    print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                                    print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                                    print_r(' sun_dates: ' . $dates . ' sun_beds_diff: '. $sun_beds_diff. ' sun_beds_avail: '. $sun_beds_avail);
                                    print_r( ' sun_dates: ' . $dates . ' sun_dorms_diff: '. $sun_dorms_diff. ' sun_dorms_avail: '. $sun_dorms_avail);
                                    print_r( ' sun_sum: ' . $sun_bed_dorms_available);
                                    print_r(' sun_bed_dorms_filled = '. $sun_bed_dorms_filled .' sun_cabin_beds_dorms_total = '. $sun_cabin_beds_dorms_total .' result(sun_bed_dorms_filled / sun_cabin_beds_dorms_total) * 100 = '. $sun_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
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

                            /* Available beds and dorms */
                            $normal_bed_dorms_available    = $normal_beds_avail + $normal_dorms_avail;

                            /* Sum of normal cabins beds and dorms */
                            $normal_cabin_beds_dorms_total = $cabin->beds + $cabin->dormitory;

                            /* Already Filled beds and dorms */
                            $normal_bed_dorms_filled       = $normal_cabin_beds_dorms_total - $normal_bed_dorms_available;

                            /* Percentage calculation */
                            $normal_percentage             = ($normal_bed_dorms_filled / $normal_cabin_beds_dorms_total) * 100;

                            if($normal_percentage > 75) {
                                $orangeDates[]     = $dates;
                            }
                            else {
                                $available_dates[] = $dates;
                            }
                            /*print_r(' ----normal_data---- ');
                            print_r(' totalBeds '. $totalBeds .' totalDorms '. $totalDorms);
                            print_r(' beds '. $beds .' dorms '. $dorms .' msBeds '. $msBeds .' msDorms '. $msDorms);
                            print_r(' normal_dates: ' . $dates . ' normal_beds_diff: '. $normal_beds_diff. ' normal_beds_avail: '. $normal_beds_avail);
                            print_r( ' normal_dates: ' . $dates . ' normal_dorms_diff: '. $normal_dorms_diff. ' normal_dorms_avail: '. $normal_dorms_avail);
                            print_r( ' normal_sum: ' . $normal_bed_dorms_available);
                            print_r(' normal_bed_dorms_filled = '. $normal_bed_dorms_filled .' normal_cabin_beds_dorms_total = '. $normal_cabin_beds_dorms_total .' result(normal_bed_dorms_filled / normal_cabin_beds_dorms_total) * 100 = '. $normal_percentage);*/

                        }
                        else {
                            $not_available_dates[] = $dates;
                            /*print_r(' ----normal_data---- ');
                            print_r(' not_available_dates '. $dates);*/
                        }
                    }
                }
                else {
                    $totalSleeps = $sleeps + $msSleeps;

                    /*print_r(' totalSleeps '. $totalSleeps);
                    print_r(' sleeps '. $sleeps .' msSleeps '. $msSleeps);*/

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

                                /* Available beds and dorms */
                                $not_regular_sleeps_avail      = ($not_regular_sleeps_diff >= 0) ? $not_regular_sleeps_diff : 0;

                                /* Already Filled beds and dorms */
                                $not_regular_sleeps_filled     = $cabin->not_regular_sleeps - $not_regular_sleeps_avail;

                                /* Percentage calculation */
                                $not_regular_sleeps_percentage = ($not_regular_sleeps_filled / $cabin->not_regular_sleeps) * 100;

                                if($not_regular_sleeps_percentage > 75) {
                                    $orangeDates[]     = $dates;
                                    //print_r(' orange_dates '. $dates);
                                }
                                else {
                                    $available_dates[] = $dates;
                                    //print_r(' available_dates '. $dates);
                                }
                                /*print_r(' ----not_regular_data---- ');
                                print_r(' not_rgl_dates: ' . $dates . ' not_regular_sleeps_avail: '. $not_regular_sleeps_avail);
                                print_r(' not_regular_sleeps_filled = '. $not_regular_sleeps_filled .' not_regular_cabin_sleeps_total = '. $cabin->not_regular_sleeps .' result(not_regular_sleeps_filled / not_regular_cabin_sleeps) * 100 = '. $not_regular_sleeps_percentage);*/
                            }
                            else {
                                $not_available_dates[] = $dates;
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

                                    /* Available beds and dorms */
                                    $mon_sleeps_avail      = ($mon_sleeps_diff >= 0) ? $mon_sleeps_diff : 0;

                                    /* Already Filled beds and dorms */
                                    $mon_sleeps_filled     = $cabin->mon_sleeps - $mon_sleeps_avail;

                                    /* Percentage calculation */
                                    $mon_sleeps_percentage = ($mon_sleeps_filled / $cabin->mon_sleeps) * 100;

                                    if($mon_sleeps_percentage > 75) {
                                        $orangeDates[]     = $dates;
                                    }
                                    else {
                                        $available_dates[] = $dates;
                                    }
                                    /*print_r(' ----mon_regular_data---- ');
                                    print_r(' mon_dates: ' . $dates . ' mon_sleeps_avail: '. $mon_sleeps_avail);
                                    print_r(' mon_sleeps_filled = '. $mon_sleeps_filled .' mon_cabin_sleeps_total = '. $cabin->mon_sleeps .' result(mon_sleeps_filled / mon_cabin_sleeps) * 100 = '. $mon_sleeps_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----mon_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                }
                            }
                        }

                        if($tue_day === $day) {

                            if(!in_array($dates, $dates_array)) {

                                $dates_array[] = $dates;

                                if(($totalSleeps < $cabin->tue_sleeps)) {
                                    $tue_sleeps_diff       = $cabin->tue_sleeps - $totalSleeps;

                                    /* Available beds and dorms */
                                    $tue_sleeps_avail      = ($tue_sleeps_diff >= 0) ? $tue_sleeps_diff : 0;

                                    /* Already Filled beds and dorms */
                                    $tue_sleeps_filled     = $cabin->tue_sleeps - $tue_sleeps_avail;

                                    /* Percentage calculation */
                                    $tue_sleeps_percentage = ($tue_sleeps_filled / $cabin->tue_sleeps) * 100;

                                    if($tue_sleeps_percentage > 75) {
                                        $orangeDates[]     = $dates;
                                    }
                                    else {
                                        $available_dates[] = $dates;
                                    }
                                    /*print_r(' ----tue_regular_data---- ');
                                    print_r(' tue_dates: ' . $dates . ' tue_sleeps_avail: '. $tue_sleeps_avail);
                                    print_r(' tue_sleeps_filled = '. $tue_sleeps_filled .' tue_cabin_sleeps_total = '. $cabin->tue_sleeps .' result(tue_sleeps_filled / tue_cabin_sleeps) * 100 = '. $tue_sleeps_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
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

                                    /* Available beds and dorms */
                                    $wed_sleeps_avail      = ($wed_sleeps_diff >= 0) ? $wed_sleeps_diff : 0;

                                    /* Already Filled beds and dorms */
                                    $wed_sleeps_filled     = $cabin->wed_sleeps - $wed_sleeps_avail;

                                    /* Percentage calculation */
                                    $wed_sleeps_percentage = ($wed_sleeps_filled / $cabin->wed_sleeps) * 100;

                                    if($wed_sleeps_percentage > 75) {
                                        $orangeDates[]     = $dates;
                                    }
                                    else {
                                        $available_dates[] = $dates;
                                    }
                                    /*print_r(' ----wed_regular_data---- ');
                                    print_r(' wed_dates: ' . $dates . ' wed_sleeps_avail: '. $wed_sleeps_avail);
                                    print_r(' wed_sleeps_filled = '. $wed_sleeps_filled .' wed_cabin_sleeps_total = '. $cabin->wed_sleeps .' result(wed_sleeps_filled / wed_cabin_sleeps) * 100 = '. $wed_sleeps_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----wed_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                }
                            }
                        }

                        if($thu_day === $day) {

                            if(!in_array($dates, $dates_array)) {

                                $dates_array[] = $dates;

                                if(($totalSleeps < $cabin->thu_sleeps)) {
                                    $thu_sleeps_diff       = $cabin->thu_sleeps - $totalSleeps;

                                    /* Available beds and dorms */
                                    $thu_sleeps_avail      = ($thu_sleeps_diff >= 0) ? $thu_sleeps_diff : 0;

                                    /* Already Filled beds and dorms */
                                    $thu_sleeps_filled     = $cabin->thu_sleeps - $thu_sleeps_avail;

                                    /* Percentage calculation */
                                    $thu_sleeps_percentage = ($thu_sleeps_filled / $cabin->thu_sleeps) * 100;

                                    if($thu_sleeps_percentage > 75) {
                                        $orangeDates[]     = $dates;
                                    }
                                    else {
                                        $available_dates[] = $dates;
                                    }
                                    /*print_r(' ----thu_regular_data---- ');
                                    print_r(' thu_dates: ' . $dates . ' thu_sleeps_avail: '. $thu_sleeps_avail);
                                    print_r(' thu_sleeps_filled = '. $thu_sleeps_filled .' thu_cabin_sleeps_total = '. $cabin->thu_sleeps .' result(thu_sleeps_filled / thu_cabin_sleeps) * 100 = '. $thu_sleeps_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----thu_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                }
                            }
                        }

                        if($fri_day === $day) {

                            if(!in_array($dates, $dates_array)) {

                                $dates_array[] = $dates;

                                if(($totalSleeps < $cabin->fri_sleeps)) {
                                    $fri_sleeps_diff       = $cabin->fri_sleeps - $totalSleeps;

                                    /* Available beds and dorms */
                                    $fri_sleeps_avail      = ($fri_sleeps_diff >= 0) ? $fri_sleeps_diff : 0;

                                    /* Already Filled beds and dorms */
                                    $fri_sleeps_filled     = $cabin->fri_sleeps - $fri_sleeps_avail;

                                    /* Percentage calculation */
                                    $fri_sleeps_percentage = ($fri_sleeps_filled / $cabin->fri_sleeps) * 100;

                                    if($fri_sleeps_percentage > 75) {
                                        $orangeDates[]     = $dates;
                                    }
                                    else {
                                        $available_dates[] = $dates;
                                    }
                                    /*print_r(' ----fri_regular_data---- ');
                                    print_r(' fri_dates: ' . $dates . ' fri_sleeps_avail: '. $fri_sleeps_avail);
                                    print_r(' fri_sleeps_filled = '. $fri_sleeps_filled .' fri_cabin_sleeps_total = '. $cabin->fri_sleeps .' result(fri_sleeps_filled / fri_cabin_sleeps) * 100 = '. $fri_sleeps_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----fri_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                }
                            }
                        }

                        if($sat_day === $day) {

                            if(!in_array($dates, $dates_array)) {

                                $dates_array[] = $dates;

                                if(($totalSleeps < $cabin->sat_sleeps)) {
                                    $sat_sleeps_diff       = $cabin->sat_sleeps - $totalSleeps;

                                    /* Available beds and dorms */
                                    $sat_sleeps_avail      = ($sat_sleeps_diff >= 0) ? $sat_sleeps_diff : 0;

                                    /* Already Filled beds and dorms */
                                    $sat_sleeps_filled     = $cabin->sat_sleeps - $sat_sleeps_avail;

                                    /* Percentage calculation */
                                    $sat_sleeps_percentage = ($sat_sleeps_filled / $cabin->sat_sleeps) * 100;

                                    if($sat_sleeps_percentage > 75) {
                                        $orangeDates[]     = $dates;
                                    }
                                    else {
                                        $available_dates[] = $dates;
                                    }
                                    /*print_r(' ----sat_regular_data---- ');
                                    print_r(' sat_dates: ' . $dates . ' sat_sleeps_avail: '. $sat_sleeps_avail);
                                    print_r(' sat_sleeps_filled = '. $sat_sleeps_filled .' sat_cabin_sleeps_total = '. $cabin->sat_sleeps .' result(sat_sleeps_filled / sat_cabin_sleeps) * 100 = '. $sat_sleeps_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----sat_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                }
                            }
                        }

                        if($sun_day === $day) {

                            if(!in_array($dates, $dates_array)) {

                                $dates_array[] = $dates;

                                if(($totalSleeps < $cabin->sun_sleeps)) {
                                    $sun_sleeps_diff       = $cabin->sun_sleeps - $totalSleeps;

                                    /* Available beds and dorms */
                                    $sun_sleeps_avail      = ($sun_sleeps_diff >= 0) ? $sun_sleeps_diff : 0;

                                    /* Already Filled beds and dorms */
                                    $sun_sleeps_filled     = $cabin->sun_sleeps - $sun_sleeps_avail;

                                    /* Percentage calculation */
                                    $sun_sleeps_percentage = ($sun_sleeps_filled / $cabin->sun_sleeps) * 100;

                                    if($sun_sleeps_percentage > 75) {
                                        $orangeDates[]     = $dates;
                                    }
                                    else {
                                        $available_dates[] = $dates;
                                    }
                                    /*print_r(' ----sun_regular_data---- ');
                                    print_r(' sun_dates: ' . $dates . ' sun_sleeps_avail: '. $sun_sleeps_avail);
                                    print_r(' sun_sleeps_filled = '. $sun_sleeps_filled .' sun_cabin_sleeps_total = '. $cabin->sun_sleeps .' result(sun_sleeps_filled / sun_cabin_sleeps) * 100 = '. $sun_sleeps_percentage);*/
                                }
                                else {
                                    $not_available_dates[] = $dates;
                                    /*print_r(' ----sun_regular_data---- ');
                                    print_r(' not_available_dates '. $dates);*/
                                }
                            }
                        }
                    }

                    /* Calculating beds & dorms for normal */
                    if(!in_array($dates, $dates_array)) {

                        if(($totalSleeps < $cabin->sleeps)) {
                            $normal_sleeps_diff       = $cabin->sleeps - $totalSleeps;

                            /* Available beds and dorms */
                            $normal_sleeps_avail      = ($normal_sleeps_diff >= 0) ? $normal_sleeps_diff : 0;

                            /* Already Filled beds and dorms */
                            $normal_sleeps_filled     = $cabin->sleeps - $normal_sleeps_avail;

                            /* Percentage calculation */
                            $normal_sleeps_percentage = ($normal_sleeps_filled / $cabin->sleeps) * 100;

                            if($normal_sleeps_percentage > 75) {
                                $orangeDates[]     = $dates;
                            }
                            else {
                                $available_dates[] = $dates;
                            }
                            /*print_r(' ----normal_regular_data---- ');
                            print_r(' normal_dates: ' . $dates . ' normal_sleeps_avail: '. $normal_sleeps_avail);
                            print_r(' normal_sleeps_filled = '. $normal_sleeps_filled .' normal_cabin_sleeps_total = '. $cabin->sleeps .' result(normal_sleeps_filled / normal_cabin_sleeps) * 100 = '. $normal_sleeps_percentage);*/
                        }
                        else {
                            $not_available_dates[] = $dates;
                            /*print_r(' ----normal_regular_data---- ');
                            print_r(' not_available_dates '. $dates);*/
                        }

                    }
                }
                /* Checking bookings available ends */
            }

            return response()->json(['holidayDates' => $holidayDates, 'greenDates' => $available_dates, 'orangeDates' => $orangeDates, 'redDates' => $not_available_dates, 'not_season_time' => $not_season_time], 200);
        }
        else {
            abort(404);
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
