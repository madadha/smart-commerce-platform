<?php

namespace App\Filament\Resources\ProductDigitalCodes\Tables;

use App\Enums\DigitalCodeStatus;
use App\Models\ProductDigitalCode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class ProductDigitalCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->state(fn (ProductDigitalCode $record): string => $record->product?->getName('ar') ?? '-')
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
                    ->state(fn (ProductDigitalCode $record): string => $record->productVariant?->getName('ar') ?? '-')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('masked_code')
                    ->label('Code')
                    ->state(fn (ProductDigitalCode $record): string => $record->maskCode())
                    ->searchable(query: fn ($query, string $search) => $query->where('code', 'like', "%{$search}%"))
                    ->copyable()
                    ->copyMessage('Masked code copied'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof DigitalCodeStatus ? $state->label() : (string) $state)
                    ->color(fn ($state): string => $state instanceof DigitalCodeStatus ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reservedByUser.name')
                    ->label('Reserved By')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('soldToUser.name')
                    ->label('Sold To')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(DigitalCodeStatus::cases())->mapWithKeys(fn (DigitalCodeStatus $status) => [
                        $status->value => $status->label(),
                    ])->toArray()),

                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'manual' => 'Manual',
                        'supplier' => 'Supplier',
                        'import' => 'Import',
                        'api' => 'API',
                    ]),
            ])
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