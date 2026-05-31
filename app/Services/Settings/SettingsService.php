<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function all(): array
    {
        return Cache::rememberForever('settings.all', function () {
            return Setting::query()
                ->active()
                ->get()
                ->mapWithKeys(function (Setting $setting) {
                    return [
                        $setting->group . '.' . $setting->key => $setting->getTypedValue(),
                    ];
                })
                ->toArray();
        });
    }

    public function public(): array
    {
        return Cache::rememberForever('settings.public', function () {
            return Setting::query()
                ->active()
                ->public()
                ->get()
                ->mapWithKeys(function (Setting $setting) {
                    return [
                        $setting->group . '.' . $setting->key => $setting->getTypedValue(),
                    ];
                })
                ->toArray();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return $settings[$key] ?? $default;
    }

    public function set(
        string $group,
        string $key,
        mixed $value,
        string $type = 'text',
        bool $isPublic = false,
        bool $isActive = true,
        int $sortOrder = 0
    ): Setting {
        $setting = Setting::query()->updateOrCreate(
            [
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value,
                'type' => $type,
                'is_public' => $isPublic,
                'is_active' => $isActive,
                'sort_order' => $sortOrder,
            ]
        );

        $this->clearCache();

        return $setting;
    }

    public function clearCache(): void
    {
        Cache::forget('settings.all');
        Cache::forget('settings.public');
    }
}