<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'order_item_id',
        'product_id',
        'product_variant_id',
        'item_name',
        'sku',
        'quantity',
        'unit_price',
        'discount_total',
        'tax_total',
        'line_total',
        'options',
        'notes',
    ];

    protected $casts = [
        'item_name' => 'array',
        'options' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item): void {
            $item->line_total = max(
                0,
                ((float) $item->unit_price * (int) $item->quantity)
                - (float) $item->discount_total
                + (float) $item->tax_total
            );
        });

        static::saved(function (InvoiceItem $item): void {
            $item->invoice?->recalculateTotals();
        });

        static::deleted(function (InvoiceItem $item): void {
            $item->invoice?->recalculateTotals();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getItemName(string $locale = 'ar'): string
    {
        $name = $this->item_name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($name)) {
            return 'Item';
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? 'Item';
    }
}