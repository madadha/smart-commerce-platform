<?php

namespace App\Filament\Resources\Shipments\RelationManagers;

use App\Enums\ShipmentStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'Tracking Timeline';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')->options(collect(ShipmentStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()]))->required(),
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('location')->maxLength(255),
            DateTimePicker::make('occurred_at')->required()->default(now()),
            Textarea::make('description')->columnSpanFull(),
            Toggle::make('is_customer_visible')->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('occurred_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => $state instanceof ShipmentStatus ? $state->label() : $state),
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('location')->placeholder('-'),
            Tables\Columns\IconColumn::make('is_customer_visible')->boolean(),
        ])->defaultSort('occurred_at', 'desc')->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([CreateAction::make()]);
    }
}
