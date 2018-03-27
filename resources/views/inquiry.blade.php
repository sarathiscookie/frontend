@extends('layouts.app')

@section('title', 'Inquiry')

@inject('inquiryServices', 'App\Http\Controllers\InquiryController')

@section('content')
    <div class="container-fluid container-fluid-booking1 bg-3 text-center">
        <div class="col-md-2 col-md-2-booking1"></div>
        <div class="col-md-8" id="list-filter-booking1">
            <nav class="navbar navbar-default navbar-default-booking1">
                <h2 class="cabin-head-booking1">Edit your Inquiry</h2><h2 class="cabin-head-booking1">Step 1 of 1</h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking1"></div>
    </div>

    <main>
        <div class="container-fluid container-fluid-booking1 text-center">

            @isset($cabinDetails)

                @php
                    $amount                  = ($cabinDetails->prepayment_amount * round(abs(strtotime(session()->get('checkin_from')) - strtotime(session()->get('reserve_to')))/86400)) * session()->get('guests');
                    $prepayment_amount[]     = ($cabinDetails->prepayment_amount * round(abs(strtotime(session()->get('checkin_from')) - strtotime(session()->get('reserve_to')))/86400)) * session()->get('guests');
                @endphp
            <form action="{{ route('inquiry.store') }}" method="post">

                {{ csrf_field() }}

                <div class="panel panel-default text-left panel-booking1 panel-default-booking1">
                    <div class="panel-body panel-body-booking1">
                        <div class="row content row-booking1">
                            <div class="col-sm-2 col-sm-2-booking1">
                                <img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive img-thumbnail img-thumbnail-booking1" style="width:100%" alt="Image">
                            </div>
                            <div class="col-sm-7 text-left col-sm-7-booking1">

                                <h3 class="headliner-cabinname">{{ $cabinDetails->name }} - {{ $cabinDetails->region }}<span class="glyphicon glyphicon-question-sign" title="Please check your data and correct if necessary. To edit them, simply double-click on the desired field."></span></h3>

                                <div class="row row-booking1">
                                    <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                        <div class="row row-booking1">
                                            <div class="col-sm-4 col-sm-4-booking1">
                                                <div class="form-group">
                                                    <label>From</label>
                                                    <input type="text" class="form-control form-control-booking1 dateFrom" value="{{ session()->get('checkin_from') }}"  readonly>
                                                </div>
                                            </div>

                                            <div class="col-sm-4 col-sm-4-booking1">
                                                <div class="form-group">
                                                    <label>To</label>
                                                    <input type="text" class="form-control form-control-booking1 dateTo" value="{{ session()->get('reserve_to') }}"  readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row row-booking1">
                                            @if($cabinDetails->sleeping_place != 1)
                                                <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1">
                                                    <label>Bed(s)</label>
                                                    <select class="form-control form-control-booking1">
                                                        <option>Choose Bed(s)</option>
                                                        @for($i = 1; $i <= 30; $i++)
                                                            <option value="{{ old(session()->get('beds'), $i) }}" @if($i == session()->get('beds')) selected="selected" @endif>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <label>Dorm(s)</label>
                                                    <select class="form-control form-control-booking1">
                                                        <option>Choose Dorm(s)</option>
                                                        @for($i = 1; $i <= 30; $i++)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            @else
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <label>Sleep(s)</label>
                                                    <select class="form-control form-control-booking1">
                                                        <option>Choose Sleep(s)</option>
                                                        @for($i = 1; $i <= 30; $i++)
                                                            <option value="{{ old(session()->get('sleeps'), $i) }}" @if($i == session()->get('sleeps')) selected="selected" @endif>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="row row-booking1">
                                            @if($cabinDetails->halfboard == '1' && $cabinDetails->halfboard_price != '')
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
                                        </div>

                                        <div class="row row-booking1">
                                            <div class="col-sm-4 col-sm-4-f-booking1 comment-booking1 col-sm-4-booking1">
                                                <div class="form-group {{ $errors->has('comments') ? ' has-error' : '' }}">
                                                    <textarea id="comments" name="comments" class="form-control" rows="3" maxlength="300" placeholder="Comment...">{{ old('comments') }}</textarea>

                                                    @if ($errors->has('comments'))
                                                        <span class="help-block"><strong>{{ $errors->first('comments') }}</strong></span>
                                                    @endif
                                                    <div id="textarea_feedback"></div>
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
                                                <h5>Deposit for Cabin</h5>
                                            </div>
                                        </div>
                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                <p class="info-listing-booking1">Guest(s):</p><p class="info-listing-price-booking1">{{ session()->get('guests') }}</p>
                                                <p class="info-listing-booking1">Number night(s):</p><p class="info-listing-price-booking1">{{ date_diff(date_create(session()->get('checkin_from')), date_create(session()->get('reserve_to')))->format('%R%a days') }}</p>
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

                    <div class="row content">
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

                    <div class="row content row-booking1">
                        <div id="btn-ground-2-booking1">
                            <button type="submit" class="btn-default-booking1 btn-sm btn-details-booking1"><span class="glyphicon glyphicon-envelope" style="font-size: 16px;" aria-hidden="true"></span>  Send Inquiry</button>
                        </div>
                    </div>
                @endif
            </form>

            @endisset

        </div>
    </main>
@endsection