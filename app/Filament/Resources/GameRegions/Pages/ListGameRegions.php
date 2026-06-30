<?php

namespace App\Filament\Resources\GameRegions\Pages;

use App\Filament\Resources\GameRegions\GameRegionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGameRegions extends ListRecords
{
    protected static string $resource = GameRegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
