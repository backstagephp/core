<?php

if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        $keys = explode('.', $key);
        $setting = \Vormkracht10\Backstage\Models\Setting::where('slug', $keys[0] ?? null)->first();

        if (! $setting) {
            return $default;
        }

        if (! isset($keys[1])) {
            return $setting->values;
        }

        return $setting->values[$keys[1] ?? null] ?? $default;
    }
}
