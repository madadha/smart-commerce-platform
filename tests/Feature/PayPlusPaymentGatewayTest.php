<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProviderSetting;
use App\Services\Payments\PaymentProviderConnectionService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPlusPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_sandbox_hosted_payment_link_with_authenticated_request(): void
    {
        $this->configurePayPlus();
        Http::fake([
            'https://restapidev.payplus.co.il/api/v1.0/PaymentPages/generateLink' => Http::response([
                'results' => ['status' => 'success', 'code' => 0],
                'data' => [
                    'page_request_uid' => 'page-request-123',
                    'payment_page_link' => 'https://payments.example.test/page-request-123',
                ],
            ]),
        ]);

        $payment = app(PaymentService::class)->createAttempt(
            $this->createOrder(),
            'payplus',
            'payplus-attempt-1',
            ['locale' => 'ar'],
        );

        $this->assertSame('page-request-123', $payment->provider_reference);
        $this->assertSame('https://payments.example.test/page-request-123', $payment->checkout_url);
        $this->assertNotNull($payment->checkout_expires_at);
        Http::assertSent(function (Request $request): bool {
            return $request->hasHeader('api-key', 'sandbox-api-key')
                && $request->hasHeader('secret-key', 'sandbox-secret-key')
                && $request['payment_page_uid'] === 'sandbox-page-uid'
                && $request['currency_code'] === 'ILS'
                && $request['amount'] === 100.0
                && $request['language_code'] === 'ar'
                && $request['send_failure_callback'] === true;
        });
    }

    public function test_valid_signed_webhook_marks_payment_paid_only_once(): void
    {
        $this->configurePayPlus();
        $order = $this->createOrder();
        $payment = Payment::query()->forceCreate([
            'order_id' => $order->id,
            'payment_method' => 'payplus',
            'provider' => 'payplus',
            'provider_reference' => 'page-request-456',
            'status' => 'pending',
            'amount' => 100,
            'is_active' => true,
        ]);
        $payload = [
            'transaction_uid' => 'transaction-456',
            'payment_request_uid' => 'page-request-456',
            'transaction_type' => 'charge',
            'status' => 'success',
            'status_code' => 0,
            'amount' => 100,
            'currency_code' => 'ILS',
            'more_info_2' => (string) $payment->id,
        ];
        $rawPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $hash = base64_encode(hash_hmac('sha256', $rawPayload, 'sandbox-secret-key', true));
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_HASH' => $hash,
            'HTTP_USER_AGENT' => 'PayPlus',
        ];

        $this->call('POST', '/payments/webhooks/payplus', [], [], [], $server, $rawPayload)
            ->assertOk()
            ->assertJson(['received' => true, 'processed' => true]);
        $this->call('POST', '/payments/webhooks/payplus', [], [], [], $server, $rawPayload)
            ->assertOk()
            ->assertJson(['received' => true, 'processed' => false]);

        $payment->refresh();
        $this->assertSame('paid', $payment->status->value);
        $this->assertSame('transaction-456', $payment->transaction_id);
        $this->assertSame('paid', $order->fresh()->payment_status->value);
        $this->assertDatabaseCount('payment_webhook_events', 1);
    }

    public function test_invalid_payplus_signature_is_rejected_without_mutation(): void
    {
        $this->configurePayPlus();

        $this->withHeaders([
            'hash' => 'invalid',
            'user-agent' => 'PayPlus',
        ])->postJson('/payments/webhooks/payplus', ['transaction_uid' => 'bad'])
            ->assertUnauthorized();

        $this->assertDatabaseCount('payment_webhook_events', 0);
    }

    public function test_connection_check_uses_active_environment_and_updates_status(): void
    {
        $provider = $this->configurePayPlus(connectionStatus: 'untested');
        Http::fake([
            'https://restapidev.payplus.co.il/api/v1.0/PaymentPages/list/*' => Http::response(['data' => []]),
        ]);

        $verified = app(PaymentProviderConnectionService::class)->test($provider);

        $this->assertTrue($verified);
        $this->assertSame('verified', $provider->fresh()->connection_status);
        $this->assertNotNull($provider->fresh()->last_tested_at);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://restapidev.payplus.co.il/api/v1.0/PaymentPages/list/?terminal_uid=sandbox-terminal-uid&take=1');
    }

    public function test_payplus_refund_uses_transaction_uid_and_records_partial_refund(): void
    {
        $this->configurePayPlus();
        $order = $this->createOrder();
        $payment = Payment::query()->forceCreate([
            'order_id' => $order->id,
            'payment_method' => 'payplus',
            'provider' => 'payplus',
            'provider_reference' => 'page-request-refund',
            'transaction_id' => 'transaction-refund',
            'status' => 'paid',
            'amount' => 100,
            'paid_at' => now(),
            'is_active' => true,
        ]);
        Http::fake([
            'https://restapidev.payplus.co.il/api/v1.0/Transactions/RefundByTransactionUID' => Http::response([
                'results' => ['status' => 'success', 'code' => 0],
                'data' => ['transaction_uid' => 'refund-transaction-1'],
            ]),
        ]);

        $payment = app(PaymentService::class)->refund($payment, 25, ['reason' => 'Customer request']);

        $this->assertSame('partially_refunded', $payment->status->value);
        $this->assertSame(25.0, (float) $payment->refunded_amount);
        Http::assertSent(fn (Request $request): bool => $request['transaction_uid'] === 'transaction-refund'
            && $request['amount'] === 25.0
            && $request['more_info'] === 'Customer request');
    }

    private function configurePayPlus(string $connectionStatus = 'verified'): PaymentProviderSetting
    {
        $provider = PaymentProviderSetting::query()->where('provider', 'payplus')->firstOrFail();
        $provider->update([
            'is_enabled' => true,
            'mode' => 'sandbox',
            'connection_status' => $connectionStatus,
            'sandbox_credentials' => [
                'api_key' => 'sandbox-api-key',
                'secret_key' => 'sandbox-secret-key',
                'payment_page_uid' => 'sandbox-page-uid',
                'terminal_uid' => 'sandbox-terminal-uid',
            ],
        ]);

        return $provider->fresh();
    }

    private function createOrder(): Order
    {
        $currency = Currency::query()->forceCreate([
            'name' => ['en' => 'Israeli Shekel'],
            'code' => 'ILS',
            'symbol' => '₪',
            'exchange_rate' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        return Order::query()->forceCreate([
            'order_number' => 'ORD-PAYPLUS-'.uniqid(),
            'currency_id' => $currency->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100,
            'grand_total' => 100,
            'is_active' => true,
        ]);
    }
}
