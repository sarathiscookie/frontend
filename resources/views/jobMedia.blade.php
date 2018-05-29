@extends('layouts.app')

@section('title', 'Media Job')

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
            <div class="col-simple">
                <article class="jobdtls" id="media_design" style="display: block;">

                    <h2><strong>Mediengestalter/in</strong></h2>

                    <div class="">
                        <figure class="job-image">
                            <img src="{{ asset('storage/img/medien.jpg') }}" title="medien" alt="medien"/>
                        </figure>
                        <br><strong>Ihre Aufgaben:</strong><br><br>
                        <p>- Grafische Konzeption und Umsetzung von unseren Onlineplattformen<br>
                            - Grafische Konzeption und Umsetzung von Printmedien wie Kataloge, Broschüren, Plakate<br>
                            - Entwurf von Logos und Coporate Designs<br>
                            - Vorbereitung von Newslettern und Anzeigen auch im Onlinebereich (Layout)<br>
                            - Auswahl und Bearbeitung von Fotodaten und Dokumenten<br>
                            - Erstellung von (Zeitungs/Zeitschrift) Artikeln</p><br>
                        <strong>Ihr Profil:</strong><br><br>
                        <p>- Erfolgreich abgeschlossene Berufsausbildung im Bereich Design / Medien bzw. vergleichbare Ausbildung mit einschlägiger Praxiserfahrung<br>
                            - Studium oder abgeschlossene Ausbildung im Bereich Mediengestaltung, Kommunikations- oder Grafikdesign<br>
                            - Sie haben sehr gute Kenntnisse in Adobe CC (Photoshop, InDesign, Illustrator), kombiniert mit den gängigen Programmen MS Office (Word, Excel, etc.)<br>
                            - Kreativität, Flexibilität und Belastbarkeit Ideenreichtum für Print, Online und Social Media<br>
                            - Gute Deutsch- und Englischkenntnisse, weitere Fremdsprachen von Vorteil<br>
                            - Fundierte Kenntnisse in den Bereichen Layout, Typografie, Bildbearbeitung</p><br>
                        <strong>Unser Angebot:</strong><br><br>
                        <p>Sie setzen auf volle Kreativität? Gut! Sie lieben flache Hierachien? Gut!<br>
                            Wenn Sie Ihre Ideen verwirklichen möchten, mal was Neues ausprobieren möchten und dann noch richtig Lust auf anspruchsvolle Aufgaben haben, dann sind Sie bei uns richtig!<br>
                            Es erwartet Sie ein super Arbeitsklima mit internationalem Touch, Märkte die erst von uns geschaffen wurden. Ach ja und voller Bergblick inklusive!<br><br>
                            - Flache Hierarchien und kurze Entscheidungswege<br>
                            - Lernen und wachsen Sie mit einem erfolgreichen Team<br>
                            - Anspruchsvolle Aufgabe mit hoher Selbstständigkeit<br>
                            - Ein angenehmes Arbeitsklima<br>
                            - Ein unbefristetes Arbeitsverhältnis<br>
                            - Ein vielseitiges Aufgabengebiet in einem internationalen Arbeitsumfeld Kaffee, Tee und Wasser inklusive<br><br>
                            Falls Ihre Bewerbung mit: "Hiermit bewerbe ich mich ... bla bla bla" beginnt, dann sind Sie einer von 100 Bewerbern denen wir absagen werden. Sorry! Also legen Sie sich voll ins Zeug und zeigen uns Ihre ganze Kreativität! Wenn es am Ende ein Video ist: okay! Wenn wir morgen auf dem Balkon ein Schild mit Ihrer Bewerbung haben: super! Falls Sie eine eigene Idee haben die uns umhaut: Grandios!</p><br>
                    </div>
                </article>
            </div>
        </div><br /><br />
    </main>
@endsection

