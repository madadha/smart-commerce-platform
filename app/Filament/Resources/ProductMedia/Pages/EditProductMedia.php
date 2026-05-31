<?php

namespace App\Filament\Resources\ProductMedia\Pages;

use App\Filament\Resources\ProductMedia\ProductMediaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductMedia extends EditRecord
{
    protected static string $resource = ProductMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
