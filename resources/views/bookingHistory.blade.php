@extends('layouts.app')

@section('title', 'Booking History')

@inject('service', 'App\Http\Controllers\BookingHistoryController')

@inject('inquiryService', 'App\Http\Controllers\InquiryController')

@section('content')
    <div class="container-fluid bg-3 text-center container-fluid-history">
        <div class="col-md-2 col-md-2-history"></div>
        <div class="col-md-8 col-md-8-history" id="list-filter-history">
            <nav class="navbar navbar-default navbar-default-history">
                <h2 class="cabin-head-history">{{ __('bookingHistory.overviewHeading') }}</h2>
            </nav>
            <br>
            <div class="responseMessage"></div>
            @if (session()->has('updateBookingSuccessStatus'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>{{ __('bookingHistory.updateBookingSuccessOne') }}</strong> {{ session()->get('updateBookingSuccessStatus') }}
                </div>
            @endif

            @if (session()->has('updateBookingFailedStatus'))
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>{{ __('bookingHistory.errorOne') }}</strong> {{ session()->get('updateBookingFailedStatus') }}
                </div>
            @endif

            @if (session()->has('inquiryPaymentStatus'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>{{ __('bookingHistory.errorOne') }}</strong> {{ session()->get('inquiryPaymentStatus') }}
                </div>
            @endif

            @if (session()->has('response') && session()->get('response') === 'success')
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>{{ __('bookingHistory.updateBookingSuccessOne') }}</strong> {{ __('bookingHistory.inquiryStatusOne') }} <br> {{ __('bookingHistory.inquiryStatusTwo') }}
                </div>
            @endif
        </div>
        <div class="col-md-2 col-md-2-history"></div>
    </div>
    <main>
        <div class="container-fluid text-center container-fluid-history">
            @isset($bookings)
                @forelse($bookings as $booking)
                    @php
                        $begin              = date('Y-m-d');
                        $end                = $booking->checkin_from->format('Y-m-d');
                        $d1                 = new DateTime($begin);
                        $d2                 = new DateTime($end);
                        $dateDifference     = $d2->diff($d1);
                        $reservation_cancel = (int)$booking->reservation_cancel;
                    @endphp
                    <div class="panel panel-default text-left panel-history panel-default-history">
                        <div class="panel-body panel-body-history">
                            <div class="row row-history content">
                                <div class="col-sm-2 col-sm-2-history">
                                    <img src="{{ asset('storage/'.$service->showListImage($booking->cabinname)) }}" class="img-responsive img-thumbnail img-thumbnail-history" style="width:100%" alt="Image">
                                </div>
                                <div class="col-sm-7 text-left col-sm-7-history">
                                    <h3 class="headliner-cabinname">{{ $service->cabin($booking->cabinname)->name }} - {{ $service->cabin($booking->cabinname)->region }}</h3>
                                    <div class="row row-history">
                                        <div class="responseCancelMessage_{{ $booking->_id }}"></div>
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

                                                    <!-- Fix -->
                                                    @if($booking->status === '1' && $booking->payment_status === '1')
                                                        <span class="label label-success label-cabinlist">{{ __('bookingHistory.successStatus') }}</span> <br>
                                                        <form action="{{route('booking.history.voucher.download')}}" method="POST">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="book_id" id="book_id" value="{{ $booking->_id }}">
                                                            <button type="submit" class="btn btn-list-history">{{ __('bookingHistory.downloadVoucher') }} <span class="glyphicon glyphicon-cloud-download"></span></button>
                                                        </form>

                                                        @if($begin < $end)
                                                            <a href="{{ route('edit.booking.history', $booking->_id) }}" class="btn btn-list-history" style="color: inherit;">{{ __('bookingHistory.editBooking') }} <span class="glyphicon glyphicon-wrench"></span></a>
                                                            @if($reservation_cancel <= $dateDifference->days)
                                                                <button type="button" class="btn btn-list-history cancelMoneyReturn" data-cancel="{{ $booking->_id }}" data-return="yes" data-loading-text="{{ __('bookingHistory.cancelingLoader') }}" autocomplete="off">{{ __('bookingHistory.cancelBooking') }} <span class="glyphicon glyphicon-remove"></span></button>
                                                            @else
                                                                <button type="button" class="btn btn-list-history cancelMoneyReturn" data-cancel="{{ $booking->_id }}" data-return="no" data-loading-text="{{ __('bookingHistory.cancelingLoader') }}" autocomplete="off">{{ __('bookingHistory.cancelBooking') }} <span class="glyphicon glyphicon-remove"></span></button>
                                                            @endif

                                                        @endif
                                                    @endif

                                                    <!-- Completed -->
                                                    @if($booking->status === '3')
                                                        <span class="label label-success label-cabinlist">{{ __('bookingHistory.successStatus') }}</span> <br>
                                                        <form action="{{route('booking.history.voucher.download')}}" method="POST">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="book_id" id="book_id" value="{{ $booking->_id }}">
                                                            <button type="submit" class="btn btn-list-history">{{ __('bookingHistory.downloadVoucher') }} <span class="glyphicon glyphicon-cloud-download"></span></button>
                                                        </form>
                                                    @endif

                                                    <!-- Cancel -->
                                                    @if($booking->status === '2')
                                                        <span class="label label-danger label-cabinlist">{{ __('bookingHistory.cancelStatus') }}</span> <br>
                                                        <button type="button" class="btn btn-list-history deleteCancelledBookingHistory" data-del="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteBooking') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    <!-- Reservation -->
                                                    @if($booking->status === '4' && $booking->payment_status === '2')
                                                        <span class="label label-info label-cabinlist">{{ __('bookingHistory.reservationStatus') }}</span> <br>
                                                        <form action="{{route('booking.history.voucher.download')}}" method="POST">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="book_id" id="book_id" value="{{ $booking->_id }}">
                                                            <button type="submit" class="btn btn-list-history">{{ __('bookingHistory.downloadVoucher') }} <span class="glyphicon glyphicon-cloud-download"></span></button>
                                                        </form>
                                                    @endif

                                                    <!-- 1: Inquiry Approved -->
                                                    @if($booking->status === '5' && $booking->inquirystatus === 1 && $booking->typeofbooking === 1)
                                                        <span class="label label-success label-cabinlist">{{ __('bookingHistory.inquiryAcceptedStatus') }}</span> <br>
                                                        <a href="/booking/history/inquiry/{{ $booking->_id }}" class="btn btn-list-history inquiryPayment" name="inquiryPayment" id="inquiryPayment" value="inquiryPayment" data-inquirypayment="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.doYourPayment') }}..." autocomplete="off">{{ __('bookingHistory.doYourPayment') }} <span class="glyphicon glyphicon-euro"></span></a>
                                                        <button type="button" class="btn btn-list-history deleteInquiryApprovedBookingHistory" data-delapprovedinquiry="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteInquiry') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    <!-- Inquiry Waiting for reply -->
                                                    @if($booking->status === '7' && $booking->inquirystatus === 0 && $booking->typeofbooking === 1)
                                                        <span class="label label-warning label-cabinlist">{{ __('bookingHistory.inquiryWaitingStatus') }}</span> <br>
                                                        <button type="button" class="btn btn-list-history" data-toggle="modal" data-target="#openChat_{{ $booking->_id }}">{{ __('bookingHistory.openChat') }} <span class="glyphicon glyphicon-envelope"></span></button>
                                                        <button type="button" class="btn btn-list-history deleteInquiryWaitingBookingHistory" data-delwaitinginquiry="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteInquiry') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    <!-- Inquiry Rejected -->
                                                    @if($booking->status === '7' && $booking->inquirystatus === 2 && $booking->typeofbooking === 1)
                                                        <span class="label label-danger label-cabinlist">{{ __('bookingHistory.inquiryRejectedStatus') }}</span> <br>
                                                        <button type="button" class="btn btn-list-history deleteInquiryRejectedBookingHistory" data-delrejectedinquiry="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteInquiry') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    <!-- Waiting for payment (prepayment)-->
                                                    @if($booking->status === '5' && $booking->payment_status === '3')
                                                        <span class="label label-warning label-cabinlist">{{ __('bookingHistory.waitingStatus') }}</span> <br>
                                                        @if(!empty($booking->order_id))
                                                            <form action="{{route('payment.prepayment.download')}}" method="POST">
                                                                {{ csrf_field() }}
                                                                <input type="hidden" name="order_id" id="order_id" value="{{ $booking->order_id }}">
                                                                <button type="submit" class="btn btn-list-history">{{ __('bookingHistory.downloadBill') }} <span class="glyphicon glyphicon-cloud-download"></span></button>
                                                            </form>
                                                        @endif
                                                        <button type="button" class="btn btn-list-history deleteWaitingPrepaymentBookingHistory" data-delwaitingprepay="{{ $booking->_id }}" data-loading-text="{{ __('bookingHistory.deleteBookingLoader') }}" autocomplete="off">{{ __('bookingHistory.deleteBooking') }} <span class="glyphicon glyphicon-trash"></span></button>
                                                    @endif

                                                    <!-- Booking Failed -->
                                                    @if($booking->status === '5' && $booking->payment_status === '0')
                                                        <span class="label label-danger label-cabinlist">{{ __('bookingHistory.failedStatus') }}</span> <br>
                                                    @endif

                                                    <!-- On processing -->
                                                    @if($booking->status === '11')
                                                        <span class="label label-success label-cabinlist">{{ __('bookingHistory.onProcessStatus') }}</span> <br>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for chat -->
                    <div class="col-md-12">
                        <div class="modal fade box box_primary direct-chat direct-chat-warning box_chat" id="openChat_{{ $booking->_id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header box-header with-border">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title box-title" id="myModalLabel">Chat</h4>
                                    </div>

                                    <div class="successMessage_{{$booking->_id}}"></div>

                                    <div class="hideAfterSuccess">
                                        <div class="modal-body box-body">
                                            <div id="errorSendFailed_{{$booking->_id}}" style="display: none;"></div>
                                            <div id="chatSendFailed_{{$booking->_id}}" style="display: none;"></div>
                                            @forelse($inquiryService->message($booking->_id) as $message)
                                                <div class="direct-chat-messages">
                                                    @if($message->sender_id == Auth::user()->_id && !empty($message->text))
                                                        <div class="direct-chat-msg chat_msg">
                                                            <div class="direct-chat-info chat_info clearfix">
                                                                <span class="direct-chat-name chat_name pull-left">{{$message->sender->usrFirstname}} {{$message->sender->usrLastname}}</span>
                                                                <span class="direct-chat-timestamp chat_timestamp pull-right">{{ ($message->created_at)->format('d M H:i:s A') }}</span>
                                                            </div>
                                                            <i class="menu-icon bg-light-blue direct-chat-img chat_img text-center" style="padding: 9px;">G</i>
                                                            <div class="direct-chat-text chat_text">{{ $message->text }}</div>
                                                        </div>
                                                    @endif
                                                    @if($message->receiver_id == Auth::user()->_id && !empty($message->text))
                                                        <div class="direct-chat-msg chat_msg right">
                                                            <div class="direct-chat-info chat_info clearfix">
                                                                <span class="direct-chat-name chat_name pull-right">{{$message->sender->usrFirstname}} {{$message->sender->usrLastname}}</span>
                                                                <span class="direct-chat-timestamp chat_timestamp pull-left">{{ ($message->created_at)->format('d M H:i:s A') }}</span>
                                                            </div>
                                                            <i class="menu-icon label-default direct-chat-img chat_img text-center" style="padding: 9px;">HW</i>
                                                            <div class="direct-chat-text chat_text">{{ $message->text }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <p>{{ __('inquiry.noChats') }}</p>
                                            @endforelse
                                        </div>
                                        <div class="modal-footer box_footer">
                                            <div class="input-group margin col-md-12">
                                                <input type="text" name="message[]" id="message.{{$booking->_id}}" placeholder="{{ __('inquiry.enterYourAnswer') }}" class="form-control chatMessage_{{$booking->_id}}" autocomplete="off" maxlength="350">
                                                <span class="input-group-btn">
                                                <button type="button" class="btn btn_info btn-flat sendChatMsg" data-chatbookid="{{$booking->_id}}" data-loading-text="{{ __('inquiry.sendLoading') }}" autocomplete="off">{{ __('inquiry.sendButton') }}</button>
                                            </span>
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
            deleteFailed: '<?php echo __('bookingHistory.deleteFailed'); ?>',
            cancelBookingMoneyReturnConfirm: '<?php echo __('bookingHistory.cancelBookingMoneyReturnConfirm'); ?>',
            cancelBookingMoneyNotReturnConfirm: '<?php echo __('bookingHistory.cancelBookingMoneyNotReturnConfirm'); ?>'
        }
    </script>
@endpush

