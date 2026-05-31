<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Enums\CouponDiscountType;
use App\Models\Coupon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('coupon_name')
                    ->label('Name')
                    ->state(fn (Coupon $record): string => $record->getName('ar'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('code', 'like', "%{$search}%")
                            ->orWhere('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%")
                            ->orWhere('name->he', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof CouponDiscountType ? $state->labelAr() : (string) $state)
                    ->color(fn ($state): string => $state instanceof CouponDiscountType ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Value')
                    ->formatStateUsing(function ($state, Coupon $record): string {
                        if ($record->discount_type === CouponDiscountType::FreeShipping) {
                            return 'شحن مجاني';
                        }

                        if ($record->discount_type === CouponDiscountType::Percentage) {
                            return number_format((float) $state, 2) . '%';
                        }

                        return ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('minimum_order_total')
                    ->label('Min Order')
                    ->formatStateUsing(function ($state, Coupon $record): string {
                        if ($state === null) {
                            return '-';
                        }

                        return ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('maximum_discount_amount')
                    ->label('Max Discount')
                    ->formatStateUsing(function ($state, Coupon $record): string {
                        if ($state === null) {
                            return '-';
                        }

                        return ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Used')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->placeholder('Unlimited')
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('discount_type')
                    ->label('Discount Type')
                    ->options(collect(CouponDiscountType::cases())->mapWithKeys(fn (CouponDiscountType $type) => [
                        $type->value => $type->labelAr(),
                    ])->toArray()),

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