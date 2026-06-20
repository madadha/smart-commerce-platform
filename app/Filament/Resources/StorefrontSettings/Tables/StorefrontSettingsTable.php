<?php

namespace App\Filament\Resources\StorefrontSettings\Tables;

use App\Models\StorefrontSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class StorefrontSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_name')
                    ->label('Store Name')
                    ->state(fn (StorefrontSetting $record): string => $record->localized('store_name', 'en', 'Smart Commerce'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\ColorColumn::make('primary_color')->label('Primary'),
                Tables\Columns\ColorColumn::make('accent_color')->label('Accent'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
