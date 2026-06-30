<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Game extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'banner_image',
        'default_provider',
        'supports_player_validation',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'supports_player_validation' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Game $game): void {
            if (blank($game->slug)) {
                $game->slug = Str::slug(
                    $game->getName('en') ?: $game->getName('ar') ?: 'game'
                );
            }
        });
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(GameRegion::class)
            ->withPivot(['is_active', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('game_regions.sort_order')
            ->orderBy('game_regions.id');
    }

    public function activeRegions(): BelongsToMany
    {
        return $this->regions()
            ->wherePivot('is_active', true)
            ->where('game_regions.is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getName(string $locale = 'ar'): string
    {
        return $this->localized('name', $locale, 'Game');
    }

    public function getDescription(string $locale = 'ar'): ?string
    {
        $description = $this->localized('description', $locale, '');

        return $description !== '' ? $description : null;
    }

    public function iconUrl(): ?string
    {
        return $this->assetUrl($this->icon);
    }

    public function bannerUrl(): ?string
    {
        return $this->assetUrl($this->banner_image);
    }

    private function localized(string $field, string $locale, string $fallback = ''): string
    {
        $value = $this->{$field};

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            return $fallback;
        }

        return (string) (
            $value[$locale]
            ?? $value['ar']
            ?? $value['en']
            ?? $value['he']
            ?? $fallback
        );
    }

    private function assetUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
