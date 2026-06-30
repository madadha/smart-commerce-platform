<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Currency;
use App\Models\MediaFile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
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
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('barcode')
                            ->label('Barcode')
                            ->maxLength(255),

                        Select::make('product_type')
                            ->label('Product Type')
                            ->options(collect(ProductType::cases())->mapWithKeys(fn (ProductType $type) => [
                                $type->value => $type->label(),
                            ])->toArray())
                            ->required()
                            ->live()
                            ->default(ProductType::Physical->value),

                        Select::make('status')
                            ->label('Status')
                            ->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status) => [
                                $status->value => $status->label(),
                            ])->toArray())
                            ->required()
                            ->default(ProductStatus::Draft->value),
                    ])
                    ->columns(2),

                Section::make('Product Image')
                    ->schema([
                        FileUpload::make('main_image')
                            ->label('Upload Product Image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('products/main-images')
                            ->visibility('public')
                            ->imageEditor()
                            ->downloadable()
                            ->openable()
                            ->maxSize(5120)
                            ->helperText('Upload image directly from product page.'),

                        Select::make('main_media_id')
                            ->label('Or Select Image From Media Library')
                            ->options(fn (): array => MediaFile::query()
                                ->where('type', 'image')
                                ->latest()
                                ->get()
                                ->mapWithKeys(fn (MediaFile $mediaFile) => [
                                    $mediaFile->id => $mediaFile->getTitle('ar') . ' - ' . $mediaFile->path,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->helperText('Optional: choose an existing image from Media Library.'),
                    ])
                    ->columns(2),

                Section::make('Relations')
                    ->schema([
                        Select::make('brand_id')
                            ->label('Brand')
                            ->options(fn (): array => Brand::query()
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Brand $brand) => [
                                    $brand->id => $brand->getName('ar'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),

                        Select::make('company_id')
                            ->label('Company')
                            ->options(fn (): array => Company::query()
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Company $company) => [
                                    $company->id => $company->getName('ar'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),

                        Select::make('currency_id')
                            ->label('Currency')
                            ->options(fn (): array => Currency::query()
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Currency $currency) => [
                                    $currency->id => $currency->code . ' - ' . $currency->getName('ar'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),

                        Select::make('categories')
                            ->label('Categories')
                            ->relationship(name: 'categories', titleAttribute: 'slug')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->getFullPath('ar')),
                    ])
                    ->columns(2),

                Section::make('Descriptions')
                    ->schema([
                        Textarea::make('short_description.ar')
                            ->label('Short Description Arabic')
                            ->rows(2),

                        Textarea::make('short_description.he')
                            ->label('Short Description Hebrew')
                            ->rows(2),

                        Textarea::make('short_description.en')
                            ->label('Short Description English')
                            ->rows(2),

                        RichEditor::make('description.ar')
                            ->label('Description Arabic')
                            ->columnSpanFull(),

                        RichEditor::make('description.he')
                            ->label('Description Hebrew')
                            ->columnSpanFull(),

                        RichEditor::make('description.en')
                            ->label('Description English')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->required()
                            ->default(0),

                        TextInput::make('sale_price')
                            ->label('Sale Price')
                            ->numeric(),

                        TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric(),
                    ])
                    ->columns(3),

                Section::make('Product Video')
                    ->description('Add an optional YouTube video to the product page. The video is only displayed when enabled.')
                    ->schema([
                        Toggle::make('youtube_enabled')
                            ->label('Enable YouTube Video')
                            ->default(false)
                            ->live(),

                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->helperText('Supports youtube.com, youtu.be, Shorts, and embed URLs.'),
                    ])
                    ->columns(2),

                Section::make('Game Top-Up Settings')
                    ->description('Use this section for products such as PUBG UC, game recharge packages, subscriptions, or direct player top-ups.')
                    ->schema([
                        TextInput::make('game_title.ar')
                            ->label('Game Title Arabic')
                            ->placeholder('PUBG MOBILE'),

                        TextInput::make('game_title.he')
                            ->label('Game Title Hebrew')
                            ->placeholder('PUBG MOBILE'),

                        TextInput::make('game_title.en')
                            ->label('Game Title English')
                            ->placeholder('PUBG MOBILE'),

                        TextInput::make('game_currency_name.ar')
                            ->label('Currency / Unit Arabic')
                            ->placeholder('UC'),

                        TextInput::make('game_currency_name.he')
                            ->label('Currency / Unit Hebrew')
                            ->placeholder('UC'),

                        TextInput::make('game_currency_name.en')
                            ->label('Currency / Unit English')
                            ->placeholder('UC'),

                        Select::make('game_delivery_mode')
                            ->label('Delivery Mode')
                            ->options([
                                'manual' => 'Manual fulfillment',
                                'api' => 'API provider',
                            ])
                            ->default('manual')
                            ->required(),

                        TextInput::make('game_provider')
                            ->label('Provider')
                            ->placeholder('Midasbuy / Local provider / Manual team'),

                        TextInput::make('game_provider_sku')
                            ->label('Default Provider SKU')
                            ->helperText('Variant Provider SKU overrides this value for each package.'),

                        Toggle::make('game_requires_player_id')
                            ->label('Require Player ID / Game UID')
                            ->default(true),

                        Toggle::make('game_requires_region')
                            ->label('Require Region')
                            ->default(false),

                        Toggle::make('game_requires_server')
                            ->label('Require Server')
                            ->default(false),

                        Toggle::make('game_can_validate_player')
                            ->label('Provider Can Validate Player')
                            ->default(false)
                            ->helperText('Enable only when the selected official provider has a validation endpoint.'),

                        TextInput::make('game_player_id_label.ar')
                            ->label('Player ID Label AR')
                            ->placeholder('معرّف اللاعب'),

                        TextInput::make('game_player_id_label.he')
                            ->label('Player ID Label HE')
                            ->placeholder('מזהה שחקן'),

                        TextInput::make('game_player_id_label.en')
                            ->label('Player ID Label EN')
                            ->placeholder('Player ID'),

                        TextInput::make('game_region_label.ar')
                            ->label('Region Label AR')
                            ->placeholder('المنطقة'),

                        TextInput::make('game_region_label.he')
                            ->label('Region Label HE')
                            ->placeholder('אזור'),

                        TextInput::make('game_region_label.en')
                            ->label('Region Label EN')
                            ->placeholder('Region'),

                        TextInput::make('game_server_label.ar')
                            ->label('Server Label AR')
                            ->placeholder('السيرفر'),

                        TextInput::make('game_server_label.he')
                            ->label('Server Label HE')
                            ->placeholder('שרת'),

                        TextInput::make('game_server_label.en')
                            ->label('Server Label EN')
                            ->placeholder('Server'),

                        Textarea::make('game_topup_instructions.ar')
                            ->label('Customer Instructions AR')
                            ->rows(3),

                        Textarea::make('game_topup_instructions.he')
                            ->label('Customer Instructions HE')
                            ->rows(3),

                        Textarea::make('game_topup_instructions.en')
                            ->label('Customer Instructions EN')
                            ->rows(3),
                    ])
                    ->visible(fn ($get): bool => $get('product_type') === ProductType::GameTopUp->value)
                    ->columns(3),

                Section::make('Stock & Shipping')
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

                        Toggle::make('requires_shipping')
                            ->label('Requires Shipping')
                            ->default(true),

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

                Section::make('Specifications & Notes')
                    ->schema([
                        KeyValue::make('specifications')
                            ->label('Specifications')
                            ->keyLabel('Name')
                            ->valueLabel('Value')
                            ->columnSpanFull(),

                        KeyValue::make('notes')
                            ->label('Notes')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO')
                    ->schema([
                        TextInput::make('seo_title.ar')
                            ->label('SEO Title Arabic')
                            ->maxLength(255),

                        TextInput::make('seo_title.he')
                            ->label('SEO Title Hebrew')
                            ->maxLength(255),

                        TextInput::make('seo_title.en')
                            ->label('SEO Title English')
                            ->maxLength(255),

                        Textarea::make('seo_description.ar')
                            ->label('SEO Description Arabic')
                            ->rows(3),

                        Textarea::make('seo_description.he')
                            ->label('SEO Description Hebrew')
                            ->rows(3),

                        Textarea::make('seo_description.en')
                            ->label('SEO Description English')
                            ->rows(3),
                    ])
                    ->columns(3),

                Section::make('Visibility')
                    ->schema([
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),
            ]);
    }
}
