@extends('layouts.app')

@section('title', 'Payment')

@section('styles')
    <style type="text/css" media="screen, projection">

        fieldset {
            padding: 1em;
            border: 1px solid #000;
            width: 400px;
            margin: 10px;
            border-color: #5F6876 !important;
        }
        .paymentLabel {
            margin-right: 10px;
            width:auto;
            float:none;
            padding-top: 0.3em;
            text-align: right;
        }
        .paymentformInput {
            font-size: 1em;
            border: 1px solid #000;
            padding: 0.1em;
        }

        .paymentformInput, .inputIframe {
            display: block;
            margin-bottom: 10px;
        }

        .paymentformInput {
            width: 200px;
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
                <h2 class="cabin-head-booking2">{{ __('payment.choosePaymentHeading') }}</h2><h2 class="cabin-head-booking2">{{ __('payment.step2') }}<span class="glyphicon glyphicon-booking2 glyphicon-question-sign" title="{{ __('payment.step2Title') }}"></span></h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking2"></div>
    </div>

    <main>
        <div class="container-fluid text-center container-fluid-booking2">
            @if (session()->has('bookingFailureStatus'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Whoops!</strong> {{ session()->get('bookingFailureStatus') }}
                </div>
            @endif

            @if (session()->has('choosePaymentNullData'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Whoops!</strong> {{ session()->get('choosePaymentNullData') }}
                </div>
            @endif

            @if ($errors->has('payment'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Whoops!</strong> {{ $errors->first('payment') }}
                </div>
            @endif

            @if (session()->has('availableStatus') && session()->get('availableStatus') === 'success')
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>{{ __('payment.wellDone') }}</strong> {{ __('payment.wellDoneMsg') }}
                </div>
            @endif

            @if(isset($editBooking))
                <form action="{{ route('payment.store', $editBooking) }}" method="post" name="paymentform">
            @else
                <form action="{{ route('payment.store') }}" method="post" name="paymentform">
            @endif

                {{ csrf_field() }}

                <div class="panel panel-booking2 panel-default text-left panel-default-booking2">
                    <div class="panel-body panel-body-booking2">
                        <div class="row content row-booking2">
                            <div class="col-sm-7 text-left col-sm-7-booking2">
                                <div class="row row-booking2">
                                    <div class="col-sm-12 month-opening-booking2 col-sm-12-booking2">
                                        <div class="form-group row row-booking2">
                                            <ul class="payment-options-booking2">
                                                <li class="li-head-booking2">{{ __('payment.kindOfPayment') }}</li>

                                                <li class="check-it-list-booking2 check-it-list-spe-booking2 line-col-booking2" id="bill-booking2">
                                                    <input type="radio" name="payment" class="check-it-booking2 radio-payment" value="payByBill" @if($payByBillPossible === 'no') disabled @endif> {{ __('payment.payByBill') }} <span class="glyphicon glyphicon-booking2 glyphicon-question-sign" title="{{ __('payment.payByBillTitle') }}"></span>
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
                                                    <input type="radio" name="payment" class="check-it-booking2 radio-payment" id="creditCard" value="creditCard"> {{ __('payment.creditcard') }}
                                                </li>

                                                <li class="pay-logo-booking2 line-col-booking2">
                                                    <img src="{{ asset('storage/img/mc_acc_opt_70_3x.png') }}" class="pay-figure-booking2" alt="pay-option" title="Mastercard">
                                                    <img src="{{ asset('storage/img/Visa_BlueGradient.png') }}" class="pay-figure-booking2" alt="pay-option" title="VISA">
                                                </li>

                                                <!-- Credit card: Payone hosted Iframe begin -->
                                                <div id="creditcard" style="display: none;">

                                                        <fieldset>
                                                            <input type="hidden" name="pseudocardpan" id="pseudocardpan">
                                                            <input type="hidden" name="truncatedcardpan" id="truncatedcardpan">
                                                            <input type="hidden" name="cardtypeResponse" id="cardtypeResponse">
                                                            <input type="hidden" name="cardexpiredateResponse" id="cardexpiredateResponse">

                                                            <div>
                                                                <img src="{{ asset('storage/img/mc_acc_opt_70_3x.png') }}" class="pay-figure-booking2" alt="pay-option" title="Mastercard"  style="border: #EFEFEF solid 3px; max-width: 100px; max-height: 30px;" id="mastercard">
                                                                <img src="{{ asset('storage/img/Visa_BlueGradient.png') }}" class="pay-figure-booking2" alt="pay-option" title="VISA"  style="border: #EFEFEF  solid 3px; max-width: 100px; max-height: 30px;" id="visa">
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="paymentLabel" for="cardpanInput">{{ __('payment.cardPan') }}:</label>
                                                                <span class="inputIframe" id="cardpan"></span>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="paymentLabel" for="cvcInput">CVC:</label>
                                                                <span id="cardcvc2" class="inputIframe"></span>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="paymentLabel" for="expireInput">{{ __('payment.expireDate') }}:</label>
                                                                <span id="expireInput" class="inputIframe">
                                                                    <span id="cardexpiremonth"></span>
                                                                    <span id="cardexpireyear"></span>
                                                            </span>
                                                            </div>

                                                            <div id="errorOutput"></div>


                                                        </fieldset>

                                                    <div id="paymentform"></div>

                                                </div>
                                                <!-- Credit card: Payone hosted Iframe end -->
                                            </ul>

                                            <ul class="payment-options-booking2">
                                                <li class="li-head-booking2">{{ __('payment.termsConditions') }}</li>
                                                <li class="check-it-list-booking2{{ $errors->has('confirmThePrivacy') ? ' has-error' : '' }}">
                                                    <input type="checkbox" name="confirmThePrivacy" class="check-it-booking2"><a href="/data/protection"> {{ __('payment.confirmThePrivacy') }}</a>
                                                    @if ($errors->has('confirmThePrivacy'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('confirmThePrivacy') }}</strong>
                                                        </span>
                                                    @endif
                                                </li>
                                                <li class="check-it-list-booking2{{ $errors->has('confirmTheTerms') ? ' has-error' : '' }}">
                                                    <input type="checkbox" name="confirmTheTerms" class="check-it-booking2"><a href="/terms"> {{ __('payment.confirmTheTerms') }}</a>
                                                    @if ($errors->has('confirmTheTerms'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('confirmTheTerms') }}</strong>
                                                        </span>
                                                    @endif
                                                </li>
                                            </ul>
                                            <ul class="payment-options-booking2">
                                                <li class="li-head-booking2">{{ __('payment.newscenter') }}</li>
                                                <li class="check-it-list-booking2"><input class="check-it-booking2" type="checkbox" checked="checked" disabled=""> {{ __('payment.informationBooking') }}</li>
                                                <li class="check-it-list-booking2"><input class="check-it-booking2" type="checkbox" checked="checked"> {{ __('payment.updatedAboutSystem') }}</li>
                                                @if(Auth::user()->usrNewsletter !== 1)
                                                    <li class="check-it-list-booking2"><input class="check-it-booking2" type="checkbox" checked="checked"> {{ __('payment.subscribeNewsletter') }}</li>
                                                @endif
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
                                                    <h5>{{ __('payment.yourAmount') }}</h5>
                                                    <span class="label label-info label-cabinlist"><input type="checkbox" class="moneyBalance" name="moneyBalance" value="1"> {{ __('payment.redeemNow') }} {{ number_format($moneyBalance, 2, ',', '.') }}&euro;</span>
                                                </div>
                                            </div>
                                        @endif

                                        @isset($sumPrepaymentAmount)
                                            <div class="row row-booking2 sumPrepayAmount" data-sumprepayamount="{{ $sumPrepaymentAmount }}">
                                                <div class="col-sm-12 month-opening-booking2 col-sm-12-booking2">
                                                    <h5>{{ __('payment.completePayment') }}<span class="glyphicon glyphicon-booking2 glyphicon-question-sign" title="{{ __('cart.amountTitle') }}"></span></h5>
                                                </div>
                                            </div>
                                            <div class="row row-booking2">
                                                <div class="col-sm-12 col-sm-12-extra-booking2 col-sm-12-booking2-booking2">
                                                    <p class="info-listing-booking2">{{ __('payment.deposit') }}:</p><p class="info-listing-price-booking2">{{ number_format($sumPrepaymentAmount, 2, ',', '.') }} &euro;</p>
                                                    <div class="afterRedeem" style="display: none">
                                                        <div class="redeemAmount"></div>
                                                        <div class="moneyBalance"></div>
                                                        <div class="afterRedeemAmount"></div>
                                                    </div>

                                                    <div class="serviceFee">
                                                        <p class="info-listing-booking2">{{ __('payment.serviceFee') }}:</p><p class="info-listing-price-booking2">{{ $serviceTax }} %</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row row-booking2">
                                                <div class="col-sm-12 col-sm-12-extra-booking2 col-sm-12-booking2 totalPrepayAmount">
                                                    <h5 class="info-listing-booking2">{{ __('payment.paymentIncl') }}.<br /> {{ __('payment.paymentInclServiceFee') }}:</h5><h5 class="info-listing-price-booking2 sumPrepayServiceTotal">{{ number_format($prepayServiceTotal, 2, ',', '.') }} &euro;</h5>
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
                        <button type="submit" class="btn btn-default btn-default-booking2 btn-sm btn-details-booking2 nonCreditCardButton">{{ __('payment.bookCabin') }}</button>
                        <input id="paymentsubmit" class="btn btn-default btn-default-booking2 btn-sm btn-details-booking2" type="button" value="{{ __('payment.bookCabin') }}" onclick="check();" style="display: none;">
                    </div>
                </div>
            </form>

        </div>
        <br><br>
    </main>

    @php
        /* Main parameters for authorize request */
        $aid                     = env('AID');
        $mid                     = env('MID');
        $portalid                = env('PORTAL_ID');
        $api_version             = env('API_VERSION');
        $mode                    = env('MODE');
        $request                 = "creditcardcheck"; // or "authorization";
        $responsetype            = "JSON"; // or "REDIRECT";
        $storecarddata           = "yes"; // yes: Card data is stored, a pseudo card number is returned. no: Card data is not stored
        $successurl              = env('SUCCESSURL');
        $errorurl                = env('ERRORURL');
        $backurl                 = env('BACKURL');
        $encoding                = env('ENCODING');
        $key                     = env('KEY');
        $clearingtype            = "cc"; //cc - Credit card, rec - Invoice, cod - Cash on delivery, sb - Online Bank Transfer, wlt - e-wallet, fnc - Financing
        $ecommercemode           = "3dsecure";

        /* Parameter ( Normal data ) */
        $reference               = random_int(111, 99999).uniqid();
        $pr[1]                   = str_replace(".", "", $prepayServiceTotal);
        $no[1]                   = "1";
        $amount                  = str_replace(".", "", $prepayServiceTotal);
        $currency                = "EUR";
        $param                   = $order_number;
        $narrative_text          = $order_number;
        $document_date           = date('Ymd');
        $booking_date            = date('Ymd');
        $due_time                = mktime(0, 0, 0, date('n'), date('j') + 1);
        $id[1]                   = random_int(111, 99999).uniqid();
        $de[1]                   = $order_number; // Item description
        $va[1]                   = env('VATRATE');
        $sd[1]                   = date('Ymd');
        $ed[1]                   = date('Ymd');
        $customerid              = random_int(9999, 9999999999);
        $userid                  = random_int(9999, 9999999999); //Debtor Id (Payone)
        $personalid              = random_int(9999, 9999999999);

        /* Parameter ( Invoice ) */
        $invoiceid               = $order_number;
        $invoice_deliverydate    = date('Ymd');
        $invoice_deliveryenddate = date('Ymd');
        $invoice_deliverymode    = "P"; //PDF
        $invoiceappendix         = $order_number;

        /* Parameter ( personal data ) */
        /* Condition for user country */
        if(Auth::user()->usrCountry === 'Deutschland') {
           $countryName          = "DE";
        }
        elseif(Auth::user()->usrCountry === 'Österreich') {
           $countryName          = "AT";
        }
        else {
           $countryName          = "IT";
        }

        $country                 = $countryName;
        $salutation              = Auth::user()->salutation;
        $title                   = Auth::user()->title;
        $firstname               = Auth::user()->usrFirstname;
        $lastname                = Auth::user()->usrLastname;
        $company                 = Auth::user()->company;
        $street                  = Auth::user()->usrAddress;
        $zip                     = Auth::user()->usrZip;
        $city                    = Auth::user()->usrCity;
        $email                   = Auth::user()->usrEmail;
        $telephonenumber         = Auth::user()->usrTelephone;
        $gender                  = Auth::user()->gender;
        $language                = env('APP_LOCALE');
        $vatid                   = env('VATID');

        /* Parameter ( delivery data ) */
        $shipping_firstname      = Auth::user()->usrFirstname;
        $shipping_lastname       = Auth::user()->usrLastname;
        $shipping_company        = Auth::user()->company;
        $shipping_street         = Auth::user()->usrAddress;
        $shipping_zip            = Auth::user()->usrZip;
        $shipping_city           = Auth::user()->usrCity;
        $shipping_country        = $countryName;

        /* Hashing the parameters in sorted order */
        $hash = hash_hmac("sha384", $aid .
        $amount .
        $api_version .
        $backurl .
        $booking_date .
        $clearingtype .
        $currency .
        $customerid  .
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
        $storecarddata .
        $successurl .
        $userid .
        $va[1],
        $key);
    @endphp
@endsection

@push('scripts')
    <script type="text/javascript" src="https://secure.pay1.de/client-api/js/v1/payone_hosted_min.js"></script>
    <script>
        /* Credit card functionality */
        $("input[name='payment']").on("click", function(){
            if($(this).val() === 'creditCard') {
                $("#paymentsubmit").show();
                $(".nonCreditCardButton").hide();
            }
            else {
                $(".nonCreditCardButton").show();
                $("#paymentsubmit").hide();
            }
        });
        /* Payment gateway functionality begin */
        var request,
            supportedCardtypes = ["V", "M"],
            config = {
                fields: {
                    cardpan: {
                        selector: "cardpan",                 // put name of div-container here
                        type: "text",                        // text (default), password, tel
                        style: "font-size: 1em; border: 1px solid #000;"
                    },
                    cardcvc2: {
                        selector: "cardcvc2",                // put name of div-container here
                        type: "password",                    // select(default), text, password, tel
                        style: "font-size: 1em; border: 1px solid #000;",
                        size: "4",
                        maxlength: "4",
                        length: { "V": 3, "M": 3 } // enforce 3 digit CVC für VISA and Mastercard
                    },
                    cardexpiremonth: {
                        selector: "cardexpiremonth",         // put name of div-container here
                        type: "select",                      // select(default), text, password, tel
                        size: "2",
                        maxlength: "2",
                        iframe: {
                            width: "40px"
                        },
                        style: "font-size: 14px; width: 30px; border: solid 1px #000; height: 22px;"
                    },
                    cardexpireyear: {
                        selector: "cardexpireyear",          // put name of div-container here
                        type: "select",                      // select(default), text, password, tel
                        iframe: {
                            width: "60px"
                        },
                        style: "font-size: 14px; width: 50px; border: solid 1px #000; height: 22px;"
                    }
                },
                defaultStyle: {
                    input: "font-size: 1em; border: 1px solid #000; width: 175px;",
                    select: "font-size: 1em; border: 1px solid #000;",
                    iframe: {
                        height: "32px",
                        width: "180px"
                    }
                },
                autoCardtypeDetection: {
                    supportedCardtypes: supportedCardtypes,
                    callback: function(detectedCardtype) {
                        // For the output container below.
                        /*document.getElementById('autodetectionResponsePre').innerHTML = detectedCardtype;*/
                        if (detectedCardtype === 'V') {
                            document.getElementById('visa').style.borderColor = '#5F6876';
                            document.getElementById('mastercard').style.borderColor = '#EFEFEF';
                        }
                        else if (detectedCardtype === 'M') {
                            document.getElementById('visa').style.borderColor = '#EFEFEF';
                            document.getElementById('mastercard').style.borderColor = '#5F6876';
                        }
                        else {
                            document.getElementById('visa').style.borderColor = '#EFEFEF';
                            document.getElementById('mastercard').style.borderColor = '#EFEFEF';
                        }
                    }//,
                    // deactivate: true // To turn off automatic card type detection.
                },
                error: "errorOutput",                        // area to display error-messages (optional)
                language: Payone.ClientApi.Language.de       // Language to display error-messages. (default: Payone.ClientApi.Language.en)
            };
        request = {
            aid                     : '<?php echo $aid; ?>',
            clearingtype            : '<?php echo $clearingtype; ?>',
            reference               : '<?php echo $reference; ?>',
            'pr[1]'                 : '<?php echo $pr[1]; ?>',
            'no[1]'                 : '<?php echo $no[1]; ?>',
            amount                  : '<?php echo $amount; ?>',
            currency                : '<?php echo $currency; ?>',
            param                   : '<?php echo $param; ?>',
            narrative_text          : '<?php echo $narrative_text; ?>',
            document_date           : '<?php echo $document_date; ?>',
            booking_date            : '<?php echo $booking_date; ?>',
            due_time                : '<?php echo $due_time; ?>',
            invoiceid               : '<?php echo $invoiceid; ?>',
            invoice_deliverymode    : '<?php echo $invoice_deliverymode; ?>',
            invoice_deliverydate    : '<?php echo $invoice_deliverydate; ?>',
            invoice_deliveryenddate : '<?php echo $invoice_deliveryenddate; ?>',
            invoiceappendix         : '<?php echo $invoiceappendix; ?>',
            'id[1]'                 : '<?php echo $id[1]; ?>',
            'de[1]'                 : '<?php echo $de[1]; ?>',
            'va[1]'                 : '<?php echo $va[1]; ?>',
            'sd[1]'                 : '<?php echo $sd[1]; ?>',
            'ed[1]'                 : '<?php echo $ed[1]; ?>',
            customerid              : '<?php echo $customerid; ?>',
            userid                  : '<?php echo $userid; ?>',
            salutation              : '<?php echo $salutation; ?>',
            title                   : '<?php echo $title; ?>',
            firstname               : '<?php echo $firstname; ?>',
            lastname                : '<?php echo $lastname; ?>',
            company                 : '<?php echo $company; ?>',
            street                  : '<?php echo $street; ?>',
            zip                     : '<?php echo $zip; ?>',
            city                    : '<?php echo $city; ?>',
            country                 : '<?php echo $country; ?>',
            email                   : '<?php echo $email; ?>',
            telephonenumber         : '<?php echo $telephonenumber; ?>',
            language                : '<?php echo $language; ?>',
            vatid                   : '<?php echo $vatid; ?>',
            gender                  : '<?php echo $gender; ?>',
            personalid              : '<?php echo $personalid; ?>',
            shipping_firstname      : '<?php echo $shipping_firstname; ?>',
            shipping_lastname       : '<?php echo $shipping_lastname; ?>',
            shipping_company        : '<?php echo $shipping_company; ?>',
            shipping_street         : '<?php echo $shipping_street; ?>',
            shipping_zip            : '<?php echo $shipping_zip; ?>',
            shipping_city           : '<?php echo $shipping_city; ?>',
            shipping_country        : '<?php echo $shipping_country; ?>',
            ecommercemode           : '<?php echo $ecommercemode; ?>',
            successurl              : '<?php echo $successurl; ?>',
            errorurl                : '<?php echo $errorurl; ?>',
            mid                     : '<?php echo $mid; ?>',
            portalid                : '<?php echo $portalid; ?>',
            request                 : '<?php echo $request; ?>',
            api_version             : '<?php echo $api_version; ?>',
            mode                    : '<?php echo $mode; ?>',
            responsetype            : '<?php echo $responsetype; ?>',
            encoding                : '<?php echo $encoding; ?>',
            storecarddata           : '<?php echo $storecarddata; ?>',
            hash                    : '<?php echo $hash; ?>'
        };
        var iframes = new Payone.ClientApi.HostedIFrames(config, request);

        function check() {
            if (iframes.isComplete()) {
                iframes.creditCardCheck('checkCallback');
            }
            else {
                console.debug("not complete");
            }
        }

        function checkCallback(response) {
            if (response.status === "VALID") {
                document.getElementById("pseudocardpan").value = response.pseudocardpan;
                document.getElementById("truncatedcardpan").value = response.truncatedcardpan;
                document.getElementById("cardtypeResponse").value = response.cardtype;
                document.getElementById("cardexpiredateResponse").value = response.cardexpiredate;
                document.paymentform.submit();
            }
        }
        /* Payment gateway functionality end */

        /* Environment variables to payment js file */
        window.environment = {
            service_tax_one: '{{ env('SERVICE_TAX_ONE') }}',
            service_tax_two: '{{ env('SERVICE_TAX_TWO') }}',
            service_tax_three: '{{ env('SERVICE_TAX_THREE') }}',
            service_tax_paybybill_one: '{{ env('SERVICE_TAX_PAYBYBILL_ONE') }}',
            service_tax_paybybill_two: '{{ env('SERVICE_TAX_PAYBYBILL_TWO') }}',
            service_tax_paybybill_three: '{{ env('SERVICE_TAX_PAYBYBILL_THREE') }}',
            service_tax_sofort_one: '{{ env('SERVICE_TAX_SOFORT_ONE') }}',
            service_tax_sofort_two: '{{ env('SERVICE_TAX_SOFORT_TWO') }}',
            service_tax_sofort_three: '{{ env('SERVICE_TAX_SOFORT_THREE') }}',
            service_tax_paydirect_one: '{{ env('SERVICE_TAX_PAYDIRECT_ONE') }}',
            service_tax_paydirect_two: '{{ env('SERVICE_TAX_PAYDIRECT_TWO') }}',
            service_tax_paydirect_three: '{{ env('SERVICE_TAX_PAYDIRECT_THREE') }}',
            service_tax_paypal_one: '{{ env('SERVICE_TAX_PAYPAL_ONE') }}',
            service_tax_paypal_two: '{{ env('SERVICE_TAX_PAYPAL_TWO') }}',
            service_tax_paypal_three: '{{ env('SERVICE_TAX_PAYPAL_THREE') }}',
            service_tax_creditcard_one: '{{ env('SERVICE_TAX_CREDITCARD_ONE') }}',
            service_tax_creditcard_two: '{{ env('SERVICE_TAX_CREDITCARD_TWO') }}',
            service_tax_creditcard_three: '{{ env('SERVICE_TAX_CREDITCARD_THREE') }}',
            redeemedAmountPayment: '{{ __('payment.redeemAmount') }}',
            moneyBalancePayment: '{{ __('payment.moneyBalance') }}',
            serviceFeePayment: '{{ __('payment.serviceFee') }}',
            amountPayment: '{{ __('payment.amountPayment') }}'
        }
    </script>
@endpush