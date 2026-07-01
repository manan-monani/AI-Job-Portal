<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Traits\LogsActivity;
use App\Traits\UploadsMedia;
use Illuminate\Support\Facades\Cache;

class BusinessSettingService
{
    use LogsActivity;
    use UploadsMedia;

    public function getSettings(array $keys = [], bool $formatForFrontend = false)
    {
        $query = BusinessSetting::query();

        if (! empty($keys)) {
            $query->whereIn('key', $keys);
        }

        $settings = $query->pluck('value', 'key');

        if ($formatForFrontend) {
            return $settings->map(function ($value, $key) {
                if (in_array($key, ['logo_url', 'favicon_url', 'cover_url', 'app_logo_light', 'app_logo_dark', 'app_favicon']) && $value && ! str_starts_with($value, 'http')) {
                    return '/storage/'.$value;
                }

                return $value;
            })->toArray();
        }

        return $settings;
    }

    public function updateBranding(array $data, array $files = []): void
    {
        foreach ($files as $key => $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                // Delete old file if exists
                $oldPath = BusinessSetting::where('key', $key)->value('value');
                if ($oldPath && ! str_starts_with($oldPath, 'http')) {
                    $this->deleteOne($oldPath);
                }

                $data[$key] = $this->uploadOne($file, 'branding');
            }
        }

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $value = '';
            }

            // Fix for /storage/ prefix duplication
            if (in_array($key, ['logo_url', 'favicon_url', 'cover_url', 'app_logo_light', 'app_logo_dark', 'app_favicon']) && is_string($value) && str_starts_with($value, '/storage/')) {
                $value = substr($value, 9);
            }

            BusinessSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget('business_settings_all');
        $this->logActivity('update_branding', 'Updated branding settings');
    }

    public function updateSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            BusinessSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        Cache::forget('business_settings_all');
        $this->logActivity('update_settings', 'Updated business settings: '.implode(', ', array_keys($data)));
    }
}
