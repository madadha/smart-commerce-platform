<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductMedia extends Model
{
    protected $table = 'product_media';

    protected $fillable = [
        'product_id',
        'media_file_id',
        'image',
        'role',
        'alt_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'alt_text' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    public function getUrl(): ?string
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        return $this->mediaFile?->getUrl();
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
                ?? $this->product?->getName($locale)
                ?? 'Product Image';
        }

        return $this->product?->getName($locale) ?? 'Product Image';
    }

}
