@php
    $currencySymbol = $order?->currency?->symbol ?? '₪';

    $isRtl = $direction === 'rtl';

    $texts = match ($locale) {
        'en' => [
            'invoice' => 'Invoice',
            'professional_invoice' => 'Professional digital commerce invoice',
            'invoice_number' => 'Invoice No.',
            'order_details' => 'Order Details',
            'customer_details' => 'Customer Details',
            'order_number' => 'Order Number',
            'order_date' => 'Order Date',
            'order_status' => 'Order Status',
            'payment_status' => 'Payment Status',
            'shipping_method' => 'Shipping Method',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'city' => 'City',
            'address' => 'Address',
            'product' => 'Product',
            'sku' => 'SKU',
            'quantity' => 'Qty',
            'price' => 'Price',
            'total' => 'Total',
            'subtotal' => 'Subtotal',
            'shipping' => 'Shipping',
            'discount' => 'Discount',
            'tax' => 'Tax',
            'grand_total' => 'Grand Total',
            'customer_notes' => 'Customer Notes',
            'view_order' => 'View order online',
            'scan_to_view' => 'Scan to view order',
            'company_details' => 'Company Details',
            'thank_you' => 'Thank you for your order',
        ],
        'he' => [
            'invoice' => 'חשבונית',
            'professional_invoice' => 'חשבונית מסחר דיגיטלי מקצועית',
            'invoice_number' => 'מספר חשבונית',
            'order_details' => 'פרטי הזמנה',
            'customer_details' => 'פרטי לקוח',
            'order_number' => 'מספר הזמנה',
            'order_date' => 'תאריך הזמנה',
            'order_status' => 'סטטוס הזמנה',
            'payment_status' => 'סטטוס תשלום',
            'shipping_method' => 'שיטת משלוח',
            'name' => 'שם',
            'email' => 'אימייל',
            'phone' => 'טלפון',
            'city' => 'עיר',
            'address' => 'כתובת',
            'product' => 'מוצר',
            'sku' => 'SKU',
            'quantity' => 'כמות',
            'price' => 'מחיר',
            'total' => 'סה״כ',
            'subtotal' => 'סכום ביניים',
            'shipping' => 'משלוח',
            'discount' => 'הנחה',
            'tax' => 'מס',
            'grand_total' => 'סה״כ לתשלום',
            'customer_notes' => 'הערות לקוח',
            'view_order' => 'צפייה בהזמנה אונליין',
            'scan_to_view' => 'סרוק לצפייה בהזמנה',
            'company_details' => 'פרטי החברה',
            'thank_you' => 'תודה על הזמנתך',
        ],
        default => [
            'invoice' => 'فاتورة',
            'professional_invoice' => 'فاتورة تجارة إلكترونية احترافية',
            'invoice_number' => 'رقم الفاتورة',
            'order_details' => 'بيانات الطلب',
            'customer_details' => 'بيانات العميل',
            'order_number' => 'رقم الطلب',
            'order_date' => 'تاريخ الطلب',
            'order_status' => 'حالة الطلب',
            'payment_status' => 'حالة الدفع',
            'shipping_method' => 'طريقة الشحن',
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'city' => 'المدينة',
            'address' => 'العنوان',
            'product' => 'المنتج',
            'sku' => 'SKU',
            'quantity' => 'الكمية',
            'price' => 'السعر',
            'total' => 'المجموع',
            'subtotal' => 'المجموع الفرعي',
            'shipping' => 'الشحن',
            'discount' => 'الخصم',
            'tax' => 'الضريبة',
            'grand_total' => 'المجموع الكلي',
            'customer_notes' => 'ملاحظات العميل',
            'view_order' => 'عرض الطلب أونلاين',
            'scan_to_view' => 'امسح الرمز لعرض الطلب',
            'company_details' => 'بيانات الشركة',
            'thank_you' => 'شكرًا لطلبك',
        ],
    };

    $status = $order->status instanceof \BackedEnum
        ? $order->status->value
        : (string) ($order->status ?? '-');

    $paymentStatus = $order->payment_status instanceof \BackedEnum
        ? $order->payment_status->value
        : (string) ($order->payment_status ?? '-');

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

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
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

    $money = function ($value) use ($currencySymbol) {
        return '<span class="money-ltr" dir="ltr">' .
            e($currencySymbol) .
            '&nbsp;' .
            number_format((float) ($value ?? 0), 2) .
        '</span>';
    };

    $subtotal = $order->subtotal ?? $order->sub_total ?? $order->items_total ?? 0;
    $shipping = $order->shipping_total ?? $order->shipping_cost ?? $order->delivery_fee ?? 0;
    $discount = $order->discount_total ?? $order->discount_amount ?? 0;
    $tax = $order->tax_total ?? $order->tax_amount ?? 0;
    $grandTotal = $order->grand_total ?? $order->total ?? 0;

    $invoiceNumber = 'INV-' . ($order->order_number ?? $order->id);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: dejavusans, sans-serif;
            direction: {{ $direction }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
            color: #111827;
            background: #ffffff;
            font-size: 11.5px;
            line-height: 1.75;
        }

        .ltr-text,
        .money-ltr,
        .email-ltr,
        .url-ltr {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
            white-space: nowrap;
            font-family: dejavusans, sans-serif;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header-table td {
            vertical-align: top;
            padding-bottom: 13px;
        }

        .brand {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }

        .subtitle {
            color: #6b7280;
            font-size: 11px;
            margin-top: 4px;
        }

        .invoice-title {
            margin-top: 12px;
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }

        .qr-box {
            text-align: center;
            width: 150px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 8px;
        }

        .qr-box img {
            width: 118px;
            height: 118px;
        }

        .qr-box small {
            display: block;
            margin-top: 5px;
            color: #6b7280;
            font-size: 9px;
        }

        .company-box {
            margin-top: 8px;
            color: #4b5563;
            font-size: 10.5px;
        }

        .meta-grid {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }

        .meta-grid td {
            width: 50%;
            vertical-align: top;
            padding: 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .box-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 7px;
        }

        .line {
            margin-bottom: 4px;
        }

        .label {
            color: #374151;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: bold;
        }

        .online-link {
            margin-bottom: 16px;
            padding: 9px 12px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 10.5px;
        }

        .online-link a {
            color: #1d4ed8;
            text-decoration: none;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table.items th {
            background: #111827;
            color: #ffffff;
            padding: 8px;
            font-size: 10.5px;
            border: 1px solid #111827;
        }

        table.items td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }

        table.items tr:nth-child(even) td {
            background: #f9fafb;
        }

        .totals {
            width: 44%;
            margin-top: 18px;
            border-collapse: collapse;
            float: {{ $isRtl ? 'left' : 'right' }};
        }

        .totals td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
        }

        .totals .grand td {
            background: #2563eb;
            color: #ffffff;
            font-weight: bold;
            font-size: 13px;
        }

        .notes {
            clear: both;
            margin-top: 85px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .footer {
            clear: both;
            margin-top: 32px;
            padding-top: 13px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 10.5px;
            text-align: center;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="brand"><span class="ltr-text">Smart Commerce Platform</span></div>
                <div class="subtitle">{{ $texts['professional_invoice'] }}</div>
                <div class="invoice-title">{{ $texts['invoice'] }}</div>

                <div class="company-box">
                    <strong>{{ $texts['company_details'] }}:</strong>
                    <br>
                    <span class="ltr-text">Smart Commerce Platform</span>
                    <br>
                    <span class="email-ltr">support@smart-commerce-platform.test</span>
                </div>
            </td>

            <td style="width: 165px;">
                <div class="qr-box">
                    <img src="{{ $qrCodeDataUri }}" alt="QR Code">
                    <small>{{ $texts['scan_to_view'] }}</small>
                </div>
            </td>
        </tr>
    </table>

    <div class="online-link">
        <strong>{{ $texts['view_order'] }}:</strong>
        <span class="url-ltr">{{ $orderUrl }}</span>
    </div>

    <table class="meta-grid">
        <tr>
            <td>
                <div class="box-title">{{ $texts['order_details'] }}</div>

                <div class="line">
                    <span class="label">{{ $texts['invoice_number'] }}:</span>
                    <span class="ltr-text">{{ $invoiceNumber }}</span>
                </div>

                <div class="line">
                    <span class="label">{{ $texts['order_number'] }}:</span>
                    <span class="ltr-text">{{ $order->order_number }}</span>
                </div>

                <div class="line">
                    <span class="label">{{ $texts['order_date'] }}:</span>
                    <span class="ltr-text">{{ optional($order->created_at)->format('Y-m-d H:i') }}</span>
                </div>

                <div class="line">
                    <span class="label">{{ $texts['order_status'] }}:</span>
                    <span class="badge">{{ $status }}</span>
                </div>

                <div class="line">
                    <span class="label">{{ $texts['payment_status'] }}:</span>
                    {{ $paymentStatus }}
                </div>

                @if($order->shippingMethod)
                    <div class="line">
                        <span class="label">{{ $texts['shipping_method'] }}:</span>
                        {{ $resolveText($order->shippingMethod->name ?? '-') }}
                    </div>
                @endif
            </td>

            <td>
                <div class="box-title">{{ $texts['customer_details'] }}</div>

                <div class="line">
                    <span class="label">{{ $texts['name'] }}:</span>
                    {{ $order->customer_name ?? $order->customer?->name ?? '-' }}
                </div>

                <div class="line">
                    <span class="label">{{ $texts['email'] }}:</span>
                    <span class="email-ltr">{{ $order->customer_email ?? $order->customer?->email ?? '-' }}</span>
                </div>

                <div class="line">
                    <span class="label">{{ $texts['phone'] }}:</span>
                    <span class="ltr-text">{{ $order->customer_phone ?? $order->customer?->phone ?? '-' }}</span>
                </div>

                <div class="line">
                    <span class="label">{{ $texts['city'] }}:</span>
                    {{ $order->city ?? $order->customer?->city ?? '-' }}
                </div>

                <div class="line">
                    <span class="label">{{ $texts['address'] }}:</span>
                    {{ $order->address ?? $order->shipping_address ?? $order->customer?->address ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ $texts['product'] }}</th>
                <th>{{ $texts['sku'] }}</th>
                <th>{{ $texts['quantity'] }}</th>
                <th>{{ $texts['price'] }}</th>
                <th>{{ $texts['total'] }}</th>
            </tr>
        </thead>

        <tbody>
            @foreach($order->items as $item)
                @php
                    $quantity = (int) ($item->quantity ?? 1);
                    $unitPrice = $item->unit_price ?? $item->price ?? 0;
                    $lineTotal = $item->total ?? $item->line_total ?? ((float) $unitPrice * $quantity);
                @endphp

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $itemName($item) }}</td>
                    <td><span class="ltr-text">{{ $item->product?->sku ?? $item->sku ?? '-' }}</span></td>
                    <td>{{ $quantity }}</td>
                    <td>{!! $money($unitPrice) !!}</td>
                    <td>{!! $money($lineTotal) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>{{ $texts['subtotal'] }}</td>
            <td>{!! $money($subtotal) !!}</td>
        </tr>

        <tr>
            <td>{{ $texts['shipping'] }}</td>
            <td>{!! $money($shipping) !!}</td>
        </tr>

        <tr>
            <td>{{ $texts['discount'] }}</td>
            <td>{!! $money($discount) !!}</td>
        </tr>

        <tr>
            <td>{{ $texts['tax'] }}</td>
            <td>{!! $money($tax) !!}</td>
        </tr>

        <tr class="grand">
            <td>{{ $texts['grand_total'] }}</td>
            <td>{!! $money($grandTotal) !!}</td>
        </tr>
    </table>

    @if(! empty($order->customer_notes))
        <div class="notes">
            <strong>{{ $texts['customer_notes'] }}:</strong>
            <br>
            {{ $order->customer_notes }}
        </div>
    @endif

    <div class="footer">
        {{ $texts['thank_you'] }} — <span class="ltr-text">Smart Commerce Platform</span>
    </div>
</body>
</html>
