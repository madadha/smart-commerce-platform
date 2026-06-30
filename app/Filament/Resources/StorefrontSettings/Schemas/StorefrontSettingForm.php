<?php

namespace App\Filament\Resources\StorefrontSettings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StorefrontSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Brand Identity')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('store_name.ar')->label('Store Name AR')->default('Smart Commerce')->required(),
                        TextInput::make('store_name.he')->label('Store Name HE')->default('Smart Commerce'),
                        TextInput::make('store_name.en')->label('Store Name EN')->default('Smart Commerce'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('store_tagline.ar')->label('Tagline AR')->default('Marketplace Platform'),
                        TextInput::make('store_tagline.he')->label('Tagline HE')->default('Marketplace Platform'),
                        TextInput::make('store_tagline.en')->label('Tagline EN')->default('Marketplace Platform'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('topbar_text.ar')->label('Topbar AR')->default('Smart Commerce Platform — تجربة تجارة ديناميكية متعددة اللغات'),
                        TextInput::make('topbar_text.he')->label('Topbar HE')->default('Smart Commerce Platform — חוויית מסחר דינמית ורב־לשונית'),
                        TextInput::make('topbar_text.en')->label('Topbar EN')->default('Smart Commerce Platform — Dynamic multilingual commerce experience'),
                    ]),
                    Grid::make(2)->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->disk('public')
                            ->directory('storefront/branding')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->nullable(),
                        FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->disk('public')
                            ->directory('storefront/branding')
                            ->image()
                            ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/webp'])
                            ->visibility('public')
                            ->maxSize(1024)
                            ->imageEditor()
                            ->nullable(),
                    ]),
                    Toggle::make('is_active')->label('Active')->default(true),
                ]),

            Section::make('Theme Colors')
                ->schema([
                    Grid::make(3)->schema([
                        ColorPicker::make('primary_color')->label('Primary')->default('#2563eb'),
                        ColorPicker::make('primary_hover_color')->label('Primary Hover')->default('#1d4ed8'),
                        ColorPicker::make('secondary_color')->label('Secondary')->default('#0ea5e9'),
                        ColorPicker::make('accent_color')->label('Accent / Gold')->default('#d4a24c'),
                        ColorPicker::make('dark_color')->label('Dark')->default('#0b1120'),
                        ColorPicker::make('background_color')->label('Background')->default('#f8fafc'),
                        ColorPicker::make('card_color')->label('Card')->default('#ffffff'),
                        ColorPicker::make('text_color')->label('Text')->default('#0f172a'),
                        ColorPicker::make('muted_text_color')->label('Muted Text')->default('#64748b'),
                    ]),
                ]),

            Section::make('Typography')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('body_font_family')
                            ->label('Body Font')
                            ->options(self::fontOptions())
                            ->searchable()
                            ->default('Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif')
                            ->helperText('Used for normal storefront text.'),
                        Select::make('heading_font_family')
                            ->label('Heading Font')
                            ->options(self::fontOptions())
                            ->searchable()
                            ->default('Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif')
                            ->helperText('Used for homepage titles and section headings.'),
                    ]),
                ]),

            Section::make('Default Hero Content')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('hero_badge.ar')->label('Badge AR'),
                        TextInput::make('hero_badge.he')->label('Badge HE'),
                        TextInput::make('hero_badge.en')->label('Badge EN'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('hero_title.ar')->label('Title AR'),
                        TextInput::make('hero_title.he')->label('Title HE'),
                        TextInput::make('hero_title.en')->label('Title EN'),
                    ]),
                    Grid::make(3)->schema([
                        Textarea::make('hero_text.ar')->label('Text AR')->rows(3),
                        Textarea::make('hero_text.he')->label('Text HE')->rows(3),
                        Textarea::make('hero_text.en')->label('Text EN')->rows(3),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('hero_primary_button_text.ar')->label('Primary Button AR'),
                        TextInput::make('hero_primary_button_text.he')->label('Primary Button HE'),
                        TextInput::make('hero_primary_button_text.en')->label('Primary Button EN'),
                    ]),
                    TextInput::make('hero_primary_button_url')->label('Primary Button URL')->default('/store/products'),
                    Grid::make(3)->schema([
                        TextInput::make('hero_secondary_button_text.ar')->label('Secondary Button AR'),
                        TextInput::make('hero_secondary_button_text.he')->label('Secondary Button HE'),
                        TextInput::make('hero_secondary_button_text.en')->label('Secondary Button EN'),
                    ]),
                    TextInput::make('hero_secondary_button_url')->label('Secondary Button URL')->default('/store/products?on_sale=1'),
                ]),

            Section::make('Homepage Sections')
                ->schema([
                    Grid::make(4)->schema([
                        Toggle::make('show_categories_section')->label('Categories')->default(true),
                        Toggle::make('show_featured_section')->label('Featured Products')->default(true),
                        Toggle::make('show_latest_section')->label('Latest Products')->default(true),
                        Toggle::make('show_brands_section')->label('Brands')->default(true),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('categories_section_title.ar')->label('Categories Title AR'),
                        TextInput::make('categories_section_title.he')->label('Categories Title HE'),
                        TextInput::make('categories_section_title.en')->label('Categories Title EN'),
                    ]),
                    Grid::make(3)->schema([
                        Textarea::make('categories_section_subtitle.ar')->label('Categories Subtitle AR')->rows(2),
                        Textarea::make('categories_section_subtitle.he')->label('Categories Subtitle HE')->rows(2),
                        Textarea::make('categories_section_subtitle.en')->label('Categories Subtitle EN')->rows(2),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('featured_section_title.ar')->label('Featured Title AR'),
                        TextInput::make('featured_section_title.he')->label('Featured Title HE'),
                        TextInput::make('featured_section_title.en')->label('Featured Title EN'),
                    ]),
                    Grid::make(3)->schema([
                        Textarea::make('featured_section_subtitle.ar')->label('Featured Subtitle AR')->rows(2),
                        Textarea::make('featured_section_subtitle.he')->label('Featured Subtitle HE')->rows(2),
                        Textarea::make('featured_section_subtitle.en')->label('Featured Subtitle EN')->rows(2),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('latest_section_title.ar')->label('Latest Title AR'),
                        TextInput::make('latest_section_title.he')->label('Latest Title HE'),
                        TextInput::make('latest_section_title.en')->label('Latest Title EN'),
                    ]),
                    Grid::make(3)->schema([
                        Textarea::make('latest_section_subtitle.ar')->label('Latest Subtitle AR')->rows(2),
                        Textarea::make('latest_section_subtitle.he')->label('Latest Subtitle HE')->rows(2),
                        Textarea::make('latest_section_subtitle.en')->label('Latest Subtitle EN')->rows(2),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('brands_section_title.ar')->label('Brands Title AR'),
                        TextInput::make('brands_section_title.he')->label('Brands Title HE'),
                        TextInput::make('brands_section_title.en')->label('Brands Title EN'),
                    ]),
                    Grid::make(3)->schema([
                        Textarea::make('brands_section_subtitle.ar')->label('Brands Subtitle AR')->rows(2),
                        Textarea::make('brands_section_subtitle.he')->label('Brands Subtitle HE')->rows(2),
                        Textarea::make('brands_section_subtitle.en')->label('Brands Subtitle EN')->rows(2),
                    ]),
                ]),

            Section::make('Products Page Filters')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('products_categories_filter_title.ar')->label('Categories Filter Title AR'),
                        TextInput::make('products_categories_filter_title.he')->label('Categories Filter Title HE'),
                        TextInput::make('products_categories_filter_title.en')->label('Categories Filter Title EN'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('products_brands_filter_title.ar')->label('Brands Filter Title AR'),
                        TextInput::make('products_brands_filter_title.he')->label('Brands Filter Title HE'),
                        TextInput::make('products_brands_filter_title.en')->label('Brands Filter Title EN'),
                    ]),
                ]),

            Section::make('Footer & Contact')
                ->schema([
                    Grid::make(3)->schema([
                        Textarea::make('footer_description.ar')->label('Footer AR')->rows(3),
                        Textarea::make('footer_description.he')->label('Footer HE')->rows(3),
                        Textarea::make('footer_description.en')->label('Footer EN')->rows(3),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('address.ar')->label('Address AR'),
                        TextInput::make('address.he')->label('Address HE'),
                        TextInput::make('address.en')->label('Address EN'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('contact_email')->email()->label('Email'),
                        TextInput::make('contact_phone')->label('Phone'),
                        TextInput::make('whatsapp')->label('WhatsApp'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('facebook_url')->url()->label('Facebook'),
                        TextInput::make('instagram_url')->url()->label('Instagram'),
                        TextInput::make('tiktok_url')->url()->label('TikTok'),
                    ]),
                    Grid::make(4)->schema([
                        TextInput::make('facebook_icon')->label('Facebook Icon')->placeholder('f'),
                        TextInput::make('instagram_icon')->label('Instagram Icon')->placeholder('◎'),
                        TextInput::make('tiktok_icon')->label('TikTok Icon')->placeholder('♪'),
                        TextInput::make('youtube_icon')->label('YouTube Icon')->placeholder('▶'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('youtube_url')->url()->label('YouTube'),
                        TextInput::make('whatsapp_floating_icon')->label('Floating WhatsApp Icon')->placeholder('☘'),
                        Toggle::make('show_floating_whatsapp')->label('Show Floating WhatsApp')->default(true),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('footer_rights_text.ar')->label('Rights AR')->placeholder('جميع الحقوق محفوظة.'),
                        TextInput::make('footer_rights_text.he')->label('Rights HE')->placeholder('כל הזכויות שמורות.'),
                        TextInput::make('footer_rights_text.en')->label('Rights EN')->placeholder('All rights reserved.'),
                    ]),
                ]),
        ]);
    }

    private static function fontOptions(): array
    {
        return [
            'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' => 'Inter / System UI',
            '"Tajawal", "Segoe UI", Tahoma, Arial, sans-serif' => 'Tajawal Arabic Style',
            '"Cairo", "Segoe UI", Tahoma, Arial, sans-serif' => 'Cairo Arabic Style',
            '"Rubik", "Segoe UI", Tahoma, Arial, sans-serif' => 'Rubik Hebrew/Arabic Style',
            '"Noto Sans", "Segoe UI", Tahoma, Arial, sans-serif' => 'Noto Sans Multilingual',
            'Arial, Helvetica, sans-serif' => 'Arial',
            'Tahoma, Geneva, sans-serif' => 'Tahoma',
        ];
    }
}
