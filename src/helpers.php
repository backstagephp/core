<?php

use Backstage\Models\Setting;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        $keys = explode('.', $key);
        $setting = Setting::where('slug', $keys[0] ?? null)->with('fields')->first();

        if (! $setting) {
            return $default;
        }

        if (! isset($keys[1])) {
            return $setting->setting();
        }

        if (! ($value = $setting->setting($keys[1]))) {
            return $default;
        }

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return $value;
        }

        if (! isset($value['type']) || $value['type'] !== 'doc') {
            return $value;
        }

        $value = RichContentRenderer::make($value)->toHtml();

        return $value;
    }
}

if (! function_exists('flag_path')) {
    function flag_path(string $code): string
    {
        $vendorPath = base_path('vendor/backstage/cms/resources/img/flags/' . $code . '.svg');

        if (file_exists($vendorPath)) {
            return $vendorPath;
        }

        return dirname(base_path()) . '/cms/packages/core/resources/img/flags/' . $code . '.svg';
    }
}

if (! function_exists('localized_country_name')) {
    function localized_country_name(string $code, ?string $locale = null): string
    {
        $code = strtolower(explode('-', $code)[1] ?? $code);

        return Locale::getDisplayRegion('-' . $code, $locale ?? app()->getLocale());
    }
}

if (! function_exists('localized_language_name')) {
    function localized_language_name(string $code, ?string $locale = null): string
    {
        $code = strtolower(explode('-', $code)[0]);

        return Locale::getDisplayLanguage($code, $locale ?? app()->getLocale());
    }
}
