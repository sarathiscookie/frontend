@extends('layouts.app')

@section('title', 'Welcome')

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
                        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                            <form class="navbar-form navbar-left">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Cabin">
                                </div>
                            </form>

                            <ul class="nav navbar-nav">

                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Country <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                                    </ul>
                                </li>

                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Region <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                                    </ul>
                                </li>

                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Cabins to sleep <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                                    </ul>
                                </li>

                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Managed Facility <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                                    </ul>
                                </li>

                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Particularities <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                                        <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                                    </ul>
                                </li>

                            </ul>

                            <form class="navbar-form navbar-right">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-default">Search</button>
                                </div>
                            </form>

                        </div><!-- /.navbar-collapse -->
                    </div><!-- /.container-fluid -->
                </nav>
            </div>
            <div class="col-sm-2"></div>
        </div>
    </div><br><br>

    <div class="container-fluid bg-3 text-center">
        <div class="row content">
            <div class="col-sm-8">
                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive" style="width:100%" alt="Image"> <br>

                <div class="row">
                    <div class="col-sm-6">
                        <a href="/cabins">
                            <img src="https://placehold.it/150x80?text=Hüttensuche" class="img-responsive" style="width:100%" alt="Image">
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive" style="width:100%" alt="Image">
                    </div>
                </div>

            </div>

            <div class="col-sm-4">
                <img src="https://placehold.it/150x80?text=IMAGE" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </div>
    </div><br><br>

@endsection
