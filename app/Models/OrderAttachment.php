<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'notes',
        'is_private',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
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

    public function getUrlAttribute(): ?string
    {
        if (blank($this->file_path)) {
            return null;
        }

        return Storage::disk($this->disk ?: 'public')->url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) ($this->size_bytes ?? 0);

        if ($bytes <= 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $size = $bytes / (1024 ** $power);

        return number_format($size, $power === 0 ? 0 : 2) . ' ' . $units[$power];
    }
}
