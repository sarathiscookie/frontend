@extends('layouts.app')

@section('title', 'Payment Success')

@section('jumbotron')
    <div class="jumbotron">
        <div class="container text-center">
            <img src="{{ asset('storage/img/sunset.jpg') }}" class="img-responsive titlepicture" alt="titlepicture">
        </div>
    </div>

    <div class="clearfix"></div>
@endsection

@section('content')
    <div class="container-fluid container-fluid-booking3 bg-3 text-center">
        <div class="col-md-2 col-md-2-booking3"></div>
        <div class="col-md-8 col-md-8-booking3" id="list-filter-booking3">
            <nav class="navbar navbar-default navbar-default-booking3">
                <h2 class="cabin-head-booking3">{{ __('payment.bookingSuccessHeading') }}</h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking3"></div>
    </div>
    <main>
        <div class="container-fluid container-fluid-booking3 text-center">
            <div class="panel panel-default text-left panel-booking3 panel-default-booking3">
                <div class="panel-body panel-body-booking3">
                    <div class="row content row-booking3">
                        <div class="col-sm-10 text-left">
                            <div class="row row-booking3">
                                <div class="col-sm-12 month-opening-booking3 col-sm-12-booking3">
                                    <h2>{{ __('payment.bookingSuccess') }}</h2>
                                    <p id="info-text-booking3">
                                        @if (session()->has('bookingSuccessStatus'))
                                            {{ session()->get('bookingSuccessStatus') }}
                                        @elseif (session()->has('editBookingSuccessStatus'))
                                            {{ session()->get('editBookingSuccessStatus') }}
                                        @elseif (session()->has('inquiryBookingSuccessStatus'))
                                            {{ session()->get('inquiryBookingSuccessStatus') }}
                                        @endif</p>
                                    <p id="info-text-booking3">{{ __('payment.voucherMsg') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div id="btn-ground-2-booking3">
                    <a href="/booking/history" class="btn btn-default btn-default-booking3 btn-sm btn-details-booking3">{{ __('prepayment.bookingHistoryLink') }}</a>
                </div>
            </div>
        </div><br><br>
    </main>
@endsection
