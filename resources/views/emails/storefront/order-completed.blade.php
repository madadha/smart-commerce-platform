@php
    $currencySymbol = $order?->currency?->symbol ?? '₪';

    $labels = match ($mailLocale) {
        'he' => [
            'title' => 'ההזמנה שלך הושלמה בהצלחה',
            'intro' => 'שלום, ההזמנה שלך הושלמה ומוכנה. תודה שקנית אצלנו.',
            'order_number' => 'מספר הזמנה',
            'status' => 'סטטוס',
            'total' => 'סה״כ',
            'items' => 'פריטים',
            'view_order' => 'צפייה בהזמנה',
            'download_invoice' => 'הורדת חשבונית PDF',
            'thanks' => 'תודה רבה',
        ],
        'en' => [
            'title' => 'Your order has been completed successfully',
            'intro' => 'Hello, your order has been completed and is ready. Thank you for shopping with us.',
            'order_number' => 'Order Number',
            'status' => 'Status',
            'total' => 'Total',
            'items' => 'Items',
            'view_order' => 'View Order',
            'download_invoice' => 'Download Invoice PDF',
            'thanks' => 'Thank you',
        ],
        default => [
            'title' => 'تم إكمال طلبك بنجاح',
            'intro' => 'مرحبًا، تم إكمال طلبك بنجاح. شكرًا لتسوقك معنا.',
            'order_number' => 'رقم الطلب',
            'status' => 'حالة الطلب',
            'total' => 'المجموع',
            'items' => 'المنتجات',
            'view_order' => 'عرض الطلب',
            'download_invoice' => 'تحميل الفاتورة PDF',
            'thanks' => 'شكرًا لك',
        ],
    };

    $resolveText = function ($value) use ($mailLocale) {
        if (is_array($value)) {
            return $value[$mailLocale] ?? $value['ar'] ?? $value['en'] ?? reset($value) ?? '-';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded[$mailLocale] ?? $decoded['ar'] ?? $decoded['en'] ?? reset($decoded) ?? '-';
            }

            return $value;
        }

        return $value ? (string) $value : '-';
    };

    $itemName = function ($item) use ($mailLocale, $resolveText) {
        if ($item->product && method_exists($item->product, 'getName')) {
            return $item->product->getName($mailLocale);
        }

        return $resolveText($item->product_name ?? $item->name ?? $item->title ?? $item->sku ?? '-');
    };

    $money = function ($value) use ($currencySymbol) {
        return $currencySymbol . ' ' . number_format((float) ($value ?? 0), 2);
    };

    $grandTotal = $order->grand_total ?? $order->total ?? 0;
@endphp

<x-mail::message>
<div dir="{{ $direction }}" style="text-align: {{ $direction === 'rtl' ? 'right' : 'left' }}; font-family: Arial, sans-serif;">

# {{ $labels['title'] }}

{{ $labels['intro'] }}

**{{ $labels['order_number'] }}:** {{ $order->order_number }}  
**{{ $labels['status'] }}:** Completed  
**{{ $labels['total'] }}:** {{ $money($grandTotal) }}

<x-mail::button :url="$orderUrl">
{{ $labels['view_order'] }}
</x-mail::button>

<x-mail::button :url="$invoiceUrl" color="success">
{{ $labels['download_invoice'] }}
</x-mail::button>

## {{ $labels['items'] }}

@foreach($order->items as $item)
- {{ $itemName($item) }} × {{ (int) ($item->quantity ?? 1) }} — {{ $money($item->line_total ?? $item->total ?? (($item->unit_price ?? 0) * ($item->quantity ?? 1))) }}
@endforeach

{{ $labels['thanks'] }},  
{{ config('app.name', 'Smart Commerce Platform') }}

</div>
</x-mail::message>
