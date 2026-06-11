<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductMedia;





class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'sku',
        'barcode',
        'product_type',
        'status',
        'brand_id',
        'company_id',
        'currency_id',
        'main_media_id',
        'main_image',
        'price',
        'sale_price',
        'cost_price',
        'track_stock',
        'stock_quantity',
        'min_stock_quantity',
        'requires_shipping',
        'weight',
        'length',
        'width',
        'height',
        'specifications',
        'notes',
        'seo_title',
        'seo_description',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'specifications' => 'array',
        'notes' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'product_type' => ProductType::class,
        'status' => ProductStatus::class,
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'track_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'min_stock_quantity' => 'integer',
        'requires_shipping' => 'boolean',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = Str::slug(
                    $product->getName('en') ?: $product->getName('ar') ?: 'product'
                );
            }

            if ($product->product_type instanceof ProductType) {
                $product->requires_shipping = $product->product_type->requiresShipping();
            }
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function mainMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'main_media_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', ProductStatus::Active->value);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getName(string $locale = 'ar'): string
    {
        $name = $this->name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($name)) {
            return 'Product';
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? 'Product';
    }

    public function getShortDescription(string $locale = 'ar'): ?string
    {
        $description = $this->short_description;

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

    public function getImageUrl(): ?string
    {
        if ($this->main_image) {
            return Storage::disk('public')->url($this->main_image);
        }

        return $this->mainMedia?->getUrl();
    }

    public function finalPrice(): float
    {
        return (float) ($this->sale_price ?: $this->price);
    }

    public function hasSale(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;
    }

    public function isInStock(): bool
    {
        if (! $this->track_stock) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    public function options(): HasMany
{
    return $this->hasMany(ProductOption::class)
        ->orderBy('sort_order')
        ->orderBy('id');
}

public function variants(): HasMany
{
    return $this->hasMany(ProductVariant::class)
        ->orderBy('sort_order')
        ->orderBy('id');
}

public function activeVariants(): HasMany
{
    return $this->variants()
        ->where('is_active', true);
}

public function defaultVariant(): HasMany
{
    return $this->variants()
        ->where('is_default', true);
}

public function digitalCodes(): HasMany
{
    return $this->hasMany(ProductDigitalCode::class)
        ->orderBy('sort_order')
        ->orderBy('id');
}

public function availableDigitalCodes(): HasMany
{
    return $this->digitalCodes()
        ->where('status', \App\Enums\DigitalCodeStatus::Available->value)
        ->where('is_active', true);
}


public function media(): HasMany
{
    return $this->hasMany(ProductMedia::class)
        ->orderBy('sort_order')
        ->orderBy('id');
}

public function reviews(): HasMany
{
    return $this->hasMany(ProductReview::class);
}

public function approvedReviews(): HasMany
{
    return $this->hasMany(ProductReview::class)
        ->approved()
        ->latest();
}

public function questions(): HasMany
{
    return $this->hasMany(ProductQuestion::class);
}

public function approvedQuestions(): HasMany
{
    return $this->hasMany(ProductQuestion::class)
        ->approved()
        ->orderBy('sort_order')
        ->latest();
}

}