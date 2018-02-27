<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Huetten-Holiday.de - @yield('title')</title>

    <!-- Laravel default css -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- jQuery-ui -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Css for all plugins -->
    <link href="{{ asset('css/plugins.css') }}" rel="stylesheet">

    <!-- Css for all modules -->
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">

    @yield('styles')

</head>

<body id="app">
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid container-fluid-home">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle" id="button-nav-top" data-toggle="collapse" data-target="#app-navbar-collapse"><!--Mobile Navigation Burger-->
                <span class="mobile-menu">Menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a  href="{{ url('/') }}">
                <img src="{{ asset('storage/img/logo.png') }}" class="navbar-brand" id="nav-logo" alt="huetten-holiday logo">
            </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">

            <ul class="nav navbar-nav ">
                <li><a href="#" class="nav-points left-top-nav">Cabins</a></li>
                <li><a href="#" class="nav-points left-top-nav">Hikes</a></li>
                <li><a href="#" class="nav-points left-top-nav">Regions</a></li>
                <li><a href="#" class="nav-points left-top-nav">Shop</a></li>
                <li><a href="#" class="nav-points left-top-nav" id="last-nav-point"> <span class="glyphicon glyphicon-shopping-cart"></span>Cabin-Cart</a></li>
                <!-- Authentication Links -->
                @guest
                    <li><a href="{{ route('login') }}" class="nav-points left-top-nav"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
                    <li><a href="{{ route('register') }}" class="nav-points left-top-nav">Register</a></li>
                    @else
                        <li><a href="#" class="nav-points left-top-nav" data-toggle="dropdown"><span class="glyphicon glyphicon-home"></span> My Huetten-Holiday<span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-home">
                                <li class="check-it-list-home"><a href="#" class="dropdown-links"><span class="glyphicon glyphicon-floppy-disk"></span> My Data</a></li>
                                <li class="check-it-list-home"><a href="#" class="dropdown-links"><span class="glyphicon glyphicon-bed"></span> My Bookinghistory</a></li>
                                <li class="check-it-list-home">
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                                          document.getElementById('logout-form').submit();"
                                       class="dropdown-links"><span class="glyphicon glyphicon-log-out"></span>
                                        Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                        @endguest
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right" id="right-top-nav">
                <li><a href="#" class="nav-points nav-points-right"><span class="glyphicon glyphicon-search" title="Search"></span><span class="icons-display"> Search</span></a></li>
                <li><a href="#" class="nav-points nav-points-right"><span class="glyphicon glyphicon-earphone" title="Phone"></span><span class="icons-display"> Phone</span></a></li>
                <li><a href="#" class="nav-points nav-points-right" id="last-child"><span class="glyphicon glyphicon-envelope" title="Contact"></span><span class="icons-display"> Contact</span></a></li>
            </ul>

        </div>
    </div>
</nav>

<div class="jumbotron">
    <div class="container text-center">
        <img src="{{ asset('storage/img/namloser-wetter-spitz.jpg') }}" class="img-responsive titlepicture" alt="Title picture">
        <h1 id="headliner-home">Find your<br>favorite Cabin</h1>
    </div>
</div>

@yield('content')

<div>
    <div id="mountain"><img src="{{ asset('storage/img/Bergsilhouette-grau.png') }}" class="img-responsive" alt="mountain background"></div>
    <div id="over-footer"></div>
</div>

<footer class="container-fluid container-fluid-home text-center">
    <ul  id="footerbalcken">
        <li class="footerabschnitte">
            <h3 class="footerinhalt footer-headliner">Huetten-Holiday.de</h3><br />
            <a class="footerinhalt">Huetten-Holiday.de GmbH</a><br />
            <a class="footerinhalt">Nebelhornstraße 3</a><br />
            <a class="footerinhalt">87448 Waltenhofen</a><br />
            <a class="footerinhalt">Deutschland</a>
        </li>
        <li class="footerabschnitte">
            <h3 class="footerinhalt footer-headliner">Information</h3><br />
            <a class="footerinhalt" href="">Contact and Help</a><br />
            <a class="footerinhalt" href="">About Huetten-Holiday.de</a><br />
            <a class="footerinhalt" href="">Jobs</a>
        </li>
        <li class="footerabschnitte">
            <h3 class="footerinhalt footer-headliner">Media</h3><br />
            <a class="footerinhalt" href="https://www.facebook.com/HuettenHoliday">Facebook</a><br />
            <a class="footerinhalt" href="https://blog.huetten-holiday.de/wordpress/">Blog</a><br />
            <a class="footerinhalt" href="">Media data</a>
        </li>
        <li class="footerabschnitte">
            <h3 class="footerinhalt footer-headliner">Legal</h3><br />
            <a class="footerinhalt" href="">Imprint</a><br />
            <a class="footerinhalt" href="">Data protection</a><br />
            <a class="footerinhalt" href="">Terms of Service</a><br />
            <a class="footerinhalt" href="">Image rights</a>
        </li>
    </ul>
</footer>

<!-- Laravel default js -->
<script src="{{ asset('js/app.js') }}"></script>

<!-- jQuery UI 1.12.1 -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!-- Js for all plugins -->
<script src="{{ asset('js/plugins.js') }}"></script>

<!-- Js for all modules -->
<script src="{{ asset('js/all.js') }}"></script>

@stack('scripts')

</body>
</html>
