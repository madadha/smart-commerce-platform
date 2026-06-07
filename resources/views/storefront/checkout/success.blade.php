@extends('storefront.layout')

@section('content')
    @php
        $currencySymbol = $order?->currency?->symbol ?? '₪';
    @endphp

    <section class="scp-checkout-success-page">
        <div class="scp-container">

            <div class="scp-success-card">
                <div class="scp-success-icon">✓</div>

                <h1>{{ __('storefront.checkout.success_title') }}</h1>

                <p>{{ __('storefront.checkout.success_text') }}</p>

                <div class="scp-success-details">
                    <div>
                        <span>{{ __('storefront.checkout.order_number') }}</span>
                        <strong>{{ $order->order_number }}</strong>
                    </div>

                    <div>
                        <span>{{ __('storefront.cart.grand_total') }}</span>
                        <strong>{{ $currencySymbol }} {{ number_format((float) $order->grand_total, 2) }}</strong>
                    </div>

                    <div>
                        <span>{{ __('storefront.checkout.order_status') }}</span>
                        <strong>{{ $order->status instanceof \BackedEnum ? $order->status->value : $order->status }}</strong>
                    </div>
                </div>

                <div class="scp-success-actions">
                    <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-success-primary">
                        {{ __('storefront.cart.continue_shopping') }}
                    </a>

                    <a href="{{ route('storefront.home', ['lang' => $locale]) }}" class="scp-success-secondary">
                        {{ __('storefront.nav.home') }}
                    </a>
                </div>
            </div>

        </div>
    </section>
@endsection