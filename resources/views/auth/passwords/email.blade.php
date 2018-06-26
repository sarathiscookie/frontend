@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row row-log">
        <div class="col-md-8 col-md-8-log">
            <div class="panel panel-log panel-default">
                <div class="panel-heading">Reset Password</div> <br>

                <div class="panel-body panel-body-log">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('reset.password.manually') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') || $errors->has('user') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">{{ __('login.email') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email') || $errors->has('user'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}  {{ $errors->first('user') }} </strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4 div-btn-log">
                                <button type="submit" class="btn btn-primary btn-log">
                                    Send Password Reset Link
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
