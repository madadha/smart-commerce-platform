<?php

namespace App\Filament\Resources\Shipments;

use App\Enums\ShipmentStatus;
use App\Filament\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Resources\Shipments\Pages\EditShipment;
use App\Filament\Resources\Shipments\Pages\ListShipments;
use App\Filament\Resources\Shipments\RelationManagers\EventsRelationManager;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Shipments & Tracking';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Shipment')->schema([
                Select::make('order_id')->options(fn () => Order::query()->latest()->limit(500)->pluck('order_number', 'id'))->searchable()->required(),
                Select::make('shipping_method_id')->options(fn () => ShippingMethod::query()->get()->mapWithKeys(fn ($m) => [$m->id => $m->getName('en')]))->searchable(),
                Select::make('status')->options(collect(ShipmentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))->required()->default('pending'),
                TextInput::make('shipment_number')->disabled()->dehydrated(false),
            ])->columns(2),
            Section::make('Order Shipping Address')->schema([
                Textarea::make('order_shipping_address')
                    ->label('Order Shipping Address')
                    ->rows(4)
                    ->disabled()
                    ->dehydrated(false)
                    ->default(fn (?Shipment $record): string => self::formatShippingAddress($record?->order?->shipping_address)),
            ]),
            Section::make('Carrier & Tracking')->schema([
                TextInput::make('carrier_name')->maxLength(255),
                TextInput::make('carrier_service')->maxLength(255),
                TextInput::make('tracking_number')->maxLength(255),
                TextInput::make('tracking_url')->url()->maxLength(2048),
                TextInput::make('weight')->numeric()->minValue(0)->suffix('kg'),
                TextInput::make('shipping_cost')->numeric()->minValue(0),
                TextInput::make('estimated_delivery_at')->type('datetime-local'),
                Textarea::make('internal_notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('shipment_number')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('order.order_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('order_shipping_address')
                ->label('Address')
                ->state(fn (Shipment $record): string => self::formatShippingAddress($record->shipping_address ?? $record->order?->shipping_address))
                ->limit(35)
                ->tooltip(fn (Shipment $record): string => self::formatShippingAddress($record->shipping_address ?? $record->order?->shipping_address)),
            Tables\Columns\TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => $state instanceof ShipmentStatus ? $state->label() : $state)->color(fn ($state) => $state instanceof ShipmentStatus ? $state->color() : 'gray'),
            Tables\Columns\TextColumn::make('carrier_name')->placeholder('-'),
            Tables\Columns\TextColumn::make('tracking_number')->searchable()->copyable()->placeholder('-'),
            Tables\Columns\TextColumn::make('estimated_delivery_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->since()->sortable(),
        ])->defaultSort('id', 'desc')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListShipments::route('/'), 'create' => CreateShipment::route('/create'), 'edit' => EditShipment::route('/{record}/edit')];
    }

    public static function getRelations(): array
    {
        return [EventsRelationManager::class];
    }

    private static function formatShippingAddress(mixed $address): string
    {
        if (is_string($address)) {
            $decoded = json_decode($address, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $address = $decoded;
            }
        }

        if (! is_array($address)) {
            return '-';
        }

        $parts = array_filter([
            $address['address'] ?? null,
            $address['city'] ?? null,
            $address['country'] ?? null,
            isset($address['country_id']) ? 'Country ID: '.$address['country_id'] : null,
        ]);

        return $parts ? implode(', ', $parts) : '-';
    }
}
