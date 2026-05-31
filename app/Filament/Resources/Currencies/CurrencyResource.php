<?php

namespace App\Filament\Resources\Currencies;

use App\Filament\Resources\Currencies\Pages\CreateCurrency;
use App\Filament\Resources\Currencies\Pages\EditCurrency;
use App\Filament\Resources\Currencies\Pages\ListCurrencies;
use App\Models\Currency;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Currencies';

    protected static ?string $modelLabel = 'Currency';

    protected static ?string $pluralModelLabel = 'Currencies';

    protected static string|\UnitEnum|null $navigationGroup = 'Localization';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Currency Information')
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

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),

                        TextInput::make('symbol')
                            ->label('Symbol')
                            ->required()
                            ->maxLength(20),

                        TextInput::make('country_code')
                            ->label('Country Code')
                            ->maxLength(10),

                        TextInput::make('exchange_rate')
                            ->label('Exchange Rate')
                            ->numeric()
                            ->required()
                            ->default(1),

                        Select::make('symbol_position')
                            ->label('Symbol Position')
                            ->options([
                                'before' => 'Before',
                                'after' => 'After',
                            ])
                            ->required()
                            ->default('before'),

                        TextInput::make('decimal_places')
                            ->label('Decimal Places')
                            ->numeric()
                            ->required()
                            ->default(2),

                        Toggle::make('is_default')
                            ->label('Default Currency')
                            ->default(false),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn ($state, Currency $record): string => $record->getName('ar')),

                Tables\Columns\TextColumn::make('symbol')
                    ->label('Symbol')
                    ->sortable(),

                Tables\Columns\TextColumn::make('country_code')
                    ->label('Country')
                    ->sortable(),

                Tables\Columns\TextColumn::make('exchange_rate')
                    ->label('Exchange Rate')
                    ->sortable(),

                Tables\Columns\TextColumn::make('symbol_position')
                    ->label('Symbol Position')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}