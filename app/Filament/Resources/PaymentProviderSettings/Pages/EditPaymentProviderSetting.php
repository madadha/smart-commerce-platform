<?php

namespace App\Filament\Resources\PaymentProviderSettings\Pages;

use App\Filament\Resources\PaymentProviderSettings\PaymentProviderSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditPaymentProviderSetting extends EditRecord
{
    protected static string $resource = PaymentProviderSettingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $credentialsChanged = ($data['sandbox_credentials'] ?? []) !== ($this->record->sandbox_credentials ?? [])
            || ($data['live_credentials'] ?? []) !== ($this->record->live_credentials ?? [])
            || ($data['mode'] ?? 'sandbox') !== $this->record->mode;

        if ($credentialsChanged) {
            $data['connection_status'] = 'untested';
            $data['last_tested_at'] = null;
            $data['last_error'] = null;
        }

        return $data;
    }
}
