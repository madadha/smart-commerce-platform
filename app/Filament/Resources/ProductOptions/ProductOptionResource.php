<?php

namespace App\Filament\Resources\ProductOptions;

use App\Filament\Resources\ProductOptions\Pages\CreateProductOption;
use App\Filament\Resources\ProductOptions\Pages\EditProductOption;
use App\Filament\Resources\ProductOptions\Pages\ListProductOptions;
use App\Filament\Resources\ProductOptions\Schemas\ProductOptionForm;
use App\Filament\Resources\ProductOptions\Tables\ProductOptionsTable;
use App\Models\ProductOption;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductOptionResource extends Resource
{
    protected static ?string $model = ProductOption::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Product Options';

    protected static ?string $modelLabel = 'Product Option';

    protected static ?string $pluralModelLabel = 'Product Options';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return ProductOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductOptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductOptions::route('/'),
            'create' => CreateProductOption::route('/create'),
            'edit' => EditProductOption::route('/{record}/edit'),
        ];
    }
}