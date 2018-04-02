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
            @if (session()->has('deletedBooking'))
                <div id="flash" class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session()->get('deletedBooking') }}
                </div>
            @endif

            @isset($carts)

                @php
                    $prepayment_amount = [];
                @endphp

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
                                                    $calendar                = $calendarServices->calendar($cart->cabin_id);
                                                    $amount                  = ($cabinDetails->cabin($cart->cabin_id)->prepayment_amount * round(abs(strtotime($cart->checkin_from->format('Y-m-d')) - strtotime($cart->reserve_to->format('Y-m-d')))/86400)) * $cart->guests;
                                                    $prepayment_amount[]     = ($cabinDetails->cabin($cart->cabin_id)->prepayment_amount * round(abs(strtotime($cart->checkin_from->format('Y-m-d')) - strtotime($cart->reserve_to->format('Y-m-d')))/86400)) * $cart->guests;
                                                @endphp

                                                <div class="holiday_{{ $cart->cabin_id }}" data-holiday="{{ $calendar[0] }}"></div>
                                                <div class="green_{{ $cart->cabin_id }}" data-green="{{ $calendar[1] }}"></div>
                                                <div class="orange_{{ $cart->cabin_id }}" data-orange="{{ $calendar[2] }}"></div>
                                                <div class="red_{{ $cart->cabin_id }}" data-red="{{ $calendar[3] }}"></div>
                                                <div class="notSeasonTime_{{ $cart->cabin_id }}" data-notseasontime="{{ $calendar[4] }}"></div>

                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <input type="text" class="form-control form-control-booking1 dateFrom" id="dateFrom_{{ $cart->cabin_id }}" name="dateFrom" value="{{  $cart->checkin_from->format('d.m.y') }}"  readonly>
                                                </div>
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <input type="text" class="form-control form-control-booking1 dateTo" id="dateTo_{{ $cart->cabin_id }}" name="dateTo" value="{{ $cart->reserve_to->format('d.m.y') }}"  readonly>
                                                </div>
                                                @if($cabinDetails->cabin($cart->cabin_id)->sleeping_place != 1)
                                                    <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1">
                                                        <select class="form-control form-control-booking1">
                                                            <option>Choose Bed(s)</option>
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <option value="{{ $i }}" @if($i == $cart->beds) selected="selected" @endif>{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-4 col-sm-4-booking1">
                                                        <select class="form-control form-control-booking1">
                                                            <option>Choose Dorm(s)</option>
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <option value="{{ $i }}">{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                @else
                                                    <div class="col-sm-4 col-sm-4-booking1">
                                                        <select class="form-control form-control-booking1 jsBookCalSleep" name="sleeps">
                                                            <option>Choose Sleep(s)</option>
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <option value="{{ $i }}" @if($i == $cart->sleeps) selected="selected" @endif>{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                @endif

                                                @if($cabinDetails->cabin($cart->cabin_id)->halfboard == '1' && $cabinDetails->cabin($cart->cabin_id)->halfboard_price != '')
                                                    <div class="col-sm-4 col-sm-4-booking1">
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input type="checkbox" id="halfboard" name="halfboard" value="1">
                                                                    Half board available
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

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
                                                    <h5>Deposit for Cabin <a href="/cart/delete/{{ $cart->cabin_id }}/{{ $cart->_id }}" class="pull-right"><span class="glyphicon glyphicon-trash" title="Delete your Booking"></span></a></h5>
                                                </div>
                                            </div>
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">Guest(s):</p><p class="info-listing-price-booking1 replaceBookingGuest">{{ $cart->guests }}</p>
                                                    <p class="info-listing-booking1">Number night(s):</p><p class="info-listing-price-booking1">{{ date_diff(date_create($cart->checkin_from->format('Y-m-d')), date_create($cart->reserve_to->format('Y-m-d')))->format('%R%a days') }}</p>
                                                </div>
                                            </div><br />
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1 depsit-booking1">
                                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">{{ number_format($amount, 2, '.', '') }}&euro;</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    <p>No bookings in your cart</p>
                @endforelse

                <form>
                    @if(array_sum($prepayment_amount) > 0 )

                        @php
                            $moneyBalance                   = 0;
                            $moneyBalanceDeduct             = 0;
                            $moneyBalanceDeductPercentage   = 0;
                            $moneyBalanceDeductServiceTotal = 0;

                            $sumPrepaymentAmount = array_sum($prepayment_amount);

                            if($sumPrepaymentAmount <= 30) {
                               $serviceTax = env('SERVICE_TAX_ONE');
                            }

                            if($sumPrepaymentAmount > 30 && $sumPrepaymentAmount <= 100) {
                               $serviceTax = env('SERVICE_TAX_TWO');
                            }

                            if($sumPrepaymentAmount > 100) {
                               $serviceTax = env('SERVICE_TAX_THREE');
                            }

                            $sumPrepaymentAmountPercentage   = ($serviceTax / 100) * $sumPrepaymentAmount;
                            $sumPrepaymentAmountServiceTotal = $sumPrepaymentAmount + $sumPrepaymentAmountPercentage;
                        @endphp


                            @if($cabinDetails->user(Auth::user()->_id)->money_balance > 0)

                                @php
                                    $moneyBalance                    = $cabinDetails->user(Auth::user()->_id)->money_balance;
                                    $moneyBalanceDeduct              = $sumPrepaymentAmount - $moneyBalance;
                                    $moneyBalanceDeductPercentage    = ($serviceTax / 100) * $moneyBalanceDeduct;
                                    $moneyBalanceDeductServiceTotal  = $moneyBalanceDeduct + $moneyBalanceDeductPercentage;
                                @endphp

                                <div class="row content row-booking1">
                                    <div class="col-sm-3 col-sm-3-booking1 col-sm-r col-sm-r-booking1">
                                        <div class="panel panel-default booking-box-booking1 bottom-boxes-booking1 panel-booking1 panel-default-booking1" id="amount_box-booking1">
                                            <div class="panel-body panel-body-booking1">
                                                <div class="row row-booking1">
                                                    <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                                        <h5>Your Amount</h5>
                                                        <span class="label label-info label-cabinlist"><input type="checkbox" class="moneyBalance" name="moneyBalance" value="1"> Redeem now! {{ number_format($moneyBalance, 2, '.', '') }}&euro;</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endif


                        <div class="row content row-booking1">
                            <div class="col-sm-9">
                                <div class="panel panel-default booking-box-booking1 panel-default-booking1 text-left">
                                    <div class="panel-body panel-body-booking1">
                                        <div class="row row-booking1">
                                            <div class="col-sm-12">
                                                <h5>Contact Information</h5>
                                            </div>
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->has('street') ? ' has-error' : '' }}">
                                                            <label> Street <span class="required">*</span></label>
                                                            <input type="text" class="form-control" id="street" name="street" placeholder="Enter street" maxlength="255" value="{{ old('street', Auth::user()->usrAddress) }}">

                                                            @if ($errors->has('street'))
                                                                <span class="help-block"><strong>{{ $errors->first('street') }}</strong></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->has('city') ? ' has-error' : '' }}">
                                                            <label> City <span class="required">*</span></label>
                                                            <input type="text" class="form-control" id="city" name="city" placeholder="Enter city" maxlength="255" value="{{ old('city', Auth::user()->usrCity) }}">

                                                            @if ($errors->has('city'))
                                                                <span class="help-block"><strong>{{ $errors->first('city') }}</strong></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                                                            <label> Country <span class="required">*</span></label>
                                                            <select class="form-control" id="country" name="country">
                                                                <option value="0"> Choose Country </option>
                                                                @foreach($country as $land)
                                                                    <option value="{{ $land->name }}" @if($land->name == Auth::user()->usrCountry || old('country') == $land->name) selected="selected" @endif>{{ $land->name }}</option>
                                                                @endforeach
                                                            </select>

                                                            @if ($errors->has('country'))
                                                                <span class="help-block"><strong>{{ $errors->first('country') }}</strong></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->has('zipcode') ? ' has-error' : '' }}">
                                                            <label> Zipcode <span class="required">*</span></label>
                                                            <input type="text" class="form-control" id="zipcode" name="zipcode" placeholder="Enter zip code" maxlength="25" value="{{ old('zipcode', Auth::user()->usrZip) }}">

                                                            @if ($errors->has('zipcode'))
                                                                <span class="help-block"><strong>{{ $errors->first('zipcode') }}</strong></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->has('mobile') ? ' has-error' : '' }}">
                                                            <label> Mobile <span class="required">*</span></label>
                                                            <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Enter mobile" maxlength="20" value="{{ old('mobile', Auth::user()->usrMobile) }}">

                                                            @if ($errors->has('mobile'))
                                                                <span class="help-block"><strong>{{ $errors->first('mobile') }}</strong></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->has('phone') ? ' has-error' : '' }}">
                                                            <label> Phone <span class="required">*</span></label>
                                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone" maxlength="20" value="{{ old('phone', Auth::user()->usrTelephone) }}">

                                                            @if ($errors->has('phone'))
                                                                <span class="help-block"><strong>{{ $errors->first('phone') }}</strong></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-3 col-sm-3-booking1 col-sm-r col-sm-r-booking1">
                                <div class="panel panel-default booking-box-booking1 bottom-boxes-booking1 panel-booking1 panel-default-booking1">
                                    <div class="panel-body panel-body-booking1">
                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                                <h5>Complete Payment<span class="glyphicon glyphicon-question-sign" title="Here all costs are listed again. The service fee helps us operate Huetten-Holiday and offer services like our live-chat for your trip. It contains sales tax."></span></h5>
                                            </div>
                                        </div>

                                        <!-- Money balance deduct begin -->
                                        <div class="moneyBalanceCal" style="display: none;">
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">{{ number_format($sumPrepaymentAmount, 2, '.', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">Applied money balance:</p><p class="info-listing-price-booking1">-{{ number_format($moneyBalance, 2, '.', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">After deduction:</p><p class="info-listing-price-booking1">{{ number_format($moneyBalanceDeduct, 2, '.', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">Service fee:</p><p class="info-listing-price-booking1">{{ $serviceTax }}%</p>
                                                </div>
                                            </div>

                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <h5 class="info-listing-booking1">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking1">{{ number_format($moneyBalanceDeductServiceTotal, 2, '.', '') }}&euro;</h5>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Money balance deduct end -->

                                        <div class="normalCalculation">
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">{{ number_format($sumPrepaymentAmount, 2, '.', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">Service fee:</p><p class="info-listing-price-booking1">{{ $serviceTax }}%</p>
                                                </div>
                                            </div>

                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <h5 class="info-listing-booking1">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking1">{{ number_format($sumPrepaymentAmountServiceTotal, 2, '.', '') }}&euro;</h5>
                                                </div>
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
                    @endif
                </form>

            @endisset

        </div>
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