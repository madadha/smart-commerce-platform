<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponDiscountType;
use App\Models\Currency;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Information')
                    ->schema([
                        TextInput::make('code')
                            ->label('Coupon Code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Example: WELCOME10, SALE50, FREESHIP'),

                        TextInput::make('name.ar')
                            ->label('Name Arabic')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name.he')
                            ->label('Name Hebrew')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name.en')
                            ->label('Name English')
                            ->required()
                            ->maxLength(255),

                        Select::make('discount_type')
                            ->label('Discount Type')
                            ->options(collect(CouponDiscountType::cases())->mapWithKeys(fn (CouponDiscountType $type) => [
                                $type->value => $type->label() . ' - ' . $type->labelAr(),
                            ])->toArray())
                            ->required()
                            ->default(CouponDiscountType::Percentage->value),

                        TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->helperText('For percentage write 10 for 10%. For fixed amount write the amount.'),

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
                    ])
                    ->columns(2),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description.ar')
                            ->label('Description Arabic')
                            ->rows(3),

                        Textarea::make('description.he')
                            ->label('Description Hebrew')
                            ->rows(3),

                        Textarea::make('description.en')
                            ->label('Description English')
                            ->rows(3),
                    ])
                    ->columns(3),

                Section::make('Conditions')
                    ->schema([
                        TextInput::make('minimum_order_total')
                            ->label('Minimum Order Total')
                            ->numeric()
                            ->helperText('Coupon works only if order total reaches this value.'),

                        TextInput::make('maximum_discount_amount')
                            ->label('Maximum Discount Amount')
                            ->numeric()
                            ->helperText('Useful for percentage coupons. Leave empty for unlimited.'),

                        TextInput::make('usage_limit')
                            ->label('Total Usage Limit')
                            ->numeric()
                            ->helperText('Maximum total uses for this coupon.'),

                        TextInput::make('usage_limit_per_customer')
                            ->label('Usage Limit Per Customer')
                            ->numeric()
                            ->helperText('Maximum uses per customer.'),

                        TextInput::make('used_count')
                            ->label('Used Count')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),

                Section::make('Validity Period')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->seconds(false),

                        DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->seconds(false),
                    ])
                    ->columns(2),

                Section::make('Settings')
                    ->schema([
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