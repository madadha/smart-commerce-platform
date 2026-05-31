<?php

namespace App\Filament\Resources\ProductOptions\Schemas;

use App\Models\Product;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Option Information')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(fn (): array => Product::query()
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Product $product) => [
                                    $product->id => $product->getName('ar') . ' - ' . $product->slug,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name.ar')
                            ->label('Name Arabic')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name.he')
                            ->label('Name Hebrew')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name.en')
                            ->label('Name English')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'select' => 'Select',
                                'color' => 'Color',
                                'text' => 'Text',
                                'button' => 'Button',
                            ])
                            ->required()
                            ->default('select'),

                        Toggle::make('is_required')
                            ->label('Required')
                            ->default(true),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('Option Values')
                    ->schema([
                        Repeater::make('values')
                            ->label('Values')
                            ->schema([
                                TextInput::make('ar')
                                    ->label('Arabic')
                                    ->required(),

                                TextInput::make('he')
                                    ->label('Hebrew')
                                    ->required(),

                                TextInput::make('en')
                                    ->label('English')
                                    ->required(),

                                TextInput::make('value')
                                    ->label('Value')
                                    ->required(),

                                TextInput::make('color')
                                    ->label('Color')
                                    ->placeholder('#000000'),
                            ])
                            ->columns(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}