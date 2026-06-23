@php
    $labels = match ($mailLocale) {
        'he' => ['title' => 'עדכון על המשלוח שלך', 'intro' => 'סטטוס המשלוח של ההזמנה שלך עודכן.', 'order' => 'מספר הזמנה', 'shipment' => 'מספר משלוח', 'status' => 'סטטוס', 'carrier' => 'חברת משלוחים', 'tracking' => 'מספר מעקב', 'estimate' => 'מסירה משוערת', 'track' => 'מעקב אצל חברת המשלוחים', 'view' => 'צפייה בהזמנה'],
        'en' => ['title' => 'Your shipment has an update', 'intro' => 'The delivery status for your order has changed.', 'order' => 'Order number', 'shipment' => 'Shipment number', 'status' => 'Status', 'carrier' => 'Carrier', 'tracking' => 'Tracking number', 'estimate' => 'Estimated delivery', 'track' => 'Track with carrier', 'view' => 'View order'],
        default => ['title' => 'تحديث جديد على شحنتك', 'intro' => 'تم تحديث حالة توصيل طلبك.', 'order' => 'رقم الطلب', 'shipment' => 'رقم الشحنة', 'status' => 'الحالة', 'carrier' => 'شركة الشحن', 'tracking' => 'رقم التتبع', 'estimate' => 'الوصول المتوقع', 'track' => 'تتبع لدى شركة الشحن', 'view' => 'عرض الطلب'],
    };
    $statusLabel = match ($mailLocale) {
        'he' => match ($shipment->status->value) { 'ready' => 'מוכן לאיסוף', 'shipped' => 'נשלח', 'in_transit' => 'בדרך', 'out_for_delivery' => 'יצא למסירה', 'delivered' => 'נמסר', 'failed' => 'המסירה נכשלה', 'returned' => 'הוחזר', 'cancelled' => 'בוטל', default => 'ממתין' },
        'en' => $shipment->status->label(),
        default => match ($shipment->status->value) { 'ready' => 'جاهزة للاستلام', 'shipped' => 'تم الشحن', 'in_transit' => 'قيد النقل', 'out_for_delivery' => 'خرجت للتوصيل', 'delivered' => 'تم التسليم', 'failed' => 'فشل التوصيل', 'returned' => 'تم الإرجاع', 'cancelled' => 'ملغاة', default => 'قيد الانتظار' },
    };
@endphp

<x-mail::message>
<div dir="{{ $direction }}" style="text-align: {{ $direction === 'rtl' ? 'right' : 'left' }}; font-family: Arial, sans-serif;">

# {{ $labels['title'] }}

{{ $labels['intro'] }}

**{{ $labels['order'] }}:** {{ $shipment->order->order_number }}  
**{{ $labels['shipment'] }}:** {{ $shipment->shipment_number }}  
**{{ $labels['status'] }}:** {{ $statusLabel }}  
@if($shipment->carrier_name)
**{{ $labels['carrier'] }}:** {{ $shipment->carrier_name }}  
@endif
@if($shipment->tracking_number)
**{{ $labels['tracking'] }}:** {{ $shipment->tracking_number }}  
@endif
@if($shipment->estimated_delivery_at)
**{{ $labels['estimate'] }}:** {{ $shipment->estimated_delivery_at->format('Y-m-d H:i') }}  
@endif

@if($shipment->tracking_url)
<x-mail::button :url="$shipment->tracking_url">
{{ $labels['track'] }}
</x-mail::button>
@endif

<x-mail::button :url="$orderUrl" color="success">
{{ $labels['view'] }}
</x-mail::button>

{{ config('app.name', 'Smart Commerce Platform') }}

</div>
</x-mail::message>
