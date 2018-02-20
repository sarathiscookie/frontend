@extends('layouts.app')

@section('title', 'Cabin Details')

@section('content')

@inject('service', 'App\Http\Controllers\CabinDetailsController')

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
                                        <div class="more">{{ strip_tags($cabinDetails->other_details) }}</div>

                                        <div>
                                            @if($cabinDetails->interior)
                                                @foreach($cabinDetails->interior as $interior)
                                                    <button type="button" class="btn btn-default btn-sm btn-space pull-right" data-toggle="tooltip" data-placement="bottom" title="{{ $service->interiorLabel($interior) }}">
                                                        <span @if($interior === 'Food à la carte') class="glyphicon glyphicon-credit-card" @elseif($interior === 'breakfast') class="glyphicon glyphicon-glass" @else class="glyphicon glyphicon-home" @endif aria-hidden="true"></span>
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>

                                    </div>
                                    <br>

                                    <hr>

                                    <div class="text-left">
                                        <h4>Info</h4>
                                        @if($service->userDetails($cabinDetails->cabin_owner))
                                            <h5><strong>Cabin Owner: </strong>{{ $service->userDetails($cabinDetails->cabin_owner)->usrFirstname }} {{ $service->userDetails($cabinDetails->cabin_owner)->usrLastname }}</h5>
                                        @endif

                                        <h5><strong>Club section: </strong>{{ $cabinDetails->club }}</h5>

                                        @if( $cabinDetails->sleeping_place != 1 )
                                            <h5><strong>Beds: </strong>{{ $cabinDetails->beds }}</h5>
                                            <h5><strong>Dorms: </strong>{{ $cabinDetails->dormitory }}</h5>
                                        @else
                                            <h5><strong>Sleeping places: </strong>{{ $cabinDetails->sleeps }}</h5>
                                        @endif

                                        <h5>
                                            <strong>Payment options at the cabin: </strong>
                                            @foreach($service->paymentType() as $paymentTypeKey => $paymentType)
                                                @if(in_array($paymentTypeKey, $cabinDetails->payment_type ))
                                                    <span class="label label-default">{{ $paymentType }}</span>
                                                @endif
                                            @endforeach
                                        </h5>

                                        <h5><strong>Seasontimes: </strong><button class="btn btn-sm toggleSeasonTime" type="button">Click here to see open and closing time of summer and winter seasons</button></h5>
                                        <div class="seasonTimes" style="display: none;">
                                            <?php
                                            $firstYear = (int)date('Y');
                                            $lastYear  = (int)date('Y', strtotime('+2 year'));
                                            for($i = $firstYear; $i <= $lastYear; $i++)
                                            {
                                            ?>
                                            @if($service->seasons($cabinDetails->_id))
                                                @foreach ($service->seasons($cabinDetails->_id) as $season)
                                                    @if($season->summerSeasonYear === $i || $season->winterSeasonYear === $i)
                                                        <h5><span class="badge">{{ $i }}</span></h5>
                                                    @endif

                                                    @if($season->summerSeason === 1 && $season->summerSeasonStatus === 'open' && $season->summerSeasonYear === $i)
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <h5><strong>Summer open: </strong><small>{{ $season->earliest_summer_open->format('d.m.y') }}</small></h5>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <h5><strong>Summer close: </strong><small>{{ $season->latest_summer_close->format('d.m.y') }}</small></h5>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($season->winterSeason === 1 && $season->winterSeasonStatus === 'open' && $season->winterSeasonYear === $i)
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <h5><strong>Winter open: </strong><small>{{ $season->earliest_winter_open->format('d.m.y') }}</small></h5>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <h5><strong>Winter close: </strong><small>{{ $season->latest_winter_close->format('d.m.y') }}</small></h5>
                                                            </div>
                                                        </div>
                                                    @endif

                                                @endforeach
                                            @endif
                                            <?php
                                            }
                                            ?>
                                        </div>

                                        <h5><strong>Website: </strong>{{ $cabinDetails->website }}</h5>

                                        <hr>

                                        <h4>Price List</h4>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>
                                        <h5><strong>Test:</strong> Lorem ipsum dolor sit amet</h5>

                                        <hr>

                                        <h4>Reserve / Cancel</h4>

                                        <h5><strong>Deposit: </strong> {{ $cabinDetails->prepayment_amount }} &euro; / Person & Tag</h5>
                                        <h5><strong>Cancel booking: </strong>
                                            @foreach($service->reservationCancel() as $key => $type)
                                                @if($key == $cabinDetails->reservation_cancel)
                                                    {{ $type }}
                                                @endif
                                            @endforeach
                                        </h5>
                                        <h5><strong>Check In Time: </strong>{{ $cabinDetails->checkin_from }}</h5>

                                        <h5><strong>Check Out Time: </strong>{{ $cabinDetails->reservation_to }}</h5>

                                        <hr>

                                        <h4>Tour</h4>
                                        @if($cabinDetails->tours)
                                            <h5><strong>Tour: </strong>{{ $cabinDetails->tours }}</h5>
                                        @endif

                                        @if($cabinDetails->reachable)
                                            <h5><strong>Reachable from: </strong>{{ $cabinDetails->reachable }}</h5>
                                        @endif

                                        @if($cabinDetails->neighbour_cabin)
                                            <h5>
                                                <strong>Neighbour Cabins: </strong>
                                                @foreach($service->cabins() as $neighbour)
                                                    @if(in_array($neighbour->_id, $cabinDetails->neighbour_cabin))
                                                        <span class="label label-default">{{ $neighbour->name }}</span>
                                                    @endif
                                                @endforeach
                                            </h5>
                                        @endif

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
