<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'order_id',
        'customer_id',
        'currency_id',
        'status',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'grand_total',
        'paid_total',
        'billing_address',
        'seller_details',
        'issued_at',
        'due_at',
        'paid_at',
        'customer_notes',
        'internal_notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'billing_address' => 'array',
        'seller_details' => 'array',
        'issued_at' => 'date',
        'due_at' => 'date',
        'paid_at' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice): void {
            if (blank($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }

            if (blank($invoice->issued_at)) {
                $invoice->issued_at = now()->toDateString();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $count = self::query()
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
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

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');

        $this->subtotal = $subtotal;

        $this->grand_total = max(
            0,
            (float) $this->subtotal
            - (float) $this->discount_total
            + (float) $this->tax_total
            + (float) $this->shipping_total
        );

        $this->saveQuietly();
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::Paid;
    }
}