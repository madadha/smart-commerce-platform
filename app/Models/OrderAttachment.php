<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAttachment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'title',
        'original_name',
        'file_path',
        'disk',
        'mime_type',
        'size_bytes',
        'file_size',
        'notes',
        'is_private',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'file_size' => 'integer',
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

    public function getDisplayNameAttribute(): string
    {
        return $this->title
            ?: $this->original_name
            ?: basename((string) $this->file_path);
    }

    public function getUrlAttribute(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }

        return route('admin.orders.attachments.download', $this);
    }
}
