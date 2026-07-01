<?php

namespace App\Filament\Resources\ProductVariants\Schemas;

use App\Models\MediaFile;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Variant Information')
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
                            ->maxLength(255),

                        TextInput::make('name.he')
                            ->label('Name Hebrew')
                            ->maxLength(255),

                        TextInput::make('name.en')
                            ->label('Name English')
                            ->maxLength(255),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('provider_sku')
                            ->label('Provider SKU')
                            ->maxLength(255)
                            ->helperText('Used by Game Top-Up/API providers for this package.'),

                        TextInput::make('provider_package_id')
                            ->label('Provider Package ID')
                            ->maxLength(255)
                            ->helperText('External package identifier when the provider separates package ID from SKU.'),

                        Select::make('fulfillment_mode')
                            ->label('Fulfillment Mode')
                            ->options([
                                'inherit' => 'Inherit from product',
                                'manual' => 'Manual fulfillment',
                                'api' => 'API fulfillment',
                            ])
                            ->default('inherit'),

                        TextInput::make('barcode')
                            ->label('Barcode')
                            ->maxLength(255),

                        Toggle::make('is_default')
                            ->label('Default Variant')
                            ->default(false),

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
                        KeyValue::make('option_values')
                            ->label('Option Values')
                            ->keyLabel('Option Slug')
                            ->valueLabel('Value')
                            ->helperText('Example: color = black, storage = 256gb')
                            ->columnSpanFull(),
                    ]),

                Section::make('Game Top-Up Package')
                    ->description('Structured package metadata for gaming recharge products, such as 60 UC, 325 Diamonds, or Battle Pass.')
                    ->schema([
                        TextInput::make('package_amount')
                            ->label('Package Amount')
                            ->numeric()
                            ->helperText('Example: 60, 325, 660.'),

                        TextInput::make('package_unit')
                            ->label('Package Unit')
                            ->maxLength(255)
                            ->placeholder('UC / Diamonds / Coins'),

                        TextInput::make('package_label.ar')
                            ->label('Package Label Arabic')
                            ->maxLength(255),

                        TextInput::make('package_label.he')
                            ->label('Package Label Hebrew')
                            ->maxLength(255),

                        TextInput::make('package_label.en')
                            ->label('Package Label English')
                            ->maxLength(255),

                        KeyValue::make('provider_payload')
                            ->label('Provider Payload')
                            ->keyLabel('Provider Key')
                            ->valueLabel('Value')
                            ->helperText('Optional extra fields required by the API provider.')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Variant Image')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Upload Variant Image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('products/variants')
                            ->visibility('public')
                            ->imageEditor()
                            ->downloadable()
                            ->openable()
                            ->maxSize(5120),

                        Select::make('media_file_id')
                            ->label('Or Select From Media Library')
                            ->options(fn (): array => MediaFile::query()
                                ->where('type', 'image')
                                ->latest()
                                ->get()
                                ->mapWithKeys(fn (MediaFile $mediaFile) => [
                                    $mediaFile->id => $mediaFile->getTitle('ar') . ' - ' . $mediaFile->path,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric(),

                        TextInput::make('sale_price')
                            ->label('Sale Price')
                            ->numeric(),

                        TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric(),
                    ])
                    ->columns(3),

                Section::make('Stock')
                    ->schema([
                        Toggle::make('track_stock')
                            ->label('Track Stock')
                            ->default(true),

                        TextInput::make('stock_quantity')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->default(0),

                        TextInput::make('min_stock_quantity')
                            ->label('Minimum Stock')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),

                Section::make('Shipping Dimensions')
                    ->schema([
                        TextInput::make('weight')
                            ->label('Weight')
                            ->numeric(),

                        TextInput::make('length')
                            ->label('Length')
                            ->numeric(),

                        TextInput::make('width')
                            ->label('Width')
                            ->numeric(),

                        TextInput::make('height')
                            ->label('Height')
                            ->numeric(),
                    ])
                    ->columns(4),
            ]);
    }
}
