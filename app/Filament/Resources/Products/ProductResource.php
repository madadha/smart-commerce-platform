<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\DigitalCodesRelationManager;
use App\Filament\Resources\Products\RelationManagers\MediaRelationManager;
use App\Filament\Resources\Products\RelationManagers\OptionsRelationManager;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $modelLabel = 'Product';

    protected static ?string $pluralModelLabel = 'Products';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $stockColumn = static::resolveStockColumn();

        if (! $stockColumn) {
            return null;
        }

        $count = Product::query()
            ->where('is_active', true)
            ->where(function (Builder $query) use ($stockColumn) {
                $query->where($stockColumn, '<=', 5)
                    ->whereNotNull($stockColumn);
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $stockColumn = static::resolveStockColumn();

        if (! $stockColumn) {
            return null;
        }

        $outOfStockCount = Product::query()
            ->where('is_active', true)
            ->whereNotNull($stockColumn)
            ->where($stockColumn, '<=', 0)
            ->count();

        return $outOfStockCount > 0 ? 'danger' : 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $stockColumn = static::resolveStockColumn();

        if (! $stockColumn) {
            return null;
        }

        $outOfStockCount = Product::query()
            ->where('is_active', true)
            ->whereNotNull($stockColumn)
            ->where($stockColumn, '<=', 0)
            ->count();

        $lowStockCount = Product::query()
            ->where('is_active', true)
            ->whereNotNull($stockColumn)
            ->where($stockColumn, '>', 0)
            ->where($stockColumn, '<=', 5)
            ->count();

        return "Out of stock: {$outOfStockCount} | Low stock: {$lowStockCount}";
    }

    private static function resolveStockColumn(): ?string
    {
        foreach (['stock_quantity', 'quantity', 'stock'] as $column) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('products', $column)) {
                return $column;
            }
        }

        return null;
    }

    public static function getRelations(): array
    {
        return [
            MediaRelationManager::class,
            OptionsRelationManager::class,
            VariantsRelationManager::class,
            DigitalCodesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
