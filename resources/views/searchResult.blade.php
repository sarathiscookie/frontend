@extends('layouts.app')

@section('title', 'Search results')

@section('styles')
    <style>
        .holidayDates .ui-state-default
        {
            /*color: #0000F0;*/
            background-color: #777;
        }

        .greenDates .ui-state-default
        {
            /*color: darkgreen;*/
            background-color: #5cb85c;
        }

        .orangeDates .ui-state-default
        {
            /*color: darkorange;*/
            background-color: #f0ad4e;
        }

        .redDates .ui-state-default
        {
            /*color: red;*/
            background-color: #d9534f;
        }
    </style>
@endsection

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

                                    <a href="{{ route('cabin.details', ['id' => base64_encode($result->_id.env('MD5_Key'))]) }}" class="btn btn-default btn-sm btn-details">More details and price</a>

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
                                                    <h5 class="text-capitalize">Expected opening timings:</h5>
                                                    <?php
                                                    $firstYear = (int)date('Y');
                                                    $lastYear  = (int)date('Y', strtotime('+2 year'));
                                                    for($i = $firstYear; $i <= $lastYear; $i++)
                                                    {
                                                    ?>
                                                    @if($cabinServices->seasons($result->_id))
                                                        @foreach ($cabinServices->seasons($result->_id) as $season)
                                                            @if($season->summerSeasonYear === $i || $season->winterSeasonYear === $i)
                                                                <h5><span class="badge">{{ $i }}</span></h5>
                                                            @endif

                                                            @if($season->summerSeason === 1 && $season->summerSeasonStatus === 'open' && $season->summerSeasonYear === $i)
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <h6><b>Summer open: </b><small>{{ $season->earliest_summer_open->format('d.m.y') }}</small></h6>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <h6><b>Summer close: </b><small>{{ $season->latest_summer_close->format('d.m.y') }}</small></h6>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if($season->winterSeason === 1 && $season->winterSeasonStatus === 'open' && $season->winterSeasonYear === $i)
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <h6><b>Winter open: </b><small>{{ $season->earliest_winter_open->format('d.m.y') }}</small></h6>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <h6><b>Winter close: </b><small>{{ $season->latest_winter_close->format('d.m.y') }}</small></h6>
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

                                            <div class="row row-cabinlist">
                                                <div class="col-sm-12">
                                                    <h5>Your journey begins here <span class="glyphicon glyphicon-question-sign" title="Please choose your arrival/depature date and the number of persons with whom you would like to visit the cabin. Then enter Booking to get to the next step."></span></h5>

                                                    <div class="form-group row row-cabinlist calendar" data-id="{{ $result->_id }}">

                                                        <div class="col-sm-12" id="errors_{{ $result->_id }}"></div>
                                                        <div class="col-sm-12" id="warning_{{ $result->_id }}"></div>

                                                        @php
                                                            $calendar = $calendarServices->calendar($result->_id);
                                                        @endphp

                                                        <div class="holiday_{{ $result->_id }}" data-holiday="{{ $calendar[0] }}"></div>
                                                        <div class="green_{{ $result->_id }}" data-green="{{ $calendar[1] }}"></div>
                                                        <div class="orange_{{ $result->_id }}" data-orange="{{ $calendar[2] }}"></div>
                                                        <div class="red_{{ $result->_id }}" data-red="{{ $calendar[3] }}"></div>
                                                        <div class="notSeasonTime_{{ $result->_id }}" data-notseasontime="{{ $calendar[4] }}"></div>

                                                        <div class="col-sm-4-cabinlist col-sm-4">
                                                            <input type="text" class="form-control form-control-cabinlist dateFrom" id="dateFrom_{{ $result->_id }}" name="dateFrom_{{ $result->_id }}" placeholder="Arrival" value="{{ old('dateFrom_'.$result->_id) }}" readonly>
                                                        </div>

                                                        <div class="col-sm-4-cabinlist col-sm-4">
                                                            <input type="text" class="form-control form-control-cabinlist dateTo" id="dateTo_{{ $result->_id }}" name="dateTo_{{ $result->_id }}" placeholder="Departure" value="{{ old('dateTo_'.$result->_id) }}" readonly>
                                                        </div>

                                                        <div class="col-sm-4-cabinlist col-sm-4">
                                                            <select class="form-control form-control-cabinlist" size="3" id="persons_{{ $result->_id }}" name="persons_{{ $result->_id }}">
                                                                <option value="0">Persons</option>
                                                                @for($i = 1; $i <= 30; $i++)
                                                                    <option value="@if(old('persons_'.$result->_id)) {{ old('persons_'.$result->_id) }} @else {{ $i }}">{{ $i }} @endif</option>
                                                                @endfor
                                                            </select>
                                                        </div>

                                                    </div>

                                                    <hr>
                                                </div>
                                            </div>

                                            <div class="row row-cabinlist" data-cab="{{ $result->_id }}">
                                                <div class="col-sm-12 button-3-bottom">
                                                    <h4>{!! $cabinServices->bookingPossibleNextDays($result->_id) !!}</h4>
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
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {!! $cabinSearchResult->links() !!}

        </main>

    @endisset

@endsection

@push('scripts')
    <script>
        $(function() {
            /* Calendar availability check begin */
            /*$("body").on("mousedown", ".dateFrom", function() {
                var dataId          = $(this).parent().parent().data("id");
                var $this           = $("#dateFrom_"+dataId);
                var returnResult    = [];

                var holidayDates    = $(".holiday_"+dataId).data("holiday");
                var greenDates      = $(".green_"+dataId).data("green");
                var orangeDates     = $(".orange_"+dataId).data("orange");
                var redDates        = $(".red_"+dataId).data("red");
                var not_season_time = $(".notSeasonTime_"+dataId).data("notseasontime");
                var start_date      = '';

                $this.datepicker({
                    showAnim: "drop",
                    dateFormat: "dd.mm.y",
                    changeMonth: true,
                    changeYear: true,
                    monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
                    monthNamesShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
                    dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
                    minDate: '+1d',
                    yearRange: "0:+2"
                });

                $this.datepicker("option", "onSelect", function(date) {
                    var dt2       = $("#dateTo_"+dataId);
                    var startDate = $this.datepicker('getDate');
                    var minDate   = $this.datepicker('getDate');
                    dt2.datepicker('setDate', minDate);
                    startDate.setDate(startDate.getDate() + 60); //sets dt2 maxDate to the last day of 60 days window
                    minDate.setDate(minDate.getDate() + 1); //sets dt2 minDate to the +1 day of from date
                    dt2.datepicker('option', 'maxDate', startDate);
                    dt2.datepicker('option', 'minDate', minDate);
                });

                $this.datepicker("option", "onChangeMonthYear", function(year,month,inst) {
                    if (year != undefined && month != undefined) {
                        start_date = year +'-';
                        start_date += month +'-';
                        start_date += '01';
                    }
                    $.ajax({
                        url: '/calendar/ajax',
                        dataType: 'JSON',
                        type: 'POST',
                        data: { dateFrom: start_date, dataId: dataId },
                        success: function (response) {
                            for (var i = 0; i < response.holidayDates.length; i++) {
                                holidayDates.push(response.holidayDates[i]);
                            }

                            for (var i = 0; i < response.greenDates.length; i++) {
                                greenDates.push(response.greenDates[i]);
                            }

                            for (var i = 0; i < response.orangeDates.length; i++) {
                                orangeDates.push(response.orangeDates[i]);
                            }

                            for (var i = 0; i < response.redDates.length; i++) {
                                redDates.push(response.redDates[i]);
                            }

                            for (var i = 0; i < response.not_season_time.length; i++) {
                                not_season_time.push(response.not_season_time[i]);
                            }

                            $this.datepicker("refresh");
                        }
                    });
                });

                $this.datepicker("option", "beforeShowDay", function(date) {
                    var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    if( greenDates.indexOf(string) >=0 ) {
                        returnResult = [true, "greenDates", "Available"];
                    }
                    if( orangeDates.indexOf(string) >=0 ) {
                        returnResult = [true, "orangeDates", "Few are available"];
                    }
                    if( redDates.indexOf(string) >=0 ) {
                        returnResult = [true, "redDates", "Not available"];
                    }
                    if( not_season_time.indexOf(string) >=0 ) {
                        returnResult = [false, "", "Not season time"];
                    }
                    if( holidayDates.indexOf(string) >=0 ) {
                        returnResult = [false, "", "Holiday"];
                    }
                    return returnResult;
                });

                $this.datepicker("show");
            });*/


            /*$("body").on("mousedown", ".dateTo", function() {
                var dataId          = $(this).parent().parent().data("id");
                var $this           = $("#dateTo_"+dataId);
                var returnResults   = [];

                var holidayDates    = $(".holiday_"+dataId).data("holiday");
                var greenDates      = $(".green_"+dataId).data("green");
                var orangeDates     = $(".orange_"+dataId).data("orange");
                var redDates        = $(".red_"+dataId).data("red");
                var not_season_time = $(".notSeasonTime_"+dataId).data("notseasontime");
                var start_date      = '';

                $this.datepicker({
                    showAnim: "drop",
                    dateFormat: "dd.mm.y",
                    changeMonth: true,
                    changeYear: true,
                    monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
                    monthNamesShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
                    dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
                    yearRange: "0:+2"
                });

                $this.datepicker("option", "onChangeMonthYear", function(year,month,inst) {
                    if (year != undefined && month != undefined) {
                        start_date = year +'-';
                        start_date += month +'-';
                        start_date += '01';
                    }
                    $.ajax({
                        url: '/calendar/ajax',
                        dataType: 'JSON',
                        type: 'POST',
                        data: { dateFrom: start_date, dataId: dataId },
                        success: function (response) {
                            for (var i = 0; i < response.holidayDates.length; i++) {
                                holidayDates.push(response.holidayDates[i]);
                            }

                            for (var i = 0; i < response.greenDates.length; i++) {
                                greenDates.push(response.greenDates[i]);
                            }

                            for (var i = 0; i < response.orangeDates.length; i++) {
                                orangeDates.push(response.orangeDates[i]);
                            }

                            for (var i = 0; i < response.redDates.length; i++) {
                                redDates.push(response.redDates[i]);
                            }

                            for (var i = 0; i < response.not_season_time.length; i++) {
                                not_season_time.push(response.not_season_time[i]);
                            }

                            $this.datepicker("refresh");
                        }
                    });
                });

                $this.datepicker("option", "beforeShowDay", function(date) {
                    var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    if( greenDates.indexOf(string) >=0 ) {
                        returnResults = [true, "greenDates", "Available"];
                    }
                    if( orangeDates.indexOf(string) >=0 ) {
                        returnResults = [true, "orangeDates", "Few are available"];
                    }
                    if( redDates.indexOf(string) >=0 ) {
                        returnResults = [true, "redDates", "Not available"];
                    }
                    if( not_season_time.indexOf(string) >=0 ) {
                        returnResults = [false, "", "Not season time"];
                    }
                    if( holidayDates.indexOf(string) >=0 ) {
                        returnResults = [false, "", "Holiday"];
                    }
                    return returnResults;
                });

                $this.datepicker("show");

            });*/
            /* Calendar availability check end */
        });

    </script>
@endpush
