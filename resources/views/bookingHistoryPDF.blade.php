<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Gutschein</title>
    <style>
        @page { margin:0; }
        body {
            font-family:arial,sans-serif;
            font-size:15px;
        }
    </style>
</head>
<body>
<table style="padding:10px 30px;width:100%;font-family:arial,sans-serif;font-size:13px;">
    <tr>
        <td colspan="3" style="color:#afca14;font-size:48px;float:right;" ><img  style="margin-top:15px;" width="280px" id="logo" src="{{ public_path('storage/img/pdf_title2.png') }}" alt="Huetten-Holiday.de"></td>
        <td style="text-align: right;padding-top:10px;" colspan="4"><img style="width: 250px;" id="logo" src="{{public_path('storage/img/logo.png')}}" alt="Huetten-Holiday.de"><br><br>Waltenhofen, den {{ $cart->bookingdate->format('d.m.Y') }}</td>
    </tr>
    <tr><td colspan="7" style="color:#afca14;font-size:95px;text-align:center;padding-top:40px;padding-bottom:0px;font-family:Amienne;" ><img width="280px" id="logo" src="{{public_path('storage/img/pdf_title1.png')}}" alt="Huetten-Holiday.de"></td></tr>
    <tr><td colspan="7" style="font-size:25px;font-weight:bold;text-align:center;padding-top:0px;padding-bottom:40px;" >{{ Auth::user()->usrFirstname }} {{ Auth::user()->usrLastname }}</td></tr>
    <tr>
        <td colspan="3">
            <h2 style="font-size:20px;padding-top:40px;padding-bottom:5px;color:#afca14;font-weight:bold;" >Ihre Daten</h2>
            <table style="padding:10px 30px;width:100%;font-family:arial,sans-serif;font-size:13px;background-color:#D9D9D9;">
                <tr><td colspan="1" style="font-weight:bold;">Name</td><td colspan="6">{{ Auth::user()->usrFirstname }}</td></tr>
                <tr><td colspan="1" style="font-weight:bold;">Vorname</td><td colspan="6">{{ Auth::user()->usrLastname }}</td></tr>
                <tr><td colspan="1" style="font-weight:bold;">Wohnort</td><td colspan="6">{{ Auth::user()->usrAddress }} <br> {{ Auth::user()->usrZip }} {{ Auth::user()->usrCity }}</td></tr>
                <tr><td colspan="1" style="font-weight:bold;">Kontaktdaten</td><td colspan="6">{{ Auth::user()->usrTelephone }} <br> {{ Auth::user()->usrEmail }}</td></tr>
            </table>
        </td>
        <td colspan="4">
            <h2 style="font-size:20px;padding-top:40px;padding-bottom:5px;color:#afca14;font-weight:bold;" >Ihre Buchungsübersicht</h2>
            <table style="padding:10px 30px;width:100%;font-family:arial,sans-serif;font-size:13px;background-color:#D9D9D9;">
                <tr><td colspan="1" style="font-weight:bold;">Hütte</td><td colspan="6">{{ $cart->cabinname }}</td></tr>
                <tr><td colspan="1" style="font-weight:bold;">Buchungsnr</td><td colspan="6">{{ $cart->invoice_number }}</td></tr>
                <tr><td colspan="1" style="font-weight:bold;">Anreise</td><td colspan="6">{{ $cart->checkin_from->format('d.m.Y') }}</td></tr>
                <tr><td colspan="1" style="font-weight:bold;">Ankunft</td><td colspan="6">{{ $cart->reserve_to->format('d.m.Y') }}</td></tr>
                <tr><td colspan="1" style="font-weight:bold;">Personen</td><td colspan="6">{{ $cart->guests }}</td></tr>
                <tr><td colspan="1" style="font-weight:bold;">Nächte</td><td colspan="6">{{ $dateDifference }}</td></tr>
                @if($cart->status === '4' && $cart->payment_status === '2')
                    <tr><td colspan="1" style="font-weight:bold;">Gutscheinwert</td><td colspan="6">Nicht bezahlt</td></tr>
                @else
                    <tr><td colspan="1" style="font-weight:bold;">Gutscheinwert</td><td colspan="6">{{ number_format($cart->prepayment_amount, 2, ',', '.') }} &euro;</td></tr>
                @endif
            </table>
        </td>
    </tr>
   {{-- <tr>
        <td colspan="3">

        </td>
        <td colspan="4">

        </td>
    </tr>--}}

</table>
<table style="padding:10px 30px;width:100%;font-family:arial,sans-serif;font-size:13px;"><tr><td colspan="6" style="font-size:23px;padding-top:40px;padding-bottom:5px;color:#afca14;font-weight:bold;" >Wichtige Informationen</td></tr>
    <table style="padding:10px 30px;width:100%;font-family:arial,sans-serif;font-size:13px;background-color:#D9D9D9;">
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"> </td><td colspan="6">Der Gutschein ist nur für das ausgewählte Buchungsdatum gültig.</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Falls Sie Alpenvereinsmitglied sind, wird dies vom Hüttenwirt vor Ort geprüft.</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Ihre Anzahlung wird mit dem Übernachtungspreis direkt auf der Hütte verrechnet. </td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Ihnen wird auf der Hütte der Gutscheinwert entsprechend der anwesenden Personen verrechnet. Die Online-Gebühr bleibt hiervon unberührt.</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Ein Anspruch auf Barauszahlung vor Ort besteht nicht.</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Der Gutschein wird auf die Person, welche die Buchung veranlasst hat, verrechnet..</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Der Wertgutschein hat 4 Jahre nach Ausstellung Gültigkeit.</td>
        </tr>
    </table>
</table>
<div style="position:fixed; padding:10px 20px;height:60px;bottom:0px;font-family:arial,sans-serif;font-size:12px;width: 100%; background-color: rgb(162, 198, 20); color:#fff;">
    <span style="float:left;width:17%"><img style="width: 100px" id="logo" src="{{ public_path('storage/img/pdf_logo.png') }}"></span>
    <span style="text-align:left;float:left;width:23%;padding-top:18px;">Huetten-Holiday.de GmbH<br>Geschäftsführer: Michael Hofer</span>
    <span style="text-align:left;float:left;width:18%;padding-top:18px;">Nebelhornstraße 3<br>87448 Waltenhofen</span>
    <span style="text-align:left;float:left;width:15%;padding-top:18px;">Umsatzsteuer-Id-Nr.:<br>DE 310 927 476</span>
    <span style="text-align:right;float:left;width:25%;padding-top:18px;"><img  width="12px"  src="{{ public_path('storage/img/phone.png') }}"> +49 9001 / 329999<br><img  width="12px"  src="{{ public_path('storage/img/email.png') }}">service@huetten-holiday.de</span>
</div>
</body>
</html>