<!DOCTYPE html>
<html lang="de">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tagesliste</title>

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
            <td colspan="12" style="background-color:#fff; color:#5f6876; padding:30px 0px; width:100%; font-size:17px;"><nobr>Buchungsübersicht</nobr> vom {{date('d.m')}}</td>
        </tr>

        <tr style="background:#9ACA3B; color:#fff; height:40px;">
            <td style="width:16%; font-weight: bold;" >Buchungs-Nr.</td>
            <td style="width:15%; font-weight: bold;">Name</td>
            <td style="width:15%; font-weight: bold;">Datum</td>
            <td style="width:5%; font-weight: bold;">Nächte</td>
            <td style="width:5%; font-weight: bold;">Anzahl P.</td>
            <td style="width:8%; font-weight: bold;">Schlafplätze</td>
            @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                <td style="width:8%; font-weight: bold;">Halbpension</td>
            @endif
            <td style="width:8%; font-weight: bold;">Gutscheinwert</td>
            <td  style="width:35%; font-weight: bold;" >Kontakt</td>
        </tr>

        <tr style="position: absolute; z-index: -99999999999999; height: 0px; width: 0px;"><td></td></tr>

        @if(isset($bookings) && !empty($bookings))
            @foreach($bookings as $booking)
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
                    <td>{{ number_format($booking->prepayment_amount, 2, ',', '.') }} &euro;</td>
                    <td>{{ $email }}</td>
                </tr>

                <tr>
                    <td>Kommentar:</td>
                    <td colspan="6" class="comment">{{ $booking->comments }}</td>
                    @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                        <td>&nbsp;</td>
                    @endif
                    <td>{{ $phone }}</td>
                </tr>

            @endforeach
        @else
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">Keine Buchung</td>
                <td>&nbsp;</td>
            </tr>
        @endif

    <!-- Mountain School Booking List -->
        <tr>
            <td style="width:15%; font-weight: bold;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="12" style="background-color:#fff; color:#5f6876; width:100%; font-size:17px;"><nobr>Bergschulenübersicht</nobr> vom 01.07.</td>
        </tr>

        <tr>
            <td style="width:15%; font-weight: bold;">&nbsp;</td>
        </tr>
        <tr style="background:#33b1d2;color:#fff; height: 40px;">
            <td style="width:15%; font-weight: bold;">Buchungs-Nr.</td>
            <td style="width:10%; font-weight: bold;">Name</td>
            <td style="width:15%; font-weight: bold;">Datum</td>
            <td style="width:5%; font-weight: bold;">Nächte</td>
            <td style="width:10%; font-weight: bold;">Anzahl P.</td>
            <td style="width:10%; font-weight: bold;">Schlafplätze</td>
            @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                <td style="width:10%; font-weight: bold;">Halbpension</td>
            @endif
            <td style="width:10%; font-weight: bold;">TourNr.</td>
            <td  style="width:35%; font-weight: bold;" >Kontakt</td>
        </tr>


        @if(isset($msBookings) && !empty($msBookings))
            @foreach($msBookings as $msBooking)
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
                    <td>@if(!empty($msBooking->ind_tour_no)) {{ $msBooking->ind_tour_no }} @endif</td>
                    <td>{{ $email }}</td>
                </tr>
                <tr>
                    <td>Kommentar:</td>
                    <td colspan="6" class="comment">{{ $msBooking->comments }}</td>
                    @if(isset($cabinHalfboard) && $cabinHalfboard === '1')
                        <td>&nbsp;</td>
                    @endif
                    <td>{{ $phone }}</td>
                </tr>

            @endforeach
        @else
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">Keine Buchung</td>
                <td>&nbsp;</td>
            </tr>
        @endif
    </table>
</div></body></html>