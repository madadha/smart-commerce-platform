<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'banner_image',
        'seo_title',
        'seo_description',
        'is_active',
        'show_in_menu',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (blank($category->slug)) {
                $category->slug = Str::slug(
                    $category->getName('en') ?: $category->getName('ar') ?: 'category'
                );
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMenu($query)
    {
        return $query->where('show_in_menu', true);
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
            return 'Category';
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? 'Category';
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

    public function getFullPath(string $locale = 'ar'): string
    {
        $names = [];
        $category = $this;

        while ($category) {
            array_unshift($names, $category->getName($locale));
            $category = $category->parent;
        }

        return implode(' > ', $names);
    }
}