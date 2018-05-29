@extends('layouts.app')

@section('title', 'PHP job')

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
                    <h2><strong>PHP Entwickler (m/w)</strong></h2>
                    <div>
                        <figure class="job-image">
                            <img src="{{ asset('storage/img/php.jpg') }}" title="programmieren" alt="programmieren"/>
                        </figure>
                        <br><strong>Ihre Aufgaben:</strong><br><br>
                        <p>- Anbindung von Datenbanken und Schnittstellen (APIs)<br>
                            - Durchführung gewissenhafter Code Reviews und Unit-Testing<br>
                            - Erstellung dynamischer Frontends<br>
                            - Konzepterstellung<br>
                            - Eigenverantwortliches Arbeiten an Großprojekten<br>
                            - Optimierung von Performance und Verfügbarkeit der Softwarelösungen</p><br>
                        <strong>Ihr Profil:</strong><br><br>
                        <p>- Sie haben ein Studium der Informatik oder Wirtschaftsinformatik absolviert oder haben in der Praxis die notwendigen Kenntnisse erworben.<br>
                            - HTML, CSS und JavaScript sind für Sie eine Selbstverständlichkeit. <br>
                            - Sehr gute Kenntnisse und Erfahrungen im Umgang mit PHP<br>
                            - Erfahrungen im Umgang mit Frameworks wie Laravel, Zend<br>
                            - Erfahrung mit agilen Entwicklungsmethoden (Scrum, Kanban)<br>
                            - Git Kenntnisse<br>
                            - Erfahrungen mit Schnittstellentechnologien z.B. REST, SOAP <br>
                            - Wissen von AJAX-Verfahren sowie MVC-Konzepte</p><br>
                        <strong>Unser Angebot:</strong><br><br>
                        <p>
                            - Flache Hierarchien und kurze Entscheidungswege<br>
                            - Lernen und wachsen Sie mit einem erfolgreichen Team<br>
                            - Anspruchsvolle Aufgabe mit hoher Selbstständigkeit<br>
                            - Ein angenehmes Arbeitsklima<br>
                            - Ein unbefristetes Arbeitsverhältnis<br>
                            - Ein vielseitiges Aufgabengebiet in einem internationalen Arbeitsumfeld<br>
                            - Kaffee, Tee und Wasser inklusive<br>
                            - Weiterbildung zum Laravel-Entwickler<br><br></p><br>
                    </div>
                </article>
            </div>
        </div><br /><br />
    </main>
@endsection

