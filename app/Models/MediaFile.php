<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    protected $fillable = [
        'disk',
        'path',
        'type',
        'mime_type',
        'size',
        'width',
        'height',
        'title',
        'alt_text',
        'description',
        'dominant_color',
        'ai_generated_alt',
        'metadata',
        'uploaded_by',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'alt_text' => 'array',
        'description' => 'array',
        'ai_generated_alt' => 'array',
        'metadata' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (MediaFile $mediaFile): void {
            if (blank($mediaFile->disk)) {
                $mediaFile->disk = 'public';
            }

            if (blank($mediaFile->type)) {
                $mediaFile->type = 'image';
            }
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getUrl(): ?string
    {
        if (blank($this->path)) {
            return null;
        }

        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }

    public function getTitle(string $locale = 'ar'): string
    {
        $title = $this->title;

        if (is_string($title)) {
            $decoded = json_decode($title, true);
            $title = is_array($decoded) ? $decoded : [];
        }

        if (is_array($title)) {
            return $title[$locale]
                ?? $title['en']
                ?? $title['ar']
                ?? basename($this->path);
        }

        return basename($this->path);
    }

    public function getAltText(string $locale = 'ar'): string
    {
        $altText = $this->alt_text;

        if (is_string($altText)) {
            $decoded = json_decode($altText, true);
            $altText = is_array($decoded) ? $decoded : [];
        }

        if (is_array($altText)) {
            return $altText[$locale]
                ?? $altText['en']
                ?? $altText['ar']
                ?? $this->getTitle($locale);
        }

        return $this->getTitle($locale);
    }

    public function getDescription(string $locale = 'ar'): ?string
    {
        $description = $this->description;

        if (is_string($description)) {
            $decoded = json_decode($description, true);
            $description = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($description)) {
            return null;
        }

        return $description[$locale]
            ?? $description['en']
            ?? $description['ar']
            ?? null;
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }
}