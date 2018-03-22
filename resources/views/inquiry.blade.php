@extends('layouts.app')

@section('title', 'Inquiry')

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



                    <div class="panel panel-default text-left panel-booking1 panel-default-booking1">
                        <div class="panel-body panel-body-booking1">
                            <div class="row content row-booking1">
                                <div class="col-sm-2 col-sm-2-booking1">
                                    <img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive img-thumbnail img-thumbnail-booking1" style="width:100%" alt="Image">
                                </div>
                                <div class="col-sm-7 text-left col-sm-7-booking1">

                                    <h3 class="headliner-cabinname">Rappenseeh&uuml;tte - Allg&auml;uer Alps<span class="glyphicon glyphicon-question-sign" title="Please check your data and correct if necessary. To edit them, simply double-click on the desired field."></span></h3>

                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                            <div class="form-group row row-booking1 calendar">
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <input type="text" class="form-control form-control-booking1 dateFrom" value=""  readonly>
                                                </div>
                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <input type="text" class="form-control form-control-booking1 dateTo" value=""  readonly>
                                                </div>

                                                <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1">
                                                    <select class="form-control form-control-booking1">
                                                        <option>Choose Bed(s)</option>
                                                        @for($i = 1; $i <= 30; $i++)
                                                            <option value="{{  $i }}" >{{ $i }}</option>
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

                                                <div class="col-sm-4 col-sm-4-booking1">
                                                    <select class="form-control form-control-booking1">
                                                        <option>Choose Sleep(s)</option>
                                                        @for($i = 1; $i <= 30; $i++)
                                                            <option value="{{ $i }}" >{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>

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
                                                    <h5>Deposit for Cabin</h5>
                                                </div>
                                            </div>
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">Guest(s):</p><p class="info-listing-price-booking1">1</p>
                                                    <p class="info-listing-booking1">Number night(s):</p><p class="info-listing-price-booking1">1</p>
                                                </div>
                                            </div><br />
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1 depsit-booking1">
                                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">{{ number_format(1, 2, '.', '') }}&euro;</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>





                <form>

                            <div class="row content row-booking1">
                                <div class="col-sm-3 col-sm-3-booking1 col-sm-r col-sm-r-booking1">
                                    <div class="panel panel-default booking-box-booking1 bottom-boxes-booking1 panel-booking1 panel-default-booking1" id="amount_box-booking1">
                                        <div class="panel-body panel-body-booking1">
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                                    <h5>Your Amount</h5>
                                                    <span class="label label-info label-cabinlist"><input type="checkbox" class="moneyBalance" name="moneyBalance" value="1"> Redeem now! {{ number_format(1, 2, '.', '') }}&euro;</span>
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
                                                <h5>Complete Payment<span class="glyphicon glyphicon-question-sign" title="Here all costs are listed again. The service fee helps us operate Huetten-Holiday and offer services like our live-chat for your trip. It contains sales tax."></span></h5>
                                            </div>
                                        </div>

                                        <!-- Money balance deduct begin -->
                                        <div class="moneyBalanceCal" style="display: none;">
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">{{ number_format(1, 2, '.', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">Applied money balance:</p><p class="info-listing-price-booking1">-{{ number_format(1, 2, '.', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">After deduction:</p><p class="info-listing-price-booking1">{{ number_format(1, 2, '.', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">Service fee:</p><p class="info-listing-price-booking1">{{ 1 }}%</p>
                                                </div>
                                            </div>

                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <h5 class="info-listing-booking1">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking1">{{ number_format(1, 2, '.', '') }}&euro;</h5>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Money balance deduct end -->

                                        <div class="normalCalculation">
                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">{{ number_format(1, 2, '.', '') }}&euro;</p>
                                                    <p class="info-listing-booking1">Service fee:</p><p class="info-listing-price-booking1">{{ 1 }}%</p>
                                                </div>
                                            </div>

                                            <div class="row row-booking1">
                                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                                    <h5 class="info-listing-booking1">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking1">{{ number_format(1, 2, '.', '') }}&euro;</h5>
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

                </form>



        </div>
    </main>
@endsection
