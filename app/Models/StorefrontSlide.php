<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class StorefrontSlide extends Model
{
    protected $fillable = [
        'badge',
        'title',
        'description',
        'image_path',
        'primary_button_text',
        'primary_button_url',
        'secondary_button_text',
        'secondary_button_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'badge' => 'array',
        'title' => 'array',
        'description' => 'array',
        'primary_button_text' => 'array',
        'secondary_button_text' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function activeSlides()
    {
        if (! Schema::hasTable('storefront_slides')) {
            return collect();
        }

        return static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function localized(string $field, ?string $locale = null, ?string $fallback = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $value = $this->{$field} ?? null;

        if (is_array($value)) {
            return (string) (
                $value[$locale]
                ?? $value['ar']
                ?? $value['en']
                ?? $value['he']
                ?? $fallback
                ?? ''
            );
        }

        return (string) ($value ?: $fallback ?: '');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
