@extends('storefront.layout')

@section('content')
    @php
        $currencySymbol = $order?->currency?->symbol ?? '₪';

        $resolveItemName = function ($item) use ($locale) {
            $product = $item->product ?? null;

            if ($product && method_exists($product, 'getName')) {
                return $product->getName($locale);
            }

            $name = $item->product_name ?? null;

            if (is_array($name)) {
                return $name[$locale] ?? $name['ar'] ?? $name['en'] ?? reset($name);
            }

            if (is_string($name)) {
                $decoded = json_decode($name, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded[$locale] ?? $decoded['ar'] ?? $decoded['en'] ?? reset($decoded);
                }

                return $name;
            }

            return '-';
        };

        $resolveProductImage = function ($item) {
            $product = $item->product ?? null;

            if ($product && ! empty($product->main_image)) {
                return asset('storage/' . ltrim($product->main_image, '/'));
            }

            return null;
        };

        $resolveStatus = function ($value) {
            if ($value instanceof \BackedEnum) {
                return $value->value;
            }

            return (string) $value;
        };

        $currentStatus = strtolower($resolveStatus($order->status));

        $timelineTranslations = [
            'ar' => [
                'title' => 'مراحل الطلب',
                'subtitle' => 'تابع حالة طلبك خطوة بخطوة',
                'pending' => 'تم استلام الطلب',
                'processing' => 'قيد المعالجة',
                'shipped' => 'تم الشحن',
                'completed' => 'مكتمل',
                'cancelled' => 'تم إلغاء الطلب',
            ],
            'he' => [
                'title' => 'שלבי ההזמנה',
                'subtitle' => 'עקוב אחר מצב ההזמנה שלך שלב אחר שלב',
                'pending' => 'ההזמנה התקבלה',
                'processing' => 'בטיפול',
                'shipped' => 'נשלח',
                'completed' => 'הושלם',
                'cancelled' => 'ההזמנה בוטלה',
            ],
            'en' => [
                'title' => 'Order Timeline',
                'subtitle' => 'Track your order step by step',
                'pending' => 'Order Received',
                'processing' => 'Processing',
                'shipped' => 'Shipped',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ],
        ];

        $timelineText = $timelineTranslations[$locale] ?? $timelineTranslations['ar'];

        $timelineSteps = [
            'pending' => $timelineText['pending'],
            'processing' => $timelineText['processing'],
            'shipped' => $timelineText['shipped'],
            'completed' => $timelineText['completed'],
        ];

        $statusOrder = array_keys($timelineSteps);
        $currentTimelineIndex = array_search($currentStatus, $statusOrder, true);
    @endphp

    <section class="scp-order-details-page">
        <div class="scp-container">

            <div class="scp-order-hero">
                <div>
                    <span class="scp-order-badge">
                        {{ __('storefront.order_details.badge') }}
                    </span>

                    <h1>{{ __('storefront.order_details.page_title') }}</h1>

                    <p>{{ __('storefront.order_details.page_description') }}</p>
                </div>

                <div class="scp-order-number-card">
                    <span>{{ __('storefront.order_details.order_number') }}</span>
                    <strong>{{ $order->order_number }}</strong>
                </div>
            </div>

            <div class="scp-order-status-strip">
                <div>
                    <span>{{ __('storefront.order_details.order_status') }}</span>
                    <strong>{{ $resolveStatus($order->status) }}</strong>
                </div>

                <div>
                    <span>{{ __('storefront.order_details.payment_status') }}</span>
                    <strong>{{ $resolveStatus($order->payment_status) }}</strong>
                </div>

                <div>
                    <span>{{ __('storefront.order_details.ordered_at') }}</span>
                    <strong>{{ optional($order->ordered_at ?? $order->created_at)->format('Y-m-d H:i') }}</strong>
                </div>

                <div>
                    <span>{{ __('storefront.cart.grand_total') }}</span>
                    <strong>{{ $currencySymbol }} {{ number_format((float) $order->grand_total, 2) }}</strong>
                </div>
            </div>


            <div class="scp-order-timeline-card">
                <div class="scp-order-timeline-head">
                    <div>
                        <h2>{{ $timelineText['title'] }}</h2>
                        <p>{{ $timelineText['subtitle'] }}</p>
                    </div>

                    <span class="scp-order-timeline-status {{ $currentStatus === 'cancelled' ? 'is-cancelled' : '' }}">
                        {{ $currentStatus === 'cancelled' ? $timelineText['cancelled'] : ($timelineSteps[$currentStatus] ?? $resolveStatus($order->status)) }}
                    </span>
                </div>

                @if($currentStatus === 'cancelled')
                    <div class="scp-order-timeline-cancelled">
                        <span>✕</span>
                        <strong>{{ $timelineText['cancelled'] }}</strong>
                    </div>
                @else
                    <div class="scp-order-timeline-steps">
                        @foreach($timelineSteps as $statusKey => $statusLabel)
                            @php
                                $stepIndex = array_search($statusKey, $statusOrder, true);
                                $stepClass = 'is-upcoming';

                                if ($currentTimelineIndex !== false && $stepIndex < $currentTimelineIndex) {
                                    $stepClass = 'is-done';
                                }

                                if ($currentTimelineIndex !== false && $stepIndex === $currentTimelineIndex) {
                                    $stepClass = 'is-current';
                                }
                            @endphp

                            <div class="scp-order-timeline-step {{ $stepClass }}">
                                <div class="scp-order-timeline-dot">
                                    @if($stepClass === 'is-done')
                                        ✓
                                    @elseif($stepClass === 'is-current')
                                        ●
                                    @else
                                        ○
                                    @endif
                                </div>

                                <div class="scp-order-timeline-label">
                                    {{ $statusLabel }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="scp-order-details-grid">

                <div class="scp-order-main">

                    @include('storefront.orders.partials.shipments')

                    <div class="scp-order-card">
                        <div class="scp-order-card-head">
                            <h2>{{ __('storefront.order_details.items') }}</h2>
                            <span>{{ $order->items->count() }}</span>
                        </div>

                        <div class="scp-order-items-list">
                            @foreach($order->items as $item)
                                <article class="scp-order-item">
                                    <div class="scp-order-item-image">
                                        @if($resolveProductImage($item))
                                            <img src="{{ $resolveProductImage($item) }}" alt="{{ $resolveItemName($item) }}">
                                        @else
                                            <div class="scp-order-item-placeholder">
                                                {{ mb_substr($resolveItemName($item), 0, 1) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="scp-order-item-info">
                                        <div class="scp-order-item-type">
                                            {{ $item->item_type ?? 'product' }}
                                        </div>

                                        <h3>{{ $resolveItemName($item) }}</h3>

                                        <div class="scp-order-item-meta">
                                            @if($item->sku)
                                                <span>SKU: {{ $item->sku }}</span>
                                            @endif

                                            @if($item->productVariant)
                                                <span>
                                                    @if(method_exists($item->productVariant, 'getName'))
                                                        {{ $item->productVariant->getName($locale) }}
                                                    @else
                                                        {{ $item->productVariant->name ?? '-' }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        @if(! empty($item->notes))
                                            <div class="scp-order-item-notes">
                                                {{ $item->notes }}
                                            </div>
                                        @endif

                                        @if(! empty($digitalCodesByItem[$item->id] ?? []))
                                            <div class="scp-order-digital-codes">
                                                <strong>{{ __('storefront.order_details.digital_codes') }}</strong>

                                                @foreach($digitalCodesByItem[$item->id] as $digitalCode)
                                                    <div>
                                                        <span>{{ $digitalCode['code'] }}</span>
                                                        <small>{{ $digitalCode['status'] ?? '-' }}</small>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="scp-order-item-numbers">
                                        <div>
                                            <span>{{ __('storefront.cart.quantity') }}</span>
                                            <strong>{{ $item->quantity }}</strong>
                                        </div>

                                        <div>
                                            <span>{{ __('storefront.cart.unit_price') }}</span>
                                            <strong>{{ $currencySymbol }} {{ number_format((float) $item->unit_price, 2) }}</strong>
                                        </div>

                                        <div>
                                            <span>{{ __('storefront.cart.line_total') }}</span>
                                            <strong>{{ $currencySymbol }} {{ number_format((float) $item->line_total, 2) }}</strong>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    @if($order->customer_notes)
                        <div class="scp-order-card">
                            <div class="scp-order-card-head">
                                <h2>{{ __('storefront.order_details.customer_notes') }}</h2>
                            </div>

                            <p class="scp-order-notes-text">{{ $order->customer_notes }}</p>
                        </div>
                    @endif

                </div>

                <aside class="scp-order-sidebar">

                    <div class="scp-order-summary-card">
                        <h2>{{ __('storefront.order_details.summary') }}</h2>

                        <div class="scp-order-summary-line">
                            <span>{{ __('storefront.cart.subtotal') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $order->subtotal, 2) }}</strong>
                        </div>

                        <div class="scp-order-summary-line">
                            <span>{{ __('storefront.cart.discount') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $order->discount_total, 2) }}</strong>
                        </div>

                        <div class="scp-order-summary-line">
                            <span>{{ __('storefront.cart.shipping') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $order->shipping_total, 2) }}</strong>
                        </div>

                        <div class="scp-order-summary-line">
                            <span>{{ __('storefront.cart.tax') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $order->tax_total, 2) }}</strong>
                        </div>

                        <div class="scp-order-summary-total">
                            <span>{{ __('storefront.cart.grand_total') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $order->grand_total, 2) }}</strong>
                        </div>
                    </div>

                    <div class="scp-order-summary-card">
                        <h2>{{ __('storefront.order_details.customer_details') }}</h2>

                        <div class="scp-order-info-line">
                            <span>{{ __('storefront.checkout.full_name') }}</span>
                            <strong>{{ $order->customer_name ?? $order->customer?->getDisplayName() ?? '-' }}</strong>
                        </div>

                        <div class="scp-order-info-line">
                            <span>{{ __('storefront.checkout.phone') }}</span>
                            <strong>{{ $order->customer_phone ?? $order->customer?->phone ?? '-' }}</strong>
                        </div>

                        <div class="scp-order-info-line">
                            <span>{{ __('storefront.checkout.email') }}</span>
                            <strong>{{ $order->customer_email ?? $order->customer?->email ?? '-' }}</strong>
                        </div>

                        <div class="scp-order-info-line">
                            <span>{{ __('storefront.checkout.shipping_method') }}</span>
                            <strong>
                                @if($order->shippingMethod && method_exists($order->shippingMethod, 'getName'))
                                    {{ $order->shippingMethod->getName($locale) }}
                                @else
                                    {{ $order->shipping_method ?? '-' }}
                                @endif
                            </strong>
                        </div>

                        <div class="scp-order-info-line">
                            <span>{{ __('storefront.checkout.address') }}</span>
                            <strong>{{ $order->shipping_address ?? $order->billing_address ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="scp-order-actions">
                        <a
                            href="{{ \Illuminate\Support\Facades\URL::signedRoute('storefront.orders.invoice', ['order' => $order->id, 'lang' => $locale]) }}"
                            class="scp-order-primary-btn"
                        >
                            {{ \Illuminate\Support\Facades\Lang::has('storefront.orders.download_invoice') ? __('storefront.orders.download_invoice') : 'تحميل الفاتورة PDF' }}
                        </a>

                        <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-order-secondary-btn">
                            {{ __('storefront.cart.continue_shopping') }}
                        </a>

                        <a href="{{ route('storefront.home', ['lang' => $locale]) }}" class="scp-order-secondary-btn">
                            {{ __('storefront.nav.home') }}
                        </a>
                    </div>

                </aside>

            </div>

        </div>
    </section>
    <style>
        .scp-order-timeline-card {
            margin: 22px 0;
            padding: 22px;
            border-radius: 24px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        .scp-order-timeline-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .scp-order-timeline-head h2 {
            margin: 0;
            color: #0f172a;
            font-size: 1.25rem;
            font-weight: 900;
        }

        .scp-order-timeline-head p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 0.92rem;
        }

        .scp-order-timeline-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            padding: 9px 14px;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 900;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .scp-order-timeline-status.is-cancelled {
            background: #fef2f2;
            color: #dc2626;
        }

        .scp-order-timeline-steps {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            position: relative;
        }

        .scp-order-timeline-step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 16px 10px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            text-align: center;
        }

        .scp-order-timeline-dot {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            background: #e2e8f0;
            color: #64748b;
        }

        .scp-order-timeline-label {
            font-weight: 900;
            font-size: 0.9rem;
        }

        .scp-order-timeline-step.is-done {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #047857;
        }

        .scp-order-timeline-step.is-done .scp-order-timeline-dot {
            background: #10b981;
            color: #ffffff;
        }

        .scp-order-timeline-step.is-current {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
            box-shadow: 0 12px 25px rgba(37, 99, 235, 0.12);
        }

        .scp-order-timeline-step.is-current .scp-order-timeline-dot {
            background: #2563eb;
            color: #ffffff;
        }

        .scp-order-timeline-cancelled {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 18px;
            border-radius: 18px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            font-weight: 900;
        }

        .scp-order-timeline-cancelled span {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dc2626;
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .scp-order-timeline-head {
                flex-direction: column;
            }

            .scp-order-timeline-steps {
                grid-template-columns: 1fr;
            }

            .scp-order-timeline-step {
                align-items: flex-start;
                flex-direction: row;
                text-align: start;
            }
        }
    </style>

@endsection
