<?php

namespace App\Filament\Resources\ProductDigitalCodes;

use App\Filament\Resources\ProductDigitalCodes\Pages\CreateProductDigitalCode;
use App\Filament\Resources\ProductDigitalCodes\Pages\EditProductDigitalCode;
use App\Filament\Resources\ProductDigitalCodes\Pages\ListProductDigitalCodes;
use App\Filament\Resources\ProductDigitalCodes\Schemas\ProductDigitalCodeForm;
use App\Filament\Resources\ProductDigitalCodes\Tables\ProductDigitalCodesTable;
use App\Models\ProductDigitalCode;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductDigitalCodeResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = ProductDigitalCode::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Digital Codes';

    protected static ?string $modelLabel = 'Digital Code';

    protected static ?string $pluralModelLabel = 'Digital Codes';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return ProductDigitalCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductDigitalCodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductDigitalCodes::route('/'),
            'create' => CreateProductDigitalCode::route('/create'),
            'edit' => EditProductDigitalCode::route('/{record}/edit'),
        ];
    }
}
