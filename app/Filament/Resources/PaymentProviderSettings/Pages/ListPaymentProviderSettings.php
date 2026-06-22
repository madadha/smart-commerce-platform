<?php

namespace App\Filament\Resources\PaymentProviderSettings\Pages;

use App\Filament\Resources\PaymentProviderSettings\PaymentProviderSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentProviderSettings extends ListRecords
{
    protected static string $resource = PaymentProviderSettingResource::class;
}
