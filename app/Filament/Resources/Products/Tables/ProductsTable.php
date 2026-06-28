<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->height(48)
                    ->width(48),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Name')
                    ->state(fn (Product $record): string => $record->getName('ar'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('slug', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%")
                            ->orWhere('name->he', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('product_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ProductType ? $state->label() : (string) $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ProductStatus ? $state->label() : (string) $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand.slug')
                    ->label('Brand')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state, Product $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Sale')
                    ->formatStateUsing(function ($state, Product $record): string {
                        if ($state === null) {
                            return '-';
                        }

                        return ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->state(function (Product $record): string {
                        $stockInfo = self::resolveStockInfo($record);

                        if (! $stockInfo) {
                            return '-';
                        }

                        return (string) $stockInfo['value'];
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $stockColumn = self::resolveStockColumn();

                        if (! $stockColumn) {
                            return $query;
                        }

                        return $query->orderBy($stockColumn, $direction);
                    }),

                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->badge()
                    ->state(function (Product $record): string {
                        $stockInfo = self::resolveStockInfo($record);

                        if (! $stockInfo) {
                            return 'Not tracked';
                        }

                        if ($stockInfo['value'] <= 0) {
                            return 'Out of stock';
                        }

                        if ($stockInfo['value'] <= 5) {
                            return 'Low stock';
                        }

                        return 'In stock';
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'Out of stock' => 'danger',
                            'Low stock' => 'warning',
                            'In stock' => 'success',
                            default => 'gray',
                        };
                    })
                    ->icon(function (string $state): string {
                        return match ($state) {
                            'Out of stock' => 'heroicon-o-x-circle',
                            'Low stock' => 'heroicon-o-exclamation-triangle',
                            'In stock' => 'heroicon-o-check-circle',
                            default => 'heroicon-o-minus-circle',
                        };
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('stock_filter')
                    ->label('Stock')
                    ->options([
                        'out_of_stock' => 'Out of Stock',
                        'low_stock' => 'Low Stock',
                        'in_stock' => 'In Stock',
                        'tracked' => 'Tracked Stock',
                        'not_tracked' => 'Not Tracked',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        $stockColumn = self::resolveStockColumn();

                        if (! $stockColumn) {
                            return $query;
                        }

                        return match ($value) {
                            'out_of_stock' => $query
                                ->whereNotNull($stockColumn)
                                ->where($stockColumn, '<=', 0),

                            'low_stock' => $query
                                ->whereNotNull($stockColumn)
                                ->where($stockColumn, '>', 0)
                                ->where($stockColumn, '<=', 5),

                            'in_stock' => $query
                                ->whereNotNull($stockColumn)
                                ->where($stockColumn, '>', 5),

                            'tracked' => $query
                                ->whereNotNull($stockColumn),

                            'not_tracked' => $query
                                ->whereNull($stockColumn),

                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('active_low_stock')
                    ->label('Active low/out stock only')
                    ->query(function (Builder $query): Builder {
                        $stockColumn = self::resolveStockColumn();

                        if (! $stockColumn) {
                            return $query;
                        }

                        return $query
                            ->where('is_active', true)
                            ->whereNotNull($stockColumn)
                            ->where($stockColumn, '<=', 5);
                    }),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('set_stock')
                    ->label('Set Stock')
                    ->icon('heroicon-o-cube')
                    ->color('info')
                    ->visible(fn (Product $record): bool => self::resolveStockInfo($record) !== null)
                    ->schema([
                        TextInput::make('stock_quantity')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->fillForm(function (Product $record): array {
                        $stockInfo = self::resolveStockInfo($record);

                        return [
                            'stock_quantity' => $stockInfo['value'] ?? 0,
                        ];
                    })
                    ->action(function (Product $record, array $data): void {
                        $stockInfo = self::resolveStockInfo($record);

                        if (! $stockInfo) {
                            return;
                        }

                        $record->forceFill([
                            $stockInfo['column'] => max(0, (int) $data['stock_quantity']),
                        ])->save();
                    }),

                Action::make('mark_out_of_stock')
                    ->label('Out Stock')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (Product $record): bool {
                        $stockInfo = self::resolveStockInfo($record);

                        return $stockInfo !== null && $stockInfo['value'] > 0;
                    })
                    ->action(function (Product $record): void {
                        $stockInfo = self::resolveStockInfo($record);

                        if (! $stockInfo) {
                            return;
                        }

                        $record->forceFill([
                            $stockInfo['column'] => 0,
                        ])->save();
                    }),

                Action::make('quick_restock')
                    ->label('Restock +10')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->visible(fn (Product $record): bool => self::resolveStockInfo($record) !== null)
                    ->action(function (Product $record): void {
                        $stockInfo = self::resolveStockInfo($record);

                        if (! $stockInfo) {
                            return;
                        }

                        $record->increment($stockInfo['column'], 10);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function resolveStockColumn(): ?string
    {
        foreach (['stock_quantity', 'quantity', 'stock'] as $column) {
            if (Schema::hasColumn('products', $column)) {
                return $column;
            }
        }

        return null;
    }

    private static function resolveStockInfo(Product $product): ?array
    {
        foreach (['stock_quantity', 'quantity', 'stock'] as $column) {
            if (array_key_exists($column, $product->getAttributes()) && $product->{$column} !== null) {
                return [
                    'column' => $column,
                    'value' => (int) $product->{$column},
                ];
            }
        }

        return null;
    }
}