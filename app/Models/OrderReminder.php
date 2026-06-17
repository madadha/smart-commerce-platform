<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReminder extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'assigned_to_user_id',
        'title',
        'notes',
        'status',
        'remind_at',
        'completed_at',
        'is_private',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_private' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'done' => 'Done',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending'
            && $this->remind_at !== null
            && $this->remind_at->isPast();
    }
}
