@extends('layouts.app')

@section('title', 'Cabin Details')

@section('content')

@inject('userService', 'App\Http\Controllers\CabinDetailsController')

    <div class="container-fluid text-center">
        @isset($cabinDetails)
            <div class="row content">
                <div class="col-md-1"></div>
                <div class="col-md-10">
                    <ol class="breadcrumb text-left" style="background-color: #dddddd">
                        <li>{{ $cabinDetails->name }}</li>
                        <li>{{ $cabinDetails->region }}</li>
                        <li>{{ $cabinDetails->country }} ({{ number_format($cabinDetails->height, 0, '', '.') }} m)</li>
                    </ol>
                </div>
                <div class="col-md-1"></div>
            </div>

            <div class="row">
                <div class="col-md-1"></div>
                <div class="col-md-10">
                    <div class="row">

                        <div class="col-md-8">

                            <div class="panel panel-default">
                                <div class="panel-body">

                                    <div class="thumbnail">
                                        <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                                            </div>
                                            <div class="col-md-2">
                                                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                                            </div>
                                            <div class="col-md-2">
                                                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                                            </div>
                                            <div class="col-md-2">
                                                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                                            </div>
                                            <div class="col-md-2">
                                                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                                            </div>
                                            <div class="col-md-2">
                                                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-left">
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                        <button type="button" class="btn btn-default btn-sm pull-left">More Details</button>
                                        <button type="button" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span></button>
                                        <button type="button" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span></button>
                                        <button type="button" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span></button>
                                        <button type="button" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span></button>
                                        <button type="button" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span></button>
                                        <button type="button" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span></button>
                                        <button type="button" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span></button>
                                    </div>
                                    <br>

                                    <hr>

                                    <div class="text-left">
                                        <h3>Info</h3>
                                        @if($userService->userDetails($cabinDetails->cabin_owner))<h5><strong>Cabin Owner:</strong> {{ $userService->userDetails($cabinDetails->cabin_owner)->usrFirstname }} {{ $userService->userDetails($cabinDetails->cabin_owner)->usrLastname }}</h5>@endif
                                        <h5><strong>Club section:</strong> {{ $cabinDetails->club }}</h5>
                                        @if( $cabinDetails->sleeping_place != 1 )
                                            <h5><strong>Beds:</strong> {{ $cabinDetails->beds }}</h5>
                                            <h5><strong>Dorms:</strong> {{ $cabinDetails->dormitory }}</h5>
                                        @else
                                            <h5><strong>Sleeping places:</strong> {{ $cabinDetails->sleeps }}</h5>
                                        @endif
                                        <h5><strong>Payment:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Season Times:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Website:</strong> {{ $cabinDetails->website }}</h5>
                                    </div>

                                    <hr>

                                    <div class="text-left">
                                        <h3>Price List</h3>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                    </div>

                                    <hr>

                                    <div class="text-left">
                                        <h3>Reservation Cancel</h3>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                    </div>

                                    <hr>

                                    <div class="text-left">
                                        <h3>Tour</h3>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="col-sm-4 pull-right">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h5 class="text-capitalize">Expected opening timings:</h5>
                                            <h5><span class="badge">2018</span></h5>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <h5><b>Summer open: </b><small>01.05.18</small></h5>
                                                </div>
                                                <div class="col-sm-6">
                                                    <h5><b>Summer close: </b><small>10.10.18</small></h5>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <h5><b>Winter open: </b><small>01.05.18</small></h5>
                                                </div>
                                                <div class="col-sm-6">
                                                    <h5><b>Winter close: </b><small>10.04.18</small></h5>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h5>Your journey begins here <span class="glyphicon glyphicon-question-sign"></span></h5>
                                            <div class="form-group row calendar">
                                                <div class="col-sm-4">
                                                    <input type="text" class="form-control dateFrom" id="dateFrom" name="dateFrom" placeholder="Arrival" readonly>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="text" class="form-control dateTo" id="dateTo" name="dateTo" placeholder="Departure" readonly>
                                                </div>

                                                <div class="col-sm-4">
                                                    <select class="form-control">
                                                        <option>Persons</option>
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <hr>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-6 col-sm-6">
                                            <h4><span class="label label-info pull-left">Tomorrow cabin closed</span></h4>
                                        </div>
                                        <div class="col-xs-6 col-sm-6">
                                            <a href="/" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-shopping-cart"></span> Add Cart</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-md-1">
                </div>
            </div>
        @endisset
    </div><br><br>

@endsection