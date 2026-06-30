<?php

namespace App\Filament\Resources\GameRegions\Tables;

use App\Models\GameRegion;
use App\Support\Localization\ActiveLanguageRegistry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class GameRegionsTable
{
    public static function configure(Table $table): Table
    {
        $locale = app(ActiveLanguageRegistry::class)->defaultCode();

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('Icon')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('region_name')
                    ->label('Region')
                    ->state(fn (GameRegion $record): string => $record->getName($locale))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('code', 'like', "%{$search}%")
                            ->orWhere('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%")
                            ->orWhere('name->he', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('games_count')
                    ->counts('games')
                    ->label('Games')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
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
