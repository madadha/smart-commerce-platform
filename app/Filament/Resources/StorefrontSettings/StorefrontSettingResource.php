<?php

namespace App\Filament\Resources\StorefrontSettings;

use App\Filament\Resources\StorefrontSettings\Pages\CreateStorefrontSetting;
use App\Filament\Resources\StorefrontSettings\Pages\EditStorefrontSetting;
use App\Filament\Resources\StorefrontSettings\Pages\ListStorefrontSettings;
use App\Filament\Resources\StorefrontSettings\Schemas\StorefrontSettingForm;
use App\Filament\Resources\StorefrontSettings\Tables\StorefrontSettingsTable;
use App\Models\StorefrontSetting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StorefrontSettingResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = StorefrontSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Storefront Settings';

    protected static ?string $modelLabel = 'Storefront Setting';

    protected static ?string $pluralModelLabel = 'Storefront Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Storefront';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return StorefrontSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StorefrontSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStorefrontSettings::route('/'),
            'create' => CreateStorefrontSetting::route('/create'),
            'edit' => EditStorefrontSetting::route('/{record}/edit'),
        ];
    }
}
