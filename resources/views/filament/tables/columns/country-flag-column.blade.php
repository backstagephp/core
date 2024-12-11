@if($getState())
@php($countryCode = strtolower(explode('-', $getState())[1] ?: $getState()))
<div class="fi-ta-text w-full gap-y-1 px-3 py-4">
    <img src="data:image/svg+xml;base64,{{ base64_encode(@file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $countryCode . '.svg'))) }}" alt="{{ Locale::getDisplayRegion('-'.$countryCode, app()->getLocale()) }}" class="inline-block h-5 w-5"> {{ Locale::getDisplayRegion('-'.$countryCode, app()->getLocale()) }}
</div>
@endif