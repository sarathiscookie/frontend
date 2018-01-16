<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

    <form method="POST" action="{{ route('search') }}">
        {{ csrf_field() }}

        <div class="navbar-form navbar-left form-group " id="prefetch">
            <input type="text" class="form-control typeahead" name="cabinname" id="cabinname" placeholder="Cabin Search">
        </div>

        @inject('services', 'App\Http\Controllers\SearchController')

        <ul class="nav navbar-nav">

            @if($services->country())
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="checkbox" aria-haspopup="true" aria-expanded="false">Country <span class="caret"></span></a>
                    <ul class="dropdown-menu drop-height">
                        @foreach($services->country() as $land)
                            <li><a href="#"><input type="checkbox" name="country[]"> {{ $land->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
            @endif

            @if($services->regions())
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="checkbox" aria-haspopup="true" aria-expanded="false">Region <span class="caret"></span></a>
                    <ul class="dropdown-menu drop-height">
                        @foreach($services->regions() as $region)
                            <li><a href="#"><input type="checkbox" name="region[]"> {{ $region->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
            @endif

            @if($services->facility())
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="checkbox" aria-haspopup="true" aria-expanded="false">Facility <span class="caret"></span></a>
                    <ul class="dropdown-menu drop-height">
                        @foreach($services->facility() as $facility)
                            <li><a href="#"><input type="checkbox" name="facility[]"> {{ $facility }}</a></li>
                        @endforeach
                    </ul>
                </li>
            @endif

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Managed <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="#"><input type="checkbox" name="managed[]"> Cabins to sleep</a></li>
                    <li><a href="#"><input type="checkbox" name="managed[]"> Managed</a></li>
                </ul>
            </li>

            @if($services->openSeasons())
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Seasons <span class="caret"></span></a>
                    <ul class="dropdown-menu drop-height">
                        @foreach($services->openSeasons() as $seasonOpen)
                            <li><a href="#"><input type="checkbox" name="seasons[]"> {{ $seasonOpen }}</a></li>
                        @endforeach
                    </ul>
                </li>
            @endif

        </ul>

        <div class="navbar-form navbar-right form-group">
            <button type="submit" class="btn btn-default">Search</button>
        </div>

    </form>

</div><!-- /.navbar-collapse -->