<div class="cookie-banner">
    <p class="cookie-message">
        {!! __('cookies.content') !!}
    </p>
    <a href="{{ route('data.protection') }}" class="btn btn-outline-default">{{ __('cookies.buttons.more') }}</a>
    <button type="button" class="btn btn-default" onclick="acceptCookies()">{{ __('cookies.buttons.accept') }}</button>
</div>