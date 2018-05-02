@extends('layouts.app')

@section('title', 'Payment')

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

                                                <!-- Credit card: Payone hosted Iframe begin -->
                                                <div id="creditcard" style="display: none;">
                                                    <script type="text/javascript" src="https://secure.pay1.de/client-api/js/v1/payone_hosted_min.js"></script>
                                                    <form name="paymentform" action="" method="post">
                                                        <fieldset>
                                                            <input type="hidden" name="pseudocardpan" id="pseudocardpan">
                                                            <input type="hidden" name="truncatedcardpan" id="truncatedcardpan">

                                                            <!-- configure your cardtype-selection here -->
                                                            <label for="cardtypeInput">Card type</label>
                                                            <select id="cardtype">
                                                                <option value="V">VISA</option>
                                                                <option value="M">Mastercard</option>
                                                                <option value="A">Amex</option>
                                                            </select>

                                                            <label for="cardpanInput">Cardpan:</label>
                                                            <span class="inputIframe" id="cardpan"></span>

                                                            <label for="cvcInput">CVC:</label>
                                                            <span id="cardcvc2" class="inputIframe"></span>

                                                            <label for="expireInput">Expire Date:</label>
                                                            <span id="expireInput" class="inputIframe">
                                                            <span id="cardexpiremonth"></span>
                                                            <span id="cardexpireyear"></span>
                                                        </span>

                                                            <label for="firstname">Firstname:</label>
                                                            <input id="firstname" type="text" name="firstname" value="">
                                                            <label for="lastname">Lastname:</label>
                                                            <input id="lastname" type="text" name="lastname" value="">

                                                            <div id="errorOutput"></div>
                                                            <input id="paymentsubmit" type="button" value="Submit" onclick="check();">
                                                        </fieldset>
                                                    </form>
                                                    <div id="paymentform"></div>
                                                </div>
                                                <!-- Credit card: Payone hosted Iframe end -->

                                                <li class="pay-logo-booking2 line-col-booking2">
                                                    <img src="{{ asset('storage/img/mc_acc_opt_70_3x.png') }}" class="pay-figure-booking2" alt="pay-option" title="Mastercard"><img src="{{ asset('storage/img/Visa_BlueGradient.png') }}" class="pay-figure-booking2" alt="pay-option" title="VISA">
                                                </li>
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

@endsection

@push('scripts')
    <script>
        window.environment = {
            service_tax_one: '{{ env('SERVICE_TAX_ONE') }}',
            service_tax_two: '{{ env('SERVICE_TAX_TWO') }}',
            service_tax_three: '{{ env('SERVICE_TAX_THREE') }}'
        }
    </script>
@endpush