<?php

namespace App\Filament\Resources\Carts\Schemas;

use App\Enums\CartStatus;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cart Information')
                    ->schema([
                        TextInput::make('cart_number')
                            ->label('Cart Number')
                            ->maxLength(255)
                            ->helperText('Leave empty to generate automatically.'),

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
                            ->label('Cart Status')
                            ->options(collect(CartStatus::cases())->mapWithKeys(fn (CartStatus $status) => [
                                $status->value => $status->label(),
                            ])->toArray())
                            ->required()
                            ->default(CartStatus::Active->value),

                        Select::make('shipping_method_id')
                            ->label('Shipping Method')
                            ->options(fn (): array => ShippingMethod::query()
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (ShippingMethod $method) => [
                                    $method->id => $method->getName('ar') . ' - ' . ($method->currency?->symbol ?? '₪') . ' ' . number_format((float) $method->base_cost, 2),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Coupon / Discount')
                    ->schema([
                        Select::make('coupon_id')
                            ->label('Coupon')
                            ->options(fn (): array => Coupon::query()
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Coupon $coupon) => [
                                    $coupon->id => $coupon->code . ' - ' . $coupon->getName('ar'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->helperText('Discount will be calculated after saving.'),

                        TextInput::make('coupon_code')
                            ->label('Coupon Code')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('coupon_discount_type')
                            ->label('Coupon Discount Type')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('coupon_discount_value')
                            ->label('Coupon Discount Value')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Section::make('Cart Items')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->relationship('items')
                            ->schema([
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

                                Select::make('item_type')
                                    ->label('Item Type')
                                    ->options([
                                        'product' => 'Product',
                                        'digital_code' => 'Digital Code',
                                        'service' => 'Service',
                                    ])
                                    ->required()
                                    ->default('product'),

                                TextInput::make('product_name.ar')
                                    ->label('Product Name Arabic')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('product_name.he')
                                    ->label('Product Name Hebrew')
                                    ->maxLength(255),

                                TextInput::make('product_name.en')
                                    ->label('Product Name English')
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
                            ->addActionLabel('Add Cart Item')
                            ->reorderable()
                            ->collapsible(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->default(0)
                            ->helperText('Calculated after saving cart items.'),

                        TextInput::make('discount_total')
                            ->label('Discount Total')
                            ->numeric()
                            ->default(0)
                            ->helperText('Calculated from selected coupon if available.'),

                        TextInput::make('tax_total')
                            ->label('Tax Total')
                            ->numeric()
                            ->default(0),

                        TextInput::make('shipping_total')
                            ->label('Shipping Total')
                            ->numeric()
                            ->default(0)
                            ->helperText('Calculated according to selected shipping method.'),

                        TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->numeric()
                            ->default(0)
                            ->helperText('Calculated after saving cart.'),

                        TextInput::make('converted_at')
                            ->label('Converted At')
                            ->disabled()
                            ->dehydrated(false),

                        DateTimePicker::make('abandoned_at')
                            ->label('Abandoned At')
                            ->seconds(false),
                    ])
                    ->columns(3),

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