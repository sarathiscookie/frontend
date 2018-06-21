@extends('layouts.app')

@section('title', 'Programmer Job')

@section('jumbotron')
    <div class="jumbotron">
        <div class="container text-center">
            <img src="{{ asset('storage/img/sunset.jpg') }}" class="img-responsive titlepicture" alt="titlepicture">
            <h1 id="headliner-home">Finde deinen<br>Traumjob</h1>
        </div>
    </div>

    <div class="clearfix"></div>
@endsection

@section('content')
    <main>
        <div class="row row-simple">
            <div class="col-simple" id="jobs">
                <div id="job-intro">
                    <div>
                        <div class="glyphicon glyphicon-pencil">
                            <h3>Gestalte</h3>
                            <p>Immer auf dem neuesten Stand arbeiten - als Team.</p>
                        </div>
                    </div>
                    <div>
                        <div class="glyphicon glyphicon-book">
                            <h3>Lerne</h3>
                            <p>Wer immer tut, was er schon kann, bleibt immer das, was er schon ist.</p>
                        </div>
                    </div>
                    <div>
                        <div class="glyphicon glyphicon-fire">
                            <h3>Lebe</h3>
                            <p>Veränderung ist das Einzige, worauf wir uns wirklich verlassen können.</p>
                        </div>
                    </div>
                </div>
                <article>
                    <div id="team-work">
                        <h3>Arbeiten bei Huetten-Holiday</h3>
                        <div>
                            <p>Ständig an der Entwicklung neuer Ideen arbeiten und dabei immer mit den modernsten Techniken arbeiten? Dann bewerben Sie sich jetzt. Wir freuen uns auf Sie!
                            </p>
                        </div>
                    </div>
                </article>
                <article>
                    <div id="trainee-work">
                        <h3>Ausbildung</h3>
                        <div>
                            <p>Wo andere begeistert an ihren Mofas und Autos herumschrauben, programieren Sie lieber den ganzen Tag an diverser Tools, mit denen Sie Ihren Rechner immer schneller machen wollen? Dann ist eine Ausbildung für Sie bei uns genau richtig!
                            </p>
                            <a href="/job/trainee">Mehr erfahren Sie hier...</a>
                        </div>
                    </div>
                </article>
                <article>
                    <div id="media-work">
                        <h3>Mediengestalter/in</h3>
                        <div>
                            <p>Lassen Sie Ihren Inspirationen freien Lauf bei der Entwicklung und Gestaltung von Medienunterlagen für Huetten-Holiday.de, aber auch bei anderen Projekten.
                            </p>
                            <a href="/job/media">Mehr erfahren Sie hier...</a>
                        </div>
                    </div>
                </article>
                <article>
                    <div id="php-work">
                        <h3>PHP Entwickler/in</h3>
                        <div>
                            <p>Mit viel Gestaltungsfreiraum erarbeiten Sie in Zusammenarbeit mit Kollegen und Kunden neue, innovative Module für unsere Portal und weitere Projekte.
                            </p>
                            <a href="/job/programmer">Mehr erfahren Sie hier...</a>
                        </div>
                    </div>
                </article>
            </div>
        </div><br /><br />
    </main>
@endsection

