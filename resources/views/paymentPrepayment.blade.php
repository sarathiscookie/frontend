@extends('layouts.app')

@section('title', 'Payment prepayment')

@section('content')
    <div class="container-fluid container-fluid-booking3 bg-3 text-center">
        <div class="col-md-2 col-md-2-booking3"></div>
        <div class="col-md-8 col-md-8-booking3" id="list-filter-booking3">
            <nav class="navbar navbar-default navbar-default-booking3">
                <h2 class="cabin-head-booking3">{{ __('payment.bookingSuccessHeading') }}</h2><h2 class="cabin-head-booking3">{{ __('payment.step3') }}</h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking3"></div>
    </div>
    <main>
        <div class="container-fluid text-center">
            <div class="row">
                <div class="panel panel-primary text-left bill-container">
                    @if ( session()->has('bookingSuccessStatusPrepayment') || session()->has('editBookingSuccessStatusPrepayment') || session()->has('inquiryBookingSuccessStatusPrepayment') )

                        @php
                            if( session()->has('order') ) {
                              $order = session()->get('order');
                            }

                            if( session()->has('editBookOrder') ) {
                              $order = session()->get('editBookOrder');
                            }

                            if( session()->has('inquiryBookOrder') ) {
                              $order = session()->get('inquiryBookOrder');
                            }
                        @endphp
                        <div class="panel-heading panel-heading-bill">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h2><strong>{{ __('prepayment.wellDone') }}</strong> {{ __('prepayment.wellDoneMsg') }}</h2>
                                    <h5>{{ session()->get('bookingSuccessStatusPrepayment') }} {{ session()->get('editBookingSuccessStatusPrepayment') }} {{ session()->get('inquiryBookingSuccessStatusPrepayment') }}</h5>
                                    <h5>{{ __('prepayment.thankYouMsgTwo') }}</h5>
                                    <div class="pull-right">
                                        <form action="{{route('payment.prepayment.download')}}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="order_id" id="order_id" value="{{ $order->_id }}">
                                            <button type="submit" class="btn btn-default download-bill">
                                                {{ __('prepayment.downloadBill') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">{{ __('prepayment.bookingStatus') }}</h4>
                                            <p class="list-group-item-text">{{ __('prepayment.onProcess') }}</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">{{ __('prepayment.txnNumber') }}</h4>
                                            <p class="list-group-item-text">{{ $order->txid }}</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">{{ __('prepayment.orderNumber') }}</h4>
                                            <p class="list-group-item-text">{{ $order->order_id }}</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">{{ __('prepayment.amount') }}</h4>
                                            <p class="list-group-item-text">{{ number_format($order->order_total_amount, 2, ',', '.') }} &euro;</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">Bank Name</h4>
                                            <p class="list-group-item-text">{{$order->clearing_bankname }}</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">{{ __('prepayment.accountHolder') }}</h4>
                                            <p class="list-group-item-text">{{$order->clearing_bankaccountholder }}</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">IBAN</h4>
                                            <p class="list-group-item-text">{{ $order->clearing_bankiban }}</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">BIC</h4>
                                            <p class="list-group-item-text">{{ $order->clearing_bankbic }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div>
                    <div id="btn-ground-bill">
                        <a href="/booking/history" class="btn btn-default btn-default-booking3 btn-sm btn-details-booking3">{{ __('prepayment.bookingHistoryLink') }}</a>
                    </div>
                </div>
            </div>
        </div><br><br>
    </main>
@endsection
