<?php

namespace App\Filament\Resources\StorefrontPromotions\Pages;

use App\Filament\Resources\StorefrontPromotions\StorefrontPromotionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStorefrontPromotions extends ListRecords
{
    protected static string $resource = StorefrontPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
