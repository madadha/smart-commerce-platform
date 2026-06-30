<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GameRegion extends Model
{
    protected $fillable = [
        'name',
        'code',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (GameRegion $region): void {
            $region->code = Str::upper(Str::slug((string) $region->code, '_'));
        });
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)
            ->withPivot(['is_active', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('games.sort_order')
            ->orderBy('games.id');
    }

    public function activeGames(): BelongsToMany
    {
        return $this->games()
            ->wherePivot('is_active', true)
            ->where('games.is_active', true);
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
        $name = $this->name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($name)) {
            return 'Region';
        }

        return (string) (
            $name[$locale]
            ?? $name['ar']
            ?? $name['en']
            ?? $name['he']
            ?? 'Region'
        );
    }

    public function iconUrl(): ?string
    {
        if (blank($this->icon)) {
            return null;
        }

        if (Str::startsWith($this->icon, ['http://', 'https://'])) {
            return $this->icon;
        }

        return Storage::disk('public')->url($this->icon);
    }
}
