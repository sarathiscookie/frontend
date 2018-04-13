<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bookinghistory</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<style>
    /*For all*/

    a {
        color:#A2C617;
    }
    a:focus, a:hover {
        text-decoration:underline;
        color:#A2C617;
    }
    .h2, h2 {
        font-size:25px;
    }
    .h5, h5{
        font-size:16px;
    }
    .h3, h3{
        font-size:20px;
    }
    p{
        font-family:Segoe UI;
        font-size:16px;
        color:#5F6876;
    }
    body{
        font-family:Segoe UI;
        font-size:16px;
        color:#5F6876;
        background-color:#fff;
    }
    main{
        margin-top:30px;
    }
    @media (min-width:1350px) {main {
        margin-left:10%;
        margin-right:10%;
    }}
    /*Icons*/
    .glyphicon{
        position:relative;
        top:1px;
        display:inline-block;
        font-size:20px;
    }
    /*Pictures responsive*/
    .img-responsive{
        width:100%;
        max-width:100%;
        display:block;
        height:auto;
    }
    /*Burger Buttons*/
    .navbar-toggle{
        padding-top:11px;
        padding-bottom:7px;
    }
    .navbar-inverse .navbar-toggle {
        border-color:#A2C617;
    }
    .navbar-inverse .navbar-toggle:focus, .navbar-inverse .navbar-toggle:hover {
        background-color:#A2C617;
    }
    .navbar-inverse .navbar-toggle .icon-bar {
        background-color:#5F6876;
    }
    #button-nav-top {
        margin-top:5%;
    }
    /*Navigation Top*/
    /*Dropdown Top*/
    .navbar-inverse .navbar-collapse, .navbar-inverse .navbar-form {
        border-color:#5F6876;
    }
    .dropdown-links{
        font-family:Segoe UI;
        font-size:16px;
        color:#5F6876 !important;
    }
    .dropdown-links:hover{
        color:#A2C617 !important;
    }
    .navbar-inverse .navbar-nav>.open>a, .navbar-inverse .navbar-nav>.open>a:focus, .navbar-inverse .navbar-nav>.open>a:hover {
        background-color:#fff;
        color:#5F6876;
    }
    .dropdown-menu>li>a:focus, .dropdown-menu>li>a:hover{
        background-color:transparent !important;
    }
    .dropdown-menu{
        border:2px solid #5F6876;
        border-top:none;
        background-color:white;
        padding-top:0;
    }
    /*Logo in the Navigation*/
    #nav-logo {
        width:150px;
        height:auto;
        padding-top:15px;
        padding-bottom:15px;
        padding-right:0;
    }

    /*a in Navigation*/
    @media (max-width:767px){.navbar-nav {
        margin:0;
    }}
    .navbar-inverse .navbar-nav>li>a {
        color:#5F6876;
    }
    .navbar-inverse .navbar-nav>li>a:focus, .navbar-inverse .navbar-nav>li>a:hover{
        color:#5F6876;
    }
    @media (max-width:767px){.nav>li>a:focus, .nav>li>a:hover {
        text-decoration:none;
        background-color:#eee !important;
    }}
    @media (min-width: 768px){.left-top-nav {
        padding-bottom:25px !important;
    }}
    @media (max-width:767px){.navbar-inverse .navbar-nav>li>a{
        color:#5F6876;
        padding-left:15px !important;
        padding-right:15px !important;
        padding-top:10px !important;
        padding-bottom:10px !important;
        border-bottom: 1px solid;
    }}
    @media (min-width:768px){.navbar-inverse .navbar-nav>li>a:focus, .navbar-inverse .navbar-nav>li>a:hover {
        color:#A2C617;
    }}
    .navbar-inverse {
        background-color:#FFF !important;
        border:none;
    }
    .navbar {
        margin-bottom: 0;
        border-radius: 0;
    }
    /*Navbar always on the top*/
    .navbar-fixed-top{
        position:absolute !important;
    }
    .navbar-header {
        margin-left:10% !important;
    }
    @media (min-width: 1031px){ .nav-points{
        padding-top:42px !important;
        padding-bottom:0px;
    }}
    @media (max-width:1349px){.navbar-header {
        margin-left:0 !important;
    }}
    @media (min-width:850px){#last-child {
        margin-right:35px;
    }}
    @media (min-width:768px){#last-child {
        margin-right:15px;
    }}
    @media (max-width:1100px){#last-nav-point{
        padding-right:0;
    }}
    /*small screen nav list we have three different sizes for nav:945px 1510 >1100   */
    @media (max-width: 1030px){ .navbar-header{
        float:none;
    }}
    @media (max-width: 1349px) and (min-width:768px){ .icons-display{
        display: none;;
    }}
    @media (max-width: 1030px){ .nav-points{
        padding-top:5px !important;
    }}
    @media (max-width: 1030px){ .nav-points{
        padding-top: 10px !important;
        padding-left:10px !important;
        padding-bottom:10px !important;

    }}
    @media (max-width:8490px){.nav-points-right {
        padding-right:2px !important;
    }}
    .mobile-menu {
        float:left;
        margin-left:25px;
        margin-bottom:0;
        margin-top:-4px;
    }
    /*Title picture*/
    .container {
        margin:0;
        padding:0;
        width:100%;
    }
    .jumbotron{
        height:600px !important;
    }
    .titlepicture{
        height:552px;
        object-fit: cover;
    }
    @media (max-width: 767px){ .jumbotron{
        height:312px !important;
    }}
    @media (max-width: 767px){ .titlepicture{
        height:282px;
    }}
    /*Text in titlepicture*/
    #headliner-top{
        position:absolute;
        top:180px;
        margin-left:10%;
        text-align:left;
        color:white;
        opacity:0.7;
        font-size:50px;
    }
    @media (max-width: 1349px){ #headliner-top{
        margin-left:0;
        padding-left:15px;
    }}
    @media (max-width: 767px){ #headliner-top{
        margin-left:0;
        padding-left:15px;
        top:110px;
        font-size:30px;
    }}
    #headliner{
        position:absolute;
        top:240px;
        margin-left:10%;
        text-align:left;
        color:white;
        opacity:0.7;
        font-size:80px;
    }
    @media (max-width: 1349px){ #headliner{
        margin-left:0;
        padding-left:15px;
    }}
    @media (max-width: 767px){ #headliner{
        margin-left:0;
        padding-left:15px;
        top:140px;
        font-size:50px;
    }}
    @media (max-width: 325px){ #headliner{
        font-size:40px;
    }}
    /*Div Container around titlepicture*/
    .jumbotron {
        height: 500px;
    }
    /*Over Footer*/
    #over-footer{
        height:50px;
        background-color:#A2C617;
        opacity:0.7;
    }
    @media (max-width:768px){#over-footer{
        height:30px;
    }}
    #mountain {
        height:auto;
        margin-bottom:-50px;
    }
    @media (max-width:768px){#mountain{
        margin-bottom:-30px;
    }}
    /*Footer*/
    footer {
        background-color: #5F6876;
        padding-top: 25px;
        padding-bottom: 25px;
    }
    @media (max-width:991px){#footerbalcken{
        padding:0;
    }}
    @media (min-width:992px){.footerabschnitte{
        float:left;
        width:20%;
        margin-right:6%;
        list-style-type:none;
    }}
    @media (min-width:992px){.footerabschnitte:last-child{
        margin-right:0;
    }}
    .footerabschnitte{
        list-style-type:none;
    }
    .footerinhalt{
        color:white;
    }

    /*Headliner Footer*/
    .footer-headliner{
        border-bottom:2px solid;
        border-top:2px solid;
    }
    /*end for all*/
    /*width for most containers*/
    @media (max-width: 767px){.container-fluid-history {
        padding-right:5px;
        padding-left:5px;
    }}
    /*Checkboxes*/
    .dropdown-menu-history>li>a{
        color:#5F6876;
        font-size:15px;
    }
    /*Checkboxes*/
    .check-it-history{
        height:17px;
        width:17px;
        margin-left:10px !important;
        white-space:nowrap;
    }
    .check-it-list-history{
        white-space:nowrap;
        padding-right:10px;
        padding-bottom:15px;
    }
    .check-it-list-history:hover {
        background-color:#EFEFEF;
    }
    /*Headline under titlepicture*/
    /*kind of opacity background (the text is not opacity with rgba)*/
    .navbar-default-history{
        background-color: #D1D1D1;
        border: none;
        height:75px;
        text-align:left;
        padding-left:15px;
        padding-right:15px;
    }
    @media (max-width:424px){.navbar-default-history{
        height:105px;
    }}
    @media (max-width:1349px){.col-md-8-history {
        width:100% !important;
    }}
    @media (min-width:1511px){ div .container-fluid-history{
        padding-left:0px;
        padding-right:0px;
        margin:0;
    }}
    @media (min-width:992px){#list-filter-history{
        width:80%;
    }}
    #list-filter-history{
        padding-left:0;
        padding-right:0;
    }
    .col-md-2-history{width:10% !important
    }
    .cabin-head-history{
        float:left;
    }
    @media (max-width:424px){.cabin-head-history{
        float:none;
    }}

    /*Main*/

    /*correct sizes for mobile etc. (3 images)*/
    .row-history{
        width:100%;
    }
    .row-history{
        margin:0;
    }
    /*List*/
    /*Whole list*/
    .panel-body-history{
        padding:0;
    }
    .panel-history{
        border:none;
        border-radius:0px;
    }
    @media (min-width: 768px){.panel-history{
        margin-bottom:20px;
        background-color:#EFEFEF;
    }}
    @media (max-width: 767px){.panel-history{
        background-color:#EFEFEF;
        margin-bottom:20px;
    }}
    @media (min-width: 768px){.panel-default-history{
        border:none;
    }}
    /*Images on the left side*/
    @media (min-width: 768px){.col-sm-2-history{
        width:35%;
    }}
    @media (max-width: 1510px) and (min-width: 768px){.col-sm-2-history{
        padding-top:15px !important;
        padding-left:15px !important;
    }}
    @media (max-width: 1680px) and (min-width: 1511px){.col-sm-2-history{
        width:30%
    }}
    @media (min-width: 1681px){.col-sm-2-history{
        width:25%;
    }}
    .img-thumbnail-history{
        padding:0;
        border:none;
        border-radius:0px;
    }
    .col-sm-2-history{
        padding:0;
    }
    /*Middle-boxes*/
    @media (min-width: 1680px){.col-sm-7-history{
        width:50% !important;
    }}
    @media (max-width: 1680px) and (min-width: 1511px){.col-sm-7-history{
        width:45% !important;
    }}
    @media (min-width: 768px){.col-sm-7-history{
        width:65%;
    }}
    @media (max-width: 768px){.col-sm-7-history{
        padding-left:15px;
        padding-right:15px;
    }}
    /*Details box*/
    .col-sm-3-history{
        padding-right:0;
    }
    @media (min-width: 768px){.col-sm-3-history {
        width:100%;
        padding-left:0;
        padding-top:15px;
        padding-bottom:15px;
    }}
    @media (min-width: 1511px){.col-sm-3-history {
        width:25%;
        padding-top:0;
        padding-bottom:0px;
    }}
    @media (max-width: 767px){.col-sm-3-history {
        padding:0 !important;
    }}
    .booking-box-history{
        padding-left:10px;
        padding-right:10px;
    }
    @media (min-width: 768px) and (max-width:1510px){.booking-box-history {
        background-color:#D1D1D1;
        margin:0;
        padding-bottom:15px;
        margin-left:15px;
        margin-right:15px;
        height:auto;
    }}
    @media (max-width: 767px){.booking-box-history {
        background-color:#D1D1D1;
        padding-bottom:15px;
    }}
    @media (min-width: 1511px){.booking-box-history {
        background-color:#D1D1D1;
        margin:0;
        padding-bottom:15px;
        height:280px;
    }}
    .col-sm-12-history{
        padding-left:0;
    }
    .check-it-list-spe-history{
        float:left;
        margin-bottom:5px;
        padding-bottom:2px;
        padding-top:2px;
    }
    .in-info-history{
        float:none;
        margin-left:150px;
        margin-right:150px;
        text-align:left;
        background-color:#fff;
        padding-left:5px;
    }
    @media (max-width:1510px){.in-info-history{
        margin-right:0;
    }}
    .info-listing-history{
        background-color:#A2C617;
        color:#fff;
        padding-left:12px;
        margin-bottom:20px;
        padding-top:2px;
        padding-bottom:2px;
    }
    .btn-list-history{
        border-radius:0px;
        background-color:#fff;
        border:none;
        font-weight:normal;
        font-size:16px;
        margin-bottom:5px;
        padding-top:2px;
        padding-bottom:2px;
        width:100%;
        text-align:left;
    }
    .btn-list-history:hover{
        background-color:#e6e6e6;
        color:#333;
    }
</style>
<body id="myPage">
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid container-fluid-history">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" id="button-nav-top" data-toggle="collapse" data-target="#myNavbar"><!--Mobile Navigation Burger-->
                <span class="mobile-menu">Menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="#">
                <img src="logo.png" class="navbar-brand" id="nav-logo" alt="huetten-holiday logo">
            </a>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav ">
                <li><a href="#" class="nav-points left-top-nav">Cabins</a></li>
                <li><a href="#" class="nav-points left-top-nav">Hikes</a></li>
                <li><a href="#" class="nav-points left-top-nav">Regions</a></li>
                <li><a href="#" class="nav-points left-top-nav">Shop</a></li>
                <li><a href="#" class="nav-points left-top-nav" data-toggle="dropdown"><span class="glyphicon glyphicon-home"></span> My Huetten-Holiday<span class="caret"></span></a>
                    <ul class="dropdown-menu dropdown-menu-history">
                        <li class="check-it-list-history"><a href="#" class="dropdown-links"><span class="glyphicon glyphicon-floppy-disk"></span> My Data</a></li>
                        <li class="check-it-list-history"><a href="#" class="dropdown-links"><span class="glyphicon glyphicon-bed"></span> My Bookinghistory</a></li>
                        <li class="check-it-list-history"><a href="#" class="dropdown-links"><span class="glyphicon glyphicon-log-out"></span> Log-out</a></li>
                    </ul>
                </li>
                <li><a href="#" class="nav-points left-top-nav" id="last-nav-point"> <span class="glyphicon glyphicon-shopping-cart"></span>Cabin-Cart</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right" id="right-top-nav">
                <li><a href="#" class="nav-points nav-points-right"><span class="glyphicon glyphicon-search" title="Search"></span><span class="icons-display"> Search</span></a></li>
                <li><a href="#" class="nav-points nav-points-right"><span class="glyphicon glyphicon-earphone"  title="Phone"></span><span class="icons-display"> Phone</span></a></li>
                <li><a href="#" class="nav-points nav-points-right" id="last-child"><span class="glyphicon glyphicon-envelope"  title="Contact"></span><span class="icons-display"> Contact</span></a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="jumbotron">
    <div class="container text-center">
        <img src="sunset.jpg" class="img-responsive titlepicture" alt="titlepicture">
        <h2 id="headliner-top">Your</h2><br><h1 id="headliner">Huetten-Holiday.de</h1>
    </div>
</div>
<div class="container-fluid bg-3 text-center container-fluid-history">
    <div class="col-md-2 col-md-2-history"></div>
    <div class="col-md-8 col-md-8-history" id="list-filter-history">
        <nav class="navbar navbar-default navbar-default-history">
            <h2 class="cabin-head-history">A overview of your Bookings</h2>
        </nav>
    </div>
    <div class="col-md-2 col-md-2-history"></div>
</div>
<main>
    <div class="container-fluid text-center container-fluid-history">
        @if ( session()->has('response') && session()->get('response') === 'success' )
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Well done! </strong> Inquiry send successfully. We will get back you shortly.
            </div>
        @endif
        <div class="panel panel-default text-left panel-history panel-default-history">
            <div class="panel-body panel-body-history">
                <div class="row row-history content">
                    <div class="col-sm-2 col-sm-2-history">
                        <img src="huette.jpg" class="img-responsive img-thumbnail img-thumbnail-history" style="width:100%" alt="Image">
                    </div>
                    <div class="col-sm-7 text-left col-sm-7-history">
                        <h3 class="headliner-cabinname">Rappenseeh&uuml;tte - Allg&auml;uer Alps</h3>
                        <div class="row row-history">
                            <div class="col-sm-12 col-sm-12-history">
                                <div class="form-group row row-history">
                                    <ul class="payment-options">
                                        <li class="check-it-list-spe-history">Bookingnumber:</li><li class="check-it-list-spe-history in-info-history">KEH-18.110365</li>
                                        <li class="check-it-list-spe-history">Arrival:</li><li class="check-it-list-spe-history in-info-history">26.07.2018</li>
                                        <li class="check-it-list-spe-history">Depature:</li><li class="check-it-list-spe-history in-info-history">27.07.2018</li>
                                        <li class="check-it-list-spe-history">Bed(s):</li><li class="check-it-list-spe-history in-info-history">2</li>
                                        <li class="check-it-list-spe-history">Dorm(s):</li><li class="check-it-list-spe-history in-info-history">0</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-sm-3-history">
                        <div class="panel panel-default booking-box-history panel-history panel-default-history">
                            <div class="panel-body panel-body-history">
                                <div class="row row-history">
                                    <div class="col-sm-12 col-sm-12-history month-opening">
                                        <h5>Booking status</h5>
                                        <p class="info-listing-history">Booking successfull</p>
                                    </div>
                                </div>
                                <div class="row row-history">
                                    <div class="col-sm-12 col-sm-12-history col-sm-12-extra">
                                        <button type="button" class="btn btn-list-history">Edit booking <span class="glyphicon glyphicon-wrench"></span></button>
                                        <button type="button" class="btn btn-list-history">Cancel booking <span class="glyphicon glyphicon-remove"></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default text-left panel-history panel-default-history">
            <div class="panel-body panel-body-history">
                <div class="row content row-history">
                    <div class="col-sm-2 col-sm-2-history">
                        <img src="huette.jpg" class="img-responsive img-thumbnail img-thumbnail-history" style="width:100%" alt="Image">
                    </div>
                    <div class="col-sm-7 text-left col-sm-7-history">
                        <h3 class="headliner-cabinname">Rappenseeh&uuml;tte - Allg&auml;uer Alps</h3>
                        <div class="row row-history">
                            <div class="col-sm-12 col-sm-12-history">
                                <div class="form-group row row-history">
                                    <ul class="payment-options">
                                        <li class="check-it-list-spe-history">Bookingnumber:</li><li class="check-it-list-spe-history in-info-history">KEH-18.110365</li>
                                        <li class="check-it-list-spe-history">Arrival:</li><li class="check-it-list-spe-history in-info-history">26.07.2018</li>
                                        <li class="check-it-list-spe-history">Depature:</li><li class="check-it-list-spe-history in-info-history">27.07.2018</li>
                                        <li class="check-it-list-spe-history">Bed(s):</li><li class="check-it-list-spe-history in-info-history">2</li>
                                        <li class="check-it-list-spe-history">Dorm(s):</li><li class="check-it-list-spe-history in-info-history">0</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-sm-3-history">
                        <div class="panel panel-default booking-box-history panel-history panel-default-history">
                            <div class="panel-body panel-body-history">
                                <div class="row row-history">
                                    <div class="col-sm-12 month-opening col-sm-12-history">
                                        <h5>Booking status</h5>
                                        <p class="info-listing-history">Failed payment</p>
                                    </div>
                                </div>
                                <div class="row row-history">
                                    <div class="col-sm-12 col-sm-12-extra col-sm-12-history">
                                        <button type="button" class="btn btn-list-history">Pay again <span class="glyphicon glyphicon-refresh"></span></button>
                                        <button type="button" class="btn btn-list-history">Edit booking <span class="glyphicon glyphicon-wrench"></span></button>
                                        <button type="button" class="btn btn-list-history">Delete booking <span class="glyphicon glyphicon-trash"></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default text-left panel-history panel-default-history">
            <div class="panel-body panel-body-history">
                <div class="row content row-history">
                    <div class="col-sm-2 col-sm-2-history">
                        <img src="huette.jpg" class="img-responsive img-thumbnail img-thumbnail-history" style="width:100%" alt="Image">
                    </div>
                    <div class="col-sm-7 text-left col-sm-7-history">
                        <h3 class="headliner-cabinname">Rappenseeh&uuml;tte - Allg&auml;uer Alps</h3>
                        <div class="row row-history">
                            <div class="col-sm-12 col-sm-12-history">
                                <div class="form-group row row-history">
                                    <ul class="payment-options">
                                        <li class="check-it-list-spe-history">Bookingnumber:</li><li class="check-it-list-spe-history in-info-history">KEH-18.110365</li>
                                        <li class="check-it-list-spe-history">Arrival:</li><li class="check-it-list-spe-history in-info-history">26.07.2018</li>
                                        <li class="check-it-list-spe-history">Depature:</li><li class="check-it-list-spe-history in-info-history">27.07.2018</li>
                                        <li class="check-it-list-spe-history">Bed(s):</li><li class="check-it-list-spe-history in-info-history">2</li>
                                        <li class="check-it-list-spe-history">Dorm(s):</li><li class="check-it-list-spe-history in-info-history">0</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-sm-3-history">
                        <div class="panel panel-default booking-box-history panel-history panel-default-history">
                            <div class="panel-body panel-body-history">
                                <div class="row row-history" >
                                    <div class="col-sm-12 month-opening col-sm-12-history">
                                        <h5>Booking status</h5>
                                        <p class="info-listing-history">Wait for an message</p>
                                    </div>
                                </div>
                                <div class="row row-history">
                                    <div class="col-sm-12 col-sm-12-extra col-sm-12-history">
                                        <button type="button" class="btn btn-list-history">Open chat <span class="glyphicon glyphicon-envelope"></span></button>
                                        <button type="button" class="btn btn-list-history">Edit inquiry <span class="glyphicon glyphicon-wrench"></span></button>
                                        <button type="button" class="btn btn-list-history">Cancel inquiry <span class="glyphicon glyphicon-trash"></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default text-left panel-history panel-default-history">
            <div class="panel-body panel-body-history">
                <div class="row content row-history">
                    <div class="col-sm-2 col-sm-2-history">
                        <img src="huette.jpg" class="img-responsive img-thumbnail img-thumbnail-history" style="width:100%" alt="Image">
                    </div>
                    <div class="col-sm-7 text-left col-sm-7-history">
                        <h3 class="headliner-cabinname">Rappenseeh&uuml;tte - Allg&auml;uer Alps</h3>
                        <div class="row row-history">
                            <div class="col-sm-12 col-sm-12-history">
                                <div class="form-group row row-history">
                                    <ul class="payment-options">
                                        <li class="check-it-list-spe-history">Bookingnumber:</li><li class="check-it-list-spe-history in-info-history">KEH-18.110365</li>
                                        <li class="check-it-list-spe-history">Arrival:</li><li class="check-it-list-spe-history in-info-history">26.07.2018</li>
                                        <li class="check-it-list-spe-history">Depature:</li><li class="check-it-list-spe-history in-info-history">27.07.2018</li>
                                        <li class="check-it-list-spe-history">Bed(s):</li><li class="check-it-list-spe-history in-info-history">2</li>
                                        <li class="check-it-list-spe-history">Dorm(s):</li><li class="check-it-list-spe-history in-info-history">0</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-sm-3-history">
                        <div class="panel panel-default booking-box-history panel-history panel-default-history">
                            <div class="panel-body panel-body-history">
                                <div class="row row-history">
                                    <div class="col-sm-12 month-opening col-sm-12-history">
                                        <h5>Booking status</h5>
                                        <p class="info-listing-history">Inquiry accepted</p>
                                    </div>
                                </div>
                                <div class="row row-history">
                                    <div class="col-sm-12 col-sm-12-extra col-sm-12-history">
                                        <button type="button" class="btn btn-list-history">Do your payment! <span class="glyphicon glyphicon-usd"></span></button>
                                        <button type="button" class="btn btn-list-history">Open chat <span class="glyphicon glyphicon-envelope"></span></button>
                                        <button type="button" class="btn btn-list-history">Edit inquiry <span class="glyphicon glyphicon-wrench"></span></button>
                                        <button type="button" class="btn btn-list-history">Cancel inquiry <span class="glyphicon glyphicon-trash"></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><br><br>
</main>
<div>
    <div id="mountain"><img src="bergsilhouette-grau.png" class="img-responsive" alt="image"></div>
    <div id="over-footer"></div>
</div>
<footer class="container-fluid text-center container-fluid-history">
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
</body>
</html>