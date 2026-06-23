<?php

namespace App\Filament\Resources\ShippingMethods\Schemas;

use App\Enums\ShippingMethodType;
use App\Models\Country;
use App\Models\Currency;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShippingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Shipping Method Information')
                    ->schema([
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

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Shipping Type')
                            ->options(collect(ShippingMethodType::cases())->mapWithKeys(fn (ShippingMethodType $type) => [
                                $type->value => $type->label().' - '.$type->labelAr(),
                            ])->toArray())
                            ->required()
                            ->default(ShippingMethodType::HomeDelivery->value),

                        Select::make('country_id')
                            ->label('Country')
                            ->options(fn (): array => Country::query()
                                ->orderBy('sort_order')
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn (Country $country) => [
                                    $country->id => ($country->flag ? $country->flag.' ' : '').$country->getName('ar').' - '.$country->code,
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
                                    $currency->id => $currency->code.' - '.$currency->getName('ar'),
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

                Section::make('Pricing & Delivery Time')
                    ->schema([
                        TextInput::make('base_cost')
                            ->label('Base Cost')
                            ->numeric()
                            ->required()
                            ->default(0),

                        TextInput::make('per_kg_cost')
                            ->label('Cost per kg')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('free_shipping_min_total')
                            ->label('Free Shipping From')
                            ->numeric()
                            ->helperText('If order total reaches this value, shipping becomes free.'),

                        TextInput::make('min_delivery_days')
                            ->label('Min Delivery Days')
                            ->numeric(),

                        TextInput::make('max_delivery_days')
                            ->label('Max Delivery Days')
                            ->numeric(),
                    ])
                    ->columns(3),

                Section::make('Eligibility Rules')
                    ->description('The server checks these limits again when the order is placed.')
                    ->schema([
                        TextInput::make('min_order_total')->label('Minimum order total')->numeric()->minValue(0),
                        TextInput::make('max_order_total')->label('Maximum order total')->numeric()->minValue(0),
                        TextInput::make('min_weight')->label('Minimum weight (kg)')->numeric()->minValue(0),
                        TextInput::make('max_weight')->label('Maximum weight (kg)')->numeric()->minValue(0),
                    ])->columns(4),

                Section::make('External Shipping Company')
                    ->schema([
                        TextInput::make('external_company_name')
                            ->label('Company Name')
                            ->maxLength(255),

                        TextInput::make('external_company_phone')
                            ->label('Company Phone')
                            ->maxLength(255),

                        TextInput::make('external_company_website')
                            ->label('Company Website')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Cities Control')
                    ->schema([
                        KeyValue::make('allowed_cities')
                            ->label('Allowed Cities')
                            ->keyLabel('City Code')
                            ->valueLabel('City Name')
                            ->helperText('Optional. Leave empty to allow all cities.')
                            ->columnSpanFull(),

                        KeyValue::make('excluded_cities')
                            ->label('Excluded Cities')
                            ->keyLabel('City Code')
                            ->valueLabel('City Name')
                            ->helperText('Optional. Cities where this method is not available.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('requires_address')
                            ->label('Requires Address')
                            ->default(true),

                        Toggle::make('is_default')
                            ->label('Default Method')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(4),
            ]);
    }
}
