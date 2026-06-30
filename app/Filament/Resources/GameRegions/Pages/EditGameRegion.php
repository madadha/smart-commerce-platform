<?php

namespace App\Filament\Resources\GameRegions\Pages;

use App\Filament\Resources\GameRegions\GameRegionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGameRegion extends EditRecord
{
    protected static string $resource = GameRegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
