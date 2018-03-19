@extends('layouts.app')

@section('title', 'Cart')

@inject('cabinDetails', 'App\Http\Controllers\CartController')

@inject('calendarServices', 'App\Http\Controllers\CalendarController')

@section('content')
    <div class="container-fluid container-fluid-booking1 bg-3 text-center">
        <div class="col-md-2 col-md-2-booking1"></div>
        <div class="col-md-8" id="list-filter-booking1">
            <nav class="navbar navbar-default navbar-default-booking1">
                <h2 class="cabin-head-booking1">Edit your Booking(s)</h2><h2 class="cabin-head-booking1">Step 1 of 3<span class="glyphicon glyphicon-question-sign" title="You are on the first of three steps to book a cabin night. Control your data and enter next step to get to the next step."></span></h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking1"></div>
    </div>
    <main>
        <div class="container-fluid container-fluid-booking1 text-center">
            @isset($carts)
                @forelse($carts as $key => $cart)
                    <div class="panel panel-default text-left panel-booking1 panel-default-booking1">
                        <div class="panel-body panel-body-booking1">
                            <div class="row content row-booking1">
                                <div class="col-sm-2 col-sm-2-booking1">
                                    <img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive img-thumbnail img-thumbnail-booking1" style="width:100%" alt="Image">
                                </div>
                                <div class="col-sm-7 text-left col-sm-7-booking1">

                                    <h3 class="headliner-cabinname">{{ $cabinDetails->cabin($cart->cabin_id)->name }} - {{ $cabinDetails->cabin($cart->cabin_id)->region }}<span class="glyphicon glyphicon-question-sign" title="Please check your data and correct if necessary. To edit them, simply double-click on the desired field."></span></h3>

                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                            <div class="form-group row row-booking1 calendar" data-id="{{ $cart->cabin_id }}">

                                                @php
                                                    $calendar  = $calendarServices->calendar($cart->cabin_id);
                                                @endphp

                                                <div class="holiday_{{ $cart->cabin_id }}" data-holiday="{{ $calendar[0] }}"></div>
                                                <div class="green_{{ $cart->cabin_id }}" data-green="{{ $calendar[1] }}"></div>
                                                <div class="orange_{{ $cart->cabin_id }}" data-orange="{{ $calendar[2] }}"></div>
                                                <div class="red_{{ $cart->cabin_id }}" data-red="{{ $calendar[3] }}"></div>
                                                <div class="notSeasonTime_{{ $cart->cabin_id }}" data-notseasontime="{{ $calendar[4] }}"></div>

                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <input type="text" class="form-control form-control-booking1 dateFrom" id="dateFrom_{{ $cart->cabin_id }}" name="dateFrom" value="{{ old('dateFrom_'.$cart->_id, $cart->checkin_from->format('d.m.y')) }}"  readonly>
                                                </div>
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <input type="text" class="form-control form-control-booking1 dateTo" id="dateTo_{{ $cart->cabin_id }}" name="dateTo" value="{{ old('dateTo_'.$cart->_id, $cart->reserve_to->format('d.m.y')) }}"  readonly>
                                                </div>
                                                <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1">
                                                    <select class="form-control form-control-booking1">
                                                        <option>Bed(s)</option>
                                                        @for($i = 1; $i <= 30; $i++)
                                                            <option value="{{ old('persons_'.$cart->cabin_id, $i) }}" @if($i == $cart->beds) selected="selected" @endif>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <select class="form-control form-control-booking1">
                                                        <option>Dorm(s)</option>
                                                        @for($i = 1; $i <= 30; $i++)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-sm-4 col-sm-4-f-booking1 comment-booking1 col-sm-4-booking1">
                                                    <input class="form-control comment-box-booking1 form-control-booking1"  type="text" placeholder="Comment:">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-sm-3-booking1">
                                    <div class="panel panel-default booking-box-booking1 panel-booking1 panel-default-booking1">
                                        <div class="panel-body panel-body-booking1">
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                                    <h5>Deposit for Cabin <a href="#"><span class="glyphicon glyphicon-remove-booking1" title="Delete your Booking"></span></a></h5>
                                                </div>
                                            </div>
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">Guest(s):</p><p class="info-listing-price-booking1">{{ $cart->guests }}</p>
                                                    <p class="info-listing-booking1">Number night(s):</p><p class="info-listing-price-booking1">{{ date_diff(date_create($cart->checkin_from->format('Y-m-d')), date_create($cart->reserve_to->format('Y-m-d')))->format('%R%a days') }}</p>
                                                </div>
                                            </div><br />
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1 depsit-booking1">
                                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">{{ ($cabinDetails->cabin($cart->cabin_id)->prepayment_amount * round(abs(strtotime($cart->checkin_from->format('Y-m-d'))-strtotime($cart->reserve_to->format('Y-m-d')))/86400)) * $cart->guests }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    <p>No users</p>
                @endforelse

                    <div class="row content row-booking1">
                        <div class="col-sm-3 col-sm-3-booking1 col-sm-r col-sm-r-booking1">
                            <div class="panel panel-default booking-box-booking1 bottom-boxes-booking1 panel-booking1 panel-default-booking1" id="amount_box-booking1">
                                <div class="panel-body panel-body-booking1">
                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                            <h5>Your Amount</h5>
                                            <button type="button" class="btn btn-default btn-default-booking1 btn-amount-booking1 btn-details btn-details-booking1">Redeem now!</button>
                                            <h5 id="cash-amount-booking1">20,00€</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row content row-booking1">
                        <div class="col-sm-3 col-sm-3-booking1 col-sm-r col-sm-r-booking1">
                            <div class="panel panel-default booking-box-booking1 bottom-boxes-booking1 panel-booking1 panel-default-booking1">
                                <div class="panel-body panel-body-booking1">
                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                            <h5>Complete Payment<span class="glyphicon glyphicon-question-sign" title="Here all costs are listed again. The service fee helps us operate Huetten-Holiday and offer services like our live-chat for your trip. It contains sales tax."></span></h5>
                                        </div>
                                    </div>
                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                            <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">60,00€</p>
                                            <p class="info-listing-booking1">Amount:</p><p class="info-listing-price-booking1">- 20,00€</p>
                                            <p class="info-listing-booking1">Deposit netto:</p><p class="info-listing-price-booking1">40,00€</p>
                                            <p class="info-listing-booking1">Service fee:</p><p class="info-listing-price-booking1">7,50€</p>
                                        </div>
                                    </div>
                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                            <h5 class="info-listing-booking1">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking1">47,50€</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div id="btn-ground-2-booking1">
                            <a href="/search" class="btn btn-default-booking1 btn-default btn-sm btn-details btn-details-booking1">Continue Booking</a>
                            <button type="button" class="btn btn-default-booking1 btn-default btn-sm btn-details btn-details-booking1">Payment</button>
                        </div>
                    </div>
            @endisset

        </div>
    </main>
@endsection
