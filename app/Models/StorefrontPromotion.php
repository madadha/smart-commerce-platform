<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class StorefrontPromotion extends Model
{
    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'button_text',
        'button_url',
        'image_path',
        'placement',
        'style',
        'background_color',
        'text_color',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'eyebrow' => 'array',
        'title' => 'array',
        'description' => 'array',
        'button_text' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public static function activeForPlacement(string $placement, int $limit = 6)
    {
        if (! Schema::hasTable('storefront_promotions')) {
            return collect();
        }

        return static::query()
            ->published()
            ->where('placement', $placement)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
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
        return $this->image_path ? asset('storage/' . ltrim($this->image_path, '/')) : null;
    }
}
