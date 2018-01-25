@extends('layouts.app')

@section('title', 'Cabin List')

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

    <div class="container-fluid text-center">
        @isset($cabinSearchResult)
            @foreach($cabinSearchResult as $result)
                <div class="panel panel-default text-left">
                    <div class="panel-body">
                        <div class="row content">
                            <div class="col-sm-2">
                                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive img-thumbnail" style="width:100%" alt="Image">
                            </div>
                            <div class="col-sm-7 text-left">
                                <h3>{{ $result->name }} - {{$result->region}} - {{$result->country}} ({{$result->height}})</h3>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                <button type="button" class="btn btn-default btn-sm">More Details</button>
                                <button type="button" class="btn btn-default btn-sm btn-space pull-right">
                                    <span class="glyphicon glyphicon-home" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-sm btn-space pull-right">
                                    <span class="glyphicon glyphicon-print" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-sm btn-space pull-right">
                                    <span class="glyphicon glyphicon-music" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-sm btn-space pull-right">
                                    <span class="glyphicon glyphicon-glass" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-sm btn-space pull-right">
                                    <span class="glyphicon glyphicon-camera" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-sm btn-space pull-right">
                                    <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-default btn-sm btn-space pull-right">
                                    <span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span>
                                </button>
                            </div>

                            <div class="col-sm-3">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h5>Expected opening timings</h5>

                                                <span class="badge">J</span> <span class="badge">F</span> <span class="badge">M</span>
                                                <span class="badge">A</span> <span class="badge">M</span> <span class="badge">J</span>
                                                <span class="badge">J</span> <span class="badge">A</span> <span class="badge">S</span>
                                                <span class="badge">O</span> <span class="badge">N</span> <span class="badge">D</span>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h5>Your journey begins here <span class="glyphicon glyphicon-question-sign"></span></h5>
                                                <div class="form-group row">
                                                    <div class="col-sm-4">
                                                        <input class="form-control" id="ex1" type="text" placeholder="Arrival">
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <input class="form-control" id="ex2" type="text" placeholder="Departure">
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
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h4><span class="label label-success pull-left">Sleeping place: Available</span></h4>
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
        @endisset
    </div>

    {!! $cabinSearchResult->links() !!}

@endsection