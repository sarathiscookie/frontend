@extends('layouts.app')

@section('title', 'Search results')

@inject('cabinServices', 'App\Http\Controllers\SearchController')

@inject('calendarServices', 'App\Http\Controllers\CalendarController')

@inject('service', 'App\Http\Controllers\CabinDetailsController')

@section('content')

    @include('includes.search')

    @isset($cabinSearchResult)
        <main>
            <div class="container-fluid container-fluid-cabinlist text-center">
                @foreach($cabinSearchResult as $result)
                    <div class="panel panel-default text-left">
                        <div class="panel-body">
                            <div class="row content row-cabinlist">
                                <div class="col-sm-2">
                                    <img src="{{ asset('storage/img/huette_cabin_list.jpg') }}" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                                </div>

                                <div class="col-sm-7 text-left">
                                    <h2 class="headliner-cabinname">{{ $result->name }}&nbsp;</h2><h3 class="headliner-cabin">{{ $result->region }} - {{ $result->country }} ({{ number_format($result->height, 0, '', '.') }} m)</h3>
                                    <div class="cabinListMore">
                                        {{ strip_tags(str_replace("&nbsp;", " ", $result->other_details)) }}
                                    </div>

                                    <a href="{{ route('cabin.details', ['id' => base64_encode($result->_id.env('MD5_Key'))]) }}" class="btn btn-default btn-sm btn-details">{{ __('searchDetails.moreDetails') }}</a>

                                    <div class="row" style="float:none; text-align:right;">
                                        <div class="col-sm-12">
                                            @if($result->interior)
                                                @foreach($result->interior as $interior)
                                                    <button type="button" class="btn btn-default btn-sm btn-space facility-btn" data-toggle="tooltip" data-placement="bottom" title="{{ $service->interiorLabel($interior) }}">
                                                        <span @if($interior === 'Food à la carte') class="glyphicon glyphicon-credit-card" @elseif($interior === 'breakfast') class="glyphicon glyphicon-glass" @else class="glyphicon glyphicon-home" @endif aria-hidden="true"></span>
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="panel panel-default booking-box">
                                        <div class="panel-body">
                                            <div class="row row-cabinlist">
                                                <div class="col-sm-12 month-opening">
                                                    <h5 class="text-capitalize">{{ __('searchDetails.openingTimeHeading') }}:</h5>

                                                    @if($cabinServices->seasons($result->_id))
                                                        @foreach ($cabinServices->seasons($result->_id) as $season)
                                                            @if($season->summerSeasonYear === (int)date('Y') || $season->winterSeasonYear === (int)date('Y'))
                                                                <h5><span class="badge">{{ (int)date('Y') }}</span></h5>
                                                            @endif

                                                            @if($season->summerSeason === 1 && $season->summerSeasonStatus === 'open' && $season->summerSeasonYear === (int)date('Y'))
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <h6><b>{{ __('searchDetails.summerOpenTime') }}: </b><small>{{ $season->earliest_summer_open->format('d.m.y') }}</small></h6>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <h6><b>{{ __('searchDetails.summerCloseTime') }}: </b><small>{{ $season->latest_summer_close->format('d.m.y') }}</small></h6>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if($season->winterSeason === 1 && $season->winterSeasonStatus === 'open' && $season->winterSeasonYear === (int)date('Y'))
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <h6><b>{{ __('searchDetails.winterOpen') }}: </b><small>{{ $season->earliest_winter_open->format('d.m.y') }}</small></h6>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <h6><b>{{ __('searchDetails.winterClose') }}: </b><small>{{ $season->latest_winter_close->format('d.m.y') }}</small></h6>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                        @endforeach
                                                    @endif

                                                    <hr>
                                                </div>
                                            </div>

                                            <div class="row row-cabinlist">
                                                <div class="col-sm-12">
                                                    <h5>{{ __('searchDetails.journeyBeginsHeading') }} <span class="glyphicon glyphicon-question-sign" title="{{ __('searchDetails.journeyBeginsTitle') }}"></span></h5>

                                                    <div class="form-group row row-cabinlist calendar" data-id="{{ $result->_id }}">

                                                        <div class="col-sm-12" id="errors_{{ $result->_id }}" style="display: none;"></div>
                                                        <div class="col-sm-12" id="warning_{{ $result->_id }}" style="display: none;"></div>

                                                        @php
                                                            $calendar = $calendarServices->calendar($result->_id);
                                                        @endphp

                                                        <div class="holiday_{{ $result->_id }}" data-holiday="{{ $calendar[0] }}"></div>
                                                        <div class="green_{{ $result->_id }}" data-green="{{ $calendar[1] }}"></div>
                                                        <div class="orange_{{ $result->_id }}" data-orange="{{ $calendar[2] }}"></div>
                                                        <div class="red_{{ $result->_id }}" data-red="{{ $calendar[3] }}"></div>
                                                        <div class="notSeasonTime_{{ $result->_id }}" data-notseasontime="{{ $calendar[4] }}"></div>

                                                        <div class="col-sm-6-cabinlist col-sm-6">
                                                            <input type="text" class="form-control form-control-cabinlist backButtonDateFrom dateFrom" id="dateFrom_{{ $result->_id }}" name="dateFrom_{{ $result->_id }}" placeholder="{{ __('searchDetails.arrivalPlaceholder') }}" value="" readonly autocomplete="off">
                                                        </div>

                                                        <div class="col-sm-6-cabinlist col-sm-6">
                                                            <input type="text" class="form-control form-control-cabinlist backButtonDateTo dateTo" id="dateTo_{{ $result->_id }}" name="dateTo_{{ $result->_id }}" placeholder="{{ __('searchDetails.departurePlaceholder') }}" value="" readonly autocomplete="off">
                                                        </div>

                                                        @if($result->sleeping_place != 1)
                                                            <div class="col-sm-6-cabinlist col-sm-6 dropdown-top-buffer">
                                                                <select class="form-control form-control-cabinlist" id="beds_{{ $result->_id }}" name="beds_{{ $result->_id }}">
                                                                    <option value="">{{ __('searchDetails.chooseBeds') }}</option>
                                                                    @for($i = 1; $i <= 30; $i++)
                                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>

                                                            <div class="col-sm-6-cabinlist col-sm-6 dropdown-top-buffer">
                                                                <select class="form-control form-control-cabinlist" id="dorms_{{ $result->_id }}" name="dorms_{{ $result->_id }}">
                                                                    <option value="">{{ __('searchDetails.chooseDorms') }}</option>
                                                                    @for($i = 1; $i <= 30; $i++)
                                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                        @else
                                                            <div class="col-sm-6-cabinlist col-sm-6 dropdown-top-buffer">
                                                                <select class="form-control form-control-cabinlist" id="sleeps_{{ $result->_id }}" name="sleeps_{{ $result->_id }}">
                                                                    <option value="">{{ __('searchDetails.chooseSleeps') }}</option>
                                                                    @for($i = 1; $i <= 30; $i++)
                                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                        @endif
                                                        <input type="hidden" id="sleeping_place_{{ $result->_id }}" value="{{ $result->sleeping_place }}">
                                                    </div>

                                                    <hr>
                                                </div>
                                            </div>

                                            <div class="row row-cabinlist" data-cab="{{ $result->_id }}">
                                                <div class="col-sm-12 button-3-bottom">
                                                    <h4>{!! $cabinServices->bookingPossibleNextDays($result->_id) !!}</h4>
                                                    <!-- Authentication Links -->
                                                    @guest
                                                        <a href="{{ route('login') }}" class="btn btn-default btn-sm btn-space pull-right btn-booking">{{ __('searchDetails.addToCartButton') }}</a>
                                                        @else
                                                            <button type="button" class="btn btn-default btn-sm btn-space pull-right btn-booking addToCart" name="addToCart" value="addToCart">{{ __('searchDetails.addToCartButton') }}</button>
                                                    @endguest
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {!! $cabinSearchResult->links() !!}

        </main>

    @endisset

@endsection
