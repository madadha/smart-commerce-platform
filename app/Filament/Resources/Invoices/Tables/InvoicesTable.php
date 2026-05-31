<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->state(fn (Invoice $record): string => $record->customer?->getDisplayName() ?? $record->order?->customer?->getDisplayName() ?? '-')
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof InvoiceStatus ? $state->label() : (string) $state)
                    ->color(fn ($state): string => $state instanceof InvoiceStatus ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->formatStateUsing(fn ($state, Invoice $record): string => ($record->currency?->symbol ?? $record->order?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_total')
                    ->label('Discount')
                    ->formatStateUsing(fn ($state, Invoice $record): string => ($record->currency?->symbol ?? $record->order?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_total')
                    ->label('Tax')
                    ->formatStateUsing(fn ($state, Invoice $record): string => ($record->currency?->symbol ?? $record->order?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('shipping_total')
                    ->label('Shipping')
                    ->formatStateUsing(fn ($state, Invoice $record): string => ($record->currency?->symbol ?? $record->order?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->formatStateUsing(fn ($state, Invoice $record): string => ($record->currency?->symbol ?? $record->order?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_total')
                    ->label('Paid')
                    ->formatStateUsing(fn ($state, Invoice $record): string => ($record->currency?->symbol ?? $record->order?->currency?->symbol ?? '₪') . ' ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Issued')
                    ->date('Y-m-d')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due')
                    ->date('Y-m-d')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Invoice Status')
                    ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn (InvoiceStatus $status) => [
                        $status->value => $status->label(),
                    ])->toArray()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
           ->recordActions([
    EditAction::make(),

    \Filament\Actions\Action::make('pdf')
        ->label('PDF')
        ->icon('heroicon-o-printer')
        ->url(fn (\App\Models\Invoice $record): string => route('admin.invoices.pdf', $record))
        ->openUrlInNewTab(),
])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}