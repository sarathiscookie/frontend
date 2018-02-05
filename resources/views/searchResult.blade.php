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

@section('content')
    <div class="jumbotron" style="height: 500px;">
        <div class="container text-center">
            <h1>Some title</h1>
            <p>Some text that represents the website.</p>
        </div>
    </div>

    <div class="container-fluid bg-3 text-center">
        <div class="row content">
            <div class="col-sm-2"></div>
            <div class="col-sm-8">
                <nav class="navbar navbar-default">
                    <div class="container-fluid">
                        <!-- Brand and toggle get grouped for better mobile display -->
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>

                        <!-- Collect the nav links, forms, and other content for toggling -->
                        @include('includes.search')

                    </div><!-- /.container-fluid -->
                </nav>
            </div>
            <div class="col-sm-2"></div>
        </div>
    </div><br><br>

    @isset($cabinSearchResult)
        <div class="container-fluid text-center">
            @foreach($cabinSearchResult as $result)
                <div class="panel panel-default text-left">
                    <div class="panel-body">
                        <div class="row content">
                            <div class="col-sm-2">
                                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                            </div>

                            <div class="col-sm-7 text-left">
                                <h3>{{ $result->name }} - {{ $result->region }} - {{ $result->country }} ({{ number_format($result->height, 0, '', '.') }} m)</h3>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                <button type="button" class="btn btn-default btn-sm">More Details</button>

                                @foreach($result->interior as $interior)
                                    <button type="button" class="btn btn-default btn-sm btn-space pull-right">
                                        <span @if($interior === 'Food à la carte') class="glyphicon glyphicon-credit-card" @elseif($interior === 'breakfast') class="glyphicon glyphicon-glass" @else class="glyphicon glyphicon-home" @endif aria-hidden="true"></span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="col-sm-3">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h5 class="text-capitalize">Expected opening timings:</h5>
                                                @inject('cabinServices', 'App\Http\Controllers\SearchController')
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
                                                                    <h5><b>Summer open: </b><small>{{ $season->earliest_summer_open->format('d.m.y') }}</small></h5>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <h5><b>Summer close: </b><small>{{ $season->latest_summer_close->format('d.m.y') }}</small></h5>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if($season->winterSeason === 1 && $season->winterSeasonStatus === 'open' && $season->winterSeasonYear === $i)
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <h5><b>Winter open: </b><small>{{ $season->earliest_winter_open->format('d.m.y') }}</small></h5>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <h5><b>Winter close: </b><small>{{ $season->latest_winter_close->format('d.m.y') }}</small></h5>
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
                                                <div class="form-group row calendar" data-id="{{ $result->_id }}">

                                                    <div class="col-sm-4">
                                                        <input type="text" class="form-control dateFrom" id="dateFrom_{{ $result->_id }}" name="dateFrom" placeholder="Arrival" readonly>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <input type="text" class="form-control dateTo" id="dateTo_{{ $result->_id }}" name="dateTo" placeholder="Departure" readonly>
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
                                                <h4><span class="label label-success pull-left">Sleeping place: Available</span></h4>
                                            </div>
                                            <div class="col-xs-6 col-sm-6">
                                                <button type="button" class="btn btn-default btn-sm btn-space pull-right"><span class="glyphicon glyphicon-floppy-save" aria-hidden="true"></span> Booking</button>
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
    @endisset

@endsection

@push('scripts')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(".dateFrom").datepicker({
                showAnim: "drop",
                dateFormat: "dd.mm.y",
                monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
                monthNamesShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
                dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"]

            });

            $("body").on('mousedown', ".dateFrom", function() {
                var dataId = $(this).parent().parent().data("id");
                var $this = $(this);

                $.ajax({
                    url: '/calendar',
                    dataType: 'JSON',
                    type: 'POST',
                    data: { dataId: dataId },
                    success: function(response) {
                        var holidayDates = response.holidayDates;
                        var greenDates   = response.greenDates;
                        var orangeDates  = response.orangeDates;
                        var redDates     = response.redDates;
                        var start_date   = '';

                        $this.datepicker("option", "onSelect", function(date) {
                            /*var dt2       = $('#dateTo_'+dataId);
                            var startDate = $this.datepicker('getDate');
                            var minDate   = $this.datepicker('getDate');
                            $this.datepicker('option', 'minDate', minDate);
                            dt2.datepicker('setDate', minDate);
                            startDate.setDate(startDate.getDate() + 60); //sets dt2 maxDate to the last day of 60 days window
                            minDate.setDate(minDate.getDate() + 1); //sets dt2 minDate to the +1 day of from date
                            dt2.datepicker('option', 'maxDate', startDate);
                            dt2.datepicker('option', 'minDate', minDate);*/
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

                                    $this.datepicker("refresh");
                                }
                            });
                        });

                        $this.datepicker("option", "beforeShowDay", function(date) {
                            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                            return [true, (holidayDates.indexOf(string) >=0) ? "holidayDates" : ( (greenDates.indexOf(string) >=0) ? "greenDates": ( (orangeDates.indexOf(string) >=0) ? "orangeDates": ( (redDates.indexOf(string) >=0) ? "redDates": '' ) ) )];

                        });

                        $this.datepicker("show");
                    }
                });

            });



            $(".dateTo").datepicker({
                showAnim: "drop",
                dateFormat: "dd.mm.y",
                monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
                monthNamesShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
                dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"]

            });

            $("body").on('mousedown', ".dateTo", function() {
                var dataId = $(this).parent().parent().data("id");
                var $this = $(this);

                $.ajax({
                    url: '/calendar',
                    dataType: 'JSON',
                    type: 'POST',
                    data: { dataId: dataId },
                    success: function(response) {
                        var holidayDates = response.holidayDates;
                        var greenDates   = response.greenDates;
                        var orangeDates  = response.orangeDates;
                        var redDates     = response.redDates;
                        var start_date   = '';

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

                                    $this.datepicker("refresh");
                                }
                            });
                        });

                        $this.datepicker("option", "beforeShowDay", function(date) {
                            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                            return [true, (holidayDates.indexOf(string) >=0) ? "holidayDates" : ( (greenDates.indexOf(string) >=0) ? "greenDates": ( (orangeDates.indexOf(string) >=0) ? "orangeDates": ( (redDates.indexOf(string) >=0) ? "redDates": '' ) ) )];

                        });

                        $this.datepicker("show");
                    }
                });

            });

        });

    </script>
@endpush
