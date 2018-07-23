<!DOCTYPE html>
<html lang="de">
<head>
    <title>Huetten-Holiday.de Wartungsarbeiten</title>
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
    @media (min-width:1351px) {main {
        margin-left:10%;
        margin-right:10%;
    }}
    /*Pictures responsive*/
    .img-responsive{
        width:100%;
        max-width:100%;
        display:block;
        height:auto;
    }

    /*Logo in the Navigation*/
    #nav-logo {
        width:150px;
        height:auto;
        padding-top:15px;
        padding-bottom:15px;
        padding-right:0;
    }
    .navbar-inverse {
        background-color:#FFF !important;
        border:none;
    }
    /*Navbar always on the top*/
    .navbar-fixed-top{
        position:absolute !important;
    }
    .navbar-header {
        margin-left:10% !important;
    }

    @media (max-width:1349px){.navbar-header {
        margin-left:0 !important;
    }}
    /*small screen nav list we have three different sizes for nav:945px 1510 >1100   */
    @media (max-width: 1030px){ .navbar-header{
        float:none;
    }}
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
    /*Headliner Footer*/
    .footer-headliner{
        border-bottom:2px solid;
        border-top:2px solid;
    }
    /*end for all*/
    /*Text in titlepicture*/
    #headliner-home{
        position:absolute;
        top:180px;
        margin-left:10%;
        margin-right:10%;
        text-align:left;
        opacity:0.7;
        font-size:90px;
        color:#fff;
    }
    @media (max-width: 1349px){ #headliner-home{
        margin-right:0;
        padding-right:15px;
    }}
    @media (max-width: 767px){ #headliner-home{
        margin-right:0;
        padding-right:15px;
        top:110px;
        font-size:50px;
    }}
    /*Main*/
    .row-simple{
        width: 100%;
        margin: 0;
    }
    @media (max-width: 1350px){.col-simple{
        padding-left: 15px;
        padding-right: 15px;
    }}
    @media (max-width: 767px){.col-simple{
        padding-left: 5px;
        padding-right: 5px;
    }}

</style>
<body id="myPage">
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid container-fluid-home">
        <div class="navbar-header">
            <a href="#">
                <img src="{{ asset('storage/img/logo.png') }}" class="navbar-brand" id="nav-logo" alt="huetten-holiday logo">
            </a>
        </div>
    </div>
</nav>
<div class="jumbotron">
    <div class="container text-center">
        <img src="{{ asset('storage/img/namloser-wetter-spitz.jpg')}}" class="img-responsive titlepicture" alt="titlepicture">
        <h1 id="headliner-home">Mit uns<br>liegen Sie richtig</h1>
    </div>
</div>
<main>
    <div class="row row-simple">
        <div class="col-simple">
            <article>
                <h1>Huetten-Holiday.de - Wartungsarbeiten</h1>
                <p>Wegen Wartungsarbeiten ist unser Portal am <strong>Montag, den 23.07.2018 von 07.00 bis 18.00 Uhr</strong> nicht erreichbar.<br>
                    Wir bitten Sie, Ihre Änderungen oder Buchungen ab Dienstag den 24.07.2018 wieder vorzunehmen. <br>
                    Sollten Sie dringende Änderungen für Dienstag oder Mittwoch haben, schreiben Sie unserem Serviceteam unter <a href="mailto:service@huetten-holiday.de">service@huetten-holiday.de</a> </p>
            </article>
        </div>
    </div><br /><br />
</main>
<div>
    <div id="mountain"><img src="{{asset('storage/img/Bergsilhouette-grau.png')}}" class="img-responsive" alt="mountain background"></div>
    <div id="over-footer"></div>
</div>
<footer class="container-fluid container-fluid-home text-center">
</footer>
</body>
</html>