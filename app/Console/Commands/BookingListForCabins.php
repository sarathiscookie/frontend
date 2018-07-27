<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Booking;
use App\MountSchoolBooking;
use App\Cabin;
use App\Userlist;
use App\Tempuser;
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
        //$test              = '';
        $cabins            = Cabin::select('_id', 'cabin_owner', 'name', 'invoice_code')
            ->where('is_delete', 0)
            ->where('other_cabin', "0")
            ->get();

        foreach($cabins as $key => $cabin) {
            $invoice_code = $cabin->invoice_code.'-'.date("y").'-';

            $cabinOwner   = Userlist::select('_id', 'usrEmail')
                ->where('usrActive', '1')
                ->where('is_delete', 0)
                ->where('usrlId', 5)
                ->find($cabin->cabin_owner);

            $bookings     = Booking::where('is_delete', 0)
                ->where('status', '1')
                ->where('checkin_from', Carbon::now()->startOfDay())
                ->where('cabinname', $cabin->name)
                ->orderBy('invoice_number', 'asc')
                ->get();

            $msBookings   = MountSchoolBooking::where('is_delete', 0)
                ->where('status', '1')
                ->where('check_in', Carbon::now()->startOfDay())
                ->where('cabin_name', $cabin->name)
                ->orderBy('invoice_number', 'asc')
                ->get();

            $html = view('cron.bookingListCabinPDF', ['invoice_code' => $invoice_code, 'cabinname' => $cabin->name, 'bookings' => $bookings, 'msBookings' => $msBookings])->render();

            PDF::loadHTML($html)->setPaper(array(0,0,1000,781))->setWarnings(false)->save(storage_path("app/public/dailylistbookingforcabin/". $cabin->name . ".pdf"));

            /* Functionality to send message to user begin */
            /*bcc('backup.tageslisten@huetten-holiday.de')->to($cabinOwner->usrEmail)*/
            Mail::send('emails.bookingListCabin', ['subject' => 'Ihre tägliche Buchungsübersicht'], function ($message) use ($cabinOwner, $cabin) {
                $message->to('iamsarath1986@gmail.com')->subject('Ihre tägliche Buchungsübersicht')->attach(public_path("/storage/dailylistbookingforcabin/". $cabin->name . ".pdf"), [
                    'mime' => 'application/pdf',
                ]);
            });
            /* Functionality to send message to user end */
        }
    }
}
