<?php

namespace App\Filament\Resources\ProductMedia;

use App\Filament\Resources\ProductMedia\Pages\CreateProductMedia;
use App\Filament\Resources\ProductMedia\Pages\EditProductMedia;
use App\Filament\Resources\ProductMedia\Pages\ListProductMedia;
use App\Filament\Resources\ProductMedia\Schemas\ProductMediaForm;
use App\Filament\Resources\ProductMedia\Tables\ProductMediaTable;
use App\Models\ProductMedia;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductMediaResource extends Resource
{
    protected static ?string $model = ProductMedia::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Product Media';

    protected static ?string $modelLabel = 'Product Media';

    protected static ?string $pluralModelLabel = 'Product Media';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return ProductMediaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductMediaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductMedia::route('/'),
            'create' => CreateProductMedia::route('/create'),
            'edit' => EditProductMedia::route('/{record}/edit'),
        ];
    }
}