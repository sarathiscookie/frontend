@extends('layouts.app')

@section('title', 'Payment PayByBill')

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
            <div class="panel panel-default text-left">
                @if (session()->has('bookingSuccessStatusPrepayment') && session()->has('order'))
                    <div class="panel-heading">
                        <h2>Well Done! Successfully booked</h2>
                        <p>{{ session()->get('bookingSuccessStatusPrepayment') }} </p>
                        <p>You will get a Voucher in the next minutes per Mail. Enjoy your trip!</p>
                    </div>
                    <div class="panel-body">
                        <div class="row content col-sm-12">

                            @php
                                $order = session()->get('order')
                            @endphp
                            <div class="list-group">
                                <a href="#" class="list-group-item active">
                                    <h4 class="list-group-item-heading">Booking Status</h4>
                                    <p class="list-group-item-text">On Process</p>
                                </a>
                            </div>
                            <div class="list-group">
                                <a href="#" class="list-group-item active">
                                    <h4 class="list-group-item-heading">Transaction number</h4>
                                    <p class="list-group-item-text">{{ $order->txid }}</p>
                                </a>
                            </div>
                            <div class="list-group">
                                <a href="#" class="list-group-item active">
                                    <h4 class="list-group-item-heading">Order number</h4>
                                    <p class="list-group-item-text">{{ $order->order_id }}</p>
                                </a>
                            </div>
                            <div class="list-group">
                                <a href="#" class="list-group-item active">
                                    <h4 class="list-group-item-heading">Amount</h4>
                                    <p class="list-group-item-text">{{ number_format($order->order_total_amount, 2, ',', '.') }}&euro;</p>
                                </a>
                            </div>
                            <div class="list-group">
                                <a href="#" class="list-group-item active">
                                    <h4 class="list-group-item-heading">Bank Name</h4>
                                    <p class="list-group-item-text">{{$order->clearing_bankname }}</p>
                                </a>
                            </div>
                            <div class="list-group">
                                <a href="#" class="list-group-item active">
                                    <h4 class="list-group-item-heading">Account Holder</h4>
                                    <p class="list-group-item-text">{{$order->clearing_bankaccountholder }}</p>
                                </a>
                            </div>
                            <div class="list-group">
                                <a href="#" class="list-group-item active">
                                    <h4 class="list-group-item-heading">IBAN</h4>
                                    <p class="list-group-item-text">{{ $order->clearing_bankiban }}</p>
                                </a>
                            </div>
                            <div class="list-group">
                                <a href="#" class="list-group-item active">
                                    <h4 class="list-group-item-heading">BIC</h4>
                                    <p class="list-group-item-text">{{ $order->clearing_bankbic }}</p>
                                </a>
                            </div>

                        </div>
                    </div>
                @endif
            </div>
            <div>
                <div id="btn-ground-2-booking3">
                    <button type="button" class="btn btn-default btn-default-booking3 btn-sm btn-details-booking3">Bookinghistory</button>
                </div>
            </div>
        </div><br><br>
    </main>
@endsection
