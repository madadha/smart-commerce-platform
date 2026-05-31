<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'logo',
        'email',
        'phone',
        'website',
        'country_id',
        'notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Company $company): void {
            if (blank($company->slug)) {
                $company->slug = Str::slug($company->getName('en') ?: $company->getName('ar') ?: 'company');
            }
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getName(string $locale = 'ar'): string
    {
        return $this->name[$locale] ?? $this->name['en'] ?? $this->name['ar'] ?? 'Company';
    }
}