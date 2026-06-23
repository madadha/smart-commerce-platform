<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'line_total',
        'discount_total',
        'tax_total',
        'options',
        'digital_code_id',
        'inventory_status',
        'inventory_reserved_at',
        'inventory_fulfilled_at',
        'inventory_released_at',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'product_name' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'sort_order' => 'integer',
        'inventory_reserved_at' => 'datetime',
        'inventory_fulfilled_at' => 'datetime',
        'inventory_released_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderItem $orderItem) {
            if ($orderItem->options === null) {
                $orderItem->options = [];
            }
        });

        static::saving(function (OrderItem $orderItem) {
            if ($orderItem->options === null) {
                $orderItem->options = [];
            }
        });
    }

    protected function options(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_array($value)) {
                    return $value;
                }

                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                }

                return [];
            },
            set: function ($value) {
                if (is_array($value)) {
                    return json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
                    }
                }

                return json_encode([], JSON_UNESCAPED_UNICODE);
            }
        );
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

    public function digitalCodes(): HasMany
    {
        return $this->hasMany(ProductDigitalCode::class);
    }
}
