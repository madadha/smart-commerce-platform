<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
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
    ];

    protected static function booted(): void
    {
        static::creating(function (CartItem $cartItem) {
            if ($cartItem->options === null) {
                $cartItem->options = [];
            }
        });

        static::saving(function (CartItem $cartItem) {
            if ($cartItem->options === null) {
                $cartItem->options = [];
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

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}