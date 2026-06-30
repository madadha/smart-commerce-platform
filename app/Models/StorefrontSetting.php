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
        'categories_section_title',
        'categories_section_subtitle',
        'featured_section_title',
        'featured_section_subtitle',
        'latest_section_title',
        'latest_section_subtitle',
        'brands_section_title',
        'brands_section_subtitle',
        'products_categories_filter_title',
        'products_brands_filter_title',
        'show_categories_section',
        'show_featured_section',
        'show_latest_section',
        'show_brands_section',
        'footer_rights_text',
        'facebook_icon',
        'instagram_icon',
        'tiktok_icon',
        'youtube_icon',
        'whatsapp_floating_icon',
        'show_floating_whatsapp',
        'show_cookie_consent',
        'enable_game_topups',
        'cookie_consent_text',
        'cookie_consent_button_text',
        'cookie_consent_privacy_text',
        'cookie_consent_privacy_url',
        'cookie_consent_storage_key',
        'cookie_consent_background_color',
        'cookie_consent_button_color',
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
        'categories_section_title' => 'array',
        'categories_section_subtitle' => 'array',
        'featured_section_title' => 'array',
        'featured_section_subtitle' => 'array',
        'latest_section_title' => 'array',
        'latest_section_subtitle' => 'array',
        'brands_section_title' => 'array',
        'brands_section_subtitle' => 'array',
        'products_categories_filter_title' => 'array',
        'products_brands_filter_title' => 'array',
        'footer_rights_text' => 'array',
        'cookie_consent_text' => 'array',
        'cookie_consent_button_text' => 'array',
        'cookie_consent_privacy_text' => 'array',
        'show_categories_section' => 'boolean',
        'show_featured_section' => 'boolean',
        'show_latest_section' => 'boolean',
        'show_brands_section' => 'boolean',
        'show_floating_whatsapp' => 'boolean',
        'show_cookie_consent' => 'boolean',
        'enable_game_topups' => 'boolean',
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
