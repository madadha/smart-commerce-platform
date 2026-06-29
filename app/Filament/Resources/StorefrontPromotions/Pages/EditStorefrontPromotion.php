<?php

namespace App\Filament\Resources\StorefrontPromotions\Pages;

use App\Filament\Resources\StorefrontPromotions\StorefrontPromotionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStorefrontPromotion extends EditRecord
{
    protected static string $resource = StorefrontPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
