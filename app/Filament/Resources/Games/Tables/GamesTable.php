<?php

namespace App\Filament\Resources\Games\Tables;

use App\Models\Game;
use App\Support\Localization\ActiveLanguageRegistry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class GamesTable
{
    public static function configure(Table $table): Table
    {
        $locale = app(ActiveLanguageRegistry::class)->defaultCode();

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('Icon')
                    ->translateLabel(false)
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('game_name')
                    ->label('Game')
                    ->translateLabel(false)
                    ->state(fn (Game $record): string => $record->getName($locale))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('slug', 'like', "%{$search}%")
                            ->orWhere('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%")
                            ->orWhere('name->he', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->translateLabel(false)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('default_provider')
                    ->label('Provider')
                    ->translateLabel(false)
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('regions_count')
                    ->counts('regions')
                    ->label('Regions')
                    ->translateLabel(false)
                    ->sortable(),

                Tables\Columns\IconColumn::make('supports_player_validation')
                    ->label('Validation')
                    ->translateLabel(false)
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->translateLabel(false)
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort')
                    ->translateLabel(false)
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
