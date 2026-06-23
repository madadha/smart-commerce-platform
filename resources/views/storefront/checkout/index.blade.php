@extends('storefront.layout')

@section('content')
    @php
        $currencySymbol = $cart?->currency?->symbol ?? '₪';
        $checkoutDefaults = $checkoutDefaults ?? [];

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
                        @auth
                            <div class="scp-checkout-saved-info">
                                <div>
                                    <strong>{{ $locale === 'ar' ? 'بياناتك محفوظة' : ($locale === 'he' ? 'הפרטים שלך שמורים' : 'Saved information') }}</strong>
                                    <p>{{ $locale === 'ar' ? 'تم تعبئة بياناتك من حساب الزبون. يمكنك تعديلها من صفحة بياناتي.' : ($locale === 'he' ? 'הפרטים מולאו מהחשבון שלך. ניתן לערוך אותם בפרופיל.' : 'Your checkout details were filled from your customer profile. You can edit them from Profile.') }}</p>
                                </div>
                                <a href="{{ route('profile.edit', ['lang' => $locale]) }}">
                                    {{ $locale === 'ar' ? 'تعديل بياناتي' : ($locale === 'he' ? 'עריכת פרטים' : 'Edit profile') }}
                                </a>
                            </div>
                        @endauth

                        <form method="POST" action="{{ route('storefront.checkout.place') }}" class="scp-checkout-form">
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
                                        <input
                                            type="text"
                                            name="customer_name"
                                            value="{{ old('customer_name', $checkoutDefaults['customer_name'] ?? '') }}"
                                            placeholder="{{ __('storefront.checkout.full_name_placeholder') }}"
                                            required
                                        >
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.email') }}</label>
                                        <input
                                            type="email"
                                            name="customer_email"
                                            value="{{ old('customer_email', $checkoutDefaults['customer_email'] ?? '') }}"
                                            placeholder="example@email.com"
                                        >
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.phone') }}</label>
                                        <input
                                            type="text"
                                            name="customer_phone"
                                            value="{{ old('customer_phone', $checkoutDefaults['customer_phone'] ?? '') }}"
                                            placeholder="05x-xxxxxxx"
                                            required
                                        >
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.country') }}</label>
                                        <select name="country_id" id="checkout-country">
                                            <option value="">{{ __('storefront.checkout.select_country') }}</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}" @selected((string) old('country_id', $checkoutDefaults['country_id'] ?? '') === (string) $country->id)>
                                                    {{ $country->flag }} {{ $country->getName($locale) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.city') }}</label>
                                        <input
                                            type="text"
                                            name="city"
                                            id="checkout-city"
                                            value="{{ old('city', $checkoutDefaults['city'] ?? '') }}"
                                            placeholder="{{ __('storefront.checkout.city_placeholder') }}"
                                            required
                                        >
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
                                        <input
                                            type="text"
                                            name="address"
                                            value="{{ old('address', $checkoutDefaults['address'] ?? '') }}"
                                            placeholder="{{ __('storefront.checkout.address_placeholder') }}"
                                            required
                                        >
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.shipping_method') }}</label>
                                        <select name="shipping_method_id" id="checkout-shipping-method" required>
                                            <option value="">{{ __('storefront.checkout.select_shipping_method') }}</option>

                                            @foreach($shippingMethods as $shippingMethod)
                                                <option value="{{ $shippingMethod->id }}" @selected((string) old('shipping_method_id', $checkoutDefaults['shipping_method_id'] ?? '') === (string) $shippingMethod->id)>
                                                    @if(method_exists($shippingMethod, 'getName'))
                                                        {{ $shippingMethod->getName($locale) }}
                                                    @else
                                                        {{ $shippingMethod->name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small id="checkout-shipping-feedback"></small>
                                    </div>

                                    <div class="scp-field">
                                        <label>{{ __('storefront.checkout.payment_method') }}</label>
                                        <select name="payment_method" required>
                                            @foreach ($paymentMethods as $method => $label)
                                                <option value="{{ $method }}" @selected(old('payment_method', 'cash') === $method)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="scp-field full">
                                        <label>{{ __('storefront.checkout.notes') }}</label>
                                        <textarea
                                            name="customer_notes"
                                            rows="4"
                                            placeholder="{{ __('storefront.checkout.notes_placeholder') }}"
                                        >{{ old('customer_notes', $checkoutDefaults['customer_notes'] ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="scp-checkout-actions">
                                <a href="{{ route('storefront.cart.index', ['lang' => $locale]) }}" class="scp-checkout-back">
                                    {{ __('storefront.checkout.back_to_cart') }}
                                </a>

                                <button type="submit" class="scp-checkout-submit">
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
                    </aside>

                </div>
            @endif

        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const country = document.getElementById('checkout-country');
    const city = document.getElementById('checkout-city');
    const method = document.getElementById('checkout-shipping-method');
    const feedback = document.getElementById('checkout-shipping-feedback');
    if (!city || !method) return;
    let timer;
    const refresh = () => {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            if (!city.value.trim()) return;
            feedback.textContent = @json(__('storefront.checkout.loading_shipping'));
            const url = new URL(@json(route('storefront.checkout.shipping-quotes')), window.location.origin);
            url.searchParams.set('city', city.value.trim());
            url.searchParams.set('lang', @json($locale));
            if (country?.value) url.searchParams.set('country_id', country.value);
            try {
                const response = await fetch(url, {headers: {'Accept': 'application/json'}});
                const data = await response.json();
                const selected = method.value;
                method.innerHTML = `<option value="">${@json(__('storefront.checkout.select_shipping_method'))}</option>`;
                for (const quote of (data.quotes || [])) {
                    const option = new Option(`${quote.name} — ${Number(quote.cost).toFixed(2)}`, quote.id);
                    option.selected = String(quote.id) === String(selected);
                    method.add(option);
                }
                feedback.textContent = data.quotes?.length ? @json(__('storefront.checkout.shipping_calculated')) : @json(__('storefront.checkout.no_shipping_available'));
            } catch (error) {
                feedback.textContent = @json(__('storefront.checkout.shipping_load_failed'));
            }
        }, 350);
    };
    city.addEventListener('input', refresh);
    country?.addEventListener('change', refresh);
    if (city.value.trim()) refresh();
});
</script>
@endpush
