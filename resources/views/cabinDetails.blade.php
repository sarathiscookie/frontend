@extends('layouts.app')

@section('title', 'Cabin Details')

@section('content')

@inject('service', 'App\Http\Controllers\CabinDetailsController')

@inject('calendarServices', 'App\Http\Controllers\CalendarController')

@inject('nextDayAvailability', 'App\Http\Controllers\SearchController')

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
                                        <ul id="image-gallery" class="gallery list-unstyled cS-hidden">
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                            <li data-thumb="http://placehold.it/250x180">
                                                <img src="http://placehold.it/600x400" style="width:100%;" class="img-responsive">
                                            </li>
                                        </ul>
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
                                                            <div class="col-md-12">
                                                                <h5><b>Summer Season: </b><small>{{ $season->latest_summer_open->format('d.m.y') }} - {{ $season->earliest_summer_close->format('d.m.y')}}</small>  (Earliest: <small>{{ $season->earliest_summer_open->format('d.m.y') }}</small> - Latest: <small>{{ $season->latest_summer_close->format('d.m.y') }}</small>)</h5>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($season->winterSeason === 1 && $season->winterSeasonStatus === 'open' && $season->winterSeasonYear === $i)
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <h5><b>Winter Season: </b><small>{{ $season->latest_winter_open->format('d.m.y') }} - {{ $season->earliest_winter_close->format('d.m.y')}}</small>  (Earliest: <small>{{ $season->earliest_winter_open->format('d.m.y') }}</small> - Latest: <small>{{ $season->latest_winter_close->format('d.m.y') }}</small>)</h5>
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

                                        @if(!empty($cabinDetails->price_type) && !empty($cabinDetails->guest_type) && !empty($cabinDetails->price) && count($cabinDetails->price_type) > 0)
                                            <h4>Price List</h4>
                                            <table class="table table-bordered table-striped table-hover table-responsive">
                                                <thead>
                                                <tr>
                                                    <th></th>
                                                    @foreach ($cabinDetails->price_type as $each_type)
                                                        <th>{{$each_type}}</th>
                                                    @endforeach
                                                </tr>
                                                </thead>

                                                @php
                                                    $j = 1;
                                                    $k = 0;
                                                @endphp

                                                <tbody>
                                                @foreach ($cabinDetails->guest_type as $guest)
                                                    <tr>
                                                        <td style="font-weight: bold;">{{$guest}}</td>
                                                        @foreach ($cabinDetails->price_type as $each_type)
                                                            <td>{{$cabinDetails->price[$k]}}</td>
                                                            @php $k++; @endphp
                                                        @endforeach
                                                    </tr>
                                                    @php $j++; @endphp
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @endif

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
                                                @foreach($service->neighbourCabins() as $neighbour)
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
                                                            <div class="col-md-6">
                                                                <h5><strong>Summer open: </strong><small>{{ $season->earliest_summer_open->format('d.m.y') }}</small></h5>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h5><strong>Summer close: </strong><small>{{ $season->latest_summer_close->format('d.m.y') }}</small></h5>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($season->winterSeason === 1 && $season->winterSeasonStatus === 'open' && $season->winterSeasonYear === $i)
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h5><strong>Winter open: </strong><small>{{ $season->earliest_winter_open->format('d.m.y') }}</small></h5>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h5><strong>Winter close: </strong><small>{{ $season->latest_winter_close->format('d.m.y') }}</small></h5>
                                                            </div>
                                                        </div>
                                                    @endif

                                                @endforeach
                                            @endif
                                            <?php
                                            }
                                            ?>

                                            <hr>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h5>Your journey begins here <span class="glyphicon glyphicon-question-sign"></span></h5>
                                            <div class="form-group row calendar" data-id="{{ $cabinDetails->_id }}">

                                                @php
                                                    $calendar = $calendarServices->calendar($cabinDetails->_id)
                                                @endphp

                                                <div class="holiday_{{ $cabinDetails->_id }}" data-holiday="{{ $calendar[0] }}"></div>
                                                <div class="green_{{ $cabinDetails->_id }}" data-green="{{ $calendar[1] }}"></div>
                                                <div class="orange_{{ $cabinDetails->_id }}" data-orange="{{ $calendar[2] }}"></div>
                                                <div class="red_{{ $cabinDetails->_id }}" data-red="{{ $calendar[3] }}"></div>
                                                <div class="notSeasonTime_{{ $cabinDetails->_id }}" data-notseasontime="{{ $calendar[4] }}"></div>

                                                <div class="col-sm-4">
                                                    <input type="text" class="form-control dateFrom" id="dateFrom_{{ $cabinDetails->_id }}" name="dateFrom" placeholder="Arrival" readonly>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="text" class="form-control dateTo" id="dateTo_{{ $cabinDetails->_id }}" name="dateTo" placeholder="Departure" readonly>
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
                                            <h4>{!! $nextDayAvailability->bookingPossibleNextDays($cabinDetails->_id) !!}</h4>
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
