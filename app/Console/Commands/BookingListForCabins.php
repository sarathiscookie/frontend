<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Booking;
use App\MountSchoolBooking;
use App\Cabin;
use App\Season;
use App\Userlist;
use DateTime;
use Mail;
use PDF;
use Carbon\Carbon;

class BookingListForCabins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BookingListForCabins:bookinglist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send every day booking list to cabin owners';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date             = date('d.m.y');

        $dateFormatChange = DateTime::createFromFormat("d.m.y", $date)->format('Y-m-d');
        $dateTime         = new DateTime($dateFormatChange);
        $timeStamp        = $dateTime->getTimestamp();
        $utcDateTime      = new \MongoDB\BSON\UTCDateTime($timeStamp * 1000);

        $cabins           = Cabin::select('_id', 'cabin_owner', 'name', 'invoice_code', 'halfboard', 'sleeping_place')
            ->where('is_delete', 0)
            ->where('other_cabin', "0")
            ->get();

        if(!empty($cabins)) {
            foreach($cabins as $key => $cabin) {

                $invoice_code = $cabin->invoice_code.'-'.date("y").'-';

                $bookings     = Booking::where('is_delete', 0)
                    ->where('status', '1')
                    ->whereRaw(['checkin_from' => array('$lte' => $utcDateTime)])
                    ->whereRaw(['reserve_to' => array('$gt' => $utcDateTime)])
                    ->where('cabinname', $cabin->name)
                    ->orderBy('invoice_number', 'asc')
                    ->get();

                $msBookings   = MountSchoolBooking::where('is_delete', 0)
                    ->where('status', '1')
                    ->whereRaw(['check_in' => array('$lte' => $utcDateTime)])
                    ->whereRaw(['reserve_to' => array('$gt' => $utcDateTime)])
                    ->where('cabin_name', $cabin->name)
                    ->orderBy('invoice_number', 'asc')
                    ->get();

                $html         = view('cron.bookingListCabinPDF', ['invoice_code' => $invoice_code, 'cabinname' => $cabin->name, 'bookings' => $bookings, 'msBookings' => $msBookings, 'cabinHalfboard' => $cabin->halfboard, 'sleepingPlace' => $cabin->sleeping_place])->render();

                //setPaper('a4', 'landscape')
                //setPaper(array(0,0,1000,781))
                PDF::loadHTML($html)->setPaper('a4', 'landscape')->setWarnings(false)->save(storage_path("app/public/dailylistbookingforcabin/". $cabin->name . ".pdf"));

                $cabinOwner   = Userlist::select('_id', 'usrEmail')
                    ->where('usrActive', '1')
                    ->where('is_delete', 0)
                    ->where('usrlId', 5)
                    ->find($cabin->cabin_owner);

                $seasons = Season::where('cabin_owner', new \MongoDB\BSON\ObjectID($cabin->cabin_owner))->get();

                $cabinOpen = false;

                foreach ($seasons as $season) {
                    // Check if cabin is open in summer
                    if ($season->summerSeasonStatus == 'open') {
                        if ($season->latest_summer_close >= Carbon::now()) {
                            $cabinOpen = true;
                        }
                    }

                    // Check if cabin is open in winter
                    if ($season->winterWinterStatus == 'open') {
                        if ($season->latest_winter_close >= Carbon::now()) {
                            $cabinOpen = true;
                        }
                    }
                }

                if(!empty($cabinOwner) && $cabinOpen) {
                    /* Functionality to send message to user begin */
                    /*to($cabinOwner->usrEmail)->bcc('backup.tageslisten@huetten-holiday.de')*/
                    /*to('l.linder@huetten-holiday.de')->bcc('iamsarath1986@gmail.com')*/
                    /*to('iamsarath1986@gmail.com')*/
                    Mail::send('emails.bookingListCabin', ['subject' => 'Ihre tägliche Buchungsübersicht'], function ($message) use ($cabinOwner, $cabin) {
                        $message->to($cabinOwner->usrEmail)->bcc('backup.tageslisten@huetten-holiday.de')->subject('Ihre tägliche Buchungsübersicht')->attach(public_path("/storage/dailylistbookingforcabin/". $cabin->name . ".pdf"), [
                            'mime' => 'application/pdf',
                        ]);
                    });
                    /* Functionality to send message to user end */
                }
            }
        }
    }
}
