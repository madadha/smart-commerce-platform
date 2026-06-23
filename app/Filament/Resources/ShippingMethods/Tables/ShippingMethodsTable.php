<?php

namespace App\Filament\Resources\ShippingMethods\Tables;

use App\Enums\ShippingMethodType;
use App\Models\ShippingMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shipping_name')
                    ->label('Name')
                    ->state(fn (ShippingMethod $record): string => $record->getName('ar'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('slug', 'like', "%{$search}%")
                            ->orWhere('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%")
                            ->orWhere('name->he', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ShippingMethodType ? $state->labelAr() : (string) $state)
                    ->color(fn ($state): string => $state instanceof ShippingMethodType ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('country.code')
                    ->label('Country')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state, ShippingMethod $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('per_kg_cost')
                    ->label('Per Kg')
                    ->formatStateUsing(fn ($state, ShippingMethod $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('free_shipping_min_total')
                    ->label('Free From')
                    ->formatStateUsing(function ($state, ShippingMethod $record): string {
                        if ($state === null) {
                            return '-';
                        }

                        return ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_weight')
                    ->label('Min Wt.')
                    ->suffix('kg')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_weight')
                    ->label('Max Wt.')
                    ->suffix('kg')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_estimate')
                    ->label('Delivery')
                    ->state(fn (ShippingMethod $record): string => $record->getDeliveryEstimate()),

                Tables\Columns\TextColumn::make('external_company_name')
                    ->label('External Company')
                    ->placeholder('-')
                    ->limit(25),

                Tables\Columns\IconColumn::make('requires_address')
                    ->label('Address')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Shipping Type')
                    ->options(collect(ShippingMethodType::cases())->mapWithKeys(fn (ShippingMethodType $type) => [
                        $type->value => $type->labelAr(),
                    ])->toArray()),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Method'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
