<?php

namespace App\Filament\Resources\ProductMedia\Tables;

use App\Models\ProductMedia;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class ProductMediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->height(54)
                    ->width(54),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->state(fn (ProductMedia $record): string => $record->product?->getName('ar') ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('slug', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhere('name->ar', 'like', "%{$search}%")
                                ->orWhere('name->en', 'like', "%{$search}%")
                                ->orWhere('name->he', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('image')
                    ->label('Image Path')
                    ->limit(35)
                    ->placeholder('-')
                    ->tooltip(fn (ProductMedia $record): string => $record->image ?? ''),

                Tables\Columns\TextColumn::make('mediaFile.path')
                    ->label('Media Library')
                    ->limit(35)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('alt_text')
                    ->label('Alt')
                    ->state(fn (ProductMedia $record): string => $record->getAltText('ar'))
                    ->limit(30),

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