<?php

namespace App\Filament\Resources\ProductDigitalCodes\Schemas;

use App\Enums\DigitalCodeStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductDigitalCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Digital Code Information')
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

                        Select::make('product_variant_id')
                            ->label('Product Variant')
                            ->options(fn (): array => ProductVariant::query()
                                ->with('product')
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (ProductVariant $variant) => [
                                    $variant->id => ($variant->product?->getName('ar') ?? '-') . ' / ' . $variant->getName('ar') . ' / ' . ($variant->sku ?? '-'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('The real digital code. It will be masked in the table.'),

                        Select::make('status')
                            ->label('Status')
                            ->options(collect(DigitalCodeStatus::cases())->mapWithKeys(fn (DigitalCodeStatus $status) => [
                                $status->value => $status->label(),
                            ])->toArray())
                            ->required()
                            ->default(DigitalCodeStatus::Available->value),

                        Select::make('source')
                            ->label('Source')
                            ->options([
                                'manual' => 'Manual',
                                'supplier' => 'Supplier',
                                'import' => 'Import',
                                'api' => 'API',
                            ])
                            ->searchable()
                            ->default('manual'),

                        DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->seconds(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('Reservation / Sale')
                    ->schema([
                        Select::make('reserved_by')
                            ->label('Reserved By')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $user) => [
                                    $user->id => $user->name . ' - ' . $user->email,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        DateTimePicker::make('reserved_at')
                            ->label('Reserved At')
                            ->seconds(false),

                        Select::make('sold_to')
                            ->label('Sold To')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $user) => [
                                    $user->id => $user->name . ' - ' . $user->email,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        DateTimePicker::make('sold_at')
                            ->label('Sold At')
                            ->seconds(false),
                    ])
                    ->columns(2),

                Section::make('Internal Notes')
                    ->schema([
                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}