<?php

namespace App\Filament\Resources\Games;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Games\Pages\CreateGame;
use App\Filament\Resources\Games\Pages\EditGame;
use App\Filament\Resources\Games\Pages\ListGames;
use App\Filament\Resources\Games\Schemas\GameForm;
use App\Filament\Resources\Games\Tables\GamesTable;
use App\Models\Game;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GameResource extends BaseResource
{
    protected static ?string $model = Game::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationLabel = 'Games';

    protected static ?string $modelLabel = 'Game';

    protected static ?string $pluralModelLabel = 'Games';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 35;

    public static function form(Schema $schema): Schema
    {
        return GameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GamesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGames::route('/'),
            'create' => CreateGame::route('/create'),
            'edit' => EditGame::route('/{record}/edit'),
        ];
    }
}
