<?php

namespace App\Support\Localization;

use App\Models\Language;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ActiveLanguageRegistry
{
    public const SUPPORTED_CODES = ['ar', 'he', 'en'];

    private const CACHE_KEY = 'storefront.active-languages';

    /**
     * @return Collection<int, Language>
     */
    public function active(): Collection
    {
        try {
            if (! Schema::hasTable('languages')) {
                return collect();
            }

            return Cache::rememberForever(self::CACHE_KEY, fn (): Collection => Language::query()
                ->active()
                ->ordered()
                ->whereIn('code', self::SUPPORTED_CODES)
                ->get());
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @return array<int, string>
     */
    public function codes(): array
    {
        $codes = $this->active()->pluck('code')->values()->all();

        return $codes !== [] ? $codes : self::SUPPORTED_CODES;
    }

    public function defaultCode(): string
    {
        $languages = $this->active();

        return $languages->firstWhere('is_default', true)?->code
            ?? $languages->first()?->code
            ?? 'ar';
    }

    public function resolve(?string $locale): string
    {
        return in_array($locale, $this->codes(), true)
            ? $locale
            : $this->defaultCode();
    }

    public function isActive(string $locale): bool
    {
        return in_array($locale, $this->codes(), true);
    }

    public function direction(string $locale): string
    {
        return $this->active()->firstWhere('code', $locale)?->direction
            ?? (in_array($locale, ['ar', 'he'], true) ? 'rtl' : 'ltr');
    }

    public function shouldDisplayStatePath(string $statePath): bool
    {
        $locale = last(explode('.', $statePath));

        return ! in_array($locale, self::SUPPORTED_CODES, true) || $this->isActive($locale);
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
