<?php

namespace App\Filament\Resources\ProductOptions\Tables;

use App\Models\ProductOption;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class ProductOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->state(fn (ProductOption $record): string => $record->product?->getName('ar') ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('slug', 'like', "%{$search}%")
                                ->orWhere('name->ar', 'like', "%{$search}%")
                                ->orWhere('name->en', 'like', "%{$search}%")
                                ->orWhere('name->he', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('option_name')
                    ->label('Option')
                    ->state(fn (ProductOption $record): string => $record->getName('ar')),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('values_count')
                    ->label('Values')
                    ->state(fn (ProductOption $record): int => count($record->getValues())),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
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