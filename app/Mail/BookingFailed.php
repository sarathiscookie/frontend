<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Userlist;

class BookingFailed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var $txId
     * @var $userId
     */
    protected $txId;

    protected $userId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($txId, $userId)
    {
        $this->txId   = $txId;
        $this->userId = $userId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user         = Userlist::select('usrEmail')->where('is_delete', 0)
            ->where('usrActive', '1')
            ->where('userid', $this->userId)
            ->first();
        if(!empty($user)) {
            return $this->view('emails.bookingFailedEmail')
                ->subject('Informationen zu Ihrer Hüttenbuchung')
                ->with([
                    'transactionId' => $this->txId,
                    'userEmail' => $user->usrEmail
                ]);
        }
    }
}
