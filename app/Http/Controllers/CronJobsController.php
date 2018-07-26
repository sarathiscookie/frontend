<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Booking;
use App\MountSchoolBooking;
use App\Cabin;
use App\Userlist;
use App\Tempuser;
use Mail;
use PDF;
use Carbon\Carbon;
use App\Mail\BookingListCabin;

class CronJobsController extends Controller
{
    /**
     *
     * List temp users
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function tempUser($id)
    {
        $tempUser = Tempuser::select('_id', 'usrFirstname', 'usrLastname', 'usrEmail', 'usrTelephone')
            ->where('is_delete', 0)
            ->find($id);

        if(!empty($tempUser)) {
            return $tempUser;
        }
    }

    /**
     *
     * List users
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function user($id)
    {
        $user = Userlist::select('_id', 'usrFirstname', 'usrLastname', 'usrEmail', 'usrTelephone')
            ->where('usrActive', '1')
            ->where('is_delete', 0)
            ->find($id);

        if(!empty($user)) {
            return $user;
        }
    }

    /* Created this function for testing purpose */
    public function cabinOwnerDailyListInvoice()
    {
        $test              = '';
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

            foreach($msBookings as $booking) {
                $test .= $booking->invoice_number;
                if(!empty($booking->temp_user_id)){
                    $test .= $this->tempUser($booking->temp_user_id);
                }
                else{
                    $test .= $this->user($booking->user_id);
                }
             }

        }
        dd($test);
    }
}
