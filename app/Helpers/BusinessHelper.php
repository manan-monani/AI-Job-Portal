<?php

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('business_config')) {
    /**
     * Get the business setting value for the given key.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function business_config($key, $default = null)
    {
        // Cache settings for performance, clear on update
        // Using a short cache time or forever with clearing events is fine.
        // For simplicity, let's query DB directly or use a simple static cache if request-scoped,
        // but DB query cache is better.

        // Let's use a simple static cache within the request to avoid repeated DB calls for the same key
        static $settings = null;

        if ($settings === null) {
            $settings = Cache::remember('business_settings_all', 60 * 24, function () {
                return BusinessSetting::all()->pluck('value', 'key')->toArray();
            });
        }

        return $settings[$key] ?? $default;
    }
}
