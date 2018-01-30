@extends('layouts.app')

@section('title', 'Search results')

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

            $( ".dateFrom" ).on('click', function(){

                var dataId     = $(this).parent().parent().data("id");

                $.ajax({
                    url: '/calendar',
                    dataType: 'JSON',
                    type: 'POST',
                    data: { dataId: dataId },
                    success : function(response) {
                        var array  = response.disableDates;
                        $('.dateFrom').datepicker({
                            showAnim: "drop",
                            dateFormat: "dd.mm.y",
                            monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
                            monthNamesShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
                            dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
                            beforeShowDay: function (date) {
                                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                                return [true, array.indexOf(string) == -1]
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
