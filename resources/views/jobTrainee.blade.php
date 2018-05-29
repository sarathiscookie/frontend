@extends('layouts.app')

@section('title', 'Trainee Job')

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
                <article>
                    <h2><strong>Ausbildung zum Fachinformatiker Anwendungsentwicklung (m/w)</strong></h2>
                    <div>
                        <p><br>Als innovatives Unternehmen und Entwicklungsschmiede für neue Ideen haben wir uns in den letzten Jahren einen guten Namen mit unseren Onlineplattformen geschaffen. Wir möchten unser Know-How weiter ausbauen und dir dabei die Techniken von morgen beibringen. Aus diesem Grund bewirb dich um eine Ausbildungsstelle zum Fachinformatiker / Anwendungsentwicklung. Dabei wird der Fokus insbesondere auf die Entwicklung von Webanwendungen gelegt und du erlernst alle dafür notwendigen Technologien.</p>
                        <figure class="job-image">
                            <img src="{{ asset('storage/img/ausbildung.jpg') }}" title="arbeitsplatz" alt="arbeitsplatz"/>
                        </figure>
                        <br><strong>Tätigkeitsbeschreibung:</strong><br><br>
                        <p>- Konzeption und Planung von einfachen bis hin zu komplexen Webanwendungen mit agilen Entwicklungsmethoden<br>
                            - Entwicklung / Programmierung von Webapplikationen mit modernen Technologien insbesondere unser eigenes Webportal<br>
                            - Du wirst den Umgang mit folgende Sprachen, Frameworks und Systeme lernen: <br>
                            MySQL, NoSQL, PHP, ZEND, Laravel, CSS3, HTML5, TYPO3, Contao, Wordpress, Magento</p><br>
                        <strong>Unser Angebot:</strong><br><br>
                        <p>- Im Rahmen der Ausbildung kannst du zusätzliche Zertifikate erwerben <br>
                            - Eine hohe Chance auf eine Übernahme in unseren Betrieb<br>
                            - Entwicklung von Führungskompetenzen bereits während der Ausbildung<br>
                            - Einfache Strukturen und junges Team (Startup-Charakter)</p><br>
                        <strong>Was du mitbringen solltest:</strong><br><br>
                        <p>- Mittlere Reife, Fachhochschulreife, Abitur<br>
                            - Führerschein der Klasse B<br>
                            - Hohes technisches Interesse<br>
                            - Analyse- und Problemlösefähigkeit, Auffassungsfähigkeit/-gabe<br />
                            - Ganzheitliches Denken, Lernbereitschaft, Sorgfalt</p><br>
                        <strong>Wo findet die Ausbildung statt:</strong><br><br>
                        <p>- Die Berufsschule ist in Kempten <br>
                            - Die Ausbildung findet in Waltenhofen und Unterthingau statt<br></p><br>
                    </div>
                </article>
            </div>
        </div><br /><br />
    </main>
@endsection

