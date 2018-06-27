@extends('layouts.app')

@section('title', 'Edit Booking')

@inject('calendarServices', 'App\Http\Controllers\CalendarController')

@section('content')
    <div class="container-fluid container-fluid-booking1 bg-3 text-center">
        <div class="col-md-2 col-md-2-booking1"></div>
        <div class="col-md-8" id="list-filter-booking1">
            <nav class="navbar navbar-default navbar-default-booking1">
                <h2 class="cabin-head-booking1">{{ __('bookingHistory.editBookingHeading') }}</h2><h2 class="cabin-head-booking1"></h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking1"></div>
    </div>

    <main>
        <div class="container-fluid container-fluid-booking1 text-center">

            @isset($booking)
                @php
                    $monthBegin              = $booking->checkin_from->format('Y-m-d');
                    $monthEnd                = $booking->reserve_to->format('Y-m-d');
                    $d1                      = new DateTime($monthBegin);
                    $d2                      = new DateTime($monthEnd);
                    $dateDifference          = $d2->diff($d1);
                @endphp

                @if (session()->has('updateBookingFailedStatus'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>{{ __('bookingHistory.errorOne') }}</strong> {{ session()->get('updateBookingFailedStatus') }}
                    </div>
                @endif

                <form action="{{ route('edit.booking.history', $booking->_id) }}" method="post">

                    {{ csrf_field() }}

                    <div class="daysEditBook" data-sleepingplace="{{ $cabinDetails->sleeping_place }}" data-prevdays="{{ $dateDifference->days }}" data-days="{{ $dateDifference->days }}" data-prepaymentamount="{{ $booking->prepayment_amount }}" data-cabinprepaymentamount="{{ $cabinDetails->prepayment_amount }}"></div>

                    <div class="panel panel-default text-left panel-booking1 panel-default-booking1">
                        <div class="panel-body panel-body-booking1">
                            <div class="row content row-booking1">
                                <div class="col-sm-2 col-sm-2-booking1">
                                    <img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive img-thumbnail img-thumbnail-booking1" style="width:100%" alt="Image">
                                </div>
                                <div class="col-sm-7 text-left col-sm-7-booking1">

                                    <h3 class="headliner-cabinname">{{ $cabinDetails->name }} - {{ $cabinDetails->region }}<span class="glyphicon glyphicon-question-sign" title="{{ __('cart.headingThreeTitle') }}"></span></h3>
                                    @if (session()->has('bookingAvailableStatus'))
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            {{ session()->get('bookingAvailableStatus') }}
                                        </div>
                                    @endif

                                    <div class="row row-booking1">

                                        @if (session()->has('error'))
                                            <div class="alert alert-warning alert-dismissible" role="alert">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <strong>{{ __('bookingHistory.errorOne') }}</strong> {{ session()->get('error') }}
                                            </div>
                                        @endif

                                        <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                            <div class="row row-booking1" data-id="{{ $cabinDetails->_id }}">

                                                <div class="col-sm-12" id="errors_{{ $cabinDetails->_id }}"></div>
                                                <div class="col-sm-12" id="warning_{{ $cabinDetails->_id }}"></div>

                                                @php
                                                    $calendar = $calendarServices->calendar($cabinDetails->_id, $booking->checkin_from->format('Y-m'))
                                                @endphp

                                                <div class="holidayEditBook_{{ $cabinDetails->_id }}" data-holiday="{{ $calendar[0] }}"></div>
                                                <div class="greenEditBook_{{ $cabinDetails->_id }}" data-green="{{ $calendar[1] }}"></div>
                                                <div class="orangeEditBook_{{ $cabinDetails->_id }}" data-orange="{{ $calendar[2] }}"></div>
                                                <div class="redEditBook_{{ $cabinDetails->_id }}" data-red="{{ $calendar[3] }}"></div>
                                                <div class="notSeasonTimeEditBook_{{ $cabinDetails->_id }}" data-notseasontime="{{ $calendar[4] }}"></div>

                                                <div class="col-sm-4 col-sm-4-booking1 form-group {{ $errors->has('dateFrom') ? ' has-error' : '' }}">
                                                    <input type="text" class="form-control form-control-booking1 dateFromEditBook" id="dateFromEditBook_{{ $cabinDetails->_id }}" name="dateFrom" value="{{ $booking->checkin_from->format('d.m.y') }}" readonly>

                                                    @if ($errors->has('dateFrom'))
                                                        <span class="help-block"><strong>{{ $errors->first('dateFrom') }}</strong></span>
                                                    @endif
                                                </div>

                                                <div class="col-sm-4 col-sm-4-booking1 form-group {{ $errors->has('dateTo') ? ' has-error' : '' }}">
                                                    <input type="text" class="form-control form-control-booking1 dateToEditBook" id="dateToEditBook_{{ $cabinDetails->_id }}" name="dateTo" value="{{ $booking->reserve_to->format('d.m.y') }}" readonly>
                                                    @if ($errors->has('dateTo'))
                                                        <span class="help-block"><strong>{{ $errors->first('dateTo') }}</strong></span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row row-booking1">
                                                <input type="hidden" name="sleeping_place" value="{{ $cabinDetails->sleeping_place }}">
                                                @if($cabinDetails->sleeping_place != 1)
                                                    <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1">
                                                        <div class="form-group {{ $errors->has('beds') ? ' has-error' : '' }}">
                                                            <select class="form-control form-control-booking1 jsEditBookBed" name="beds">
                                                                <option value="">{{ __('cart.chooseBeds') }}</option>
                                                                @for($i = 1; $i <= 30; $i++)
                                                                    <option value="{{ $i }}" @if($booking->beds === $i) selected @endif>{{ $i }}</option>
                                                                @endfor
                                                            </select>

                                                            @if ($errors->has('beds'))
                                                                <span class="help-block"><strong>{{ $errors->first('beds') }}</strong></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 col-sm-4-booking1">
                                                        <div class="form-group {{ $errors->has('dormitory') ? ' has-error' : '' }}">
                                                            <select class="form-control form-control-booking1 jsEditBookDorm" name="dormitory">
                                                                <option value="">{{ __('cart.chooseDorms') }}</option>
                                                                @for($i = 1; $i <= 30; $i++)
                                                                    <option value="{{ $i }}" @if($booking->dormitory === $i) selected @endif>{{ $i }}</option>
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
                                                            <select class="form-control form-control-booking1 jsEditBookSleep" name="sleeps">
                                                                <option value="">{{ __('cart.chooseSleeps') }}</option>
                                                                @for($i = 1; $i <= 30; $i++)
                                                                    <option value="{{ $i }}" @if($booking->sleeps === $i) selected @endif>{{ $i }}</option>
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
                                                @if($cabinDetails->halfboard === '1' && $cabinDetails->halfboard_price != '')
                                                    <div class="col-sm-4 col-sm-4-booking1">
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input type="checkbox" name="halfboard" value="1">
                                                                    {{ __('cart.halfBoard') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="row row-booking1">
                                                <div class="col-sm-4 col-sm-4-f-booking1 comment-booking1 col-sm-4-booking1">
                                                    <div class="form-group {{ $errors->has('comments') ? ' has-error' : '' }}">
                                                        <textarea id="comments" name="comments" class="form-control" rows="3" maxlength="300" placeholder="{{ __('cart.comment') }}">{{ old('comments', $booking->comments) }}</textarea>

                                                        @if ($errors->has('comments'))
                                                            <span class="help-block"><strong>{{ $errors->first('comments') }}</strong></span>
                                                        @endif
                                                        <div id="update_book_comment"></div>
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
                                                    <h5>{{ __('cart.depositForCabin') }}</h5>
                                                </div>
                                            </div>
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">{{ __('cart.guests') }}:</p><p class="info-listing-price-booking1 replaceEditBookingGuest">{{ $booking->guests }}</p>
                                                    <p class="info-listing-booking1">{{ __('cart.numberOfNights') }}:</p><p class="info-listing-price-booking1 replaceNumberOfNights">{{ $dateDifference->days }} {{ __("cart.days") }}</p>
                                                </div>
                                            </div><br />
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1 depsit-booking1">
                                                    <p class="info-listing-booking1">{{ __('bookingHistory.oldAmount') }}:</p><p class="info-listing-price-booking1">{{ number_format($booking->prepayment_amount, 2, ',', '.') }} &euro;</p>
                                                </div>
                                            </div>
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
                                            <h5>{{ __('cart.completePayment') }}<span class="glyphicon glyphicon-question-sign" title="{{ __('cart.amountTitle') }}"></span></h5>
                                        </div>
                                    </div>

                                    <!-- This div shows if amount is greater than voucher amount -->
                                    <div class="amountGreater" style="display: block;">
                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                <p class="info-listing-booking1">{{ __('bookingHistory.newVoucherAmount') }}:</p><p class="info-listing-price-booking1 replaceEditBookingCompleteDeposit">0,00 &euro;</p>
                                                <p class="info-listing-booking1">{{ __('cart.serviceFee') }}:</p><p class="info-listing-price-booking1 replaceEditBookingServiceFee">0 %</p>
                                            </div>
                                        </div>

                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                <h5 class="info-listing-booking1">{{ __('cart.paymentIncl') }}<br /> {{ __('cart.paymentInclServiceFee') }}:</h5><h5 class="info-listing-price-booking1 replaceEditBookingCompletePayment">0,00 &euro;</h5>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- This div shows if voucher amount is greater than amount -->
                                    <div class="voucherGreater" style="display: none;">
                                        <div class="row row-booking1">
                                            <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                <p class="info-listing-booking1">{{ __('bookingHistory.newVoucherAmount') }}:</p><p class="info-listing-price-booking1 replaceEditBookingCompleteDeposit"></p>
                                                <p class="info-listing-booking1">{{ __('bookingHistory.storeToWallet') }}:</p><p class="info-listing-price-booking1 replaceEditBookingToWallet"></p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row content row-booking1">
                        <div id="btn-ground-2-booking1">
                            <button type="submit" class="btn-default-booking1 btn-sm btn-details-booking1" name="updateBooking" value="updateBooking"><span class="glyphicon glyphicon-credit-card" style="font-size: 14px;" aria-hidden="true"></span> {{ __('bookingHistory.updateButton') }}</button>
                        </div>
                    </div>
                </form>

            @endisset
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        window.environment = {
            service_tax_one_for_edit_booking: '{{ env('SERVICE_TAX_ONE') }}',
            service_tax_two_for_edit_booking: '{{ env('SERVICE_TAX_TWO') }}',
            service_tax_three_for_edit_booking: '{{ env('SERVICE_TAX_THREE') }}'
        }
    </script>
@endpush