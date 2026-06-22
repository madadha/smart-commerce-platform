<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'idempotency_key',
        'order_id',
        'customer_id',
        'currency_id',
        'payment_method',
        'status',
        'amount',
        'refunded_amount',
        'transaction_id',
        'provider',
        'provider_reference',
        'provider_payload',
        'failure_code',
        'failure_message',
        'paid_at',
        'failed_at',
        'refunded_at',
        'internal_notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'status' => PaymentTransactionStatus::class,
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'provider_payload' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            if (blank($payment->payment_number)) {
                $payment->payment_number = self::generatePaymentNumber();
            }
        });

        static::saved(function (Payment $payment): void {
            $payment->syncOrderPaymentTotals();
        });

        static::deleted(function (Payment $payment): void {
            $payment->syncOrderPaymentTotals();
        });
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY-'.now()->format('Ymd').'-';

        $count = self::query()
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentTransactionStatus::Paid;
    }

    public function isRefunded(): bool
    {
        return $this->status === PaymentTransactionStatus::Refunded;
    }

    public function syncOrderPaymentTotals(): void
    {
        if (! $this->order) {
            return;
        }

        $payments = $this->order->payments()->get(['status', 'amount', 'refunded_amount']);
        $grossPaid = 0.0;
        $refundedTotal = 0.0;

        foreach ($payments as $payment) {
            if (in_array($payment->status, [
                PaymentTransactionStatus::Paid,
                PaymentTransactionStatus::PartiallyRefunded,
                PaymentTransactionStatus::Refunded,
            ], true)) {
                $grossPaid += (float) $payment->amount;
            }

            if (in_array($payment->status, [
                PaymentTransactionStatus::PartiallyRefunded,
                PaymentTransactionStatus::Refunded,
            ], true)) {
                $refundedAmount = (float) $payment->refunded_amount;
                $refundedTotal += $refundedAmount > 0 ? $refundedAmount : (float) $payment->amount;
            } else {
                $refundedTotal += (float) $payment->refunded_amount;
            }
        }

        $paidTotal = max($grossPaid - $refundedTotal, 0);

        $this->order->paid_total = $paidTotal;

        if ($refundedTotal > 0 && $paidTotal <= 0) {
            $this->order->payment_status = PaymentStatus::Refunded;
        } elseif ((float) $paidTotal <= 0) {
            $this->order->payment_status = PaymentStatus::Unpaid;
        } elseif ((float) $paidTotal >= (float) $this->order->grand_total) {
            $this->order->payment_status = PaymentStatus::Paid;
            $this->order->paid_at = $this->order->paid_at ?: now();
        } else {
            $this->order->payment_status = PaymentStatus::PartiallyPaid;
        }

        $this->order->saveQuietly();
    }
}
