<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Payments\Gateways\PayPlusPaymentGateway;
use App\Services\Payments\PaymentService;
use App\Services\Payments\WebhookEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class PayPlusPaymentController extends Controller
{
    public function return(Payment $payment, string $status): RedirectResponse
    {
        abort_unless(in_array($status, ['success', 'failure', 'cancel'], true), 404);
        abort_unless($payment->provider === 'payplus' && $payment->order, 404);

        return redirect()->to(URL::signedRoute('storefront.orders.show', [
            'order' => $payment->order_id,
            'lang' => session('storefront_locale', 'ar'),
        ]))->with(
            $payment->isPaid() ? 'success' : 'info',
            $payment->isPaid()
                ? __('storefront.checkout.order_created_successfully')
                : 'Payment response received. Confirmation may take a few moments.',
        );
    }

    public function webhook(
        Request $request,
        PayPlusPaymentGateway $gateway,
        WebhookEventService $events,
        PaymentService $payments,
    ): JsonResponse {
        $rawPayload = $request->getContent();

        if (! $gateway->verifySignature(
            $rawPayload,
            $request->header('hash'),
            $request->header('user-agent'),
        )) {
            return response()->json(['message' => 'Invalid signature.'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid JSON payload.'], Response::HTTP_BAD_REQUEST);
        }

        $transactionId = (string) ($payload['transaction_uid'] ?? data_get($payload, 'data.transaction_uid', ''));
        $requestUid = (string) ($payload['payment_request_uid'] ?? data_get($payload, 'data.payment_request_uid', ''));
        $transactionType = strtolower((string) ($payload['transaction_type'] ?? data_get($payload, 'data.transaction_type', 'payment')));
        $eventId = trim($transactionId !== '' ? $transactionId.':'.$transactionType : $requestUid.':'.$transactionType, ':');

        if ($eventId === '') {
            return response()->json(['message' => 'Missing event identifier.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $processed = $events->process(
            provider: 'payplus',
            eventId: $eventId,
            eventType: $transactionType,
            payload: $payload,
            handler: function (PaymentWebhookEvent $event) use ($payload, $payments, $transactionId, $requestUid, $transactionType): void {
                $payment = $this->resolvePayment($payload, $requestUid, $transactionId);
                $isRefund = str_contains($transactionType, 'refund');
                $this->assertPaymentMatchesCallback($payment, $payload, $isRefund);

                if ($isRefund) {
                    $refundAmount = (float) ($payload['amount'] ?? data_get($payload, 'data.amount', 0));
                    $payments->recordRefund($payment, $refundAmount, $transactionId, [
                        'payplus_webhook' => $event->payload,
                    ]);

                    return;
                }

                if ($this->isSuccessful($payload)) {
                    $payments->markPaid($payment, $transactionId ?: $requestUid, [
                        'payplus_webhook' => $event->payload,
                    ]);

                    return;
                }

                $payments->markFailed(
                    $payment,
                    failureCode: (string) ($payload['status_code'] ?? data_get($payload, 'results.code', 'payplus_rejected')),
                    failureMessage: (string) ($payload['description'] ?? data_get($payload, 'results.description', 'PayPlus payment failed.')),
                    payload: ['payplus_webhook' => $event->payload],
                );
            },
        );

        return response()->json(['received' => true, 'processed' => $processed]);
    }

    private function resolvePayment(array $payload, string $requestUid, string $transactionId): Payment
    {
        $paymentId = $payload['more_info_2'] ?? data_get($payload, 'data.more_info_2');

        return Payment::query()
            ->where('provider', 'payplus')
            ->where(function ($query) use ($paymentId, $requestUid, $transactionId): void {
                if (is_numeric($paymentId)) {
                    $query->whereKey((int) $paymentId);
                }

                if ($requestUid !== '') {
                    $query->orWhere('provider_reference', $requestUid);
                }

                if ($transactionId !== '') {
                    $query->orWhere('transaction_id', $transactionId);
                }
            })
            ->firstOrFail();
    }

    private function assertPaymentMatchesCallback(Payment $payment, array $payload, bool $isRefund): void
    {
        $amount = $payload['amount'] ?? data_get($payload, 'data.amount');
        $currency = $payload['currency_code'] ?? data_get($payload, 'data.currency_code');
        $expectedCurrency = $payment->order?->currency?->code;

        abort_if(! $isRefund && $amount !== null && abs((float) $amount - (float) $payment->amount) > 0.001, 422, 'Payment amount mismatch.');
        abort_if($isRefund && ($amount === null || (float) $amount <= 0 || (float) $amount > (float) $payment->amount), 422, 'Refund amount mismatch.');
        abort_if($currency && $expectedCurrency && strtoupper((string) $currency) !== strtoupper($expectedCurrency), 422, 'Payment currency mismatch.');
    }

    private function isSuccessful(array $payload): bool
    {
        $status = strtolower((string) (
            $payload['status']
            ?? data_get($payload, 'results.status')
            ?? data_get($payload, 'data.status')
            ?? ''
        ));
        $code = $payload['status_code'] ?? data_get($payload, 'results.code');

        return in_array($status, ['success', 'approved', 'paid'], true)
            && ($code === null || in_array((string) $code, ['0', '000'], true));
    }
}
