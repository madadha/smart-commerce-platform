<?php

namespace App\Filament\Resources\Carts\Tables;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Services\Checkout\CartCheckoutService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Throwable;

class CartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cart_number')
                    ->label('Cart Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->state(fn (Cart $record): string => $record->customer?->getDisplayName() ?? $record->user?->name ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof CartStatus ? $state->label() : (string) $state)
                    ->color(fn ($state): string => $state instanceof CartStatus ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),

                Tables\Columns\TextColumn::make('shipping_method_name')
                    ->label('Shipping')
                    ->state(fn (Cart $record): string => $record->shippingMethod?->getName('ar') ?? '-')
                    ->badge()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('coupon_code')
                    ->label('Coupon')
                    ->badge()
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->formatStateUsing(fn ($state, Cart $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_total')
                    ->label('Discount')
                    ->formatStateUsing(fn ($state, Cart $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('shipping_total')
                    ->label('Shipping')
                    ->formatStateUsing(fn ($state, Cart $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->formatStateUsing(fn ($state, Cart $record): string => ($record->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('converted_at')
                    ->label('Converted')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('abandoned_at')
                    ->label('Abandoned')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Cart Status')
                    ->options(collect(CartStatus::cases())->mapWithKeys(fn (CartStatus $status) => [
                        $status->value => $status->label(),
                    ])->toArray()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('convert_to_order')
                    ->label('Convert to Order')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Convert Cart to Order')
                    ->modalDescription('This will create a new order from this cart and mark the cart as converted.')
                    ->visible(fn (Cart $record): bool => $record->status === CartStatus::Active)
                    ->action(function (Cart $record): void {
                        try {
                           $order = app(CartCheckoutService::class)->convertToOrder(
    cart: $record,
    createPayment: true,
    paymentMethod: 'cash',
    transactionId: null
);

                            Notification::make()
                                ->title('Cart converted successfully')
                                ->body('Order created: ' . $order->order_number)
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Checkout failed')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}