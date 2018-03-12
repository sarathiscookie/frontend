@extends('layouts.app')

@section('title', 'Cart')

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
                                    <div class="form-group row row-booking1">
                                        <div class="col-sm-4 col-sm-4-booking1">
                                            <input class="form-control form-control-booking1" type="text" placeholder="Arrival">
                                        </div>
                                        <div class="col-sm-4 col-sm-4-booking1">
                                            <input class="form-control form-control-booking1" type="text" placeholder="Departure">
                                        </div>
                                        <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1">
                                            <select class="form-control form-control-booking1">
                                                <option>Bed(s)</option>
                                                <option>1</option>
                                                <option>2</option>
                                                <option>3</option>
                                                <option>4</option>
                                                <option>5</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 col-sm-4-booking1">
                                            <select class="form-control form-control-booking1">
                                                <option>Dorm(s)</option>
                                                <option>1</option>
                                                <option>2</option>
                                                <option>3</option>
                                                <option>4</option>
                                                <option>5</option>
                                            </select>
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
                                            <h5>Deposit for Cabin <a href="#"><span class="glyphicon glyphicon-remove-booking1" title="Delete your Booking"></span></a></h5>
                                        </div>
                                    </div>
                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                            <p class="info-listing-booking1">Guest(s):</p><p class="info-listing-price-booking1">1</p>
                                            <p class="info-listing-booking1">Number night(s):</p><p class="info-listing-price-booking1">5</p>
                                        </div>
                                    </div><br />
                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1 depsit-booking1">
                                            <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">50,00€</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default text-left panel-booking1 panel-default-booking1">
                <div class="panel-body panel-body-booking1">
                    <div class="row content row-booking1">
                        <div class="col-sm-2 col-sm-2-booking1">
                            <img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive img-thumbnail img-thumbnail-booking1" style="width:100%" alt="Image">
                        </div>
                        <div class="col-sm-7 text-left col-sm-7-booking1">
                            <h3 class="headliner-cabinname">Kemptner Hütte - Zillertaler Alpen<span class="glyphicon glyphicon-question-sign" title="Please check your data and correct if necessary. To edit them, simply double-click on the desired field."></span></h3>
                            <div class="row row-booking1">
                                <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                    <div class="form-group row row-booking1">
                                        <div class="col-sm-4 col-sm-4-booking1">
                                            <input class="form-control form-control-booking1" type="text" placeholder="Arrival">
                                        </div>
                                        <div class="col-sm-4 col-sm-4-booking1">
                                            <input class="form-control form-control-booking1" type="text" placeholder="Departure">
                                        </div>
                                        <div class="col-sm-4 col-sm-4-f-booking1 col-sm-4-booking1">
                                            <select class="form-control form-control-booking1">
                                                <option>Bed(s)</option>
                                                <option>1</option>
                                                <option>2</option>
                                                <option>3</option>
                                                <option>4</option>
                                                <option>5</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 col-sm-4-booking1">
                                            <select class="form-control form-control-booking1">
                                                <option>Dorm(s)</option>
                                                <option>1</option>
                                                <option>2</option>
                                                <option>3</option>
                                                <option>4</option>
                                                <option>5</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 col-sm-4-f-booking1 comment-booking1 col-sm-4-booking1">
                                            <input class="form-control comment-box-booking1 form-control-booking1" type="text" placeholder="Comment:">
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
                                            <h5>Deposit for Cabin <a href="#"><span class="glyphicon glyphicon-remove-booking1" title="Delete your Booking"></span></a></h5>
                                        </div>
                                    </div>
                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                            <p class="info-listing-booking1">Guest(s):</p><p class="info-listing-price-booking1">1</p>
                                            <p class="info-listing-booking1">Number night(s):</p><p class="info-listing-price-booking1">5</p>
                                        </div>
                                    </div><br />
                                    <div class="row row-booking1">
                                        <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1 depsit-booking1">
                                            <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">50,00€</p>
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
                    <div class="panel panel-default booking-box-booking1 bottom-boxes-booking1 panel-booking1 panel-default-booking1" id="amount_box-booking1">
                        <div class="panel-body panel-body-booking1">
                            <div class="row row-booking1">
                                <div class="col-sm-12 col-sm-12-booking1 month-opening-booking1">
                                    <h5>Your Amount</h5>
                                    <button type="button" class="btn btn-default btn-default-booking1 btn-amount-booking1 btn-details btn-details-booking1">Redeem now!</button>
                                    <h5 id="cash-amount-booking1">20,00€</h5>
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
                            <div class="row row-booking1">
                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                    <p class="info-listing-booking1">Deposit:</p><p class="info-listing-price-booking1">60,00€</p>
                                    <p class="info-listing-booking1">Amount:</p><p class="info-listing-price-booking1">- 20,00€</p>
                                    <p class="info-listing-booking1">Deposit netto:</p><p class="info-listing-price-booking1">40,00€</p>
                                    <p class="info-listing-booking1">Service fee:</p><p class="info-listing-price-booking1">7,50€</p>
                                </div>
                            </div>
                            <div class="row row-booking1">
                                <div class="col-sm-12 col-sm-12-booking1 col-sm-12-extra-booking1">
                                    <h5 class="info-listing-booking1">Payment incl.<br /> Service fee:</h5><h5 class="info-listing-price-booking1">47,50€</h5>
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
        </div><br><br>
    </main>
@endsection
