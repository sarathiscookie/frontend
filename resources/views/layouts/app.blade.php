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
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- jQuery-ui -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Css for all plugins -->
    <link href="{{ mix('css/plugins.css') }}" rel="stylesheet">

    <!-- Css for all modules -->
    <link href="{{ mix('css/all.css') }}" rel="stylesheet">

    @yield('styles')

</head>

<body id="app">

@inject('service', 'App\Http\Controllers\ServiceController')

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid container-fluid-home">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle" id="button-nav-top" data-toggle="collapse" data-target="#app-navbar-collapse"><!--Mobile Navigation Burger-->
                <span class="mobile-menu">{{ __('app.mobileMenu') }}</span>
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
                <li><a href="{{ route('search') }}" class="nav-points left-top-nav">{{ __('app.menuCabin') }}</a></li>
                <li><a href="#" class="nav-points left-top-nav">{{ __('app.menuHikes') }}</a></li>
                <li><a href="#" class="nav-points left-top-nav">{{ __('app.menuRegions') }}</a></li>
                {{--<li><a href="#" class="nav-points left-top-nav">{{ __('app.menuShop') }}</a></li>--}}
                <!-- Authentication Links -->
                @guest
                    <li><a href="{{ route('login') }}" class="nav-points left-top-nav"><span class="glyphicon glyphicon-log-in"></span> {{ __('app.menuLogin') }}</a></li>
                    <li><a href="{{ route('register') }}" class="nav-points left-top-nav">{{ __('app.menuRegister') }}</a></li>
                    @else
                        <li><a href="#" class="nav-points left-top-nav" data-toggle="dropdown"><span class="glyphicon glyphicon-home"></span> {{ __('app.menuMyHuettenHoliday') }}<span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-home">
                                <li class="check-it-list-home"><a href="#" class="dropdown-links"><span class="glyphicon glyphicon-floppy-disk"></span> {{ __('app.menuMyData') }}</a></li>
                                <li class="check-it-list-home"><a href="/booking/history" class="dropdown-links"><span class="glyphicon glyphicon-bed"></span> {{ __('app.menuMyBookingHistory') }}</a></li>
                                <li class="check-it-list-home">
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                                          document.getElementById('logout-form').submit();"
                                       class="dropdown-links"><span class="glyphicon glyphicon-log-out"></span>
                                        {{ __('app.menuLogout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <li><a href="{{ route('cart') }}" class="nav-points left-top-nav" id="last-nav-point"> <span class="glyphicon glyphicon-shopping-cart"></span>{{ __('app.menuCabinCart') }} <span class="badge">{!! $service->cart() !!}</span></a></li>
                @endguest
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right" id="right-top-nav">
                <li><a href="#" class="nav-points nav-points-right"><span class="glyphicon glyphicon-search" title="{{ __('app.searchPlaceholder') }}"></span><span class="icons-display"> {{ __('app.search') }}</span></a></li>
                <li><a href="#" class="nav-points nav-points-right"><span class="glyphicon glyphicon-earphone" title="{{ __('app.menuPhone') }}"></span><span class="icons-display"> {{ __('app.menuPhone') }}</span></a></li>
                <li><a href="#" class="nav-points nav-points-right" id="last-child"><span class="glyphicon glyphicon-envelope" title="{{ __('app.menuContact') }}"></span><span class="icons-display"> {{ __('app.menuContact') }}</span></a></li>
            </ul>

        </div>
    </div>
</nav>

<div class="jumbotron">
    <div class="container text-center">
        <img src="{{ asset('storage/img/namloser-wetter-spitz.jpg') }}" class="img-responsive titlepicture" alt="Title picture">
        <h1 id="headliner-home">{{ __('app.imageHeadline1') }}<br>{{ __('app.imageHeadline2') }}</h1>
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
            <h3 class="footerinhalt footer-headliner">{{ __('app.footerInformation') }}</h3><br />
            <a class="footerinhalt" href="">{{ __('app.footerInformationContact') }}</a><br />
            <a class="footerinhalt" href="">{{ __('app.footerInformationAbout') }}</a><br />
            <a class="footerinhalt" href="">{{ __('app.footerInformationJob') }}</a>
        </li>
        <li class="footerabschnitte">
            <h3 class="footerinhalt footer-headliner">{{ __('app.footerMedia') }}</h3><br />
            <a class="footerinhalt" href="https://www.facebook.com/HuettenHoliday">Facebook</a><br />
            <a class="footerinhalt" href="https://blog.huetten-holiday.de/wordpress/">Blog</a><br />
            <a class="footerinhalt" href="">{{ __('app.footerMediaData') }}</a>
        </li>
        <li class="footerabschnitte">
            <h3 class="footerinhalt footer-headliner">{{ __('app.footerLegal') }}</h3><br />
            <a class="footerinhalt" href="">{{ __('app.footerImprint') }}</a><br />
            <a class="footerinhalt" href="">{{ __('app.footerDataProtection') }}</a><br />
            <a class="footerinhalt" href="">{{ __('app.footerTermsOfService') }}</a><br />
            <a class="footerinhalt" href="">{{ __('app.footerImageRights') }}</a>
        </li>
    </ul>
</footer>

<!-- Laravel default js -->
<script src="{{ mix('js/app.js') }}"></script>

<!-- To avoid conflict with jQuery UI -->
<script>
    $.fn.btnBootstrap = $.fn.button.noConflict();
</script>

<!-- jQuery UI 1.12.1 -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!-- Js for all plugins -->
<script src="{{ mix('js/plugins.js') }}"></script>

<!-- Js for all modules -->
<script src="{{ mix('js/all.js') }}"></script>

@stack('scripts')

</body>
</html>
