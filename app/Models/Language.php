<?php

namespace App\Models;

use App\Support\Localization\ActiveLanguageRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Language extends Model
{
    protected $fillable = [
        'name',
        'native_name',
        'code',
        'direction',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    public function isLtr(): bool
    {
        return $this->direction === 'ltr';
    }

    protected static function booted(): void
    {
        static::saving(function (Language $language): void {
            if (! $language->is_active && $language->is_default) {
                throw ValidationException::withMessages([
                    'is_active' => 'The default language must remain active.',
                ]);
            }

            if ($language->exists && $language->isDirty('is_active') && ! $language->is_active) {
                $hasAnotherActiveLanguage = static::query()
                    ->whereKeyNot($language->getKey())
                    ->where('is_active', true)
                    ->exists();

                if (! $hasAnotherActiveLanguage) {
                    throw ValidationException::withMessages([
                        'is_active' => 'At least one storefront language must remain active.',
                    ]);
                }
            }
        });

        static::saved(fn (): mixed => app(ActiveLanguageRegistry::class)->forget());
        static::deleted(fn (): mixed => app(ActiveLanguageRegistry::class)->forget());
    }
}
