<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\DigitalCodeStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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

class DigitalCodesRelationManager extends RelationManager
{
    protected static string $relationship = 'digitalCodes';

    protected static ?string $title = 'Digital Codes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_variant_id')->label('Variant')->options(fn () => $this->getOwnerRecord()->variants->mapWithKeys(fn ($variant) => [$variant->id => $variant->getName('en') . ' / ' . ($variant->sku ?? '-')]))->searchable(),
            TextInput::make('code')->password()->revealable()->required()->unique(ignoreRecord: true),
            Select::make('status')->options(collect(DigitalCodeStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()]))->default(DigitalCodeStatus::Available->value)->required(),
            Select::make('source')->options(['manual' => 'Manual', 'supplier' => 'Supplier', 'import' => 'Import', 'api' => 'API'])->default('manual'),
            DateTimePicker::make('expires_at'),
            Toggle::make('is_active')->default(true),
            Textarea::make('internal_notes')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productVariant.name.en')->label('Variant')->placeholder('-'),
                Tables\Columns\TextColumn::make('code')->formatStateUsing(fn ($state) => strlen($state) > 8 ? substr($state, 0, 4) . '••••' . substr($state, -4) : '••••••••')->copyable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('source'),
                Tables\Columns\TextColumn::make('expires_at')->dateTime(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([CreateAction::make(), BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
