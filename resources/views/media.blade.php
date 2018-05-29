@extends('layouts.app')

@section('title', 'Media')

@section('jumbotron')
    <div class="jumbotron">
        <div class="container text-center">
            <img src="{{ asset('storage/img/sunset.jpg') }}" class="img-responsive titlepicture" alt="titlepicture">
            {{--<h1 id="headliner-home">Finde deinen<br>Traumjob</h1>--}}
        </div>
    </div>

    <div class="clearfix"></div>
@endsection

@section('content')
    <main>
        <div class="row row-simple">
            <div class="col-simple">
                <h1>Mediadaten</h1>
                <p>
                    Bei Interesse schreiben Sie uns bitte eine Mail an <a href="mailto:service@huetten-holiday.de">service@huetten-holiday.de</a>
                </p>
            </div>
        </div><br /><br />
    </main>
@endsection

