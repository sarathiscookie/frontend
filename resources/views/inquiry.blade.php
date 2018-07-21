@extends('layouts.app')

@section('title', 'Inquiry')

@inject('inquiryServices', 'App\Http\Controllers\InquiryController')

@section('content')
    <div class="container-fluid container-fluid-booking1 bg-3 text-center">
        <div class="col-md-2 col-md-2-booking1"></div>
        <div class="col-md-8" id="list-filter-booking1">
            <nav class="navbar navbar-default navbar-default-booking1">
                <h2 class="cabin-head-booking1">{{ __('inquiry.inquiryHeading') }}</h2><h2 class="cabin-head-booking1">{{ __('inquiry.step1') }}</h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking1"></div>
    </div>

    <main>
        <div class="container-fluid container-fluid-booking1 text-center">

            @isset($cabinDetails)
                @php
                    $monthBegin              = DateTime::createFromFormat('d.m.y', session()->get('checkin_from'))->format('Y-m-d');
                    $monthEnd                = DateTime::createFromFormat('d.m.y', session()->get('reserve_to'))->format('Y-m-d');
                    $d1                      = new DateTime($monthBegin);
                    $d2                      = new DateTime($monthEnd);
                    $dateDifference          = $d2->diff($d1);
                    $amount                  = round(($cabinDetails->prepayment_amount * $dateDifference->days) * session()->get('guests'), 2);

                    /* For javascript cal */
                    $amountDays              = round($cabinDetails->prepayment_amount * $dateDifference->days, 2);
                @endphp
            <form action="{{ route('inquiry.store') }}" method="post">

                {{ csrf_field() }}

                <div class="amountDays" data-amountdays="{{ $amountDays }}"></div>

                <div class="panel panel-default text-left panel-booking1 panel-default-booking1">
                    <div class="panel-body panel-body-booking1">
                        <div class="row content row-booking1">
                            <div class="col-sm-2 col-sm-2-booking1">
                                <img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive img-thumbnail img-thumbnail-booking1" style="width:100%" alt="Image">
                            </div>
                            <div class="col-sm-7 text-left col-sm-7-booking1">

                                <h3 class="headliner-cabinname">{{ $cabinDetails->name }} - {{ $cabinDetails->region }}<span class="glyphicon glyphicon-question-sign" title="{{__('cart.headingThreeTitle')}}"></span></h3>

                                <div class="row row-booking1">

                                    @if (session()->has('error'))
                                        <div class="alert alert-warning alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <strong>{{ __('inquiry.errorOne') }}</strong> {{ session()->get('error') }}
                                        </div>
                                    @endif

                                    <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                        <div class="row row-booking1">
                                            <div class="col-sm-4 col-sm-4-booking1">
                                                <div class="form-group">
                                                    <input type="text" class="form-control form-control-booking1 dateFrom" name="dateFrom" value="{{ session()->get('checkin_from') }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-sm-4 col-sm-4-booking1">
                                                <div class="form-group">
                                                    <input type="text" class="form-control form-control-booking1 dateTo" name="dateTo" value="{{ session()->get('reserve_to') }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row row-booking1">
                                            @if($cabinDetails->sleeping_place != 1)
                                                <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1">
                                                    <div class="form-group {{ $errors->has('beds') ? ' has-error' : '' }}">
                                                        <select class="form-control form-control-booking1 jsCalBed" name="beds">
                                                            <option value="">{{__('cart.chooseBeds')}}</option>
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <option value="{{ $i }}" @if(session()->get('beds') == $i) selected @endif @if($i < session()->get('beds')) disabled @endif>{{ $i }}</option>
                                                            @endfor
                                                        </select>

                                                        @if ($errors->has('beds'))
                                                            <span class="help-block"><strong>{{ $errors->first('beds') }}</strong></span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <div class="form-group {{ $errors->has('dormitory') ? ' has-error' : '' }}">
                                                        <select class="form-control form-control-booking1 jsCalDorm" name="dormitory">
                                                            <option value="">{{__('cart.chooseDorms')}}</option>
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <option value="{{ $i }}" @if(session()->get('dormitory') == $i) selected @endif @if($i < session()->get('dormitory')) disabled @endif>{{ $i }}</option>
                                                            @endfor
                                                        </select>

                                                        @if ($errors->has('dormitory'))
                                                            <span class="help-block"><strong>{{ $errors->first('dormitory') }}</strong></span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <div class="form-group {{ $errors->has('sleeps') ? ' has-error' : '' }}">
                                                        <select class="form-control form-control-booking1 jsCalSleep" name="sleeps">
                                                            <option value="">{{__('cart.chooseSleeps')}}</option>
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <option value="{{ $i }}" @if(session()->get('sleeps') == $i) selected @endif @if($i < session()->get('sleeps')) disabled @endif>{{ $i }}</option>
                                                            @endfor
                                                        </select>

                                                        @if ($errors->has('sleeps'))
                                                            <span class="help-block"><strong>{{ $errors->first('sleeps') }}</strong></span>
                                                        @endif
                                                    </div>
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
                                                                {{__('cart.halfBoard')}}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="row row-booking1">
                                            <div class="col-sm-4 col-sm-4-f-booking1 comment-booking1 col-sm-4-booking1">
                                                <div class="form-group {{ $errors->has('comments') ? ' has-error' : '' }}">
                                                    <textarea id="comments" name="comments" class="form-control" rows="3" maxlength="300" placeholder="{{__('cart.comment')}}">{{ old('comments') }}</textarea>

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
                                                <h5>{{__('cart.depositForCabin')}}</h5>
                                            </div>
                                        </div>
                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                <p class="info-listing-booking1">{{__('cart.guests')}}:</p><p class="info-listing-price-booking1 replaceInquiryGuest">{{ session()->get('guests') }}</p>
                                                <p class="info-listing-booking1">{{__('cart.numberOfNights')}}:</p><p class="info-listing-price-booking1">{{ $dateDifference->days }} {{ __("cart.days") }}</p>
                                            </div>
                                        </div><br />
                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1 depsit-booking1">
                                                <p class="info-listing-booking1">{{__('cart.deposit')}}:</p><p class="info-listing-price-booking1 replaceInquiryDeposit">{{ number_format($amount, 2, ',', '.') }} &euro;</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($amount > 0 )

                    @php
                        $sumPrepaymentAmount = $amount;

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
                        <div class="col-sm-9" id="contact_information">
                            <div class="panel panel-default booking-box-booking1 panel-default-booking1 text-left">
                                <div class="panel-body panel-body-booking1">
                                    <div class="row row-booking1">
                                        <div class="col-sm-12">
                                            <h5>{{ __('cart.contactInfoHeading') }}</h5>
                                        </div>
                                        <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group {{ $errors->has('street') ? ' has-error' : '' }}">
                                                        <label> {{ __('cart.street') }} <span class="required">*</span></label>
                                                        <input type="text" class="form-control" id="street" name="street" placeholder="{{ __('cart.streetPlaceholder') }}" maxlength="255" value="{{ old('street', Auth::user()->usrAddress) }}">

                                                        @if ($errors->has('street'))
                                                            <span class="help-block"><strong>{{ $errors->first('street') }}</strong></span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group {{ $errors->has('city') ? ' has-error' : '' }}">
                                                        <label> {{ __('cart.city') }} <span class="required">*</span></label>
                                                        <input type="text" class="form-control" id="city" name="city" placeholder="{{ __('cart.cityPlaceholder') }}" maxlength="255" value="{{ old('city', Auth::user()->usrCity) }}">

                                                        @if ($errors->has('city'))
                                                            <span class="help-block"><strong>{{ $errors->first('city') }}</strong></span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                                                        <label> {{ __('cart.country') }} <span class="required">*</span></label>
                                                        <select class="form-control" id="country" name="country">
                                                            <option value="0"> {{ __('cart.chooseCountry') }} </option>
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
                                                        <label> {{ __('cart.zipcode') }} <span class="required">*</span></label>
                                                        <input type="text" class="form-control" id="zipcode" name="zipcode" placeholder="{{ __('cart.zipcodePlaceholder') }}" maxlength="25" value="{{ old('zipcode', Auth::user()->usrZip) }}">

                                                        @if ($errors->has('zipcode'))
                                                            <span class="help-block"><strong>{{ $errors->first('zipcode') }}</strong></span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group {{ $errors->has('mobile') ? ' has-error' : '' }}">
                                                        <label> {{ __('cart.mobile') }}</label>
                                                        <input type="text" class="form-control" id="mobile" name="mobile" placeholder="{{ __('cart.mobilePlaceholder') }}" maxlength="20" value="{{ old('mobile', Auth::user()->usrMobile) }}">

                                                        @if ($errors->has('mobile'))
                                                            <span class="help-block"><strong>{{ $errors->first('mobile') }}</strong></span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group {{ $errors->has('phone') ? ' has-error' : '' }}">
                                                        <label> {{ __('cart.phone') }}</label>
                                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="{{ __('cart.phonePlaceholder') }}" maxlength="20" value="{{ old('phone', Auth::user()->usrTelephone) }}">

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
                                            <h5>{{ __('cart.completePayment') }}<span class="glyphicon glyphicon-question-sign" title="{{ __('cart.amountTitle') }}"></span></h5>
                                        </div>
                                    </div>

                                    <div class="normalCalculation">
                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                <p class="info-listing-booking1">{{ __('cart.deposit') }}:</p><p class="info-listing-price-booking1 replaceInquiryCompleteDeposit">{{ number_format($sumPrepaymentAmount, 2, ',', '.') }} &euro;</p>
                                                <p class="info-listing-booking1">{{ __('cart.serviceFee') }}:</p><p class="info-listing-price-booking1 replaceInquiryServiceFee">{{ $serviceTax }} %</p>
                                            </div>
                                        </div>

                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                <h5 class="info-listing-booking1">{{ __('cart.paymentIncl') }}<br /> {{ __('cart.paymentInclServiceFee') }}:</h5><h5 class="info-listing-price-booking1 replaceInquiryCompletePayment">{{ number_format($sumPrepaymentAmountServiceTotal, 2, ',', '.') }} &euro;</h5>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row content row-booking1">
                        <div id="btn-ground-2-booking1">
                            <input type="hidden" name="sleeping_place" value="{{ $cabinDetails->sleeping_place }}">
                            <button type="submit" class="btn-default-booking1 btn-sm btn-details-booking1" name="inquirySend" value="inquirySend"><span class="glyphicon glyphicon-envelope" style="font-size: 16px;" aria-hidden="true"></span> {{ __('inquiry.sendInquiryButton') }}</button>
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