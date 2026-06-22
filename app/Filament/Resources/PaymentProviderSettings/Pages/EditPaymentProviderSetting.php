<?php

namespace App\Filament\Resources\PaymentProviderSettings\Pages;

use App\Filament\Resources\PaymentProviderSettings\PaymentProviderSettingResource;
use App\Services\Payments\PaymentProviderConnectionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPaymentProviderSetting extends EditRecord
{
    protected static string $resource = PaymentProviderSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testConnection')
                ->label('Test Connection')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->requiresConfirmation()
                ->modalDescription('This sends the active Sandbox or Live credentials to the provider to verify access.')
                ->action(function (PaymentProviderConnectionService $connections): void {
                    $verified = $connections->test($this->record);

                    Notification::make()
                        ->title($verified ? 'Connection verified' : 'Connection failed')
                        ->body($verified ? 'This provider can now be enabled safely.' : ($this->record->fresh()->last_error ?? 'Review the credentials and try again.'))
                        ->color($verified ? 'success' : 'danger')
                        ->send();

                    $this->refreshFormData([
                        'connection_status',
                        'last_tested_at',
                        'last_error',
                    ]);
                }),
        ];
    }

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
