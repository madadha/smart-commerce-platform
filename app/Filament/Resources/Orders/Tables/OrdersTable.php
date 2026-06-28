<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->state(fn (Order $record): string => $record->customer?->getDisplayName() ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof OrderStatus ? $state->label() : (string) $state)
                    ->color(fn ($state): string => $state instanceof OrderStatus ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof PaymentStatus ? $state->label() : (string) $state)
                    ->color(fn ($state): string => $state instanceof PaymentStatus ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shipping_method_name')
                    ->label('Shipping')
                    ->state(fn (Order $record): string => $record->shippingMethod?->getName('ar') ?? '-')
                    ->badge()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('coupon_code')
                    ->label('Coupon')
                    ->badge()
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->formatStateUsing(fn ($state, Order $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_total')
                    ->label('Discount')
                    ->formatStateUsing(fn ($state, Order $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('shipping_total')
                    ->label('Shipping Cost')
                    ->formatStateUsing(fn ($state, Order $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->formatStateUsing(fn ($state, Order $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_total')
                    ->label('Paid')
                    ->formatStateUsing(fn ($state, Order $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('ordered_at')
                    ->label('Ordered At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Order Status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status) => [
                        $status->value => $status->label(),
                    ])->toArray()),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status) => [
                        $status->value => $status->label(),
                    ])->toArray()),
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
