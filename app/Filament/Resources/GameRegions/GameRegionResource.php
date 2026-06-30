<?php

namespace App\Filament\Resources\GameRegions;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\GameRegions\Pages\CreateGameRegion;
use App\Filament\Resources\GameRegions\Pages\EditGameRegion;
use App\Filament\Resources\GameRegions\Pages\ListGameRegions;
use App\Filament\Resources\GameRegions\Schemas\GameRegionForm;
use App\Filament\Resources\GameRegions\Tables\GameRegionsTable;
use App\Models\GameRegion;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GameRegionResource extends BaseResource
{
    protected static ?string $model = GameRegion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Game Regions';

    protected static ?string $modelLabel = 'Game Region';

    protected static ?string $pluralModelLabel = 'Game Regions';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 36;

    public static function form(Schema $schema): Schema
    {
        return GameRegionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GameRegionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGameRegions::route('/'),
            'create' => CreateGameRegion::route('/create'),
            'edit' => EditGameRegion::route('/{record}/edit'),
        ];
    }
}
