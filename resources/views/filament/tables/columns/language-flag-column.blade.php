@if($getState())
@php($languageCode = strtolower(explode('-', $getState())[0] ?: $getState()))
@dd($languageCode)
<div class="fi-ta-text w-full gap-y-1 px-3 py-4">
    <img src="data:image/svg+xml;base64,{{ base64_encode(@file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $languageCode . '.svg'))) }}" alt="{{ Locale::getDisplayLanguage($languageCode, app()->getLocale()) }}" class="inline-block h-5 w-5"> {{ Locale::getDisplayLanguage($languageCode, app()->getLocale()) }}
</div>
@endif