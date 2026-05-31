<?php

namespace App\Filament\Resources\ProductMedia\Pages;

use App\Filament\Resources\ProductMedia\ProductMediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductMedia extends ListRecords
{
    protected static string $resource = ProductMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
