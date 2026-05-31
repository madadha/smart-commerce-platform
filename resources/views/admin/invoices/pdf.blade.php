<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة {{ $invoice->invoice_number }}</title>

    <style>
        body {
            font-family: dejavusans, sans-serif;
            direction: rtl;
            text-align: right;
            color: #111827;
            font-size: 12px;
            line-height: 1.7;
        }

        .invoice-container {
            width: 100%;
        }

        .header {
            border-bottom: 3px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .brand-title {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 12px;
            color: #4b5563;
            margin-top: 4px;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            color: #1d4ed8;
            text-align: left;
        }

        .invoice-number {
            font-size: 13px;
            color: #374151;
            text-align: left;
        }

        .two-columns {
            width: 100%;
            margin-bottom: 18px;
        }

        .two-columns td {
            width: 50%;
            vertical-align: top;
        }

        .box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px;
            background: #f9fafb;
        }

        .box-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .meta-label {
            color: #6b7280;
            width: 35%;
        }

        .meta-value {
            color: #111827;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 18px;
        }

        .items-table th {
            background: #111827;
            color: #ffffff;
            padding: 8px;
            border: 1px solid #111827;
            font-size: 11px;
        }

        .items-table td {
            border: 1px solid #d1d5db;
            padding: 7px;
            font-size: 11px;
        }

        .items-table tr:nth-child(even) td {
            background: #f9fafb;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .totals {
            width: 40%;
            margin-right: auto;
            border-collapse: collapse;
        }

        .totals td {
            border: 1px solid #d1d5db;
            padding: 7px;
        }

        .totals .label {
            background: #f3f4f6;
            color: #374151;
            font-weight: bold;
        }

        .totals .grand {
            background: #1d4ed8;
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
        }

        .notes {
            margin-top: 20px;
            border: 1px solid #d1d5db;
            padding: 10px;
            background: #f9fafb;
        }

        .footer {
            margin-top: 25px;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            background: #dcfce7;
            color: #166534;
            border-radius: 20px;
            font-weight: bold;
            font-size: 11px;
        }
    </style>
</head>

<body>
<div class="invoice-container">

    <table class="header" width="100%">
        <tr>
            <td width="55%">
                <h1 class="brand-title">
                    {{ $invoice->seller_details['name'] ?? 'Smart Commerce Platform' }}
                </h1>

                <div class="brand-subtitle">
                    {{ $invoice->seller_details['email'] ?? 'info@example.com' }}
                    |
                    {{ $invoice->seller_details['phone'] ?? '+972000000000' }}
                </div>

                <div class="brand-subtitle">
                    رقم ضريبي:
                    {{ $invoice->seller_details['tax_number'] ?? '-' }}
                </div>
            </td>

            <td width="45%">
                <div class="invoice-title">فاتورة</div>
                <div class="invoice-number">
                    رقم الفاتورة:
                    <strong>{{ $invoice->invoice_number }}</strong>
                </div>
                <div class="invoice-number">
                    الحالة:
                    <span class="status">
                        {{ $invoice->status?->label() ?? $invoice->status }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="two-columns">
        <tr>
            <td style="padding-left: 8px;">
                <div class="box">
                    <div class="box-title">بيانات الزبون</div>

                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">الاسم:</td>
                            <td class="meta-value">
                                {{ $invoice->customer?->getDisplayName() ?? ($invoice->billing_address['name'] ?? '-') }}
                            </td>
                        </tr>

                        <tr>
                            <td class="meta-label">الهاتف:</td>
                            <td class="meta-value">
                                {{ $invoice->customer?->phone ?? ($invoice->billing_address['phone'] ?? '-') }}
                            </td>
                        </tr>

                        <tr>
                            <td class="meta-label">البريد:</td>
                            <td class="meta-value">
                                {{ $invoice->customer?->email ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="meta-label">العنوان:</td>
                            <td class="meta-value">
                                {{ $invoice->billing_address['address'] ?? $invoice->customer?->getFullAddress() ?? '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <td style="padding-right: 8px;">
                <div class="box">
                    <div class="box-title">تفاصيل الفاتورة</div>

                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">رقم الطلب:</td>
                            <td class="meta-value">
                                {{ $invoice->order?->order_number ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="meta-label">تاريخ الإصدار:</td>
                            <td class="meta-value">
                                {{ $invoice->issued_at?->format('Y-m-d') ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="meta-label">تاريخ الاستحقاق:</td>
                            <td class="meta-value">
                                {{ $invoice->due_at?->format('Y-m-d') ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="meta-label">العملة:</td>
                            <td class="meta-value">
                                {{ $invoice->currency?->code ?? $invoice->order?->currency?->code ?? 'ILS' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
        <tr>
            <th width="5%">#</th>
            <th width="33%">الصنف</th>
            <th width="14%">SKU</th>
            <th width="10%">الكمية</th>
            <th width="13%">سعر الوحدة</th>
            <th width="12%">الخصم</th>
            <th width="13%">المجموع</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($invoice->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>

                <td>
                    <strong>{{ $item->getItemName('ar') }}</strong>
                    @if($item->notes)
                        <br>
                        <small>{{ $item->notes }}</small>
                    @endif
                </td>

                <td class="text-center">{{ $item->sku ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>

                <td class="text-left">
                    {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                    {{ number_format((float) $item->unit_price, 2) }}
                </td>

                <td class="text-left">
                    {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                    {{ number_format((float) $item->discount_total, 2) }}
                </td>

                <td class="text-left">
                    {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                    {{ number_format((float) $item->line_total, 2) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">لا توجد عناصر داخل الفاتورة</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">المجموع الفرعي</td>
            <td class="text-left">
                {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                {{ number_format((float) $invoice->subtotal, 2) }}
            </td>
        </tr>

        <tr>
            <td class="label">الخصم</td>
            <td class="text-left">
                {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                {{ number_format((float) $invoice->discount_total, 2) }}
            </td>
        </tr>

        <tr>
            <td class="label">الضريبة</td>
            <td class="text-left">
                {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                {{ number_format((float) $invoice->tax_total, 2) }}
            </td>
        </tr>

        <tr>
            <td class="label">الشحن</td>
            <td class="text-left">
                {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                {{ number_format((float) $invoice->shipping_total, 2) }}
            </td>
        </tr>

        <tr>
            <td class="grand">المجموع النهائي</td>
            <td class="grand text-left">
                {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                {{ number_format((float) $invoice->grand_total, 2) }}
            </td>
        </tr>

        <tr>
            <td class="label">المدفوع</td>
            <td class="text-left">
                {{ $invoice->currency?->symbol ?? $invoice->order?->currency?->symbol ?? '₪' }}
                {{ number_format((float) $invoice->paid_total, 2) }}
            </td>
        </tr>
    </table>

    @if($invoice->customer_notes || $invoice->internal_notes)
        <div class="notes">
            @if($invoice->customer_notes)
                <strong>ملاحظات للزبون:</strong>
                <br>
                {{ $invoice->customer_notes }}
                <br><br>
            @endif

            @if($invoice->internal_notes)
                <strong>ملاحظات داخلية:</strong>
                <br>
                {{ $invoice->internal_notes }}
            @endif
        </div>
    @endif

    <div class="footer">
        تم إنشاء هذه الفاتورة بواسطة Smart Commerce Platform
        <br>
        {{ now()->format('Y-m-d H:i') }}
    </div>

</div>
</body>
</html>