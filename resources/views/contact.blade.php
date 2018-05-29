@extends('layouts.app')

@section('title', 'Contact us')

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
            <div class="col-simple contactandhelp">
                <h1>Kontakt und Hilfe</h1>
                <article>
                    <h3 class="accordion">Ich habe eine Nachricht zu einer nicht bezahlten Buchung bekommen, habe aber bezahlt?</h3>

                    <div class="panel" style="">
                        <p>Prüfen Sie in Ihrer Buchungsübersicht ob Sie die Buchung nicht versehentlich doppelt getätigt haben.</p>
                    </div>
                </article>
                <article>
                    <h3 class="accordion">Wie kann ich zwischen Bett und Matratzenlager wählen?</h3>
                    <div class="panel">
                        <p>Die Betten beziehungsweise Lager Vergabe erfolgt vom Hüttenwirt selbst auf der Hütte. Sprich wer zuerst
                            ankommt hat noch die freie Auswahl. Sie können Ihren Wunsch allerdings in Form eines Kommentars in der
                            Kommentarbox hinterlegen, dies kann der Hüttenwirt dann berücksichtigen.</p>
                    </div>
                </article>

                <article>

                    <h3 class="accordion">Was passiert mit meiner Anzahlung, wenn ich Storniere?</h3>
                    <div class="panel">
                        <p>Wenn Sie innerhalb der Stornierungsfrist Stornieren, bekommen Sie die Anzahlung auf Ihren Account gutgeschrieben. Dieses Guthaben können Sie später für weitere Buchungen vornehmen. Gerne Zahlen wir Ihnen das Geld aber auch aus. Weitere Infos über die Stornierungsfristen finden Sie in unseren AGB. </p>
                    </div>
                </article>

                <article>

                    <h3 class="accordion">Wann erhalte ich meinen Wertgutschein?</h3>
                    <div class="panel">
                        <p>Den Wertgutschein erhalten Sie sobald die Anzahlung für Ihre Buchung bei uns eingegangen ist</p>
                    </div>
                </article>

                <article>

                    <h3 class="accordion">Warum muss ich eine Anzahlung leisten?</h3>
                    <div class="panel">
                        <p>Die Moral, Reservierungen einzuhalten bzw. zumindest rechtzeitig zu stornieren, war in den letzten Jahren
                            leider sehr schlecht. Weswegen sich viele Hüttenwirte eine Anzahlung wünschten, welche bei nicht
                            erscheinen als <strong>Umsatzausfallgebühr</strong> beim Hüttenwirt verbleibt</p>
                        <p>Als Wanderer sollte Ihnen bewusst sein, dass ein enormer Aufwand betrieben wird, um eine alpine
                            Gebirgshütte zu bewirtschaften. Sollten Sie also aus welchen Gründen auch immer nicht kommen können oder
                            wollen, nutzen Sie bitte die Möglichkeit einer rechtzeitigen Stornierung.</p>
                    </div>
                </article>

                <article>

                    <h3 class="accordion">Warum muss ich eine Service-Gebühr bezahlen?</h3>
                    <div class="panel">
                        <p>Beim Zahlungsverkehr im Internet entstehen je nach Zahlungsart (PayPal, Kreditkarte, Sofortüberweisung)
                            Gebühren. Mit der Servicegebühr werden diese Gebühren gedeckt. Außerdem hilft uns dies unsere Plattform
                            zu betreiben und weiter zu entwickeln.</p>
                    </div>
                </article>

                <article>

                    <h3 class="accordion">Was muss ich für die Übernachtung auf einer Hütte dabei haben?</h3>
                    <div class="panel">
                        <p>Generell möchten wir Sie hier auf die Detailseiten der jeweiligen Hütte bzw. auf deren eigenen Webseiten
                            verweisen. Eine pauschale Aussage unsererseits ist nicht möglich.</p>
                    </div>
                </article>

                <article>

                    <h3 class="accordion">Ich möchte den Hüttenwirt kontaktieren finde jedoch keine Kontaktmöglichkeit</h3>
                    <div class="panel">
                        <p>Viele Hütten haben kein Telefon oder nur Satellitentelefone für den Notfall. Sollten Sie noch eine Frage
                            zur Übernachtung, Verpflegung oder Sonstiges haben, können Sie dies bei Ihrer Buchung in das
                            Bemerkungsfeld eingeben. Sie erhalten dann eine Antwort vom Hüttenwirt. In der Hochsaison ist die
                            Bearbeitung sehr aufwendig da Fragen oft in den Nachtstunden vom Hüttenwirt bearbeitet werden. Bitte
                            halten Sie sich daher kurz und beschränken sich auf das Wesentliche.</p>
                    </div>
                </article>
                <article>

                    <h3 class="accordion">Wie sehe ich ob eine Hütte ausgebucht ist?</h3>
                    <div class="panel">
                        <p>Jede der Hütten hat einen eigenen Kalender. Auf diesem sehen Sie ob noch frei Plätze verfügbar sind. Rot=kein freier Platz | Orange=wenige freie Plätze | Grün=einige freie Plätze. </p>
                    </div>
                </article>

                <article>

                    <h3 class="accordion">Warum kann ich die Huetten-Holiday.de nur über eine 0900-Nummer erreichen?</h3>
                    <div class="panel">
                        <p>Zuletzt wurden wir mit anrufen überhäuft. Das ist prinzipiell kein Problem für uns und wir helfen Ihnen
                            jederzeit gerne weiter! </p>
                        <p>Jedoch beziehen sich ein Großteil der Anrufe nicht zu einer Reservierung über unser System, sondern
                            vielmehr zu Wegeauskünften, Organisatorisches auf den Hütten, Lawinenberichte usw.</p>
                        <p>Wir bieten ein System an, welches Hüttenwirten und Wanderern die Reservierungsverwaltung erleichtern
                            soll. Auskünfte über Wege oder Wetterberichte können bzw. dürfen wir nicht machen.</p>
                    </div>
                </article>
                <article>
                    <h3 class="accordion">Haben Sie Fragen zu einem anderem Thema?</h3>
                    <br>
                    <p>service@huetten-holiday.de </p>
                    <p>Telefon: +49 (0) 9001 / 32 99 99<span id="footer_phone">(1,99 €/min aus dem dt. Festnetz, Mobilfunk ggf. abweichend)</span></p>
                    <p> Öffnungszeiten: Mo. bis Do. 8:00 Uhr bis 16:00 Uhr und Fr. bis 13:00 Uhr</p>
                </article>
            </div>
        </div><br /><br />
    </main>
@endsection

