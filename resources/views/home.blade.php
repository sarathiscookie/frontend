@extends('layouts.app')

@section('title', 'Home')

@section('content')

    @include('includes.search')

    <main>
        <div class="container-fluid container-fluid-home bg-3 text-center">
            <div class="col-sm-4 col-sm-4-home" id="div-advertising-home">
                <a href="https://www.bockstark.de/" target="_blank"><img src="{{ asset('storage/img/Werbung.jpg') }}" class="img-responsive" id="advertising-home" alt="advertising"></a>
            </div>
            <div class="row row-home">
                <div class="col-sm-8 col-sm-8-home">
                    <a href="#"><img src="{{ asset('storage/img/Waltenbergerhaus.jpg') }}" class="img-responsive" alt="image"></a>
                    <div class="navbar-picture-home" id="block_nr_1-home"><strong>{{ __('home.imageLabelFavoriteCabin') }}</strong></div>
                </div>
                <br>
                <div class="col-sm-4 col-sm-4-home col-2 col-2-home" id="list-col4-home">
                    <a href="/search"><img src="{{ asset('storage/img/huette.jpg') }}" class="img-responsive" alt="image"></a>
                    <div class="navbar-picture-home"><strong>{{ __('home.imageLabelCabinList') }}</strong></div>
                </div>
                <div class="col-sm-4 col-sm-4-home col-2 col-2-home" id="region-col4-home">
                    <a href="#"><img src="{{ asset('storage/img/maps.jpg') }}" class="img-responsive" alt="map"></a>
                    <div class="navbar-picture-home"><strong>{{ __('home.imageLabelSearchRegion') }}</strong></div>
                </div>
            </div>
        </div>
        <div class="row row-home">
            <div class="col-extra-home">
                <h2>{{ __('home.imageHomeHeading') }}</h2>
                <p>
                    {{ __('home.imageHomeDescription') }}
                </p>
                <a href="/about">{{ __('home.readMoreLink') }}</a>
            </div>
        </div><br /><br />
    </main>

@endsection
