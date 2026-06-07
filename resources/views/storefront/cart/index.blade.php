@extends('storefront.layout')

@section('content')
    @php
        $currencySymbol = $cart?->currency?->symbol ?? '₪';

        $resolveProductImage = function ($item) {
            $product = $item->product ?? null;

            if ($product && ! empty($product->main_image)) {
                return asset('storage/' . ltrim($product->main_image, '/'));
            }

            return null;
        };

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

        $resolveBrandName = function ($item) use ($locale) {
            $brand = $item->product?->brand ?? null;

            if (! $brand) {
                return null;
            }

            if (method_exists($brand, 'getName')) {
                return $brand->getName($locale);
            }

            $name = $brand->name ?? null;

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

            return null;
        };

        $resolveVariantName = function ($item) use ($locale) {
            $variant = $item->productVariant ?? null;

            if (! $variant) {
                return null;
            }

            if (method_exists($variant, 'getName')) {
                return $variant->getName($locale);
            }

            $name = $variant->name ?? null;

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

            return null;
        };
    @endphp

    <section class="scp-cart-page">
        <div class="scp-container">

            <div class="scp-cart-page-header">
                <div class="scp-cart-page-intro">
                    <span class="scp-cart-page-badge">{{ __('storefront.cart.badge') }}</span>
                    <h1>{{ __('storefront.cart.page_title') }}</h1>
                    <p>{{ __('storefront.cart.page_description') }}</p>
                </div>

                <div class="scp-cart-page-stat">
                    <span>{{ __('storefront.cart.items_count') }}</span>
                    <strong>{{ $cart?->items?->count() ?? 0 }}</strong>
                </div>
            </div>

            @if(! $cart || $cart->items->isEmpty())
                <div class="scp-cart-empty-state">
                    <div class="scp-cart-empty-icon">🛒</div>
                    <h2>{{ __('storefront.cart.empty_title') }}</h2>
                    <p>{{ __('storefront.cart.empty_text') }}</p>

                    <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-cart-empty-btn">
                        {{ __('storefront.cart.continue_shopping') }}
                    </a>
                </div>
            @else
                <div class="scp-cart-grid">

                    <div class="scp-cart-list">

                        @foreach($cart->items as $item)
                            <article class="scp-cart-card">
                                <div class="scp-cart-card-media">
                                    @if($resolveProductImage($item))
                                        <img src="{{ $resolveProductImage($item) }}" alt="{{ $resolveItemName($item) }}">
                                    @else
                                        <div class="scp-cart-card-placeholder">
                                            {{ mb_substr($resolveItemName($item), 0, 1) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="scp-cart-card-content">
                                    <div class="scp-cart-card-topline">
                                        <span class="scp-cart-card-type">
                                            {{ $item->item_type ?? 'product' }}
                                        </span>

                                        @if($resolveBrandName($item))
                                            <span class="scp-cart-card-brand">
                                                {{ $resolveBrandName($item) }}
                                            </span>
                                        @endif
                                    </div>

                                    <h3>{{ $resolveItemName($item) }}</h3>

                                    <div class="scp-cart-card-meta">
                                        @if($item->sku)
                                            <span>SKU: {{ $item->sku }}</span>
                                        @endif

                                        @if($resolveVariantName($item))
                                            <span>{{ $resolveVariantName($item) }}</span>
                                        @endif
                                    </div>

                                    <div class="scp-cart-card-pricing">
                                        <div class="scp-cart-price-box">
                                            <small>{{ __('storefront.cart.unit_price') }}</small>
                                            <strong>{{ $currencySymbol }} {{ number_format((float) $item->unit_price, 2) }}</strong>
                                        </div>

                                        <div class="scp-cart-price-box">
                                            <small>{{ __('storefront.cart.line_total') }}</small>
                                            <strong>{{ $currencySymbol }} {{ number_format((float) $item->line_total, 2) }}</strong>
                                        </div>
                                    </div>

                                    <div class="scp-cart-card-actions">
                                        <form method="POST" action="{{ route('storefront.cart.items.update', ['item' => $item->id, 'lang' => $locale]) }}" class="scp-cart-update-form">
                                            @csrf
                                            @method('PATCH')

                                            <label for="quantity_{{ $item->id }}">{{ __('storefront.cart.quantity') }}</label>

                                            <div class="scp-cart-update-row">
                                                <input
                                                    id="quantity_{{ $item->id }}"
                                                    type="number"
                                                    name="quantity"
                                                    value="{{ $item->quantity }}"
                                                    min="1"
                                                    max="99"
                                                >

                                                <button type="submit">
                                                    {{ __('storefront.cart.update') }}
                                                </button>
                                            </div>
                                        </form>

                                        <form method="POST" action="{{ route('storefront.cart.items.remove', ['item' => $item->id, 'lang' => $locale]) }}" class="scp-cart-remove-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit">
                                                {{ __('storefront.cart.remove') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach

                    </div>

                    <aside class="scp-cart-summary-card">
                        <h2>{{ __('storefront.cart.summary') }}</h2>

                        <div class="scp-cart-summary-line">
                            <span>{{ __('storefront.cart.cart_number') }}</span>
                            <strong>{{ $cart->cart_number }}</strong>
                        </div>

                        <div class="scp-cart-summary-line">
                            <span>{{ __('storefront.cart.items_count') }}</span>
                            <strong>{{ $cart->items->count() }}</strong>
                        </div>

                        <div class="scp-cart-summary-line">
                            <span>{{ __('storefront.cart.subtotal') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $cart->subtotal, 2) }}</strong>
                        </div>

                        <div class="scp-cart-summary-line">
                            <span>{{ __('storefront.cart.discount') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $cart->discount_total, 2) }}</strong>
                        </div>

                        <div class="scp-cart-summary-line">
                            <span>{{ __('storefront.cart.tax') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $cart->tax_total, 2) }}</strong>
                        </div>

                        <div class="scp-cart-summary-line">
                            <span>{{ __('storefront.cart.shipping') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $cart->shipping_total, 2) }}</strong>
                        </div>

                        <div class="scp-cart-summary-total">
                            <span>{{ __('storefront.cart.grand_total') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $cart->grand_total, 2) }}</strong>
                        </div>

                      <a href="{{ route('storefront.checkout.index', ['lang' => $locale]) }}" class="scp-cart-checkout-button">
    {{ __('storefront.cart.checkout') }}
</a>

                        <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-cart-continue-button">
                            {{ __('storefront.cart.continue_shopping') }}
                        </a>
                    </aside>

                </div>
            @endif

        </div>
    </section>
@endsection