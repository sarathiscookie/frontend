<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Ihre Zahlungsinformationen</title>
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
        <td style="text-align: right;padding-top:10px;" colspan="4"><img style="width: 250px;" id="logo" src="{{public_path('storage/img/logo.png')}}" alt="Huetten-Holiday.de"><br><br>Waltenhofen, den {{$order->created_at->format('d.m.Y')}}</td>
    </tr>
    <tr><td colspan="7" style="color:#afca14;font-size:95px;text-align:center;padding-top:40px;padding-bottom:0px;font-family:Amienne;" ><img width="280px" id="logo" src="{{public_path('storage/img/pdf_title1.png')}}" alt="Huetten-Holiday.de"></td></tr>
    <tr><td colspan="7" style="font-size:25px;font-weight:bold;text-align:center;padding-top:0px;padding-bottom:40px;" >{{ Auth::user()->usrFirstname }} {{ Auth::user()->usrLastname }}</td></tr>
    <tr><td colspan="6" style="font-size:23px;padding-top:40px;padding-bottom:5px;color:#afca14;font-weight:bold;" >Ihre Daten</td></tr>
    <table style="padding:10px 30px;width:100%;font-family:arial,sans-serif;font-size:13px;background-color:#D9D9D9;">
        <tr><td colspan="1" style="font-weight:bold;">Buchungsstatus</td><td colspan="6">In Bearbeitung</td></tr>
        <tr><td colspan="1" style="font-weight:bold;">Transaktionsnummer</td><td colspan="6">{{$order->txid}}</td></tr>
        <tr><td colspan="1" style="font-weight:bold;">Bestellnummer</td><td colspan="6">{{$order->order_id}}</td></tr>
        <tr>
            <td colspan="1" style="font-weight:bold;">Buchungsnummer/-n</td><td colspan="6">
                @foreach($carts as $cart)
                    {{$cart->invoice_number}}<br>
                @endforeach
            </td>
        </tr>
        <tr><td colspan="1" style="font-weight:bold;">Anzahlungsbetrag</td><td colspan="6">{{ number_format($order->order_total_amount, 2, ',', '.') }} &euro;</td></tr>
        <tr><td colspan="1" style="font-weight:bold;">Kreditinstitut</td><td colspan="6">{{$order->clearing_bankname}}</td></tr>
        <tr><td colspan="1" style="font-weight:bold;">Kontoinhaber</td><td colspan="6">{{$order->clearing_bankaccountholder}}</td></tr>
        <tr><td colspan="1" style="font-weight:bold;">IBAN</td><td colspan="6">{{$order->clearing_bankiban}}</td></tr>
        <tr><td colspan="1" style="font-weight:bold;">BIC</td><td colspan="6">{{$order->clearing_bankbic}}</td></tr>
    </table>
</table>
<table style="padding:10px 30px;width:100%;font-family:arial,sans-serif;font-size:13px;"><tr><td colspan="6" style="font-size:23px;padding-top:40px;padding-bottom:5px;color:#afca14;font-weight:bold;" >Wichtige Informationen</td></tr>
    <table style="padding:10px 30px;width:100%;font-family:arial,sans-serif;font-size:13px;background-color:#D9D9D9;">
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"> </td><td colspan="6">Für eine korrekte und schnelle Zuordnung ist es wichtig das Sie die <strong>Transaktionsnummer</strong> sowie die <strong>Bestellnummer</strong> in Ihrer<br>Überweisung angeben. Sie können auch die Buchungsnummer angeben. Weitere Angaben wie "Betten" oder "wir kommen<br>mit 4 Personen" werden aufgrund der automatischen Verarbeitung nicht gelesen.</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Sie erhalten Ihren Übernachtungsgutschein, sobald wir einen Zahlungseingang feststellen.</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">In Ihrem Kundenportal sehen Sie dann, das Ihre Buchung den Status "fix" hat.</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Bezahlen Sie bitte innerhalb von 10 Tagen, da Ihre Anfrage sonst wieder aus dem System entfernt wird.</td>
        </tr>
        <tr>
            <td colspan="1"><img style="width: 10px" id="logo" src="{{ public_path('storage/img/plus.png') }}"></td><td colspan="6">Sollten Sie trotz einer Anzahlung keinen Übernachtungsgutschein erhalten haben, prüfen Sie bitte Ihren Spamordner<br>(evtl. auch "Junk-E-Mail" oder "Werbungen" genannt.) Andernfalls schreiben Sie unserem Support unter service@huetten-<br>holiday.de, wir werden dann die Zahlungseingänge prüfen.</td>
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