<?php

namespace App\Filament\Resources\ProductDigitalCodes\Pages;

use App\Filament\Resources\ProductDigitalCodes\ProductDigitalCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductDigitalCodes extends ListRecords
{
    protected static string $resource = ProductDigitalCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
