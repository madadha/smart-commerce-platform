<?php

namespace App\Filament\Resources\ProductDigitalCodes\Pages;

use App\Filament\Resources\ProductDigitalCodes\ProductDigitalCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductDigitalCode extends EditRecord
{
    protected static string $resource = ProductDigitalCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
