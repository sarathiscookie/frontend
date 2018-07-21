@extends('layouts.app')

@section('title', 'User Profile')

@section('jumbotron')
    <div class="jumbotron">
        <div class="container text-center">
            <img src="{{ asset('storage/img/sunset.jpg') }}" class="img-responsive titlepicture" alt="titlepicture">
            <h1 id="headliner-home">{{ __('userProfile.titleHeading') }}<br>Huetten-Holiday.de</h1>
        </div>
    </div>

    <div class="clearfix"></div>
@endsection

@section('content')
    <div class="container-fluid bg-3 text-center container-fluid-data">
        <div class="col-md-2 col-md-2-data"></div>
        <div class="col-md-8 col-md-8-data" id="list-filter-data">
            <nav class="navbar navbar-default navbar-default-data">
                <h2 class="cabin-head-data">{{ __('userProfile.dataOverview') }}</h2>
            </nav>
        </div>
        <div class="col-md-2 col-md-2-data"></div>
    </div>
    <main>
        <div class="container-fluid text-center container-fluid-data">
            <div class="panel panel-default text-left panel-data panel-default-data">
                <div class="panel-body panel-body-data">
                    <div class="col-sm-12 month-opening-data col-sm-12-data">

                        <div class="col-md-12">
                            <div class="col-md-8">
                                <form method="POST" action="{{ route('user.profile.store') }}">
                                    {{ csrf_field() }}

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group{{ $errors->has('salutation') ? ' has-error' : '' }}">
                                                <label for="salutation" class="control-label">{{ __('register.salutation') }}</label>

                                                <select class="form-control" name="salutation" id="salutation">
                                                    <option>{{ __('register.salutationChoose') }}</option>
                                                    <option value="mr" @if(Auth::user()->salutation == 'mr' || old('salutation') == 'mr') selected="selected" @endif>{{ __('register.salutationOne') }}</option>
                                                    <option value="mrs" @if(Auth::user()->salutation == 'mrs' || old('salutation') == 'mrs') selected="selected" @endif>{{ __('register.salutationTwo') }}</option>
                                                </select>

                                                @if ($errors->has('salutation'))
                                                    <span class="help-block"><strong>{{ $errors->first('salutation') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group{{ $errors->has('company') ? ' has-error' : '' }}">
                                                <label for="company" class="control-label">{{ __('register.company') }}</label>

                                                <input id="company" type="text" class="form-control" name="company" value="{{ old('company', Auth::user()->company) }}" maxlength="100" placeholder="{{ __('register.companyPlaceholder') }}"  autofocus autocomplete="off">

                                                @if ($errors->has('company'))
                                                    <span class="help-block"><strong>{{ $errors->first('company') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group{{ $errors->has('firstName') ? ' has-error' : '' }}">
                                                <label for="firstName" class="control-label">{{ __('register.firstname') }}  <span class="required">*</span></label>

                                                <input id="firstName" type="text" class="form-control" name="firstName" value="{{ old('firstName', Auth::user()->usrFirstname) }}" placeholder="{{ __('register.firstnamePlaceholder') }}"  autofocus autocomplete="off" maxlength="255">

                                                @if ($errors->has('firstName'))
                                                    <span class="help-block"><strong>{{ $errors->first('firstName') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group{{ $errors->has('lastName') ? ' has-error' : '' }}">
                                                <label for="lastName" class="control-label">{{ __('register.lastname') }}  <span class="required">*</span></label>

                                                <input id="lastName" type="text" class="form-control" name="lastName" value="{{ old('lastName', Auth::user()->usrLastname) }}" placeholder="{{ __('register.lastnamePlaceholder') }}"  autofocus autocomplete="off" maxlength="255">

                                                @if ($errors->has('lastName'))
                                                    <span class="help-block"><strong>{{ $errors->first('lastName') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                                                <label for="country" class="control-label"> {{ __('cart.country') }} <span class="required">*</span></label>
                                                <select class="form-control" id="country" name="country">
                                                    <option value="0"> {{ __('cart.chooseCountry') }} </option>
                                                    @foreach($country as $land)
                                                        <option value="{{ $land->name }}" @if($land->name == Auth::user()->usrCountry || old('country') == $land->name) selected="selected" @endif>{{ $land->name }}</option>
                                                    @endforeach
                                                </select>

                                                @if ($errors->has('country'))
                                                    <span class="help-block"><strong>{{ $errors->first('country') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('street') ? ' has-error' : '' }}">
                                                <label for="street" class="control-label"> {{ __('cart.street') }} <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="street" name="street" placeholder="{{ __('cart.streetPlaceholder') }}" maxlength="255" value="{{ old('street', Auth::user()->usrAddress) }}">

                                                @if ($errors->has('street'))
                                                    <span class="help-block"><strong>{{ $errors->first('street') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('city') ? ' has-error' : '' }}">
                                                <label for="city" class="control-label"> {{ __('cart.city') }} <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="city" name="city" placeholder="{{ __('cart.cityPlaceholder') }}" maxlength="255" value="{{ old('city', Auth::user()->usrCity) }}">

                                                @if ($errors->has('city'))
                                                    <span class="help-block"><strong>{{ $errors->first('city') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('zipcode') ? ' has-error' : '' }}">
                                                <label for="zipcode" class="control-label"> {{ __('cart.zipcode') }} <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="zipcode" name="zipcode" placeholder="{{ __('cart.zipcodePlaceholder') }}" maxlength="25" value="{{ old('zipcode', Auth::user()->usrZip) }}">

                                                @if ($errors->has('zipcode'))
                                                    <span class="help-block"><strong>{{ $errors->first('zipcode') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('mobile') ? ' has-error' : '' }}">
                                                <label for="mobile" class="control-label"> {{ __('cart.mobile') }}</label>
                                                <input type="text" class="form-control" id="mobile" name="mobile" placeholder="{{ __('cart.mobilePlaceholder') }}" maxlength="20" value="{{ old('mobile', Auth::user()->usrMobile) }}">

                                                @if ($errors->has('mobile'))
                                                    <span class="help-block"><strong>{{ $errors->first('mobile') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('phone') ? ' has-error' : '' }}">
                                                <label for="phone" class="control-label"> {{ __('cart.phone') }}</label>
                                                <input type="text" class="form-control" id="phone" name="phone" placeholder="{{ __('cart.phonePlaceholder') }}" maxlength="20" value="{{ old('phone', Auth::user()->usrTelephone) }}">

                                                @if ($errors->has('phone'))
                                                    <span class="help-block"><strong>{{ $errors->first('phone') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 pull-right">
                                            <button type="submit" class="btn btn-default pull-right">{{ __('userProfile.updateButton') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-4">
                                <div class="row">
                                    <span class="label label-default">Money Balance</span> <span class="badge">42</span>
                                    <button class="btn btn-primary pull-right" type="button">
                                        Download
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div><br><br>
    </main>
@endsection

@push('scripts')

@endpush