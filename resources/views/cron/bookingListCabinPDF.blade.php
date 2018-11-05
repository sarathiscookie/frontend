<!DOCTYPE html>
<html lang="de">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('cronCabinBookingList.pageTitle') }}</title>

    <style>
        body {
            font-size:13px;
            font-family:Segoe UI, helvetica, arial;
        }
        .comment{
            color: #f90011;
        }
        @page { margin: 80px 0px; }
        #header { position: fixed; left: 20px; top: -80px; right: 0px; height: 50px;  }
        #footer { position:relative; padding:10px 0px; height:60px;left: 0px; bottom: 0px; right: 0px; font-size:12px;width: 100%; background-color: rgb(162, 198, 20); color:#fff; }
        #footer .page:after { content: counter(page, upper-roman); }
        table { border-collapse: collapse; }
        .page-break {
            page-break-after: always;
        }
        tr.spaceUnder>td {
            padding-bottom: 1em;
        }

        tr.last-row>td {
            border-bottom: 2px solid #000;
        }
    </style>

</head>
<body>

<div>
    <table id="ctable" style="padding:20px; width:1500px; margin-right:40px;" class="page-break" >

        <tr>
            <td colspan="12" style="background-color:#fff; color:#9ACA3B; width:100%; font-size:20px; font-weight: bold;">{{ $cabinname }}</td>
        </tr>

    @inject('cronServices', 'App\Http\Controllers\CronJobsController')

    <!-- Normal Booking List -->
        <tr>
            <td colspan="12" style="background-color:#fff; color:#5f6876; padding:30px 0px; width:100%; font-size:17px;"><nobr>{{ __('cronCabinBookingList.normalBookingListHeading') }}</nobr> {{ __('cronCabinBookingList.from') }} {{date('d.m')}}</td>
        </tr>

        <tr style="background:#9ACA3B; color:#fff; height:40px;">
            <td style="width:16%; font-weight: bold;" >{{ __('cronCabinBookingList.bookingNumber') }}</td>
            <td style="width:15%; font-weight: bold;">{{ __('cronCabinBookingList.name') }}</td>
            <td style="width:15%; font-weight: bold;">{{ __('cronCabinBookingList.date') }}</td>
            <td style="width:5%; font-weight: bold;">{{ __('cronCabinBookingList.nights') }}</td>
            <td style="width:5%; font-weight: bold;">{{ __('cronCabinBookingList.numberOfPersons') }}</td>
            <td style="width:8%; font-weight: bold;">{{ __('cronCabinBookingList.sleeps') }}</td>
            @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                <td style="width:8%; font-weight: bold;">{{ __('cronCabinBookingList.halfBoard') }}</td>
            @endif
            <td style="width:8%; font-weight: bold;">{{ __('cronCabinBookingList.clubMember') }}</td>
            <td style="width:8%; font-weight: bold;">{{ __('cronCabinBookingList.amount') }}</td>
            <td  style="width:35%; font-weight: bold;" >{{ __('cronCabinBookingList.contact') }}</td>
        </tr>

        @php
            $firstName = '';
            $lastName  = '';
            $email     = '';
            $phone     = '';
        @endphp

        @forelse($bookings as $booking)
            @php
                if($booking->temp_user_id != ''){
                   $userTempDetails = $cronServices->tempUser($booking->temp_user_id);
                   if(!empty($userTempDetails))
                   {
                    $firstName = $userTempDetails->usrFirstname;
                    $lastName  = $userTempDetails->usrLastname;
                    $email     = $userTempDetails->usrEmail;
                    $phone     = $userTempDetails->usrTelephone;
                   }
                }
                else {
                      $userDetails = $cronServices->user($booking->user);
                      if(!empty($userDetails))
                      {
                       $firstName = $userDetails->usrFirstname;
                       $lastName  = $userDetails->usrLastname;
                       $email     = $userDetails->usrEmail;
                       $phone     = $userDetails->usrTelephone;
                      }
                }

                /* Checking condition for date */
                if(!empty($booking->checkin_from) && !empty($booking->reserve_to)) {
                   $daysDifference = round(abs(strtotime(date_format($booking->checkin_from, 'd.m.Y')) - strtotime(date_format($booking->reserve_to, 'd.m.Y'))) / 86400);
                }
                else {
                     $daysDifference = 'Date not set';
                }

                /* Checking halfboard available or not */
                if(!empty($booking->halfboard) && $booking->halfboard === '1') {
                   $halfboard = 'ja';
                }
                else {
                   $halfboard = 'Nein';
                }

                /* Listing beds dorms separately */
                if($sleepingPlace != 1) {
                   $category = $booking->beds .'B '. $booking->dormitory .'M';
                }
                else {
                      $category = $booking->sleeps;
                }

                /* Fetch inquiry message */
                $message = App\PrivateMessage::where('booking_id', new \MongoDB\BSON\ObjectID($booking->_id))->pluck('text')->first();

            @endphp
            <tr>
                <td>{{ $booking->invoice_number }}</td>
                <td>{{ $firstName}} {{ $lastName }}</td>
                <td @if($daysDifference > 1) style="font-weight: bold;" @endif>{{ $booking->checkin_from->format('d.m') }} bis {{ $booking->reserve_to->format('d.m') }}</td>
                <td>{{ $daysDifference }}</td>
                <td>{{ $booking->sleeps }}</td>
                <td>{{ $category }}</td>
                @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                    <td>{{ $halfboard }}</td>
                @endif
                <td>@if($booking->clubmember) {{ $booking->clubmember }} @else 0 @endif</td>
                <td>{{ number_format($booking->prepayment_amount, 2, ',', '.') }} &euro;</td>
                <td>{{ $email }}</td>
            </tr>

            @if ($booking->comments)
                <tr @if(!$message && !$booking->notes) class="last-row" @endif>
                    <td>{{ __('cronCabinBookingList.comment') }}:</td>
                    <td colspan="6" class="comment">{{ $booking->comments }}</td>
                    @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                        <td>&nbsp;</td>
                    @endif
                    <td>&nbsp;</td>
                    <td>{{ $phone }}</td>
                </tr>
            @endif

            @if ($message)
                <tr @if(!$booking->comments && !$booking->notes) class="last-row" @endif>
                    <td>{{ __('cronCabinBookingList.chat') }}:</td>
                    <td colspan="9" class="comment">{{ $message }}</td>
                </tr>
            @endif

            @if ($booking->notes)
                <tr @if(!$message && !$booking->comments) class="last-row" @endif>
                    <td>{{ __('cronCabinBookingList.notes') }}:</td>
                    <td colspan="9" class="comment">{{ $booking->notes }}</td>
                </tr>
            @endif
        @empty
            <tr>
                <td>&nbsp;</td>
                <td colspan="8">{{ __('cronCabinBookingList.noBookings') }}</td>
                <td>&nbsp;</td>
            </tr>
        @endforelse

    <!-- Mountain School Booking List -->
        <tr style="padding-bottom: 1em;">
            <td colspan="12" style="background-color:#fff; color:#5f6876; width:100%; font-size:17px; padding-bottom: 1em; padding-top: 1em;"><nobr>{{ __('cronCabinBookingList.mschoolBookingHeading') }}</nobr> {{ __('cronCabinBookingList.from') }} {{date('d.m')}}</td>
        </tr>

        <tr style="background:#33b1d2;color:#fff; height: 40px;">
            <td style="width:15%; font-weight: bold;">{{ __('cronCabinBookingList.bookingNumber') }}</td>
            <td style="width:10%; font-weight: bold;">{{ __('cronCabinBookingList.name') }}</td>
            <td style="width:15%; font-weight: bold;">{{ __('cronCabinBookingList.date') }}</td>
            <td style="width:5%; font-weight: bold;">{{ __('cronCabinBookingList.nights') }}</td>
            <td style="width:10%; font-weight: bold;">{{ __('cronCabinBookingList.numberOfPersons') }}</td>
            <td style="width:10%; font-weight: bold;">{{ __('cronCabinBookingList.sleeps') }}</td>
            @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                <td style="width:10%; font-weight: bold;">{{ __('cronCabinBookingList.halfBoard') }}</td>
            @endif
            <td style="width:10%; font-weight: bold;">{{ __('cronCabinBookingList.guides') }}</td>
            <td style="width:10%; font-weight: bold;">{{ __('cronCabinBookingList.tourNo') }}</td>
            <td style="width:45%; font-weight: bold;" >{{ __('cronCabinBookingList.contact') }}</td>
        </tr>

        @forelse($msBookings as $msBooking)
            @php
                $userMsDetails = $cronServices->user($msBooking->user_id);
                if(!empty($userMsDetails))
                {
                   $firstName = $userMsDetails->usrFirstname;
                   $lastName  = $userMsDetails->usrLastname;
                   $email     = $userMsDetails->usrEmail;
                   $phone     = $userMsDetails->usrTelephone;
                }

                if(!empty($msBooking->check_in) && !empty($msBooking->reserve_to)) {
                    $daysDifference = round(abs(strtotime(date_format($msBooking->check_in, 'd.m.Y')) - strtotime(date_format($msBooking->reserve_to, 'd.m.Y'))) / 86400);
                }
                else {
                    $daysDifference = 'Date not set';
                }

                /* Checking halfboard available or not */
                if(!empty($msBooking->half_board) && $msBooking->half_board === '1') {
                    $msHalfboard = 'ja';
                }
                else {
                    $msHalfboard = 'Nein';
                }

                /* Listing beds dorms separately */
                if($sleepingPlace != 1) {
                   $msCategory = $msBooking->beds .'B '. $msBooking->dormitory .'M';
                }
                else {
                   $msCategory = $msBooking->sleeps;
                }
            @endphp

            <tr>
                <td>{{ $msBooking->invoice_number }}</td>
                <td>{{ $firstName }} {{ $lastName }}</td>
                <td @if($daysDifference > 1) style="font-weight: bold;" @endif>{{ $msBooking->check_in->format('d.m') }} bis {{ $msBooking->reserve_to->format('d.m') }}</td>
                <td>{{ $daysDifference }}</td>
                <td>{{ $msBooking->sleeps }}</td>
                <td>{{ $msCategory }}</td>
                @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                    <td>{{ $msHalfboard }}</td>
                @endif
                <td>{{ $msBooking->no_guides }}</td>
                <td>@if(!empty($msBooking->ind_tour_no)) {{ $msBooking->ind_tour_no }} @endif</td>
                <td>{{ $email }}</td>
            </tr>
            <tr>
                <td>{{ __('cronCabinBookingList.comment') }}:</td>
                <td colspan="6" class="comment">{{ $msBooking->comments }}</td>
                @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                    <td>&nbsp;</td>
                @endif
                <td>&nbsp;</td>
                <td>{{ $phone }}</td>
            </tr>
        @empty
            <tr>
                <td>&nbsp;</td>
                <td colspan="8">{{ __('cronCabinBookingList.noBookings') }}</td>
                <td>&nbsp;</td>
            </tr>
        @endforelse
    </table>
</div></body></html>