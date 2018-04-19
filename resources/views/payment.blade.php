@extends('layouts.app')

@section('title', 'Payment')

@section('content')
    <div class="container-fluid bg-3 text-center container-fluid-booking2">
        <div class="col-md-2 col-md-2-booking2"></div>
        <div class="col-md-8 col-md-8-booking2" id="list-filter-booking2">
            <nav class="navbar navbar-default navbar-default-booking2">
                <h2 class="cabin-head-booking2">Choose a payment</h2><h2 class="cabin-head-booking2">Step 2 of 3<span class="glyphicon glyphicon-booking2 glyphicon-question-sign" title="You are on the first of three steps to book a cabin night. Control your data and enter next step to get to the next step."></span></h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking2"></div>
    </div>

    <main>
        <div class="container-fluid text-center container-fluid-booking2">
            <div class="panel panel-booking2 panel-default text-left panel-default-booking2">
                <div class="panel-body panel-body-booking2">
                    <div class="row content row-booking2">
                        <div class="col-sm-7 text-left col-sm-7-booking2">
                            <div class="row row-booking2">
                                <div class="col-sm-12 month-opening-booking2 col-sm-12-booking2">
                                    <div class="form-group row row-booking2">
                                        <ul class="payment-options-booking2">
                                            <li class="li-head-booking2">Kind of payment</li>
                                            <li class="check-it-list-booking2 check-it-list-spe-booking2 line-col-booking2" id="bill-booking2"><input type="radio" value="payment" class="check-it-booking2 radio-payment" > Pay by bill </li>
                                            <li class="check-it-list-booking2 check-it-list-spe-booking2"><input type="radio" value="payment" class="check-it-booking2 radio-payment" > Klarna </li>
                                            <li class="pay-logo-booking2">
                                                <img src="{{ asset('storage/img/logo_black.png') }}" class="pay-figure-booking2" alt="pay-option" title="SOFORT Überweisung (Klarna)">
                                            </li>
                                            <li class="check-it-list-booking2 check-it-list-spe-booking2 line-col-booking2"><input class="check-it-booking2 radio-payment" type="radio" value="payment"> Paydirekt </li>
                                            <li class="pay-logo-booking2 line-col-booking2">
                                                <img src="{{ asset('storage/img/paydirekt_logo_4C.png') }}" class="pay-figure-booking2" alt="pay-option" title="Paydirect">
                                            </li>
                                            <li class="check-it-list-booking2 check-it-list-spe-booking2"><input class="check-it-booking2 radio-payment" type="radio" value="payment"> PayPal </li>
                                            <li class="pay-logo-booking2">
                                                <img src="{{ asset('storage/img/de-pp-logo-100px.png') }}" class="pay-figure-booking2" alt="pay-option" title="PayPal">
                                            </li>
                                            <li class="check-it-list-booking2 check-it-list-spe-booking2 line-col-booking2"><input class="check-it-booking2 radio-payment" type="radio" value="payment"> Creditcard </li>
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
                                <div class="panel-body panel-body-booking2">
                                    <div class="row row-booking2">
                                        <div class="col-sm-12 month-opening-booking2 col-sm-12-booking2">
                                            <h5>Complete Payment<span class="glyphicon glyphicon-booking2 glyphicon-question-sign" title="Here all costs are listed again. The service fee helps us operate Huetten-Holiday and offer services like our live-chat for your trip. It contains sales tax."></span></h5>
                                        </div>
                                    </div>
                                    <div class="row row-booking2">
                                        <div class="col-sm-12 col-sm-12-extra-booking2 col-sm-12-booking2-booking2">
                                            <p class="info-listing-booking2">Deposit:</p><p class="info-listing-price-booking2">60,00€</p>
                                            <p class="info-listing-booking2">Amount:</p><p class="info-listing-price-booking2">-20,00€</p>
                                            <p class="info-listing-booking2">Deposit netto:</p><p class="info-listing-price-booking2">40,00€</p>
                                            <p class="info-listing-booking2">Service fee:</p><p class="info-listing-price-booking2">7,50€</p>
                                        </div>
                                    </div>
                                    <div class="row row-booking2">
                                        <div class="col-sm-12 col-sm-12-extra-booking2 col-sm-12-booking2">
                                            <h5 class="info-listing-booking2">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking2">47,50€</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div id="btn-ground-2-booking2">
                    <button type="button" class="btn btn-default btn-default-booking2 btn-sm btn-details-booking2">Book the Cabin</button>
                </div>
            </div>
        </div><br><br>
    </main>

@endsection

@push('scripts')
@endpush