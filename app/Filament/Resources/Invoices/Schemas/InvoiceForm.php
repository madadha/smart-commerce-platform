<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Information')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->maxLength(255)
                            ->helperText('Leave empty to generate automatically.'),

                        Select::make('order_id')
                            ->label('Order Number')
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

                        Select::make('status')
                            ->label('Invoice Status')
                            ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn (InvoiceStatus $status) => [
                                $status->value => $status->label(),
                            ])->toArray())
                            ->required()
                            ->default(InvoiceStatus::Draft->value),

                        DatePicker::make('issued_at')
                            ->label('Issued At'),

                        DatePicker::make('due_at')
                            ->label('Due At'),

                        DatePicker::make('paid_at')
                            ->label('Paid At'),
                    ])
                    ->columns(2),

                Section::make('Invoice Items')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->relationship('items')
                            ->schema([
                                Select::make('order_item_id')
                                    ->label('Order Item')
                                    ->options(fn (): array => OrderItem::query()
                                        ->with('order')
                                        ->latest()
                                        ->get()
                                        ->mapWithKeys(fn (OrderItem $item) => [
                                            $item->id => ($item->order?->order_number ?? '-') . ' / ' . $item->getProductName('ar') . ' / ' . ($item->sku ?? '-'),
                                        ])
                                        ->toArray())
                                    ->searchable()
                                    ->preload(),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(fn (): array => Product::query()
                                        ->orderBy('sort_order')
                                        ->orderBy('id')
                                        ->get()
                                        ->mapWithKeys(fn (Product $product) => [
                                            $product->id => $product->getName('ar') . ' - ' . ($product->sku ?? $product->slug),
                                        ])
                                        ->toArray())
                                    ->searchable()
                                    ->preload(),

                                Select::make('product_variant_id')
                                    ->label('Variant')
                                    ->options(fn (): array => ProductVariant::query()
                                        ->with('product')
                                        ->orderBy('sort_order')
                                        ->orderBy('id')
                                        ->get()
                                        ->mapWithKeys(fn (ProductVariant $variant) => [
                                            $variant->id => ($variant->product?->getName('ar') ?? '-') . ' / ' . $variant->getName('ar') . ' / ' . ($variant->sku ?? '-'),
                                        ])
                                        ->toArray())
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('item_name.ar')
                                    ->label('Item Name Arabic')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('item_name.he')
                                    ->label('Item Name Hebrew')
                                    ->maxLength(255),

                                TextInput::make('item_name.en')
                                    ->label('Item Name English')
                                    ->maxLength(255),

                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->maxLength(255),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1),

                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->default(0),

                                TextInput::make('discount_total')
                                    ->label('Discount')
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('tax_total')
                                    ->label('Tax')
                                    ->numeric()
                                    ->default(0),

                                KeyValue::make('options')
                                    ->label('Options')
                                    ->keyLabel('Option')
                                    ->valueLabel('Value')
                                    ->columnSpanFull(),

                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->addActionLabel('Add Invoice Item')
                            ->reorderable()
                            ->collapsible(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->default(0)
                            ->helperText('Calculated after saving invoice items.'),

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
                            ->default(0)
                            ->helperText('Calculated after saving invoice.'),

                        TextInput::make('paid_total')
                            ->label('Paid Total')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),

                Section::make('Billing & Seller Details')
                    ->schema([
                        KeyValue::make('billing_address')
                            ->label('Billing Address')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull(),

                        KeyValue::make('seller_details')
                            ->label('Seller Details')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),

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
