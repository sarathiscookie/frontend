@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-register">
            <div class="panel panel-default panel-register">
                <div class="panel-heading">{{ __('register.panelHeading') }}</div> <br>

                <div class="panel-body">
                    <form class="form-horizontal" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('firstName') ? ' has-error' : '' }}">
                            <label for="firstName" class="col-md-4 control-label">{{ __('register.firstname') }}</label>

                            <div class="col-md-6">
                                <input id="firstName" type="text" class="form-control" name="firstName" value="{{ old('firstName') }}" placeholder="{{ __('register.firstnamePlaceholder') }}"  autofocus>

                                @if ($errors->has('firstName'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('firstName') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('lastName') ? ' has-error' : '' }}">
                            <label for="lastName" class="col-md-4 control-label">{{ __('register.lastname') }}</label>

                            <div class="col-md-6">
                                <input id="lastName" type="text" class="form-control" name="lastName" value="{{ old('lastName') }}" placeholder="{{ __('register.lastnamePlaceholder') }}"  autofocus>

                                @if ($errors->has('lastName'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('lastName') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">{{ __('register.email') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="{{ __('register.emailPlaceholder') }}" >

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">{{ __('register.password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" placeholder="******" >

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password-confirm" class="col-md-4 control-label">{{ __('register.cpassword') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="******" >
                            </div>
                        </div>


                        <div class="col-md-4 check-register">
                            <div class="checkbox {{ $errors->has('dataProtection') ? ' has-error' : '' }}">
                                <label for="dataProtection">
                                    <input type="checkbox" name="dataProtection" id="dataProtection" class="check-it-register" value="1"> <a class="confirm-terms-register" style="text-decoration: none; cursor: pointer;">{{ __('register.dataProtection') }}</a>
                                </label>
                                @if ($errors->has('dataProtection'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('dataProtection') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 check-register">
                            <div class="checkbox {{ $errors->has('termsService') ? ' has-error' : '' }}">
                                <label for="termsService">
                                    <input type="checkbox" name="termsService" id="termsService" class="check-it-register" value="1"> <a class="confirm-terms-register" style="text-decoration: none; cursor: pointer;">{{ __('register.termsOfService') }}</a>
                                </label>
                                @if ($errors->has('termsService'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('termsService') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 check-register">
                            <div class="checkbox {{ $errors->has('newsletter') ? ' has-error' : '' }}">
                                <label for="newsletter">
                                    <input type="checkbox" name="newsletter" id="newsletter" class="check-it-register" value="1"> {{ __('register.newsletterSignup') }}
                                </label>
                                @if ($errors->has('newsletter'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('newsletter') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4 div-btn-register">
                                <button type="submit" class="btn btn-primary btn-register">
                                    {{ __('register.registerButton') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection