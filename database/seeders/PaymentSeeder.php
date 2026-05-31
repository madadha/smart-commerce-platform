<?php

namespace Database\Seeders;

use App\Enums\PaymentTransactionStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $order = Order::query()->first();

        if (! $order) {
            return;
        }

        Payment::query()->updateOrCreate(
            [
                'payment_number' => 'PAY-DEMO-00001',
            ],
            [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'currency_id' => $order->currency_id,
                'payment_method' => 'credit_card',
                'status' => PaymentTransactionStatus::Paid,
                'amount' => $order->grand_total,
                'refunded_amount' => 0,
                'transaction_id' => 'TRX-DEMO-123456',
                'provider' => 'demo_gateway',
                'provider_reference' => 'DEMO-REF-001',
                'provider_payload' => [
                    'gateway' => 'demo',
                    'approved' => true,
                    'last4' => '1234',
                ],
                'paid_at' => now(),
                'internal_notes' => 'Demo payment for first order.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }
}