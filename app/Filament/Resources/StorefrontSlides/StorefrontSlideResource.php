<?php

namespace App\Filament\Resources\StorefrontSlides;

use App\Filament\Resources\StorefrontSlides\Pages\CreateStorefrontSlide;
use App\Filament\Resources\StorefrontSlides\Pages\EditStorefrontSlide;
use App\Filament\Resources\StorefrontSlides\Pages\ListStorefrontSlides;
use App\Filament\Resources\StorefrontSlides\Schemas\StorefrontSlideForm;
use App\Filament\Resources\StorefrontSlides\Tables\StorefrontSlidesTable;
use App\Models\StorefrontSlide;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StorefrontSlideResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = StorefrontSlide::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Storefront Slides';

    protected static ?string $modelLabel = 'Storefront Slide';

    protected static ?string $pluralModelLabel = 'Storefront Slides';

    protected static string|\UnitEnum|null $navigationGroup = 'Storefront';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return StorefrontSlideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StorefrontSlidesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStorefrontSlides::route('/'),
            'create' => CreateStorefrontSlide::route('/create'),
            'edit' => EditStorefrontSlide::route('/{record}/edit'),
        ];
    }
}
