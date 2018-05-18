@extends('layouts.app')

@section('title', 'Payment Error')

@section('content')
    <div class="container-fluid container-fluid-booking3 bg-3 text-center">
        <div class="col-md-2 col-md-2-booking3"></div>
        <div class="col-md-8 col-md-8-booking3" id="list-filter-booking3">
            <nav class="navbar navbar-default navbar-default-booking3">
                <h2 class="cabin-head-booking3">Booked!</h2><h2 class="cabin-head-booking3">Step 3 of 3</h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking3"></div>
    </div>
    <main>
        <div class="container-fluid container-fluid-booking3 text-center">
            <div class="panel panel-default text-left panel-booking3 panel-default-booking3">
                <div class="panel-body panel-body-booking3">
                    <div class="row content row-booking3">
                        <div class="col-sm-7 text-left col-sm-7-booking3">
                            <div class="row row-booking3">
                                <div class="col-sm-12 month-opening-booking3 col-sm-12-booking3">
                                    <h2>OOPS! Booing Failed</h2>
                                    <p id="info-text-booking3">@if (session()->has('bookingErrorStatus')) {{ session()->get('bookingErrorStatus') }} @endif</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-sm-3-booking3">
                            <div class="panel panel-default booking-box-booking3 bottom-boxes-booking3 panel-booking3 panel-default-booking3">
                                <div class="panel-body panel-body-booking3">
                                    <div class="row row-booking3">
                                        <div class="col-sm-12 month-opening-booking3 col-sm-12-booking3">
                                            <h5>Complete Payment<span class="glyphicon glyphicon-question-sign" title="Here all costs are listed again. The service fee helps us operate Huetten-Holiday and offer services like our live-chat for your trip. It contains sales tax."></span></h5>
                                        </div>
                                    </div>
                                    <div class="row row-booking3">
                                        <div class="col-sm-12 col-sm-12-extra-booking3 col-sm-12-booking3">
                                            <p class="info-listing-booking3">Deposit:</p><p class="info-listing-price-booking3">60,00?</p>
                                            <p class="info-listing-booking3">Amount:</p><p class="info-listing-price-booking3">- 20,00?</p>
                                            <p class="info-listing-booking3">Deposit netto:</p><p class="info-listing-price-booking3">40,00?</p>
                                            <p class="info-listing-booking3">Service fee:</p><p class="info-listing-price-booking3">7,50?</p>
                                        </div>
                                    </div>
                                    <div class="row row-booking3">
                                        <div class="col-sm-12 col-sm-12-extra-booking3 col-sm-12-booking3">
                                            <h5 class="info-listing-booking3">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking3">47,50?</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div id="btn-ground-2-booking3">
                    <button type="button" class="btn btn-default btn-default-booking3 btn-sm btn-details-booking3">Bookinghistory</button>
                </div>
            </div>
        </div><br><br>
    </main>
@endsection
