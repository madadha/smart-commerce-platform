@php
    $currencySymbol = $order?->currency?->symbol ?? '₪';

    $customerName = $order->customer_name ?? $order->customer?->name ?? '';

    $status = $order->status instanceof \BackedEnum
        ? $order->status->value
        : (string) ($order->status ?? '-');

    $paymentStatus = $order->payment_status instanceof \BackedEnum
        ? $order->payment_status->value
        : (string) ($order->payment_status ?? '-');

    $money = function ($value) use ($currencySymbol) {
        return $currencySymbol . ' ' . number_format((float) ($value ?? 0), 2);
    };

    $resolveText = function ($value) use ($locale) {
        if (is_array($value)) {
            return $value[$locale] ?? $value['ar'] ?? $value['en'] ?? reset($value) ?? '-';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded[$locale] ?? $decoded['ar'] ?? $decoded['en'] ?? reset($decoded) ?? '-';
            }

            return $value;
        }

        return $value ? (string) $value : '-';
    };

    $itemName = function ($item) use ($locale, $resolveText) {
        if ($item->product && method_exists($item->product, 'getName')) {
            return $item->product->getName($locale);
        }

        foreach (['product_name', 'name', 'title'] as $column) {
            if (! empty($item->{$column})) {
                return $resolveText($item->{$column});
            }
        }

        return $item->product?->sku ?? '-';
    };
@endphp

<x-mail::message>
<div dir="{{ $direction }}" style="text-align: {{ $direction === 'rtl' ? 'right' : 'left' }}; font-family: Arial, sans-serif;">

# {{ $locale === 'en' ? 'Order Received' : ($locale === 'he' ? 'ההזמנה התקבלה' : 'تم استلام طلبك بنجاح') }}

@if($customerName)
{{ $locale === 'en' ? 'Hello' : ($locale === 'he' ? 'שלום' : 'مرحباً') }} {{ $customerName }},
@endif

{{ $locale === 'en'
    ? 'Thank you for your order. Here is a quick summary:'
    : ($locale === 'he'
        ? 'תודה על ההזמנה. הנה סיכום קצר:'
        : 'شكراً لطلبك. هذا ملخص سريع للطلب:') }}

<x-mail::panel>
**{{ $locale === 'en' ? 'Order Number' : ($locale === 'he' ? 'מספר הזמנה' : 'رقم الطلب') }}:** {{ $order->order_number }}  
**{{ $locale === 'en' ? 'Order Status' : ($locale === 'he' ? 'סטטוס הזמנה' : 'حالة الطلب') }}:** {{ $status }}  
**{{ $locale === 'en' ? 'Payment Status' : ($locale === 'he' ? 'סטטוס תשלום' : 'حالة الدفع') }}:** {{ $paymentStatus }}  
**{{ $locale === 'en' ? 'Total' : ($locale === 'he' ? 'סה״כ' : 'المجموع') }}:** {{ $money($order->grand_total ?? $order->total ?? 0) }}
</x-mail::panel>

@if($order->items && $order->items->count() > 0)
| {{ $locale === 'en' ? 'Product' : ($locale === 'he' ? 'מוצר' : 'المنتج') }} | {{ $locale === 'en' ? 'Qty' : ($locale === 'he' ? 'כמות' : 'الكمية') }} | {{ $locale === 'en' ? 'Total' : ($locale === 'he' ? 'סה״כ' : 'المجموع') }} |
| --- | ---: | ---: |
@foreach($order->items as $item)
@php
    $quantity = (int) ($item->quantity ?? 1);
    $unitPrice = $item->unit_price ?? $item->price ?? 0;
    $lineTotal = $item->total ?? $item->line_total ?? ((float) $unitPrice * $quantity);
@endphp
| {{ $itemName($item) }} | {{ $quantity }} | {{ $money($lineTotal) }} |
@endforeach
@endif

<x-mail::button :url="$orderUrl">
{{ $locale === 'en' ? 'View Order' : ($locale === 'he' ? 'צפה בהזמנה' : 'عرض الطلب') }}
</x-mail::button>

<x-mail::button :url="$invoiceUrl" color="success">
{{ $locale === 'en' ? 'Download Invoice PDF' : ($locale === 'he' ? 'הורדת חשבונית PDF' : 'تحميل الفاتورة PDF') }}
</x-mail::button>

{{ $locale === 'en'
    ? 'Thank you for shopping with us.'
    : ($locale === 'he'
        ? 'תודה שקנית אצלנו.'
        : 'شكراً لتسوقك معنا.') }}

</div>
</x-mail::message>
