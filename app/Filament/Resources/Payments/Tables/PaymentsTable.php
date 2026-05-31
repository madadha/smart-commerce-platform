<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentTransactionStatus;
use App\Models\Payment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('Payment Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->state(fn (Payment $record): string => $record->customer?->getDisplayName() ?? $record->order?->customer?->getDisplayName() ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })->orWhereHas('order.customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof PaymentTransactionStatus ? $state->label() : (string) $state)
                    ->color(fn ($state): string => $state instanceof PaymentTransactionStatus ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state, Payment $record): string => ($record->currency?->symbol ?? $record->order?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('refunded_amount')
                    ->label('Refunded')
                    ->formatStateUsing(fn ($state, Payment $record): string => ($record->currency?->symbol ?? $record->order?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction')
                    ->limit(25)
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
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
                    ->label('Status')
                    ->options(collect(PaymentTransactionStatus::cases())->mapWithKeys(fn (PaymentTransactionStatus $status) => [
                        $status->value => $status->label(),
                    ])->toArray()),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'paypal' => 'PayPal',
                        'payplus' => 'PayPlus',
                        'stripe' => 'Stripe',
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