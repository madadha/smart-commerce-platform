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

            <div class="scp-order-details-grid">

                <div class="scp-order-main">

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
                        <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-order-primary-btn">
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
@endsection