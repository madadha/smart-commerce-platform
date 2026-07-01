<?php

namespace App\Filament\Resources\ProductVariants\Tables;

use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->height(48)
                    ->width(48),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->state(fn (ProductVariant $record): string => $record->product?->getName('ar') ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('slug', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhere('name->ar', 'like', "%{$search}%")
                                ->orWhere('name->en', 'like', "%{$search}%")
                                ->orWhere('name->he', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('variant_name')
                    ->label('Variant')
                    ->state(fn (ProductVariant $record): string => $record->getName('ar')),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('package')
                    ->label('Package')
                    ->state(fn (ProductVariant $record): string => $record->getPackageLabel(app()->getLocale()))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('package_unit', 'like', "%{$search}%")
                            ->orWhere('package_label->ar', 'like', "%{$search}%")
                            ->orWhere('package_label->en', 'like', "%{$search}%")
                            ->orWhere('package_label->he', 'like', "%{$search}%");
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('provider_sku')
                    ->label('Provider SKU')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('provider_package_id')
                    ->label('Provider Package ID')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('option_values')
                    ->label('Options')
                    ->formatStateUsing(function ($state): string {
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            $state = is_array($decoded) ? $decoded : [];
                        }

                        if (! is_array($state) || empty($state)) {
                            return '-';
                        }

                        return collect($state)
                            ->map(fn ($value, $key) => $key . ': ' . $value)
                            ->implode(' | ');
                    })
                    ->limit(40),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(function ($state, ProductVariant $record): string {
                        if ($state === null) {
                            return '-';
                        }

                        return ($record->product?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Sale')
                    ->formatStateUsing(function ($state, ProductVariant $record): string {
                        if ($state === null) {
                            return '-';
                        }

                        return ($record->product?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
