<?php

namespace App\Filament\Resources\MediaFiles\Pages;

use App\Filament\Resources\MediaFiles\MediaFileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMediaFile extends EditRecord
{
    protected static string $resource = MediaFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
