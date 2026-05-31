<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'sku',
        'item_type',
        'quantity',
        'unit_price',
        'discount_total',
        'tax_total',
        'line_total',
        'options',
        'digital_code_id',
        'notes',
    ];

    protected $casts = [
        'product_name' => 'array',
        'options' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (OrderItem $item): void {
            $item->line_total = max(
                0,
                ((float) $item->unit_price * (int) $item->quantity)
                - (float) $item->discount_total
                + (float) $item->tax_total
            );
        });

        static::saved(function (OrderItem $item): void {
            $item->order?->recalculateTotals();
        });

        static::deleted(function (OrderItem $item): void {
            $item->order?->recalculateTotals();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function digitalCode(): BelongsTo
    {
        return $this->belongsTo(ProductDigitalCode::class, 'digital_code_id');
    }

    public function getProductName(string $locale = 'ar'): string
    {
        $name = $this->product_name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($name)) {
            return 'Product';
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? 'Product';
    }
}