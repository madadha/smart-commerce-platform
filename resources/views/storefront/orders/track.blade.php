@extends('storefront.layout')

@section('content')
    <section class="scp-track-page">
        <div class="scp-container">

            <div class="scp-track-layout">

                <div class="scp-track-hero">
                    <span class="scp-track-badge">
                        {{ __('storefront.order_tracking.badge') }}
                    </span>

                    <h1>{{ __('storefront.order_tracking.page_title') }}</h1>

                    <p>{{ __('storefront.order_tracking.page_description') }}</p>

                    <div class="scp-track-steps">
                        <div>
                            <strong>1</strong>
                            <span>{{ __('storefront.order_tracking.step_1') }}</span>
                        </div>

                        <div>
                            <strong>2</strong>
                            <span>{{ __('storefront.order_tracking.step_2') }}</span>
                        </div>

                        <div>
                            <strong>3</strong>
                            <span>{{ __('storefront.order_tracking.step_3') }}</span>
                        </div>
                    </div>
                </div>

                <div class="scp-track-card">
                    <h2>{{ __('storefront.order_tracking.form_title') }}</h2>
                    <p>{{ __('storefront.order_tracking.form_hint') }}</p>

                    @if(! empty($orderNotFound))
                        <div class="scp-track-error">
                            {{ __('storefront.order_tracking.not_found') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="scp-track-error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('storefront.orders.track.result') }}" class="scp-track-form">
                        @csrf

                        <input type="hidden" name="lang" value="{{ $locale }}">

                        <div class="scp-field">
                            <label>{{ __('storefront.order_tracking.order_number') }}</label>
                            <input
                                type="text"
                                name="order_number"
                                value="{{ old('order_number', $oldOrderNumber ?? '') }}"
                                placeholder="ORD-20260607-01313"
                                required
                            >
                        </div>

                        <div class="scp-field">
                            <label>{{ __('storefront.order_tracking.phone') }}</label>
                            <input
                                type="text"
                                name="customer_phone"
                                value="{{ old('customer_phone', $oldCustomerPhone ?? '') }}"
                                placeholder="05x-xxxxxxx"
                                required
                            >
                        </div>

                        <button type="submit" class="scp-track-submit">
                            {{ __('storefront.order_tracking.submit') }}
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </section>
@endsection