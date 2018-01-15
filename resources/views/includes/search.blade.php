<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

    <form method="POST" action="{{ route('search') }}">
        {{ csrf_field() }}

        <div class="navbar-form navbar-left form-group " id="prefetch">
            <input type="text" class="form-control typeahead" id="cabinname" placeholder="Cabin Search">
        </div>

        <ul class="nav navbar-nav">

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Country <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Region <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Cabins to sleep <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Managed Facility <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Particularities <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="#"><input type="checkbox" aria-label="..."> Action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Another action</a></li>
                    <li><a href="#"><input type="checkbox" aria-label="..."> Something else here</a></li>
                </ul>
            </li>

        </ul>

        <div class="navbar-form navbar-right form-group">
            <button type="submit" class="btn btn-default">Search</button>
        </div>

    </form>

</div><!-- /.navbar-collapse -->