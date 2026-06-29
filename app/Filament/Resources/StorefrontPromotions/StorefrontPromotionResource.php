<?php

namespace App\Filament\Resources\StorefrontPromotions;

use App\Filament\Resources\StorefrontPromotions\Pages\CreateStorefrontPromotion;
use App\Filament\Resources\StorefrontPromotions\Pages\EditStorefrontPromotion;
use App\Filament\Resources\StorefrontPromotions\Pages\ListStorefrontPromotions;
use App\Filament\Resources\StorefrontPromotions\Schemas\StorefrontPromotionForm;
use App\Filament\Resources\StorefrontPromotions\Tables\StorefrontPromotionsTable;
use App\Models\StorefrontPromotion;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StorefrontPromotionResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = StorefrontPromotion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Storefront Promotions';

    protected static ?string $modelLabel = 'Storefront Promotion';

    protected static ?string $pluralModelLabel = 'Storefront Promotions';

    protected static string|\UnitEnum|null $navigationGroup = 'Storefront';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return StorefrontPromotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StorefrontPromotionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStorefrontPromotions::route('/'),
            'create' => CreateStorefrontPromotion::route('/create'),
            'edit' => EditStorefrontPromotion::route('/{record}/edit'),
        ];
    }
}
