@extends('layouts.app')

@section('title', 'Home')

@section('styles')
@endsection

@section('content')

    @include('includes.search')

    <main>
        <div class="container-fluid container-fluid-home bg-3 text-center">
            <div class="col-sm-4 col-sm-4-home" id="div-advertising-home">
                <a href="#"><img src="{{ asset('storage/img/werbung.jpg') }}" class="img-responsive" id="advertising-home" alt="advertising"></a>
            </div>
            <div class="row row-home">
                <div class="col-sm-8 col-sm-8-home">
                    <a href="#"><img src="{{ asset('storage/img/waltenbergerhaus.jpg') }}" class="img-responsive" alt="image"></a>
                    <div class="navbar-picture-home" id="block_nr_1-home"><strong>Favorite Cabins</strong></div>
                </div>
                <br>
                <div class="col-sm-4 col-sm-4-home col-2 col-2-home" id="list-col4-home">
                    <a href="#"><img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive" alt="image"></a>
                    <div class="navbar-picture-home"><strong>Cabin list</strong></div>
                </div>
                <div class="col-sm-4 col-sm-4-home col-2 col-2-home" id="region-col4-home">
                    <a href="#"><img src="{{ asset('storage/img/maps.jpg') }}" class="img-responsive" alt="map"></a>
                    <div class="navbar-picture-home"><strong>Search a Region</strong></div>
                </div>
            </div>
        </div>
        <div class="row row-home">
            <div class="col-extra-home">
                <h2>What means Huetten-Holiday.de - Who is Huetten-Holiday.de for?</h2>
                <p>
                    Huetten-Holiday.de is a reservation and management system for mountain cabins.
                    Our service is for the cabins hosts, but of course also for the hikers, mountaineers
                    as well as mountain schools. We've been working on a reservation system for quite some time
                    to develop that benefits everyone involved.
                </p>
                <a href="#">Read more here ...</a>
            </div>
        </div><br /><br />
    </main>
    <div>
        <div id="mountain"><img src="{{ asset('storage/img/bergsilhouette-grau.png') }}" class="img-responsive" alt="mountain background"></div>
        <div id="over-footer"></div>
    </div>

@endsection

@push('scripts')
@endpush