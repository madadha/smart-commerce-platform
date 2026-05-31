<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'country_code',
        'exchange_rate',
        'symbol_position',
        'decimal_places',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'exchange_rate' => 'decimal:6',
        'decimal_places' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
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
        return $this->name[$locale] ?? $this->name['en'] ?? $this->code;
    }

    public function format(float|int $amount): string
    {
        $formatted = number_format((float) $amount, $this->decimal_places);

        return $this->symbol_position === 'after'
            ? $formatted . ' ' . $this->symbol
            : $this->symbol . ' ' . $formatted;
    }
}