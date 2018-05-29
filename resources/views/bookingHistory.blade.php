@extends('layouts.app')

@section('title', 'Cabin Details')

@inject('service', 'App\Http\Controllers\BookingHistoryController')

@section('content')
    <div class="container-fluid bg-3 text-center container-fluid-history">
        <div class="col-md-2 col-md-2-history"></div>
        <div class="col-md-8 col-md-8-history" id="list-filter-history">
            <nav class="navbar navbar-default navbar-default-history">
                <h2 class="cabin-head-history">{{ __('bookingHistory.overviewHeading') }}</h2>
            </nav>
            <br>
            <div class="responseMessage"></div>
        </div>
        <div class="col-md-2 col-md-2-history"></div>
    </div>
    <main>
        <div class="container-fluid text-center container-fluid-history">
            @isset($bookings)
                @forelse($bookings as $booking)
                    <div class="panel panel-default text-left panel-history panel-default-history">
                        <div class="panel-body panel-body-history">
                            <div class="row row-history content">
                                <div class="col-sm-2 col-sm-2-history">
                                    <img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive img-thumbnail img-thumbnail-history" style="width:100%" alt="Image">
                                </div>
                                <div class="col-sm-7 text-left col-sm-7-history">
                                    <h3 class="headliner-cabinname">{{ $service->cabin($booking->cabinname)->name }} - {{ $service->cabin($booking->cabinname)->region }}</h3>
                                    <div class="row row-history">
                                        <div class="col-sm-12 col-sm-12-history">
                                            <div class="form-group row row-history">
                                                <ul class="payment-options">
                                                    <li class="check-it-list-spe-history">{{ __('bookingHistory.bookingNumber') }}:</li><li class="check-it-list-spe-history in-info-history">{{ $booking->invoice_number }}</li>
                                                    <li class="check-it-list-spe-history">{{ __('bookingHistory.arrival') }}:</li><li class="check-it-list-spe-history in-info-history">{{ $booking->checkin_from->format('d.m.y') }}</li>
                                                    <li class="check-it-list-spe-history">{{ __('bookingHistory.departure') }}:</li><li class="check-it-list-spe-history in-info-history">{{ $booking->reserve_to->format('d.m.y') }}</li>
                                                    @if($service->cabin($booking->cabinname)->sleeping_place != 1)
                                                        <li class="check-it-list-spe-history">{{ __('bookingHistory.beds') }}:</li><li class="check-it-list-spe-history in-info-history">{{ $booking->beds }}</li>
                                                        <li class="check-it-list-spe-history">{{ __('bookingHistory.dorms') }}:</li><li class="check-it-list-spe-history in-info-history">{{ $booking->dormitory }}</li>
                                                    @else
                                                        <li class="check-it-list-spe-history">{{ __('bookingHistory.sleeps') }}:</li><li class="check-it-list-spe-history in-info-history">{{ $booking->sleeps }}</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-sm-3-history">
                                    <div class="panel panel-default booking-box-history panel-history panel-default-history">

                                        <div class="panel-body panel-body-history">
                                            <div class="row row-history">
                                                <div class="col-sm-12 col-sm-12-history month-opening">
                                                    <h5>{{ __('bookingHistory.bookingStatusHeading') }}</h5>
                                                    <br>
                                                    @if($booking->status === '1'  || $booking->status === '3') <!-- Fix or Completed -->
                                                        <span class="label label-success label-cabinlist">{{ __('bookingHistory.successStatus') }}</span> <br>
                                                        <form action="{{route('booking.history.voucher.download')}}" method="POST">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="book_id" id="book_id" value="{{ $booking->_id }}">
                                                            <button type="submit" class="btn btn-list-history">{{ __('bookingHistory.downloadVoucher') }} <span class="glyphicon glyphicon-cloud-download"></span></button>
                                                        </form>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.editBooking') }} <span class="glyphicon glyphicon-wrench"></span></button>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.cancelBooking') }} <span class="glyphicon glyphicon-remove"></span></button>
                                                    @endif

                                                    @if($booking->status === '2') <!-- Cancel -->
                                                        <span class="label label-danger label-cabinlist">{{ __('bookingHistory.cancelStatus') }}</span> <br>
                                                        <button type="button" class="btn btn-list-history deleteCancelledBookingHistory" data-del="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteBooking') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    @if($booking->status === '4' && $booking->payment_status === '2') <!-- Reservation -->
                                                        <span class="label label-success label-cabinlist">{{ __('bookingHistory.successStatus') }}</span> <br>
                                                        <form action="{{route('booking.history.voucher.download')}}" method="POST">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="book_id" id="book_id" value="{{ $booking->_id }}">
                                                            <button type="submit" class="btn btn-list-history">{{ __('bookingHistory.downloadVoucher') }} <span class="glyphicon glyphicon-cloud-download"></span></button>
                                                        </form>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.editBooking') }} <span class="glyphicon glyphicon-wrench"></span></button>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.cancelBooking') }} <span class="glyphicon glyphicon-remove"></span></button>
                                                    @endif

                                                    @if($booking->status === '5' && $booking->inquirystatus === 1 && $booking->typeofbooking === 1) <!-- 1: Inquiry Approved -->
                                                        <span class="label label-success label-cabinlist">{{ __('bookingHistory.inquiryAcceptedStatus') }}</span> <br>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.doYourPayment') }} <span class="glyphicon glyphicon-euro"></span></button>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.openChat') }} <span class="glyphicon glyphicon-envelope"></span></button>
                                                        <button type="button" class="btn btn-list-history deleteInquiryApprovedBookingHistory" data-delapprovedinquiry="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteInquiry') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    @if($booking->status === '7' && $booking->inquirystatus === 0 && $booking->typeofbooking === 1) <!-- Inquiry Waiting for reply -->
                                                        <span class="label label-warning label-cabinlist">{{ __('bookingHistory.inquiryWaitingStatus') }}</span> <br>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.openChat') }} <span class="glyphicon glyphicon-envelope"></span></button>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.editInquiry') }} <span class="glyphicon glyphicon-wrench"></span></button>
                                                        <button type="button" class="btn btn-list-history deleteInquiryWaitingBookingHistory" data-delwaitinginquiry="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteInquiry') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    @if($booking->status === '7' && $booking->inquirystatus === 2 && $booking->typeofbooking === 1) <!-- Inquiry Rejected -->
                                                        <span class="label label-danger label-cabinlist">{{ __('bookingHistory.inquiryRejectedStatus') }}</span> <br>
                                                        <button type="button" class="btn btn-list-history deleteInquiryRejectedBookingHistory" data-delrejectedinquiry="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteInquiry') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    @if($booking->status === '5' && $booking->payment_status === '3') <!-- Waiting for payment (prepayment)-->
                                                        <span class="label label-warning label-cabinlist">{{ __('bookingHistory.waitingStatus') }}</span> <br>
                                                        <button type="button" class="btn btn-list-history">{{ __('bookingHistory.downloadBill') }} <span class="glyphicon glyphicon-cloud-download"></span></button>
                                                        <button type="button" class="btn btn-list-history deleteWaitingPrepaymentBookingHistory" data-delwaitingprepay="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteBooking') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p>{{ __('bookingHistory.noBookingHistory') }}</p>
                @endforelse

                    {!! $bookings->links() !!}
            @endisset
        </div><br><br>
    </main>
@endsection

@push('scripts')
    <script>
        window.environment = {
            confirmDeleteBooking: '<?php echo __('bookingHistory.confirmDeleteBooking'); ?>',
            deleteFailed: '<?php echo __('bookingHistory.deleteFailed'); ?>'
        }
    </script>
@endpush

