<?php

if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        $keys = explode('.', $key);
        $setting = \Backstage\Models\Setting::where('slug', $keys[0] ?? null)->with('fields')->first();

        if (! $setting) {
            return $default;
        }

        if (! isset($keys[1])) {
            return $setting->setting();
        }

        return $setting->setting($keys[1]) ?? $default;
    }
}

if (!function_exists('render_field_preview')) {
    /**
     * Render a field preview based on the field type and value
     *
     * @param string $type The field type
     * @param mixed $value The field value
     * @param string|null $name The field name (optional)
     * @return string
     */
    function render_field_preview(string $type, mixed $value = null, ?string $name = null): string
    {
        $viewData = [
            'value' => $value,
            'name' => $name,
            'type' => $type
        ];

        $customTemplatePath = "field-previews.{$type}";

        if (view()->exists($customTemplatePath)) {
            return view($customTemplatePath, $viewData)->render();
        }

        $vendorTemplatePath = "vendor.backstage.field-previews.{$type}";

        if (view()->exists($vendorTemplatePath)) {
            return view($vendorTemplatePath, $viewData)->render();
        }

        $backstageTemplatePath = "backstage::field-previews.{$type}";

        if (view()->exists($backstageTemplatePath)) {
            return view($backstageTemplatePath, $viewData)->render();
        }
        
        return view('backstage::field-previews.default', $viewData)->render();
    }
}
