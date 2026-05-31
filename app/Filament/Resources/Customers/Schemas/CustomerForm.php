<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Country;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        Select::make('user_id')
                            ->label('Linked User')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $user) => [
                                    $user->id => $user->name . ' - ' . $user->email,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('customer_type')
                            ->label('Customer Type')
                            ->options(collect(CustomerType::cases())->mapWithKeys(fn (CustomerType $type) => [
                                $type->value => $type->label(),
                            ])->toArray())
                            ->required()
                            ->default(CustomerType::Regular->value),

                        Select::make('status')
                            ->label('Status')
                            ->options(collect(CustomerStatus::cases())->mapWithKeys(fn (CustomerStatus $status) => [
                                $status->value => $status->label(),
                            ])->toArray())
                            ->required()
                            ->default(CustomerStatus::Active->value),

                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('identity_number')
                            ->label('Identity Number')
                            ->maxLength(255),

                        DatePicker::make('birth_date')
                            ->label('Birth Date'),
                    ])
                    ->columns(2),

                Section::make('Company / Reseller Information')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->maxLength(255),

                        TextInput::make('tax_number')
                            ->label('Tax Number')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Address')
                    ->schema([
                        Select::make('country_id')
                            ->label('Country')
                            ->options(fn (): array => Country::query()
                                ->orderBy('sort_order')
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn (Country $country) => [
                                    $country->id => $country->flag . ' ' . $country->getName('ar') . ' - ' . $country->code,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),

                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(255),

                        TextInput::make('area')
                            ->label('Area')
                            ->maxLength(255),

                        TextInput::make('street')
                            ->label('Street')
                            ->maxLength(255),

                        TextInput::make('building')
                            ->label('Building')
                            ->maxLength(255),

                        TextInput::make('apartment')
                            ->label('Apartment')
                            ->maxLength(255),

                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->maxLength(255),

                        Textarea::make('address_notes')
                            ->label('Address Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Settings & Notes')
                    ->schema([
                        Toggle::make('accepts_marketing')
                            ->label('Accepts Marketing')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),

                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}