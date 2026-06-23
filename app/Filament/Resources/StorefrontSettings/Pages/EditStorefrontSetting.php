<?php

namespace App\Filament\Resources\StorefrontSettings\Pages;

use App\Filament\Resources\StorefrontSettings\StorefrontSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStorefrontSetting extends EditRecord
{
    protected static string $resource = StorefrontSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
