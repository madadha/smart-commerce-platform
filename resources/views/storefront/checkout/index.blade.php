@extends('storefront.layout')

@section('content')
    @php
        $currencySymbol = $cart?->currency?->symbol ?? '₪';

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
    @endphp

    <section class="scp-checkout-page">
        <div class="scp-container">

            <div class="scp-checkout-header">
                <div>
                    <span class="scp-checkout-badge">
                        {{ __('storefront.checkout.badge') }}
                    </span>

                    <h1>{{ __('storefront.checkout.page_title') }}</h1>

                    <p>{{ __('storefront.checkout.page_description') }}</p>
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
                <div class="scp-checkout-grid">

                    <div class="scp-checkout-main">
                        <form method="POST" action="#" class="scp-checkout-form">
                            @csrf

                            <input type="hidden" name="lang" value="{{ $locale }}">

                            <div class="scp-checkout-card">
                                <div class="scp-checkout-card-head">
                                    <span>1</span>
                                    <div>
                                        <h2>{{ __('storefront.checkout.customer_details') }}</h2>
                                        <p>{{ __('storefront.checkout.customer_details_hint') }}</p>
                                    </div>
                                </div>

                                <div class="scp-checkout-form-grid">
                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.full_name') }}</label>
                                        <input type="text" name="customer_name" placeholder="{{ __('storefront.checkout.full_name_placeholder') }}">
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.email') }}</label>
                                        <input type="email" name="customer_email" placeholder="example@email.com">
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.phone') }}</label>
                                        <input type="text" name="customer_phone" placeholder="05x-xxxxxxx">
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.city') }}</label>
                                        <input type="text" name="city" placeholder="{{ __('storefront.checkout.city_placeholder') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="scp-checkout-card">
                                <div class="scp-checkout-card-head">
                                    <span>2</span>
                                    <div>
                                        <h2>{{ __('storefront.checkout.shipping_details') }}</h2>
                                        <p>{{ __('storefront.checkout.shipping_details_hint') }}</p>
                                    </div>
                                </div>

                                <div class="scp-checkout-form-grid">
                                    <div class="scp-field full">
                                        <label>{{ __('storefront.checkout.address') }}</label>
                                        <input type="text" name="address" placeholder="{{ __('storefront.checkout.address_placeholder') }}">
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.shipping_method') }}</label>
                                        <select name="shipping_method_id">
                                            <option value="">{{ __('storefront.checkout.select_shipping_method') }}</option>

                                            @foreach($shippingMethods as $shippingMethod)
                                                <option value="{{ $shippingMethod->id }}">
                                                    @if(method_exists($shippingMethod, 'getName'))
                                                        {{ $shippingMethod->getName($locale) }}
                                                    @else
                                                        {{ $shippingMethod->name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.payment_method') }}</label>
                                        <select name="payment_method">
                                            <option value="cash">{{ __('storefront.checkout.cash') }}</option>
                                            <option value="credit_card">{{ __('storefront.checkout.credit_card') }}</option>
                                            <option value="bank_transfer">{{ __('storefront.checkout.bank_transfer') }}</option>
                                        </select>
                                    </div>

                                    <div class="scp-field full">
                                        <label>{{ __('storefront.checkout.notes') }}</label>
                                        <textarea name="customer_notes" rows="4" placeholder="{{ __('storefront.checkout.notes_placeholder') }}"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="scp-checkout-actions">
                                <a href="{{ route('storefront.cart.index', ['lang' => $locale]) }}" class="scp-checkout-back">
                                    {{ __('storefront.checkout.back_to_cart') }}
                                </a>

                                <button type="button" class="scp-checkout-submit">
                                    {{ __('storefront.checkout.place_order') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <aside class="scp-checkout-summary">
                        <h2>{{ __('storefront.checkout.order_summary') }}</h2>

                        <div class="scp-checkout-items">
                            @foreach($cart->items as $item)
                                <div class="scp-checkout-item">
                                    <div class="scp-checkout-item-image">
                                        @if($resolveProductImage($item))
                                            <img src="{{ $resolveProductImage($item) }}" alt="{{ $resolveItemName($item) }}">
                                        @else
                                            <div class="scp-cart-card-placeholder">
                                                {{ mb_substr($resolveItemName($item), 0, 1) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="scp-checkout-item-info">
                                        <h3>{{ $resolveItemName($item) }}</h3>
                                        <span>{{ __('storefront.cart.quantity') }}: {{ $item->quantity }}</span>
                                    </div>

                                    <strong>
                                        {{ $currencySymbol }} {{ number_format((float) $item->line_total, 2) }}
                                    </strong>
                                </div>
                            @endforeach
                        </div>

                        <div class="scp-checkout-summary-lines">
                            <div>
                                <span>{{ __('storefront.cart.subtotal') }}</span>
                                <strong>{{ $currencySymbol }} {{ number_format((float) $cart->subtotal, 2) }}</strong>
                            </div>

                            <div>
                                <span>{{ __('storefront.cart.discount') }}</span>
                                <strong>{{ $currencySymbol }} {{ number_format((float) $cart->discount_total, 2) }}</strong>
                            </div>

                            <div>
                                <span>{{ __('storefront.cart.shipping') }}</span>
                                <strong>{{ $currencySymbol }} {{ number_format((float) $cart->shipping_total, 2) }}</strong>
                            </div>

                            <div>
                                <span>{{ __('storefront.cart.tax') }}</span>
                                <strong>{{ $currencySymbol }} {{ number_format((float) $cart->tax_total, 2) }}</strong>
                            </div>
                        </div>

                        <div class="scp-checkout-total">
                            <span>{{ __('storefront.cart.grand_total') }}</span>
                            <strong>{{ $currencySymbol }} {{ number_format((float) $cart->grand_total, 2) }}</strong>
                        </div>

                        <div class="scp-checkout-note">
                            {{ __('storefront.checkout.finalization_note') }}
                        </div>
                    </aside>

                </div>
            @endif

        </div>
    </section>
@endsection