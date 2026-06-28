<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(),

                Tables\Columns\TextColumn::make('category_name')
                    ->label('Name')
                    ->state(fn (Category $record): string => $record->getName('ar'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('slug', 'like', "%{$search}%")
                            ->orWhere('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%")
                            ->orWhere('name->he', 'like', "%{$search}%");
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('full_path')
                    ->label('Full Path')
                    ->state(fn (Category $record): string => $record->getFullPath('ar'))
                    ->limit(60)
                    ->tooltip(fn (Category $record): string => $record->getFullPath('ar')),

                Tables\Columns\TextColumn::make('parent.slug')
                    ->label('Parent')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('show_in_menu')
                    ->label('Menu')
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