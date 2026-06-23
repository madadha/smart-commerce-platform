<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Product Variants';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name.ar')->label('Name (Arabic)'),
            TextInput::make('name.he')->label('Name (Hebrew)'),
            TextInput::make('name.en')->label('Name (English)'),
            TextInput::make('sku')->unique(ignoreRecord: true),
            KeyValue::make('option_values')->keyLabel('Option slug')->valueLabel('Technical value')->helperText('Must match Product Options, e.g. storage = 256gb')->columnSpanFull(),
            FileUpload::make('image')->image()->disk('public')->directory('products/variants')->visibility('public')->imageEditor(),
            TextInput::make('price')->numeric()->helperText('Leave empty to use the product price.'),
            TextInput::make('sale_price')->numeric(),
            Toggle::make('track_stock')->default(true),
            TextInput::make('stock_quantity')->numeric()->default(0),
            Toggle::make('is_default')->default(false),
            Toggle::make('is_active')->default(true),
            TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->disk('public')->square(),
                Tables\Columns\TextColumn::make('name.ar')->label('Variant'),
                Tables\Columns\TextColumn::make('sku')->searchable(),
                Tables\Columns\TextColumn::make('option_values')->formatStateUsing(fn ($state) => collect($state ?? [])->map(fn ($value, $key) => "$key: $value")->implode(' | ')),
                Tables\Columns\TextColumn::make('price')->money('ILS'),
                Tables\Columns\TextColumn::make('stock_quantity')->label('Stock'),
                Tables\Columns\IconColumn::make('is_default')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([CreateAction::make(), BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
