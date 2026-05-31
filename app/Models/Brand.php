<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'banner_image',
        'description',
        'website_url',
        'seo_title',
        'seo_description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Brand $brand): void {
            if (blank($brand->slug)) {
                $brand->slug = Str::slug(
                    $brand->getName('en') ?: $brand->getName('ar') ?: 'brand'
                );
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getName(string $locale = 'ar'): string
    {
        $name = $this->name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($name)) {
            return 'Brand';
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? 'Brand';
    }

    public function getDescription(string $locale = 'ar'): ?string
    {
        $description = $this->description;

        if (is_string($description)) {
            $decoded = json_decode($description, true);
            $description = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($description)) {
            return null;
        }

        return $description[$locale]
            ?? $description['en']
            ?? $description['ar']
            ?? null;
    }
}