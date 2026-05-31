<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductOption extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'slug',
        'type',
        'values',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'values' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductOption $option): void {
            if (blank($option->slug)) {
                $option->slug = Str::slug(
                    $option->getName('en') ?: $option->getName('ar') ?: 'option'
                );
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getName(string $locale = 'ar'): string
    {
        $name = $this->name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($name)) {
            return 'Option';
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? 'Option';
    }

    public function getValues(): array
    {
        $values = $this->values;

        if (is_string($values)) {
            $decoded = json_decode($values, true);
            $values = is_array($decoded) ? $decoded : [];
        }

        return is_array($values) ? $values : [];
    }
}