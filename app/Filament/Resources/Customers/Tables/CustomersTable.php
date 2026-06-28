<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Customer')
                    ->state(fn (Customer $record): string => $record->getDisplayName())
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('whatsapp', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('customer_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof CustomerType ? $state->label() : (string) $state)
                    ->color(fn ($state): string => $state instanceof CustomerType ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('requested_customer_type')
                    ->label('Requested')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof CustomerType ? $state->label() : (blank($state) ? '-' : (string) $state))
                    ->color(fn ($state): string => $state instanceof CustomerType ? 'warning' : 'gray')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof CustomerStatus ? $state->label() : (string) $state)
                    ->color(fn ($state): string => $state instanceof CustomerStatus ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('country.code')
                    ->label('Country')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('full_address')
                    ->label('Address')
                    ->state(fn (Customer $record): string => $record->getFullAddress())
                    ->limit(45)
                    ->tooltip(fn (Customer $record): string => $record->getFullAddress()),

                Tables\Columns\IconColumn::make('accepts_marketing')
                    ->label('Marketing')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('customer_type')
                    ->label('Customer Type')
                    ->options(collect(CustomerType::cases())->mapWithKeys(fn (CustomerType $type) => [
                        $type->value => $type->label(),
                    ])->toArray()),

                Tables\Filters\SelectFilter::make('requested_customer_type')
                    ->label('Requested Type')
                    ->options([
                        CustomerType::Reseller->value => CustomerType::Reseller->label(),
                        CustomerType::Vip->value => CustomerType::Vip->label(),
                        CustomerType::Company->value => CustomerType::Company->label(),
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(CustomerStatus::cases())->mapWithKeys(fn (CustomerStatus $status) => [
                        $status->value => $status->label(),
                    ])->toArray()),
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
