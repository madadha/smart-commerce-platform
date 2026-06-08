<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'reviewer_name',
        'reviewer_email',
        'rating',
        'comment',
        'status',
        'locale',
        'approved_by',
        'approved_at',
        'rejected_at',
        'ip_address',
        'user_agent',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'integer',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
            'approved_by' => $userId,
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
}