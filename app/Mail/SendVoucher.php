<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Booking;
use PDF;
use DateTime;
use DatePeriod;
use DateInterval;

class SendVoucher extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var Booking
     */
    public $newBooking;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Booking $newBooking)
    {
        $this->newBooking     = $newBooking;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /* Date difference */
        $checkin_from = $this->newBooking->checkin_from->format('Y-m-d');
        $reserve_to   = $this->newBooking->reserve_to->format('Y-m-d');
        $d1           = new DateTime($checkin_from);
        $d2           = new DateTime($reserve_to);
        $dateDiff     = $d2->diff($d1);

        $html         = view('bookingHistoryPDF', ['cart' => $this->newBooking, 'dateDifference' => $dateDiff->days]);

        $pdf          = PDF::loadHTML($html)->setPaper('a4', 'portrait')->setWarnings(false)->save(storage_path("app/public/invoice/". $this->newBooking->invoice_number . ".pdf"));;

        return $this->view('emails.sendVoucherEmail')
            ->bcc(env('MAIL_BCC_PAYMENT'))
            ->subject('Informationen zu Ihrer Hüttenbuchung')
            ->attach(public_path('/storage/invoice/Huetten-Holiday-AGB.pdf'), [
                'mime' => 'application/pdf',
            ])
            ->attach(public_path("/storage/invoice/". $this->newBooking->invoice_number . ".pdf"), [
                'mime' => 'application/pdf',
            ]);
    }
}
