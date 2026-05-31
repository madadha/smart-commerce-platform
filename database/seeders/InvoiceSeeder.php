<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $order = Order::query()
            ->with(['items', 'customer', 'currency'])
            ->first();

        if (! $order) {
            return;
        }

        $invoice = Invoice::query()->updateOrCreate(
            [
                'invoice_number' => 'INV-DEMO-00001',
            ],
            [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'currency_id' => $order->currency_id,
                'status' => InvoiceStatus::Issued,
                'discount_total' => $order->discount_total,
                'tax_total' => $order->tax_total,
                'shipping_total' => $order->shipping_total,
                'paid_total' => $order->paid_total,
                'billing_address' => $order->billing_address,
                'seller_details' => [
                    'name' => 'Smart Commerce Platform',
                    'email' => 'info@example.com',
                    'phone' => '+972000000000',
                    'tax_number' => '000000000',
                ],
                'issued_at' => now()->toDateString(),
                'due_at' => now()->addDays(14)->toDateString(),
                'customer_notes' => 'Demo invoice created from order.',
                'internal_notes' => 'Created by InvoiceSeeder.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        foreach ($order->items as $orderItem) {
            InvoiceItem::query()->updateOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'order_item_id' => $orderItem->id,
                ],
                [
                    'product_id' => $orderItem->product_id,
                    'product_variant_id' => $orderItem->product_variant_id,
                    'item_name' => $orderItem->product_name,
                    'sku' => $orderItem->sku,
                    'quantity' => $orderItem->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'discount_total' => $orderItem->discount_total,
                    'tax_total' => $orderItem->tax_total,
                    'options' => $orderItem->options,
                    'notes' => $orderItem->notes,
                ]
            );
        }

        $invoice->refresh();
        $invoice->recalculateTotals();
    }
}