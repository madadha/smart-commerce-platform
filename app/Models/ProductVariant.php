<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'barcode',
        'option_values',
        'media_file_id',
        'image',
        'price',
        'sale_price',
        'cost_price',
        'track_stock',
        'stock_quantity',
        'min_stock_quantity',
        'weight',
        'length',
        'width',
        'height',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'option_values' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'track_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'min_stock_quantity' => 'integer',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (ProductVariant $variant): void {
            if ($variant->is_default) {
                static::query()
                    ->where('product_id', $variant->product_id)
                    ->whereKeyNot($variant->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    public function getName(string $locale = 'ar'): string
    {
        $name = $this->name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (is_array($name)) {
            return $name[$locale]
                ?? $name['en']
                ?? $name['ar']
                ?? $this->sku
                ?? 'Variant';
        }

        return $this->sku ?? 'Variant';
    }

    public function getOptionValues(): array
    {
        $values = $this->option_values;

        if (is_string($values)) {
            $decoded = json_decode($values, true);
            $values = is_array($decoded) ? $decoded : [];
        }

        return is_array($values) ? $values : [];
    }

    public function getImageUrl(): ?string
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        return $this->mediaFile?->getUrl();
    }

    public function finalPrice(): float
    {
        return (float) ($this->sale_price ?: $this->price ?: $this->product?->finalPrice() ?: 0);
    }

    public function isInStock(): bool
    {
        if (! $this->track_stock) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    public function digitalCodes(): HasMany
{
    return $this->hasMany(ProductDigitalCode::class)
        ->orderBy('sort_order')
        ->orderBy('id');
}

public function availableDigitalCodes(): HasMany
{
    return $this->digitalCodes()
        ->where('status', \App\Enums\DigitalCodeStatus::Available->value)
        ->where('is_active', true);
}
}
