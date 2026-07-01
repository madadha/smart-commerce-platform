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
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'youtube_url',
        'youtube_enabled',
        'game_id',
        'game_title',
        'game_currency_name',
        'game_delivery_mode',
        'game_provider',
        'game_provider_sku',
        'game_requires_player_id',
        'game_requires_region',
        'game_requires_server',
        'game_server_options',
        'game_can_validate_player',
        'game_player_id_label',
        'game_region_label',
        'game_server_label',
        'game_topup_instructions',
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
        'game_title' => 'array',
        'game_currency_name' => 'array',
        'game_requires_player_id' => 'boolean',
        'game_requires_region' => 'boolean',
        'game_requires_server' => 'boolean',
        'game_server_options' => 'array',
        'game_can_validate_player' => 'boolean',
        'game_player_id_label' => 'array',
        'game_region_label' => 'array',
        'game_server_label' => 'array',
        'game_topup_instructions' => 'array',
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
        'youtube_enabled' => 'boolean',
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

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
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

    public function gameRegions(): BelongsToMany
    {
        return $this->belongsToMany(GameRegion::class, 'game_product_region')
            ->withPivot(['is_active', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('game_regions.sort_order')
            ->orderBy('game_regions.id');
    }

    public function activeGameRegions(): BelongsToMany
    {
        return $this->gameRegions()
            ->wherePivot('is_active', true)
            ->where('game_regions.is_active', true);
    }

    public function availableGameRegions()
    {
        if ($this->relationLoaded('gameRegions') && $this->gameRegions->isNotEmpty()) {
            return $this->gameRegions
                ->filter(fn (GameRegion $region): bool => (bool) $region->is_active && (bool) ($region->pivot?->is_active ?? true))
                ->values();
        }

        if ($this->relationLoaded('game') && $this->game?->relationLoaded('activeRegions')) {
            return $this->game->activeRegions;
        }

        if ($this->game_id) {
            return $this->activeGameRegions()->get()->whenEmpty(function () {
                return $this->game?->activeRegions()->get() ?? collect();
            });
        }

        return collect();
    }

    public function gameServerOptions(): array
    {
        $options = $this->game_server_options ?? [];

        if (! is_array($options)) {
            return [];
        }

        return collect($options)
            ->mapWithKeys(function ($label, $key): array {
                if (is_array($label)) {
                    $value = $label['value'] ?? $label['label'] ?? $key;
                    $key = $label['key'] ?? $value;
                    $label = $value;
                }

                $key = trim((string) $key);
                $label = trim((string) $label);

                if ($key === '' || $label === '') {
                    return [];
                }

                return [$key => $label];
            })
            ->all();
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

    public function getYouTubeEmbedUrl(): ?string
    {
        if (! $this->youtube_enabled || blank($this->youtube_url)) {
            return null;
        }

        $url = trim((string) $this->youtube_url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $videoId = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $videoId = $query['v'] ?? null;

            if (! $videoId && preg_match('~^(?:embed|shorts)/([A-Za-z0-9_-]{11})~', $path, $matches)) {
                $videoId = $matches[1];
            }
        }

        if (! is_string($videoId) || ! preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/' . $videoId;
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

public function defaultVariant(): HasOne
{
    return $this->hasOne(ProductVariant::class)
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

public function activeMedia(): HasMany
{
    return $this->media()->where('is_active', true);
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
