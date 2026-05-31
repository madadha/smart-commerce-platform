<?php

namespace App\Models;

use App\Enums\DigitalCodeStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDigitalCode extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'code',
        'status',
        'source',
        'expires_at',
        'reserved_by',
        'reserved_at',
        'sold_to',
        'sold_at',
        'internal_notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'status' => DigitalCodeStatus::class,
        'expires_at' => 'datetime',
        'reserved_at' => 'datetime',
        'sold_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function reservedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    public function soldToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_to');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', DigitalCodeStatus::Available->value)
            ->where('is_active', true);
    }

    public function scopeSold($query)
    {
        return $query->where('status', DigitalCodeStatus::Sold->value);
    }

    public function scopeReserved($query)
    {
        return $query->where('status', DigitalCodeStatus::Reserved->value);
    }

    public function maskCode(): string
    {
        $code = (string) $this->code;

        if (strlen($code) <= 8) {
            return str_repeat('*', strlen($code));
        }

        return substr($code, 0, 4)
            . str_repeat('*', max(strlen($code) - 8, 0))
            . substr($code, -4);
    }

    public function isAvailable(): bool
    {
        return $this->status === DigitalCodeStatus::Available && $this->is_active;
    }

    public function isSold(): bool
    {
        return $this->status === DigitalCodeStatus::Sold;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}