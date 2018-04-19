@extends('layouts.app')

@section('title', 'Cabin Details')

@section('content')

    @inject('service', 'App\Http\Controllers\CabinDetailsController')

    @inject('cabinServices', 'App\Http\Controllers\SearchController')

    @inject('calendarServices', 'App\Http\Controllers\CalendarController')

    @isset($cabinDetails)
        <div class="container-fluid container-fluid-cabin-details bg-3 text-center">
            <div class="col-md-2 col-md-2-cabin-details"></div>
            <div class="col-md-8 col-md-8-cabin-details" id="list-filter-cabin-details">
                <nav class="navbar navbar-default navbar-default-cabin-details">
                    <h2 id="cabin-head-cabin-details">{{ $cabinDetails->name }} -<br id="cabin-head-br-cabin-details" /> {{ $cabinDetails->region }} - {{ $cabinDetails->country }} ({{ number_format($cabinDetails->height, 0, '', '.') }} m)</h2>
                </nav>
            </div>
            <div class="col-md-2 col-md-2-cabin-details"></div>
        </div>

        <main>
            <div class="container-fluid container-fluid-cabin-details text-center">
                <div class="panel panel-default text-left panel-cabin-details panel-default-cabin-details">
                    <div class="panel-body panel-body-cabin-details">
                        <div class="row content row-cabin-details">
                            <div class="col-sm-3 col-sm-3-cabin-details">
                                <div class="panel panel-default booking-box-cabin-details panel-cabin-details panel-default-cabin-details">
                                    <div class="panel-body panel-body-cabin-details">
                                        <div class="row row-cabin-details">
                                            <div class="col-sm-12 col-sm-12-cabin-details month-opening">
                                                <h5>Expected opening timings <span class="glyphicon glyphicon-question-sign" title="Here you can see if the cabin is open (green) or closed (white) for the respective month."></span></h5>
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
                                        <div class="row row-cabin-details">
                                            <div class="col-sm-12 col-sm-12-cabin-details">
                                                <h5>Your journey begins here <span class="glyphicon glyphicon-question-sign" title="Please choose your arrival/depature date and the number of persons with whom you would like to visit the cabin. Then enter Booking to get to the next step."></span></h5>
                                                <div class="form-group row row-cabin-details calendar" data-id="{{ $cabinDetails->_id }}">

                                                    <div class="col-sm-12" id="errors_{{ $cabinDetails->_id }}"></div>
                                                    <div class="col-sm-12" id="warning_{{ $cabinDetails->_id }}"></div>

                                                    @php
                                                        $calendar = $calendarServices->calendar($cabinDetails->_id)
                                                    @endphp

                                                    <div class="holiday_{{ $cabinDetails->_id }}" data-holiday="{{ $calendar[0] }}"></div>
                                                    <div class="green_{{ $cabinDetails->_id }}" data-green="{{ $calendar[1] }}"></div>
                                                    <div class="orange_{{ $cabinDetails->_id }}" data-orange="{{ $calendar[2] }}"></div>
                                                    <div class="red_{{ $cabinDetails->_id }}" data-red="{{ $calendar[3] }}"></div>
                                                    <div class="notSeasonTime_{{ $cabinDetails->_id }}" data-notseasontime="{{ $calendar[4] }}"></div>

                                                    <div class="col-sm-6 col-sm-6-cabin-details">
                                                        <input type="text" class="form-control form-control-cabin-details dateFrom" id="dateFrom_{{ $cabinDetails->_id }}" name="dateFrom_{{ $cabinDetails->_id }}" placeholder="Arrival" readonly>
                                                    </div>
                                                    <div class="col-sm-6 col-sm-6-cabin-details">
                                                        <input type="text" class="form-control form-control-cabin-details dateTo" id="dateTo_{{ $cabinDetails->_id }}" name="dateTo_{{ $cabinDetails->_id }}" placeholder="Departure" readonly>
                                                    </div>

                                                    @if($cabinDetails->sleeping_place != 1)
                                                        <div class="col-sm-6 col-sm-6-cabin-details dropdown-cabin-details-top-buffer">
                                                            <select class="form-control form-control-cabin-details" size="3" id="beds_{{ $cabinDetails->_id }}" name="beds_{{ $cabinDetails->_id }}">
                                                                <option value="">Choose Bed(s)</option>
                                                                @for($i = 1; $i <= 30; $i++)
                                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                                @endfor
                                                            </select>
                                                        </div>

                                                        <div class="col-sm-6 col-sm-6-cabin-details dropdown-cabin-details-top-buffer">
                                                            <select class="form-control form-control-cabin-details" size="3" id="dorms_{{ $cabinDetails->_id }}" name="dorms_{{ $cabinDetails->_id }}">
                                                                <option value="">Choose Dorm(s)</option>
                                                                @for($i = 1; $i <= 30; $i++)
                                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                    @else
                                                        <div class="col-sm-6 col-sm-6-cabin-details dropdown-cabin-details-top-buffer">
                                                            <select class="form-control form-control-cabin-details" size="3" id="sleeps_{{ $cabinDetails->_id }}" name="sleeps_{{ $cabinDetails->_id }}">
                                                                <option value="">Choose Sleep(s)</option>
                                                                @for($i = 1; $i <= 30; $i++)
                                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                    @endif

                                                    <input type="hidden" id="sleeping_place_{{ $cabinDetails->_id }}" value="{{ $cabinDetails->sleeping_place }}">
                                                </div>
                                                <hr>
                                            </div>
                                        </div>
                                        <div class="row row-cabin-details" data-cab="{{ $cabinDetails->_id }}">
                                            <div class="col-sm-12 -cabin-details col-sm-12-cabin-details">
                                                <h4>{!! $cabinServices->bookingPossibleNextDays($cabinDetails->_id) !!}</h4>
                                                <!-- Authentication Links -->
                                                @guest
                                                    <a href="{{ route('login') }}" class="btn btn-default btn-sm btn-space pull-right btn-booking">Add To Cart</a>
                                                    @else
                                                        <button type="button" class="btn btn-default btn-sm btn-space pull-right btn-booking addToCart" name="addToCart" value="addToCart">Add To Cart</button>
                                                @endguest
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3 text-left main-cabin-details">
                                <h2 class="details-headline-cabin-details">Info</h2>
                                <div class="details-info-cabin-details more">{{ strip_tags(str_replace("&nbsp;", " ", $cabinDetails->other_details)) }}</div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        @if($cabinDetails->interior)
                                            @foreach($cabinDetails->interior as $interior)
                                                <button type="button" class="btn btn-default btn-sm btn-space pull-right facility-btn-cabin-details btn-default-cabin-details" data-toggle="tooltip" data-placement="bottom" title="{{ $service->interiorLabel($interior) }}">
                                                    <span @if($interior === 'Food à la carte') class="glyphicon glyphicon-credit-card" @elseif($interior === 'breakfast') class="glyphicon glyphicon-glass" @else class="glyphicon glyphicon-home" @endif aria-hidden="true"></span>
                                                </button>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div class="detail-points-cabin-details">
                                    @if($service->userDetails($cabinDetails->cabin_owner))
                                        <strong class="details-underheadline-cabin-details">Cabinowner: </strong><p class="inh-cabin-details">{{ $service->userDetails($cabinDetails->cabin_owner)->usrFirstname }} {{ $service->userDetails($cabinDetails->cabin_owner)->usrLastname }}</p></h5>
                                    @endif

                                    <strong class="details-underheadline-cabin-details">Club section: </strong><p class="inh-cabin-details">{{ $cabinDetails->club }}</p>

                                    @if( $cabinDetails->sleeping_place != 1 )
                                        <strong class="details-underheadline-cabin-details">Beds: </strong><p class="inh-cabin-details">{{ $cabinDetails->beds }}</p>
                                        <strong class="details-underheadline-cabin-details">Dorms: </strong><p class="inh-cabin-details">{{ $cabinDetails->dormitory }}</p>
                                    @else
                                        <strong class="details-underheadline-cabin-details">Sleeping places: </strong><p class="inh-cabin-details">{{ $cabinDetails->sleeps }}</p>
                                    @endif

                                    <strong class="details-underheadline-cabin-details">Seasontimes: </strong><button class="btn btn-sm toggleSeasonTime" type="button">Click here to see seasons</button> <br>
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
                                                            <strong class="details-underheadline-cabin-details">Summer Season: </strong><p class="inh-cabin-details"><small>{{ $season->latest_summer_open->format('d.m.y') }} - {{ $season->earliest_summer_close->format('d.m.y')}}</small>  (Earliest: <small>{{ $season->earliest_summer_open->format('d.m.y') }}</small> - Latest: <small>{{ $season->latest_summer_close->format('d.m.y') }}</small>)</p>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($season->winterSeason === 1 && $season->winterSeasonStatus === 'open' && $season->winterSeasonYear === $i)
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <strong class="details-underheadline-cabin-details">Winter Season: </strong><p class="inh-cabin-details"><small>{{ $season->latest_winter_open->format('d.m.y') }} - {{ $season->earliest_winter_close->format('d.m.y')}}</small>  (Earliest: <small>{{ $season->earliest_winter_open->format('d.m.y') }}</small> - Latest: <small>{{ $season->latest_winter_close->format('d.m.y') }}</small>)</p>
                                                        </div>
                                                    </div>
                                                @endif

                                            @endforeach
                                        @endif
                                        <?php
                                        }
                                        ?>
                                    </div>

                                    <strong class="details-underheadline-cabin-details">Website: </strong><p class="inh-cabin-details"><a href="{{ $cabinDetails->website }}" target="_blank">https://huetten-holiday.de</a></p>

                                    <strong class="details-underheadline-cabin-details">Payment: </strong>
                                    @foreach($service->paymentType() as $paymentTypeKey => $paymentType)
                                        @if(in_array($paymentTypeKey, $cabinDetails->payment_type ))
                                            <span class="label label-default">{{ $paymentType }}</span>
                                        @endif
                                    @endforeach
                                </div>

                                @if(!empty($cabinDetails->price_type) && !empty($cabinDetails->guest_type) && !empty($cabinDetails->price) && count($cabinDetails->price_type) > 0)
                                    <div class="detail-points-cabin-details">
                                        <h2 class="details-headline-cabin-details">Prise list</h2>
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
                                                        <td>{{ money_format('%i', $cabinDetails->price[$k]) }} &euro;</td>
                                                        @php $k++; @endphp
                                                    @endforeach
                                                </tr>
                                                @php $j++; @endphp
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <div class="detail-points-cabin-details">

                                    <h2 class="details-headline-cabin-details">Reservation / cancelation</h2>
                                    <strong class="details-underheadline-cabin-details">Deposit: </strong><p class="inh-cabin-details">{{ number_format($cabinDetails->prepayment_amount, 2, ',', '') }}&euro;</p>

                                    <strong class="details-underheadline-cabin-details">Cancelation deadline: </strong>
                                    <p class="inh-cabin-details">
                                        @foreach($service->reservationCancel() as $key => $type)
                                            @if($key == $cabinDetails->reservation_cancel)
                                                {{ $type }}
                                            @endif
                                        @endforeach
                                    </p>

                                    <strong class="details-underheadline-cabin-details">Check-in: </strong><p class="inh-cabin-details">{{ $cabinDetails->checkin_from }}</p>

                                    <strong class="details-underheadline-cabin-details">Check-out: </strong><p class="inh-cabin-details">{{ $cabinDetails->reservation_to }}</p>

                                    @if($cabinDetails->halfboard == '1' && $cabinDetails->halfboard_price != '')
                                        <div>
                                            <strong class="details-underheadline-cabin-details">Halfboard: </strong>
                                            <p class="label label-default">{{ $cabinDetails->halfboard_price }} &euro;</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="detail-points-cabin-details">
                                    <h2 class="details-headline-cabin-details">Tour</h2>
                                    @if($cabinDetails->tours)
                                        <strong class="details-underheadline-cabin-details">Hikes: </strong><p class="inh-cabin-details">{{ $cabinDetails->tours }}</p>
                                    @endif

                                    @if($cabinDetails->reachable)
                                        <strong class="details-underheadline-cabin-details">Regable from: </strong><p class="inh-cabin-details">{{ $cabinDetails->reachable }}</p>
                                    @endif

                                    @if($cabinDetails->neighbour_cabin)
                                        <strong class="details-underheadline-cabin-details">Neighbour Cabins: </strong>
                                        @foreach($cabinDetails->neighbour_cabin as $neighbour)
                                           <span class="label label-default">{{ $service->neighbourCabins($neighbour) }}</span>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="thumbnail" style="border:none; border-radius:0px; background-color: #EFEFEF;">
                                    <ul id="image-gallery" class="gallery list-unstyled cS-hidden">
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                        <li data-thumb="https://placehold.it/250x180">
                                            <img src="https://placehold.it/600x400" class="img-responsive">
                                        </li>
                                    </ul>
                                </div>

                            </div>
                            <div class="col-sm-6 col-sm-6-cabin-details" id="div-advertising-cabin-details">
                                <a href="#"><img src="{{ asset('storage/img/Werbung.jpg') }}" class="img-responsive" id="advertising" alt="advertising"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    @endisset

@endsection
