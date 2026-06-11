<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQuestion extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'customer_name',
        'customer_email',
        'question',
        'answer',
        'status',
        'locale',
        'answered_by',
        'answered_at',
        'approved_at',
        'rejected_at',
        'ip_address',
        'user_agent',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query
            ->where('status', 'approved')
            ->where('is_active', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function approve(?int $userId = null): void
    {
        $this->forceFill([
            'status' => 'approved',
            'approved_at' => now(),
            'rejected_at' => null,
            'is_active' => true,
        ])->save();
    }

    public function reject(): void
    {
        $this->forceFill([
            'status' => 'rejected',
            'approved_at' => null,
            'rejected_at' => now(),
            'is_active' => false,
        ])->save();
    }

    public function answer(string $answer, ?int $userId = null): void
    {
        $this->forceFill([
            'answer' => $answer,
            'answered_by' => $userId,
            'answered_at' => now(),
        ])->save();
    }
}