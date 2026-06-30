<?php

namespace App\Filament\Resources\StorefrontPromotions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StorefrontPromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Promotion Content')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('eyebrow.ar')->label('Eyebrow AR')->maxLength(255),
                        TextInput::make('eyebrow.he')->label('Eyebrow HE')->maxLength(255),
                        TextInput::make('eyebrow.en')->label('Eyebrow EN')->maxLength(255),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('title.ar')->label('Title AR')->required()->maxLength(255),
                        TextInput::make('title.he')->label('Title HE')->maxLength(255),
                        TextInput::make('title.en')->label('Title EN')->maxLength(255),
                    ]),
                    Grid::make(3)->schema([
                        Textarea::make('description.ar')->label('Description AR')->rows(3),
                        Textarea::make('description.he')->label('Description HE')->rows(3),
                        Textarea::make('description.en')->label('Description EN')->rows(3),
                    ]),
                ]),

            Section::make('Call To Action & Media')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('button_text.ar')->label('Button AR')->maxLength(255),
                        TextInput::make('button_text.he')->label('Button HE')->maxLength(255),
                        TextInput::make('button_text.en')->label('Button EN')->maxLength(255),
                    ]),
                    TextInput::make('button_url')
                        ->label('Button URL')
                        ->default('/store/products?on_sale=1')
                        ->helperText('Use an internal path such as /store/products?on_sale=1 or a full external URL.'),
                    FileUpload::make('image_path')
                        ->label('Promotion Image')
                        ->disk('public')
                        ->directory('storefront/promotions')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->visibility('public')
                        ->maxSize(5120)
                        ->imageEditor()
                        ->nullable(),
                ]),

            Section::make('Layout & Publishing')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('placement')
                            ->label('Placement')
                            ->options([
                                'home_after_hero' => 'Homepage after hero',
                                'home_between_products' => 'Homepage between product sections',
                                'products_ads_hero' => 'Products page large ads slider',
                                'products_ads_strip' => 'Products page small ads strip',
                            ])
                            ->default('home_after_hero')
                            ->required(),
                        Select::make('style')
                            ->label('Card Style')
                            ->options([
                                'gradient' => 'Gradient',
                                'dark' => 'Dark',
                                'light' => 'Light',
                                'accent' => 'Accent',
                            ])
                            ->default('gradient')
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('background_color')
                            ->label('Background Color')
                            ->placeholder('#2563eb')
                            ->maxLength(20),
                        TextInput::make('text_color')
                            ->label('Text Color')
                            ->placeholder('#ffffff')
                            ->maxLength(20),
                    ]),
                    Grid::make(3)->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->seconds(false),
                        DateTimePicker::make('ends_at')
                            ->label('Ends At')
                            ->seconds(false),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
                ]),
        ]);
    }
}
