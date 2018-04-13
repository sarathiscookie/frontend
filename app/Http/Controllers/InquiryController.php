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
        $monthBegin              = DateTime::createFromFormat('d.m.y', session()->get('checkin_from'))->format('Y-m-d');
        $monthEnd                = DateTime::createFromFormat('d.m.y', session()->get('reserve_to'))->format('Y-m-d');

        $bedsRequest             = (int)$request->beds;
        $dormsRequest            = (int)$request->dormitory;
        $sleepsRequest           = (int)$request->sleeps;
        $requestBedsSumDorms     = $bedsRequest + $dormsRequest;

        $available               = 'failure';

        if($monthBegin < $monthEnd) {
            $not_regular_dates       = [];
            $dates_array             = [];
            $availableStatus         = [];

            $cabin                   = Cabin::where('is_delete', 0)
                ->where('other_cabin', "0")
                ->findOrFail(session()->get('cabin_id'));

            $generateBookingDates    = $this->generateDates($monthBegin, $monthEnd);

            foreach ($generateBookingDates as $generateBookingDate) {

                $dates               = $generateBookingDate->format('Y-m-d');
                $day                 = $generateBookingDate->format('D');

                /* Checking requested inquiry count is greater than or equal to cabin contingent inquiry count begins */
                $mon_day     = ($cabin->mon_day === 1) ? 'Mon' : 0;
                $tue_day     = ($cabin->tue_day === 1) ? 'Tue' : 0;
                $wed_day     = ($cabin->wed_day === 1) ? 'Wed' : 0;
                $thu_day     = ($cabin->thu_day === 1) ? 'Thu' : 0;
                $fri_day     = ($cabin->fri_day === 1) ? 'Fri' : 0;
                $sat_day     = ($cabin->sat_day === 1) ? 'Sat' : 0;
                $sun_day     = ($cabin->sun_day === 1) ? 'Sun' : 0;

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
                                return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                            return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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

                            if($sleepsRequest >= $cabin->not_regular_sleeps) {
                                $availableStatus[] = 'possible';
                            }
                            else {
                                $availableStatus[] = 'notPossible';
                                return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
                            }
                        }
                    }

                    /* Calculating sleeps for regular */
                    if($cabin->regular === 1) {

                        if($mon_day === $day) {

                            if(!in_array($dates, $dates_array)) {

                                $dates_array[] = $dates;

                                if($sleepsRequest >= $cabin->mon_inquiry_guest) {
                                    $availableStatus[] = 'possible';
                                }
                                else {
                                    $availableStatus[] = 'notPossible';
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
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
                                    return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
                                }
                            }
                        }
                    }

                    /* Calculating sleeps for normal */
                    if(!in_array($dates, $dates_array)) {

                        if($sleepsRequest >= $cabin->inquiry_starts) {
                            $availableStatus[] = 'possible';
                        }
                        else {
                            $availableStatus[] = 'notPossible';
                            return redirect()->back()->with('error', 'Change a few things up and try submitting again.');
                        }

                    }

                }

                /* Checking bookings available ends */
            }

            if(!in_array('notPossible', $availableStatus)) {
                $available = 'success';

                /*
      "halfboard" => "1"
      "comments" => "test"*/
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
            return redirect()->back()->with('error', 'Arrival date should be less than departure date.');
        }

        return redirect()->route('booking.history')->with('response', $available);
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
