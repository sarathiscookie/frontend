@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
    <div class="container">
        <div class="row row-log">
            <div class="col-md-8 col-md-8-log">
                <div class="panel panel-log panel-default">
                    <div class="panel-heading">{{ __('passwords.passwordResetHeading') }}</div>

                    <div class="panel-body panel-body-log">
                        @if (session()->has('passwordResetSuccess'))
                            <div class="alert alert-info">
                                {{ session()->get('passwordResetSuccess') }}
                            </div>
                        @endif
                        <br>
                        <form class="form-horizontal" method="POST" action="{{ route('reset.password.token') }}">
                            {{ csrf_field() }}

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">{{ __('register.email') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}"  autofocus>

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
                                    <input id="password" type="password" class="form-control" name="password">

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                <label for="password-confirm" class="col-md-4 control-label">{{ __('register.cpassword') }}</label>
                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation">

                                    @if ($errors->has('password_confirmation'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4 div-btn-log">
                                    <button type="submit" class="btn btn-primary btn-log">
                                        {{ __('passwords.passwordResetHeading') }}
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
