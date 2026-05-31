<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->maxLength(255)
                            ->helperText('Leave empty to generate automatically.'),

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

                        Select::make('user_id')
                            ->label('User')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $user) => [
                                    $user->id => $user->name . ' - ' . $user->email,
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

                        Select::make('status')
                            ->label('Order Status')
                            ->options(collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status) => [
                                $status->value => $status->label(),
                            ])->toArray())
                            ->required()
                            ->default(OrderStatus::Pending->value),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status) => [
                                $status->value => $status->label(),
                            ])->toArray())
                            ->required()
                            ->default(PaymentStatus::Unpaid->value),

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
                            ->searchable(),

                        Select::make('shipping_method')
                            ->label('Shipping Method')
                            ->options(collect(ShippingMethod::cases())->mapWithKeys(fn (ShippingMethod $method) => [
                                $method->value => $method->label(),
                            ])->toArray())
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make('Totals')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->default(0),

                        TextInput::make('discount_total')
                            ->label('Discount Total')
                            ->numeric()
                            ->default(0),

                        TextInput::make('tax_total')
                            ->label('Tax Total')
                            ->numeric()
                            ->default(0),

                        TextInput::make('shipping_total')
                            ->label('Shipping Total')
                            ->numeric()
                            ->default(0),

                        TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->numeric()
                            ->default(0),

                        TextInput::make('paid_total')
                            ->label('Paid Total')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),

                Section::make('Addresses')
                    ->schema([
                        KeyValue::make('billing_address')
                            ->label('Billing Address')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull(),

                        KeyValue::make('shipping_address')
                            ->label('Shipping Address')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),

                Section::make('Dates')
                    ->schema([
                        DateTimePicker::make('ordered_at')
                            ->label('Ordered At')
                            ->seconds(false),

                        DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->seconds(false),

                        DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->seconds(false),

                        DateTimePicker::make('cancelled_at')
                            ->label('Cancelled At')
                            ->seconds(false),
                    ])
                    ->columns(2),

                Section::make('Notes & Settings')
                    ->schema([
                        Textarea::make('customer_notes')
                            ->label('Customer Notes')
                            ->rows(3),

                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(3),

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