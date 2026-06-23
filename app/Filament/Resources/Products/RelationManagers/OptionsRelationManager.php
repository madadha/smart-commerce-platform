<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    protected static ?string $title = 'Product Options';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name.ar')->label('Name (Arabic)')->required(),
            TextInput::make('name.he')->label('Name (Hebrew)')->required(),
            TextInput::make('name.en')->label('Name (English)')->required(),
            TextInput::make('slug')->helperText('Stable key used by variants, e.g. storage or color')->required(),
            Select::make('type')->options(['select' => 'Select', 'button' => 'Button', 'color' => 'Color', 'text' => 'Text'])->default('button')->required()->live(),
            Toggle::make('is_required')->default(true),
            Toggle::make('is_active')->default(true),
            TextInput::make('sort_order')->numeric()->default(0),
            Repeater::make('values')->schema([
                TextInput::make('value')->label('Technical value')->required(),
                TextInput::make('ar')->label('Arabic')->required(),
                TextInput::make('he')->label('Hebrew')->required(),
                TextInput::make('en')->label('English')->required(),
                ColorPicker::make('color')
                    ->label('Color')
                    ->helperText('Choose the exact color. The hexadecimal value is saved automatically.')
                    ->visible(fn (Get $get): bool => $get('../../type') === 'color')
                    ->required(fn (Get $get): bool => $get('../../type') === 'color'),
            ])->columns(5)->columnSpanFull()->addActionLabel('Add option value'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name.ar')->label('Option'),
                Tables\Columns\TextColumn::make('slug')->badge(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('values')->label('Values')->formatStateUsing(fn ($state) => collect($state ?? [])->pluck('value')->implode(', ')),
                Tables\Columns\IconColumn::make('is_required')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([CreateAction::make(), BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
