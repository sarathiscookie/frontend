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
                 <form action="{{ route('cart.store') }}" method="post">

                        {{ csrf_field() }}

                        @php
                            $prepayment_amount = [];
                        @endphp

                        @forelse($carts as $key => $cart)

                         @php
                             $monthBegin              = $cart->checkin_from->format('Y-m-d');
                             $monthEnd                = $cart->reserve_to->format('Y-m-d');
                             $d1                      = new DateTime($monthBegin);
                             $d2                      = new DateTime($monthEnd);
                             $dateDifference          = $d2->diff($d1);
                             $amount                  = ($cabinDetails->cabin($cart->cabin_id)->prepayment_amount * $dateDifference->days) * $cart->guests;
                             $prepayment_amount[]     = ($cabinDetails->cabin($cart->cabin_id)->prepayment_amount * $dateDifference->days) * $cart->guests;

                             /* For javascript cal */
                             $amountBookingDays       = $cabinDetails->cabin($cart->cabin_id)->prepayment_amount * $dateDifference->days;
                             $inputBeds               = 'guest.'.$cart->_id.'.beds';
                             $inputDormitory          = 'guest.'.$cart->_id.'.dormitory';
                             $inputSleeps             = 'guest.'.$cart->_id.'.sleeps';
                             $halfBoard               = 'guest.'.$cart->_id.'.halfboard';
                             $inputComments           = 'guest.'.$cart->_id.'.comments';
                             $notAvailStatus          = 'notAvailable.'.$cart->_id.'.status';
                         @endphp

                            <div class="panel panel-default text-left panel-booking1 panel-default-booking1">
                                <div class="panel-body panel-body-booking1">
                                    <div class="row content row-booking1">
                                        <div class="col-sm-2 col-sm-2-booking1">
                                            <img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive img-thumbnail img-thumbnail-booking1" style="width:100%" alt="Image">
                                        </div>
                                        <div class="col-sm-7 text-left col-sm-7-booking1">

                                            <h3 class="headliner-cabinname">{{ $cabinDetails->cabin($cart->cabin_id)->name }} - {{ $cabinDetails->cabin($cart->cabin_id)->region }}<span class="glyphicon glyphicon-question-sign" title="Please check your data and correct if necessary. To edit them, simply double-click on the desired field."></span></h3>

                                            @if (session()->has($notAvailStatus))
                                                <div id="flash" class="alert alert-danger">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                    {!! session()->get($notAvailStatus) !!}
                                                </div>
                                            @endif

                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                                    <div class="form-group row row-booking1 forComments" data-cartid="{{ $cart->_id }}">

                                                        <div class="amountBookingDays_{{ $cart->_id }}" data-amountbookingdays="{{ $amountBookingDays }}"></div>

                                                        <div class="col-sm-4 col-sm-4-booking1">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control form-control-booking1" value="{{ $cart->checkin_from->format('d.m.y') }}"  readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-4 col-sm-4-booking1">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control form-control-booking1" value="{{ $cart->reserve_to->format('d.m.y') }}"  readonly>
                                                            </div>
                                                        </div>

                                                        @if($cabinDetails->cabin($cart->cabin_id)->sleeping_place != 1)
                                                            <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1 form-group {{ $errors->has($inputBeds) ? ' has-error' : '' }}">
                                                                <select class="form-control form-control-booking1 jsBookCalBeds" name="guest[{{ $cart->_id }}][beds]" id="beds_{{ $cart->_id }}">
                                                                    <option value="">Choose Bed(s)</option>
                                                                    @for($i = 1; $i <= 30; $i++)
                                                                        <option value="{{ $i }}" @if($i == $cart->beds) selected @endif>{{ $i }}</option>
                                                                    @endfor
                                                                </select>

                                                                @if ($errors->has($inputBeds))
                                                                    <span class="help-block"><strong>{{ $errors->first($inputBeds) }}</strong></span>
                                                                @endif
                                                            </div>

                                                            <div class="col-sm-4 col-sm-4-booking1 form-group {{ $errors->has($inputDormitory) ? ' has-error' : '' }}">
                                                                <select class="form-control form-control-booking1 jsBookCalDormitory" name="guest[{{ $cart->_id }}][dormitory]" id="dormitory_{{ $cart->_id }}">
                                                                    <option value="">Choose Dorm(s)</option>
                                                                    @for($i = 1; $i <= 30; $i++)
                                                                        <option value="{{ $i }}" @if($i == $cart->dormitory) selected @endif>{{ $i }}</option>
                                                                    @endfor
                                                                </select>

                                                                @if ($errors->has($inputDormitory))
                                                                    <span class="help-block"><strong>{{ $errors->first($inputDormitory) }}</strong></span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="col-sm-4 col-sm-4-booking1 form-group {{ $errors->has($inputSleeps) ? ' has-error' : '' }}">
                                                                <select class="form-control form-control-booking1 jsBookCalSleep"  name="guest[{{ $cart->_id }}][sleeps]" id="sleeps_{{ $cart->_id }}">
                                                                    <option value="">Choose Sleep(s)</option>
                                                                    @for($i = 1; $i <= 30; $i++)
                                                                        <option value="{{ $i }}" @if($i == $cart->sleeps) selected @endif>{{ $i }}</option>
                                                                    @endfor
                                                                </select>

                                                                @if ($errors->has($inputSleeps))
                                                                    <span class="help-block"><strong>{{ $errors->first($inputSleeps) }}</strong></span>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        <input type="hidden" name="guest[{{ $cart->_id }}][sleeping_place]" value="{{ $cabinDetails->cabin($cart->cabin_id)->sleeping_place }}">

                                                        @if($cabinDetails->cabin($cart->cabin_id)->halfboard == '1' && $cabinDetails->cabin($cart->cabin_id)->halfboard_price != '')
                                                            <div class="col-sm-4 col-sm-4-booking1">
                                                                <div class="form-group">
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input type="checkbox" id="halfboard_{{ $cart->_id }}" name="guest[{{ $cart->_id }}][halfboard]" value="1" @if (old($halfBoard) === "1") checked @endif>
                                                                            Half board available
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="col-sm-4 col-sm-4-f-booking1 comment-booking1 col-sm-4-booking1">
                                                            <div class="form-group {{ $errors->has($inputComments) ? ' has-error' : '' }}">
                                                                <textarea id="comments_{{ $cart->_id }}" name="guest[{{ $cart->_id }}][comments]" class="form-control" rows="3" maxlength="300" placeholder="Comment...">{{ old($inputComments) }}</textarea>

                                                                @if ($errors->has($inputComments))
                                                                    <span class="help-block"><strong>{{ $errors->first($inputComments) }}</strong></span>
                                                                @endif
                                                                <div id="textarea_feedback_{{ $cart->_id }}"></div>
                                                            </div>
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
                                                            <p class="info-listing-booking1">Guest(s):</p><p class="info-listing-price-booking1 replaceBookingGuest_{{ $cart->_id }}">{{ $cart->guests }}</p>
                                                            <p class="info-listing-booking1">Number night(s):</p><p class="info-listing-price-booking1">{{ date_diff(date_create($cart->checkin_from->format('Y-m-d')), date_create($cart->reserve_to->format('Y-m-d')))->format('%R%a days') }}</p>
                                                        </div>
                                                    </div><br />
                                                    <div class="row row-booking1">
                                                        <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1 depsit-booking1">
                                                            <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1 bookingDeposit replaceBookingDeposit_{{ $cart->_id }}">{{ number_format($amount, 2, ',', '') }}&euro;</p>
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


                        @if(array_sum($prepayment_amount) > 0 )

                            @php
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
                                                                        <option value="{{ $land->name }}" @if($land->name == Auth::user()->usrCountry || old('country') == $land->name) selected @endif>{{ $land->name }}</option>
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

                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1 replaceBookingCompleteDeposit">{{ number_format($sumPrepaymentAmount, 2, ',', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">Service fee:</p><p class="info-listing-price-booking1 replaceBookingServiceFee">{{ $serviceTax }}%</p>
                                                </div>
                                            </div>

                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <h5 class="info-listing-booking1">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking1 replaceBookingCompletePayment">{{ number_format($sumPrepaymentAmountServiceTotal, 2, ',', '') }}&euro;</h5>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div id="btn-ground-2-booking1">
                                    <a href="/search" class="btn btn-default-booking1 btn-default btn-sm btn-details btn-details-booking1"><span class="glyphicon glyphicon-list-alt" style="font-size: 14px;" aria-hidden="true"></span> Continue Booking</a>
                                    <button type="submit" class="btn btn-default-booking1 btn-default btn-sm btn-details btn-details-booking1" name="createBooking" value="createBooking"><span class="glyphicon glyphicon-credit-card" style="font-size: 14px;" aria-hidden="true"></span> Payment</button>
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