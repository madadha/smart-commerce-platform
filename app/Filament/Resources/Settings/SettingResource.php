<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = Setting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?string $pluralModelLabel = 'Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Core';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Setting Information')
                    ->schema([
                        Select::make('group')
                            ->label('Group')
                            ->options([
                                'general' => 'General',
                                'store' => 'Store',
                                'theme' => 'Theme',
                                'language' => 'Language',
                                'currency' => 'Currency',
                                'shipping' => 'Shipping',
                                'payment' => 'Payment',
                                'social' => 'Social',
                                'sharing' => 'Sharing',
                                'qr' => 'QR',
                                'barcode' => 'Barcode',
                                'reseller' => 'Reseller',
                                'ai' => 'AI',
                            ])
                            ->searchable()
                            ->required(),

                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'text' => 'Text',
                                'textarea' => 'Textarea',
                                'email' => 'Email',
                                'url' => 'URL',
                                'number' => 'Number',
                                'integer' => 'Integer',
                                'decimal' => 'Decimal',
                                'boolean' => 'Boolean',
                                'color' => 'Color',
                                'json' => 'JSON',
                                'image' => 'Image',
                                'file' => 'File',
                            ])
                            ->required()
                            ->default('text'),

                        Textarea::make('value')
                            ->label('Value')
                            ->rows(5)
                            ->columnSpanFull(),

                        Toggle::make('is_public')
                            ->label('Public')
                            ->helperText('Public settings can be used on the storefront.')
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
                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('group')
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
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}
