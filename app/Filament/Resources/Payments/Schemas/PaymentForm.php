<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentTransactionStatus;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Information')
                    ->schema([
                        TextInput::make('payment_number')
                            ->label('Payment Number')
                            ->maxLength(255)
                            ->helperText('Leave empty to generate automatically.'),

                        Select::make('order_id')
                            ->label('Order')
                            ->options(fn (): array => Order::query()
                                ->latest()
                                ->get()
                                ->mapWithKeys(fn (Order $order) => [
                                    $order->id => $order->order_number . ' - ' . ($order->customer?->getDisplayName() ?? '-'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),

                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn (): array => Customer::query()
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Customer $customer) => [
                                    $customer->id => $customer->getDisplayName() . ' - ' . ($customer->phone ?? $customer->email ?? '-'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),

                        Select::make('currency_id')
                            ->label('Currency')
                            ->options(fn (): array => Currency::query()
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Currency $currency) => [
                                    $currency->id => $currency->code . ' - ' . $currency->getName('ar'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'paypal' => 'PayPal',
                                'payplus' => 'PayPlus',
                                'stripe' => 'Stripe',
                            ])
                            ->required()
                            ->default('cash')
                            ->searchable(),

                        Select::make('status')
                            ->label('Status')
                            ->options(collect(PaymentTransactionStatus::cases())->mapWithKeys(fn (PaymentTransactionStatus $status) => [
                                $status->value => $status->label(),
                            ])->toArray())
                            ->required()
                            ->default(PaymentTransactionStatus::Pending->value),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->default(0),

                        TextInput::make('refunded_amount')
                            ->label('Refunded Amount')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('Transaction Details')
                    ->schema([
                        TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->maxLength(255),

                        TextInput::make('provider')
                            ->label('Provider')
                            ->maxLength(255)
                            ->placeholder('payplus, stripe, paypal, demo_gateway'),

                        TextInput::make('provider_reference')
                            ->label('Provider Reference')
                            ->maxLength(255),

                        KeyValue::make('provider_payload')
                            ->label('Provider Payload')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Dates')
                    ->schema([
                        DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->seconds(false),

                        DateTimePicker::make('failed_at')
                            ->label('Failed At')
                            ->seconds(false),

                        DateTimePicker::make('refunded_at')
                            ->label('Refunded At')
                            ->seconds(false),
                    ])
                    ->columns(3),

                Section::make('Notes & Settings')
                    ->schema([
                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(4)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}