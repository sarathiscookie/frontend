@extends('layouts.app')

@section('title', 'Payment prepayment')

@section('content')
    <div class="container-fluid container-fluid-booking3 bg-3 text-center">
        <div class="col-md-2 col-md-2-booking3"></div>
        <div class="col-md-8 col-md-8-booking3" id="list-filter-booking3">
            <nav class="navbar navbar-default navbar-default-booking3">
                <h2 class="cabin-head-booking3">Booked!</h2><h2 class="cabin-head-booking3">Step 3 of 3</h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-booking3"></div>
    </div>
    <main>
        <div class="container-fluid text-center">
            <div class="row">
                <div class="panel panel-primary text-left bill-container">
                    @if (session()->has('bookingSuccessStatusPrepayment') && session()->has('order'))
                        @php
                            $order = session()->get('order')
                        @endphp
                        <div class="panel-heading panel-heading-bill">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h2>Well Done! Successfully booked</h2>
                                    <h5>{{ session()->get('bookingSuccessStatusPrepayment') }} </h5>
                                    <h5>You will get your voucher after you pay the amount. Enjoy your trip!</h5>
                                    <div class="pull-right">
                                        <form action="{{route('payment.prepayment.download')}}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="order_id" id="order_id" value="{{ $order->_id }}">
                                            <button type="submit" class="btn btn-default download-bill">
                                                Click here to download Ihre Zahlungsinformationen
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
                                            <h4 class="list-group-item-heading">Booking Status</h4>
                                            <p class="list-group-item-text">On Process</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">Transaction number</h4>
                                            <p class="list-group-item-text">{{ $order->txid }}</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">Order number</h4>
                                            <p class="list-group-item-text">{{ $order->order_id }}</p>
                                        </div>
                                    </div>
                                    <div class="list-group bill-list">
                                        <div class="list-group-item">
                                            <h4 class="list-group-item-heading">Amount</h4>
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
                                            <h4 class="list-group-item-heading">Account Holder</h4>
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
                        <a href="/booking/history" class="btn btn-default btn-default-booking3 btn-sm btn-details-booking3">Bookinghistory</a>
                    </div>
                </div>
            </div>
        </div><br><br>
    </main>
@endsection
