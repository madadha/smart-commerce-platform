<?php

namespace App\Filament\Resources\Countries;

use App\Filament\Resources\Countries\Pages\CreateCountry;
use App\Filament\Resources\Countries\Pages\EditCountry;
use App\Filament\Resources\Countries\Pages\ListCountries;
use App\Models\Country;
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

class CountryResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = Country::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Countries';

    protected static ?string $modelLabel = 'Country';

    protected static ?string $pluralModelLabel = 'Countries';

    protected static string|\UnitEnum|null $navigationGroup = 'Localization';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Country Information')
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

                        Select::make('currency_id')
                            ->label('Currency')
                            ->options(function (): array {
                                return Currency::query()
                                    ->orderBy('sort_order')
                                    ->orderBy('code')
                                    ->get()
                                    ->mapWithKeys(fn (Currency $currency) => [
                                        $currency->id => $currency->code . ' - ' . $currency->getName('ar'),
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload(),

                        TextInput::make('tax_rate')
                            ->label('Tax Rate %')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->helperText('Country tax percentage used during checkout.')
                            ->default(0),

                        TextInput::make('phone_code')
                            ->label('Phone Code')
                            ->maxLength(10),

                        TextInput::make('flag')
                            ->label('Flag')
                            ->maxLength(255),

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
                    ->formatStateUsing(fn ($state, Country $record): string => $record->getName('ar')),

                Tables\Columns\TextColumn::make('currency.code')
                    ->label('Currency')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_rate')
                    ->label('Tax %')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone_code')
                    ->label('Phone Code')
                    ->sortable(),

                Tables\Columns\TextColumn::make('flag')
                    ->label('Flag'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
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
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }
}
