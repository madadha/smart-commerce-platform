@extends('storefront.layout')

@section('content')
    @php
        $resolveStatus = function ($value) {
            if ($value instanceof \BackedEnum) {
                return $value->value;
            }

            return (string) $value;
        };
    @endphp

    <section class="scp-history-page">
        <div class="scp-container">

            <div class="scp-history-hero">
                <div>
                    <span class="scp-history-badge">
                        {{ __('storefront.order_history.badge') }}
                    </span>

                    <h1>{{ __('storefront.order_history.page_title') }}</h1>

                    <p>{{ __('storefront.order_history.page_description') }}</p>
                </div>

                <div class="scp-history-count-card">
                    <span>{{ __('storefront.order_history.total_orders') }}</span>
                    <strong>{{ $orders->total() }}</strong>
                </div>
            </div>

            @if($orders->count() > 0)
                <div class="scp-history-table-card">
                    <div class="scp-history-table-head">
                        <h2>{{ __('storefront.order_history.orders_list') }}</h2>

                        <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}">
                            {{ __('storefront.cart.continue_shopping') }}
                        </a>
                    </div>

                    <div class="scp-history-table-wrap">
                        <table class="scp-history-table">
                            <thead>
                                <tr>
                                    <th>{{ __('storefront.order_details.order_number') }}</th>
                                    <th>{{ __('storefront.order_details.ordered_at') }}</th>
                                    <th>{{ __('storefront.order_details.order_status') }}</th>
                                    <th>{{ __('storefront.order_details.payment_status') }}</th>
                                    <th>{{ __('storefront.order_history.items_count') }}</th>
                                    <th>{{ __('storefront.cart.grand_total') }}</th>
                                    <th>{{ __('storefront.order_history.actions') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($orders as $order)
                                    @php
                                        $currencySymbol = $order?->currency?->symbol ?? '₪';
                                    @endphp

                                    <tr>
                                        <td>
                                            <strong>{{ $order->order_number }}</strong>
                                        </td>

                                        <td>
                                            {{ optional($order->ordered_at ?? $order->created_at)->format('Y-m-d H:i') }}
                                        </td>

                                        <td>
                                            <span class="scp-history-status">
                                                {{ $resolveStatus($order->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="scp-history-payment">
                                                {{ $resolveStatus($order->payment_status) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $order->items->count() }}
                                        </td>

                                        <td>
                                            <strong>
                                                {{ $currencySymbol }} {{ number_format((float) $order->grand_total, 2) }}
                                            </strong>
                                        </td>

                                        <td>
                                            <a
                                                class="scp-history-view-btn"
                                                href="{{ URL::signedRoute('storefront.orders.show', ['order' => $order->id, 'lang' => $locale]) }}"
                                            >
                                                {{ __('storefront.order_history.view_details') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="scp-history-pagination">
                        {{ $orders->links() }}
                    </div>
                </div>
            @else
                <div class="scp-history-empty">
                    <div class="scp-history-empty-icon">🧾</div>

                    <h2>{{ __('storefront.order_history.empty_title') }}</h2>

                    <p>{{ __('storefront.order_history.empty_text') }}</p>

                    <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}">
                        {{ __('storefront.cart.continue_shopping') }}
                    </a>
                </div>
            @endif

        </div>
    </section>
@endsection