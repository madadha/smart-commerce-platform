<?php

namespace App\Payments\Gateways;

use App\Contracts\Payments\PaymentGateway;
use App\Enums\PaymentTransactionStatus;
use App\Models\Payment;
use App\Models\PaymentProviderSetting;
use App\Payments\GatewayPaymentResult;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class PayPlusPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'payplus';
    }

    public function createPayment(Payment $payment, array $context = []): GatewayPaymentResult
    {
        $settings = $this->settings();
        $credentials = $settings->activeCredentials();
        $payment->loadMissing(['order.items', 'order.customer', 'order.currency']);
        $order = $payment->order;

        if (! $order) {
            throw new RuntimeException('PayPlus payment must belong to an order.');
        }

        $response = $this->client($credentials)
            ->post($this->baseUrl($settings).'/PaymentPages/generateLink', [
                'payment_page_uid' => $credentials['payment_page_uid'],
                'charge_method' => 1,
                'language_code' => $this->payPlusLocale($context['locale'] ?? app()->getLocale()),
                'amount' => (float) $payment->amount,
                'currency_code' => $order->currency?->code ?? 'ILS',
                'expiry_datetime' => 30,
                'refURL_success' => $this->returnUrl($payment, 'success'),
                'refURL_failure' => $this->returnUrl($payment, 'failure'),
                'refURL_cancel' => $this->returnUrl($payment, 'cancel'),
                'refURL_callback' => route('payments.webhooks.payplus'),
                'send_failure_callback' => true,
                'sendEmailApproval' => true,
                'sendEmailFailure' => false,
                'create_token' => false,
                'more_info' => $order->order_number,
                'more_info_2' => (string) $payment->id,
                'customer' => array_filter([
                    'customer_name' => $order->customer?->getDisplayName(),
                    'email' => $order->customer?->email,
                    'phone' => $order->customer?->phone,
                ]),
                'items' => $order->items->map(fn ($item): array => [
                    'name' => is_array($item->product_name)
                        ? ($item->product_name['en'] ?? $item->sku ?? 'Product')
                        : (string) $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->unit_price,
                ])->values()->all(),
            ]);

        $response->throw();
        $payload = $response->json();

        if (data_get($payload, 'results.status') !== 'success') {
            throw new RuntimeException((string) data_get($payload, 'results.description', 'PayPlus rejected the payment request.'));
        }

        $reference = data_get($payload, 'data.page_request_uid');
        $redirectUrl = data_get($payload, 'data.payment_page_link');

        if (! is_string($reference) || ! is_string($redirectUrl)) {
            throw new RuntimeException('PayPlus response is missing the payment reference or checkout URL.');
        }

        return new GatewayPaymentResult(
            status: PaymentTransactionStatus::Pending,
            providerReference: $reference,
            redirectUrl: $redirectUrl,
            payload: ['create_payment' => $payload],
        );
    }

    public function refund(Payment $payment, float $amount, array $context = []): GatewayPaymentResult
    {
        $settings = $this->settings();
        $credentials = $settings->activeCredentials();
        $transactionUid = $payment->transaction_id;

        if (! $transactionUid) {
            throw new RuntimeException('PayPlus transaction UID is missing.');
        }

        $response = $this->client($credentials)
            ->post($this->baseUrl($settings).'/Transactions/RefundByTransactionUID', [
                'transaction_uid' => $transactionUid,
                'amount' => $amount,
                'more_info' => $context['reason'] ?? "Refund for {$payment->payment_number}",
                'initial_invoice' => false,
            ]);

        $response->throw();
        $payload = $response->json();

        if (data_get($payload, 'results.status') !== 'success') {
            throw new RuntimeException((string) data_get($payload, 'results.description', 'PayPlus refund failed.'));
        }

        return new GatewayPaymentResult(
            status: ((float) $payment->refunded_amount + $amount) >= (float) $payment->amount
                ? PaymentTransactionStatus::Refunded
                : PaymentTransactionStatus::PartiallyRefunded,
            providerReference: $payment->provider_reference,
            payload: ['refund' => $payload],
        );
    }

    public function verifySignature(string $rawPayload, ?string $hash, ?string $userAgent): bool
    {
        $secret = $this->settings()->activeCredentials()['secret_key'] ?? null;

        if (! is_string($secret) || ! is_string($hash) || strcasecmp((string) $userAgent, 'PayPlus') !== 0) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $rawPayload, $secret, true));

        return hash_equals($expected, $hash);
    }

    private function settings(): PaymentProviderSetting
    {
        $settings = PaymentProviderSetting::query()->where('provider', 'payplus')->first();

        if (! $settings?->is_enabled || ! $settings->hasRequiredCredentials()) {
            throw new RuntimeException('PayPlus is not fully configured.');
        }

        return $settings;
    }

    private function client(array $credentials): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'api-key' => $credentials['api_key'],
                'secret-key' => $credentials['secret_key'],
            ])
            ->timeout(20)
            ->retry(2, 250, throw: false);
    }

    private function baseUrl(PaymentProviderSetting $settings): string
    {
        return $settings->mode === 'live'
            ? 'https://restapi.payplus.co.il/api/v1.0'
            : 'https://restapidev.payplus.co.il/api/v1.0';
    }

    private function returnUrl(Payment $payment, string $status): string
    {
        return URL::signedRoute('payments.payplus.return', [
            'payment' => $payment->id,
            'status' => $status,
        ]);
    }

    private function payPlusLocale(string $locale): string
    {
        return match ($locale) {
            'ar' => 'ar',
            'en' => 'en',
            default => 'he',
        };
    }
}
