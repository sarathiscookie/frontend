<div class="container-fluid bg-3 text-center container-fluid-home">
    <div class="col-md-2 col-md-2-home"></div>
    <div class="col-md-8 col-md-8-home" id="list-filter-home">
        <nav class="navbar navbar-default navbar-default-home">
            <div class="container-fluid container-fluid-home" id="filter-line-home">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" ><!--Mobile Navigation Burger-->
                        <span class="mobile-menu">Filter</span>
                        <span class="sr-only"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <form action="{{ route('search') }}" method="POST" class="navbar-form navbar-left" id="search-nav-home">

                        {{ csrf_field() }}

                        @inject('services', 'App\Http\Controllers\SearchController')

                        <div class="form-group navbar-form navbar-left" id="prefetch">
                            <input type="text" class="form-control-home typeahead" name="cabinname" id="cabinname" placeholder="Search Cabin">
                        </div>

                        <ul class="nav navbar-nav" id="filter-home">

                            @if($services->country())
                                <li class="dropdown">
                                    <!-- Dropdown in Filter -->
                                    <a href="#" class="dropdown-toggle dropdown-toggle-home" data-toggle="dropdown">Country <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-home">
                                        @foreach($services->country() as $land)
                                            <li class="check-it-list-home"><input type="checkbox" class="check-it-home" name="country[]" value="{{ $land->name }}"> {{ $land->name }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if($services->regions())
                               <li class="dropdown">
                                   <!-- Dropdown in Filter -->
                                   <a href="#" class="dropdown-toggle dropdown-toggle-home" data-toggle="dropdown">Region<span class="caret"></span></a>
                                   <ul class="dropdown-menu dropdown-menu-home drop-height">
                                       @foreach($services->regions() as $region)
                                           <li class="check-it-list-home"><input type="checkbox" name="region[]" value="{{ $region->name }}" class="check-it-home"> {{ $region->name }}
                                               @if($services->cabinCount($region->name))
                                                   <span class="badge">{!! $services->cabinCount($region->name) !!}</span>
                                                @endif
                                           </li>
                                       @endforeach
                                   </ul>
                               </li>
                            @endif

                            @if($services->facility())
                                <li class="dropdown">
                                    <!-- Dropdown in Filter -->
                                    <a href="#" class="dropdown-toggle dropdown-toggle-home" data-toggle="dropdown">Facility<span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-home drop-height">
                                        @foreach($services->facility() as $facilityKey => $facility)
                                            <li class="check-it-list-home"><input type="checkbox" name="facility[]" value="{{ $facilityKey }}" class="check-it-home"> {{ $facility }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if($services->openSeasons())
                                <li class="dropdown">
                                    <!-- Dropdown in Filter -->
                                    <a href="#" class="dropdown-toggle dropdown-toggle-home" data-toggle="dropdown">Opening hours<span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-home">
                                        @foreach($services->openSeasons() as $seasonOpen)
                                            <li  class="check-it-list-home"><input type="checkbox" name="seasons[]" value="open" class="check-it-home"> {{ $seasonOpen }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                                {{--<li class="dropdown">
                                    <a href="#" class="dropdown-toggle dropdown-toggle-home" data-toggle="dropdown">Managed <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-home">
                                        <li class="check-it-list-home"><input type="checkbox" name="managed[]" class="check-it-home"> Cabins to sleep</li>
                                        <li class="check-it-list-home"><input type="checkbox" name="managed[]" class="check-it-home"> Managed</li>
                                    </ul>
                                </li>--}}

                        </ul>

                        <div class="form-group navbar-form navbar-right" id="navbar-right-filter-home">
                            <button type="submit" class="btn btn-default-home btn-filter-home">Filter Cabins</button>
                        </div>

                    </form>
                </div>
            </div>
        </nav>

    </div>
    <div class="col-md-2 col-md-2-home"></div>
</div>









