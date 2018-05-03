@extends('layouts.app')

@section('title', 'Payment')

@section('styles')
    <style type="text/css" media="screen, projection">

        fieldset {
            padding: 1em;
            border: 1px solid #000;
            width: 275px;
            margin: 10px;
        }
        .paymentLabel {
            margin-right: 10px;
            float: left;
            width: 80px;
            padding-top: 0.3em;
            text-align: right;
        }
        .paymentformInput, .paymentformSelect{
            font-size: 1em;
            border: 1px solid #000;
            padding: 0.1em;
        }
        .paymentformSelect {
            margin-right: 10px;
        }

        .paymentformInput, .inputIframe, .paymentformSelect {
            display: block;
            margin-bottom: 10px;
        }

        .paymentformInput {
            width: 175px;
        }

        #paymentsubmit {
            float: right;
            width: auto;
            padding: 5px;
            margin-bottom: 0px;
            margin-right: 10px;
        }
        #errorOutput {
            text-align: center;
            color: #ff0000;
            display: block;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid bg-3 text-center container-fluid-booking2">
        <div class="col-md-2 col-md-2-booking2"></div>
        <div class="col-md-8 col-md-8-booking2" id="list-filter-booking2">
            <nav class="navbar navbar-default navbar-default-booking2">
                <h2 class="cabin-head-booking2">Choose a payment</h2><h2 class="cabin-head-booking2">Step 1 of 3<span class="glyphicon glyphicon-booking2 glyphicon-question-sign" title="You are on the first of three steps to book a cabin night. Control your data and enter next step to get to the next step."></span></h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking2"></div>
    </div>

    <main>
        <div class="container-fluid text-center container-fluid-booking2">
            @if (session()->has('choosePaymentNullData'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>OOPS!</strong> {{ session()->get('choosePaymentNullData') }}
                </div>
            @endif

            @if ($errors->has('payment'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>OOPS!</strong> {{ $errors->first('payment') }}
                </div>
            @endif

            @if (session()->has('availableStatus') && session()->get('availableStatus') === 'success')
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Well done!</strong> Here you can choose your payment.
                </div>
            @endif

            <form action="{{ route('payment.store') }}" method="post">

                {{ csrf_field() }}

                <div class="panel panel-booking2 panel-default text-left panel-default-booking2">
                    <div class="panel-body panel-body-booking2">
                        <div class="row content row-booking2">
                            <div class="col-sm-7 text-left col-sm-7-booking2">
                                <div class="row row-booking2">
                                    <div class="col-sm-12 month-opening-booking2 col-sm-12-booking2">
                                        <div class="form-group row row-booking2">
                                            <ul class="payment-options-booking2">
                                                <li class="li-head-booking2">Kind of payment</li>
                                                <li class="check-it-list-booking2 check-it-list-spe-booking2 line-col-booking2" id="bill-booking2">
                                                    <input type="radio" name="payment" class="check-it-booking2 radio-payment" value="payByBill"> Pay by bill
                                                </li>

                                                <li class="check-it-list-booking2 check-it-list-spe-booking2">
                                                    <input type="radio" name="payment" class="check-it-booking2 radio-payment" value="sofort"> Klarna
                                                </li>

                                                <li class="pay-logo-booking2">
                                                    <img src="{{ asset('storage/img/logo_black.png') }}" class="pay-figure-booking2" alt="pay-option" title="SOFORT Überweisung (Klarna)">
                                                </li>

                                                <li class="check-it-list-booking2 check-it-list-spe-booking2 line-col-booking2">
                                                    <input type="radio" name="payment" class="check-it-booking2 radio-payment" value="payDirect"> Paydirekt
                                                </li>

                                                <li class="pay-logo-booking2 line-col-booking2">
                                                    <img src="{{ asset('storage/img/paydirekt_logo_4C.png') }}" class="pay-figure-booking2" alt="pay-option" title="Paydirect">
                                                </li>

                                                <li class="check-it-list-booking2 check-it-list-spe-booking2">
                                                    <input type="radio" name="payment" class="check-it-booking2 radio-payment" value="payPal"> PayPal
                                                </li>

                                                <li class="pay-logo-booking2">
                                                    <img src="{{ asset('storage/img/de-pp-logo-100px.png') }}" class="pay-figure-booking2" alt="pay-option" title="PayPal">
                                                </li>

                                                <li class="check-it-list-booking2 check-it-list-spe-booking2 line-col-booking2">
                                                    <input type="radio" name="payment" class="check-it-booking2 radio-payment" id="creditCard" value="creditCard"> Creditcard
                                                </li>

                                                <li class="pay-logo-booking2 line-col-booking2">
                                                    <img src="{{ asset('storage/img/mc_acc_opt_70_3x.png') }}" class="pay-figure-booking2" alt="pay-option" title="Mastercard"><img src="{{ asset('storage/img/Visa_BlueGradient.png') }}" class="pay-figure-booking2" alt="pay-option" title="VISA">
                                                </li>

                                                <!-- Credit card: Payone hosted Iframe begin -->
                                                <div id="creditcard" style="display: none;">
                                                    <form name="paymentform" action="" method="post">
                                                        <fieldset>
                                                            <input type="hidden" name="pseudocardpan" id="pseudocardpan">
                                                            <input type="hidden" name="truncatedcardpan" id="truncatedcardpan">

                                                            <!-- configure your cardtype-selection here -->
                                                            <label class="paymentLabel" for="cardtypeInput">Card type</label>
                                                            <span id="cardtype" class="inputIframe"></span>

                                                            <label class="paymentLabel" for="cardpanInput">Cardpan:</label>
                                                            <span class="inputIframe" id="cardpan"></span>

                                                            <label class="paymentLabel" for="cvcInput">CVC:</label>
                                                            <span id="cardcvc2" class="inputIframe"></span>

                                                            <label class="paymentLabel" for="expireInput">Expire Date:</label>
                                                            <span id="expireInput" class="inputIframe">
                                                            <span id="cardexpiremonth"></span>
                                                            <span id="cardexpireyear"></span>
                                                        </span>

                                                            <label class="paymentLabel" for="firstname">Firstname:</label>
                                                            <input class="paymentformInput" id="firstname" type="text" name="firstname" value="">

                                                            <label class="paymentLabel" for="lastname">Lastname:</label>
                                                            <input class="paymentformInput" id="lastname" type="text" name="lastname" value="">

                                                            <div id="errorOutput"></div>
                                                            <input class="paymentformInput" id="paymentsubmit" type="button" value="Submit" onclick="check();">
                                                        </fieldset>
                                                    </form>
                                                    <div id="paymentform"></div>
                                                </div>
                                                <!-- Credit card: Payone hosted Iframe end -->

                                            </ul>

                                            <ul class="payment-options-booking2">
                                                <li class="li-head-booking2">Terms and conditions</li>
                                                <li class="check-it-list-booking2"><input class="check-it-booking2" type="checkbox"><a href="#"> Please confirm the privacy of Huetten-Holiday.de</a></li>
                                                <li class="check-it-list-booking2"><input class="check-it-booking2" type="checkbox"><a href="#"> Please confirm the terms and conditions of Huetten-Holiday.de</a></li>
                                            </ul>
                                            <ul class="payment-options-booking2">
                                                <li class="li-head-booking2">Newscenter</li>
                                                <li class="check-it-list-booking2"><input class="check-it-booking2" type="checkbox" checked="checked" disabled=""> Informations about your booking</li>
                                                <li class="check-it-list-booking2"><input class="check-it-booking2" type="checkbox" checked="checked"> Updates about our system</li>
                                                <li class="check-it-list-booking2"><input class="check-it-booking2" type="checkbox" checked="checked"> Subscribe to Newsletter</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3 col-sm-3-booking2">
                                <div class="panel panel-default booking-box-booking2 panel-booking2 panel-default-booking2">

                                    <!-- Calculation amount with money balance -->
                                    <div class="panel-body panel-body-booking2">
                                        @if(isset($moneyBalance) && $moneyBalance > 0)
                                            <div class="row row-booking2" data-redeem="{{ $moneyBalance }}">
                                                <div class="col-sm-12 col-sm-12-booking2 month-opening-booking2">
                                                    <h5>Your Amount</h5>
                                                    <span class="label label-info label-cabinlist"><input type="checkbox" class="moneyBalance" name="moneyBalance" value="1"> Redeem now! {{ number_format($moneyBalance, 2, ',', '.') }}&euro;</span>
                                                </div>
                                            </div>
                                        @endif

                                        @isset($sumPrepaymentAmount)
                                            <div class="row row-booking2 sumPrepayAmount" data-sumprepayamount="{{ $sumPrepaymentAmount }}">
                                                <div class="col-sm-12 month-opening-booking2 col-sm-12-booking2">
                                                    <h5>Complete Payment<span class="glyphicon glyphicon-booking2 glyphicon-question-sign" title="Here all costs are listed again. The service fee helps us operate Huetten-Holiday and offer services like our live-chat for your trip. It contains sales tax."></span></h5>
                                                </div>
                                            </div>
                                            <div class="row row-booking2">
                                                <div class="col-sm-12 col-sm-12-extra-booking2 col-sm-12-booking2-booking2">
                                                    <p class="info-listing-booking2">Deposit:</p><p class="info-listing-price-booking2">{{ number_format($sumPrepaymentAmount, 2, ',', '.') }}&euro;</p>
                                                    <div class="afterRedeem" style="display: none">
                                                        <p class="info-listing-booking2">Deducted:</p><p class="info-listing-price-booking2 reducedAmount"></p>
                                                        <p class="info-listing-booking2">Amount:</p><p class="info-listing-price-booking2 afterRedeemAmount"></p>
                                                    </div>
                                                    <p class="info-listing-booking2">Service fee:</p><p class="info-listing-price-booking2">{{ $serviceTax }}%</p>
                                                </div>
                                            </div>
                                            <div class="row row-booking2">
                                                <div class="col-sm-12 col-sm-12-extra-booking2 col-sm-12-booking2">
                                                    <h5 class="info-listing-booking2">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking2 sumPrepayServiceTotal">{{ number_format($prepayServiceTotal, 2, ',', '.') }}&euro;</h5>
                                                </div>
                                            </div>
                                        @endisset
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div id="btn-ground-2-booking2">
                        <button type="submit" class="btn btn-default btn-default-booking2 btn-sm btn-details-booking2">Book the Cabin</button>
                    </div>
                </div>
            </form>

        </div>
        <br><br>
    </main>

    @php
        $aid                  = env('AID');
        $amount               = str_replace(".", "", $prepayServiceTotal);
        $api_version          = env('API_VERSION');
        $booking_date         = date('Ymd');
        $clearingtype         = 'cc'; //cc - Credit card, rec - Invoice, cod - Cash on delivery, sb - Online Bank Transfer, wlt - e-wallet, fnc - Financing
        $currency             = 'EUR';
        $customerid           = mt_rand(111, 99999999);
        $de[1]                = 'Credit card Booking'; // Item description
        $document_date        = date('Ymd');
        $due_time             = date('Ymd');
        $ecommercemode        = '3dsecure';
        $encoding             = env('ENCODING');
        $errorurl             = 'https://payone.test/cancelled.php?reference=your_unique_reference';
        $id[1]                = mt_rand(111, 99999999);
        $invoice_deliverydate = date('Ymd');
        $invoice_deliveryenddate = date('Ymd');
        $invoice_deliverymode = 'P'; //PDF
        $invoiceappendix      = 'Dynamic text on invoice'; //Dynamic text on the invoice
        $invoiceid            = mt_rand(111, 99999999);
        $mid                  = env('MID');
        $mode                 = env('MODE');
        $narrative_text       = "creditcardstatement";
        $no[1]                = "1";
        $param                = str_shuffle('abcdefghij');
        $portalid             = env('PORTAL_ID');
        $pr[1]                = str_replace(".", "", $prepayServiceTotal);
        $reference            = uniqid();
        $request              = 'authorization';
        $responsetype         = 'JSON';
        $successurl           = 'https://payone.test/success.php?reference=your_unique_reference';
        $userid               = mt_rand(111, 99999999); //Debtor Id (Payone)
        $va[1]                = "19";
        $key                  = env('KEY');

        $hash = hash_hmac("sha384", $aid .
                $amount .
                $api_version .
                $booking_date .
                $clearingtype .
                $currency .
                $customerid .
                $de[1] .
                $document_date .
                $due_time .
                $ecommercemode .
                $encoding .
                $errorurl .
                $id[1] .
                $invoice_deliverydate .
                $invoice_deliveryenddate .
                $invoice_deliverymode .
                $invoiceappendix .
                $invoiceid .
                $mid .
                $mode .
                $narrative_text .
                $no[1] .
                $param .
                $portalid .
                $pr[1] .
                $reference .
                $request .
                $responsetype .
                $successurl .
                $userid .
                $va[1],
                $key);
    @endphp
@endsection

@push('scripts')
    <script type="text/javascript" src="https://secure.pay1.de/client-api/js/v1/payone_hosted_min.js"></script>
    <script>
        /* Payment gateway functionality begin */
        var request, config;

        config = {
            fields: {
                cardtype: {
                    selector: "cardtype",
                    cardtypes: ["V", "M", "A"]
                },
                cardpan: {
                    selector: "cardpan",                 // put name of your div-container here
                    type: "text",                        // text (default), password, tel
                    style: "font-size: 1em; border: 1px solid #000;"
                },
                cardcvc2: {
                    selector: "cardcvc2",                // put name of your div-container here
                    type: "password",                    // select(default), text, password, tel
                    style: "font-size: 1em; border: 1px solid #000;",
                    size: "4",
                    maxlength: "4",
                    length: { "A": 4, "V": 3, "M": 3 } // Set required CVC length per card type.
                },
                cardexpiremonth: {
                    selector: "cardexpiremonth",         // put name of your div-container here
                    type: "select",                      // select(default), text, password, tel
                    size: "2",
                    maxlength: "2",
                    iframe: {
                        width: "50px"
                    }
                },
                cardexpireyear: {
                    selector: "cardexpireyear",          // put name of your div-container here
                    type: "select",                      // select(default), text, password, tel
                    iframe: {
                        width: "80px"
                    }
                }
            },
            defaultStyle: {
                input: "font-size: 1em; border: 1px solid #000; width: 175px;",
                select: "font-size: 1em; border: 1px solid #000;",
                iframe: {
                    height: "33px",
                    width: "180px"
                }
            },
            error: "errorOutput",                        // area to display error-messages (optional)
            language: Payone.ClientApi.Language.de       // Language to display error-messages. (default: Payone.ClientApi.Language.en)
        };

        request = {
            aid: '<?php echo $aid; ?>',
            amount: '<?php echo $amount; ?>',
            api_version: '<?php echo $api_version; ?>',
            booking_date: '<?php echo $booking_date; ?>',
            clearingtype: '<?php echo $clearingtype; ?>',
            currency: '<?php echo $currency; ?>',
            'de[1]': '<?php echo $de[1]; ?>',
            ecommercemode: '<?php echo $ecommercemode; ?>',
            encoding: '<?php echo $encoding; ?>',
            errorurl: '<?php echo $errorurl; ?>',
            invoice_deliverymode: '<?php echo $invoice_deliverymode; ?>',
            invoiceappendix: '<?php echo $invoiceappendix; ?>',
            mid: '<?php echo $mid; ?>',
            mode: '<?php echo $mode; ?>',
            portalid: '<?php echo $portalid; ?>',
            reference: '<?php echo $reference; ?>',
            request: '<?php echo $request; ?>',
            responsetype: '<?php echo $responsetype; ?>',
            successurl: '<?php echo $successurl; ?>',
            key: '<?php echo $key; ?>',
            storecarddata: 'yes',
            hash: '<?php echo $hash; ?>'
        };
        var iframes = new Payone.ClientApi.HostedIFrames(config, request);

        function check() {
            console.debug("check");
            // Function called by submitting PAY-button
            if (iframes.isComplete()) {
                iframes.creditCardCheck('checkCallback');// Perform "CreditCardCheck" to create and get a "checkCallback". PseudoCardPan; then call your function
            } else {
                console.debug("not complete");
            }
        }

        function checkCallback(response) {
            console.debug(response);
            if (response.status === "VALID") {
                document.getElementById("pseudocardpan").value = response.pseudocardpan;
                document.getElementById("truncatedcardpan").value = response.truncatedcardpan;
                document.paymentform.submit();
            }
        }
        /* Payment gateway functionality end */

        window.environment = {
            service_tax_one: '{{ env('SERVICE_TAX_ONE') }}',
            service_tax_two: '{{ env('SERVICE_TAX_TWO') }}',
            service_tax_three: '{{ env('SERVICE_TAX_THREE') }}'
        }
    </script>
@endpush