<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Mail\StorefrontOrderCompletedMail;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected ?string $originalOrderStatus = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->originalOrderStatus = $this->normalizeOrderStatus($this->record?->status ?? ($data['status'] ?? null));

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Order $order */
        $order = $this->record->fresh([
            'customer',
            'user',
            'items.product',
            'currency',
            'shippingMethod',
        ]);

        $currentStatus = $this->normalizeOrderStatus($order->status);

        if ($this->originalOrderStatus !== 'completed' && $currentStatus === 'completed') {
            $this->sendCompletedOrderEmail($order);
        }

        $this->originalOrderStatus = $currentStatus;
    }

    private function sendCompletedOrderEmail(Order $order): void
    {
        $email = $this->resolveCustomerEmail($order);

        if (! $email) {
            return;
        }

        $mailLocale = $this->resolveMailLocale($order);

        try {
            Mail::to($email)->send(new StorefrontOrderCompletedMail($order, $mailLocale));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function resolveCustomerEmail(Order $order): ?string
    {
        foreach ([
            $order->customer_email ?? null,
            $order->email ?? null,
            $order->customer?->email ?? null,
            $order->user?->email ?? null,
        ] as $email) {
            if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    private function resolveMailLocale(Order $order): string
    {
        $locale = $order->locale
            ?? $order->language
            ?? request()->query('lang')
            ?? request()->input('lang')
            ?? session('storefront_locale')
            ?? App::getLocale()
            ?? 'ar';

        return in_array($locale, ['ar', 'he', 'en'], true) ? $locale : 'ar';
    }

    private function normalizeOrderStatus(mixed $status): string
    {
        if ($status instanceof BackedEnum) {
            $status = $status->value;
        }

        return strtolower(trim((string) $status));
    }
}
