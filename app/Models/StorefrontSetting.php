<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class StorefrontSetting extends Model
{
    protected $fillable = [
        'store_name',
        'store_tagline',
        'topbar_text',
        'logo_path',
        'favicon_path',
        'footer_description',
        'contact_email',
        'contact_phone',
        'whatsapp',
        'address',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'youtube_url',
        'primary_color',
        'primary_hover_color',
        'secondary_color',
        'accent_color',
        'dark_color',
        'background_color',
        'card_color',
        'text_color',
        'muted_text_color',
        'body_font_family',
        'heading_font_family',
        'hero_badge',
        'hero_title',
        'hero_text',
        'hero_primary_button_text',
        'hero_primary_button_url',
        'hero_secondary_button_text',
        'hero_secondary_button_url',
        'show_categories_section',
        'show_featured_section',
        'show_latest_section',
        'show_brands_section',
        'is_active',
    ];

    protected $casts = [
        'store_name' => 'array',
        'store_tagline' => 'array',
        'topbar_text' => 'array',
        'footer_description' => 'array',
        'address' => 'array',
        'hero_badge' => 'array',
        'hero_title' => 'array',
        'hero_text' => 'array',
        'hero_primary_button_text' => 'array',
        'hero_secondary_button_text' => 'array',
        'show_categories_section' => 'boolean',
        'show_featured_section' => 'boolean',
        'show_latest_section' => 'boolean',
        'show_brands_section' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function current(): ?self
    {
        if (! Schema::hasTable('storefront_settings')) {
            return null;
        }

        return static::query()
            ->where('is_active', true)
            ->latest('id')
            ->first()
            ?? static::query()->latest('id')->first();
    }

    public function localized(string $field, ?string $locale = null, ?string $fallback = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $value = $this->{$field} ?? null;

        if (is_array($value)) {
            return (string) (
                $value[$locale]
                ?? $value['ar']
                ?? $value['en']
                ?? $value['he']
                ?? $fallback
                ?? ''
            );
        }

        return (string) ($value ?: $fallback ?: '');
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon_path ? asset('storage/' . $this->favicon_path) : null;
    }

    public function cssVariables(): string
    {
        $vars = [
            '--scp-primary' => $this->primary_color,
            '--scp-primary-hover' => $this->primary_hover_color,
            '--scp-secondary' => $this->secondary_color,
            '--scp-accent' => $this->accent_color,
            '--scp-gold' => $this->accent_color,
            '--scp-dark' => $this->dark_color,
            '--scp-bg' => $this->background_color,
            '--scp-card' => $this->card_color,
            '--scp-text' => $this->text_color,
            '--scp-muted' => $this->muted_text_color,
            '--scp-font-body' => $this->body_font_family,
            '--scp-font-heading' => $this->heading_font_family ?: $this->body_font_family,
        ];

        return collect($vars)
            ->filter()
            ->map(fn ($value, $key) => $key . ': ' . $value . ';')
            ->implode(' ');
    }
}
