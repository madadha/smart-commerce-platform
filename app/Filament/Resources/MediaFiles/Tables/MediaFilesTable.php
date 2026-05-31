<?php

namespace App\Filament\Resources\MediaFiles\Tables;

use App\Models\MediaFile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class MediaFilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Preview')
                    ->disk('public')
                    ->square()
                    ->height(48)
                    ->width(48),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->state(fn (MediaFile $record): string => $record->getTitle('ar'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('path', 'like', "%{$search}%")
                            ->orWhere('title->ar', 'like', "%{$search}%")
                            ->orWhere('title->en', 'like', "%{$search}%")
                            ->orWhere('title->he', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('path')
                    ->label('Path')
                    ->limit(45)
                    ->searchable()
                    ->tooltip(fn (MediaFile $record): string => $record->path ?? ''),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Mime')
                    ->limit(25)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(function ($state): string {
                        if (! $state) {
                            return '-';
                        }

                        $size = (int) $state;

                        if ($size >= 1048576) {
                            return round($size / 1048576, 2) . ' MB';
                        }

                        if ($size >= 1024) {
                            return round($size / 1024, 2) . ' KB';
                        }

                        return $size . ' B';
                    }),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
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