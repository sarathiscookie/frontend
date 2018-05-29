@extends('layouts.app')

@section('title', 'About us')

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
                <h1>Was ist Huetten-Holiday.de - Für wen ist Huetten-Holiday.de</h1>
                <p>
                    Huetten-Holiday.de ist das Reservierungs- und Verwaltungssystem für Berghütten. Unser Service richtet sich an Hüttenwirte, aber natürlich auch an Wanderer, Bergsteiger als auch an Bergschulen. Dadurch profitieren alle Beteiligten.
                </p>
                <article>
                    <strong>Einfach reservieren</strong>
                    <p>Mit Huetten-Holiday.de können Sie bequem Berghütten suchen, finden und schlussendlich auch reservieren. So sparen Sie wertvolle recherche Arbeit und können sich auf den nächsten Bergurlaub freuen.</p>

                    <strong>Überblick bekommen</strong>
                    <p>Mit unserer Kartendarstellung können Sie sich einen schnellen Überblick über verfügbare Hütten auf Huetten-Holiday.de verschaffen. So können Sie Ihre "Wunsch-Berg-Region" einfach und schnell reservieren. Dafür gibt´s auch einen Regionenauswahl und ein Schnellsuche für Hütten. Bei der Schnellsuche werden nach Eingabe von bereits einem Buchstaben verschiedenen Hütten vorgeschlagen. Das hilft falls Ihnen ein Name nicht sofort einfällt.</p>

                    <strong>Touren planen und buchen</strong>
                    <p>In naher Zukunft lassen sich mit unserem Tourenplaner einfache Routen für Sie und Ihre Gruppe erstellen und gleich alle entsprechenden Hütten auf dem Weg mit einem Klick reservieren. </p>

                    <strong>Für Hüttenbetreiber</strong>
                    <p>Als Hüttenbetreiber können Sie von vielen Vorteilen profitieren und zusätzlich auf sich und Ihre Hütte aufmerksam machen. Gerade das für die nahe Zukunft geplante Tourenbuchungsprogramm hilft Ihnen sicherlich weitere Gäste zu bekommen.
                        <br>Des Weiteren können Sie anhand des Partnerbereiches viele nützliche Module nutzen. Angefangen von der Planung und Einteilung Ihrer Zimmer bis hin zu einer vollständigen Übersicht Ihrer Reservierungen.
                    </p>

                </article>
            </div>
        </div><br /><br />
    </main>
@endsection

