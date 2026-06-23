<?php

namespace App\Filament\Resources\StorefrontSlides\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StorefrontSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Slide Content')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('badge.ar')->label('Badge AR'),
                        TextInput::make('badge.he')->label('Badge HE'),
                        TextInput::make('badge.en')->label('Badge EN'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('title.ar')->label('Title AR')->required(),
                        TextInput::make('title.he')->label('Title HE'),
                        TextInput::make('title.en')->label('Title EN'),
                    ]),
                    Grid::make(3)->schema([
                        Textarea::make('description.ar')->label('Description AR')->rows(3),
                        Textarea::make('description.he')->label('Description HE')->rows(3),
                        Textarea::make('description.en')->label('Description EN')->rows(3),
                    ]),
                    FileUpload::make('image_path')
                        ->label('Slide Image')
                        ->disk('public')
                        ->directory('storefront/slides')
                        ->image()
                        ->imageEditor()
                        ->nullable(),
                ]),

            Section::make('Buttons')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('primary_button_text.ar')->label('Primary Button AR'),
                        TextInput::make('primary_button_text.he')->label('Primary Button HE'),
                        TextInput::make('primary_button_text.en')->label('Primary Button EN'),
                    ]),
                    TextInput::make('primary_button_url')->label('Primary Button URL')->default('/store/products'),
                    Grid::make(3)->schema([
                        TextInput::make('secondary_button_text.ar')->label('Secondary Button AR'),
                        TextInput::make('secondary_button_text.he')->label('Secondary Button HE'),
                        TextInput::make('secondary_button_text.en')->label('Secondary Button EN'),
                    ]),
                    TextInput::make('secondary_button_url')->label('Secondary Button URL')->default('/store/products?on_sale=1'),
                ]),

            Section::make('Publishing')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
                ]),
        ]);
    }
}
