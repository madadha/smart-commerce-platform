@extends('storefront.layout')

@section('content')
    @php
        $currencySymbol = $latestOrder?->currency?->symbol ?? '₪';

        $resolveStatus = function ($value) {
            if ($value instanceof \BackedEnum) {
                return $value->value;
            }

            return (string) $value;
        };

        $locale = $locale ?? request('lang', app()->getLocale() ?? 'ar');
        $formatLocalizedNumber = function ($value, int $decimals = 0) use ($locale) {
            $formatted = number_format((float) $value, $decimals, '.', ',');

            if ($locale !== 'ar') {
                return $formatted;
            }

            return strtr($formatted, [
                '0' => '٠',
                '1' => '١',
                '2' => '٢',
                '3' => '٣',
                '4' => '٤',
                '5' => '٥',
                '6' => '٦',
                '7' => '٧',
                '8' => '٨',
                '9' => '٩',
            ]);
        };
        $formatLocalizedMoney = function ($value) use ($currencySymbol, $formatLocalizedNumber) {
            return $currencySymbol.' '.$formatLocalizedNumber($value, 2);
        };

        $logoutText = match ($locale) {
            'he' => 'התנתקות',
            'en' => 'Logout',
            default => 'تسجيل الخروج',
        };

        $logoutHint = match ($locale) {
            'he' => 'צא מהחשבון שלך בצורה בטוחה.',
            'en' => 'Sign out securely from your account.',
            default => 'الخروج الآمن من حسابك.',
        };
    @endphp

    <section class="scp-account-page">
        <div class="scp-container">
            <div class="scp-account-hero">
                <div class="scp-account-hero-content">
                    <span class="scp-account-badge">
                        {{ __('storefront.account_dashboard.badge') }}
                    </span>

                    <h1>
                        {{ __('storefront.account_dashboard.welcome') }},
                        <span>{{ $user->name }}</span>
                    </h1>

                    <p>{{ __('storefront.account_dashboard.page_description') }}</p>
                </div>

                <div class="scp-account-profile-card">
                    <div class="scp-account-avatar">
                        {{ mb_substr($user->name ?? 'U', 0, 1) }}
                    </div>

                    <div>
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                </div>
            </div>

            <div class="scp-account-stats-grid">
                <div class="scp-account-stat-card">
                    <div class="scp-account-stat-icon">🧾</div>
                    <span>{{ __('storefront.account_dashboard.total_orders') }}</span>
                    <strong>{{ $formatLocalizedNumber($totalOrders, 0) }}</strong>
                </div>

                <div class="scp-account-stat-card">
                    <div class="scp-account-stat-icon">💳</div>
                    <span>{{ __('storefront.account_dashboard.total_spent') }}</span>
                    <strong>{{ $formatLocalizedMoney((float) $totalSpent) }}</strong>
                </div>

                <div class="scp-account-stat-card">
                    <div class="scp-account-stat-icon">⏳</div>
                    <span>{{ __('storefront.account_dashboard.pending_orders') }}</span>
                    <strong>{{ $formatLocalizedNumber($pendingOrders, 0) }}</strong>
                </div>

                <div class="scp-account-stat-card">
                    <div class="scp-account-stat-icon">✅</div>
                    <span>{{ __('storefront.account_dashboard.completed_orders') }}</span>
                    <strong>{{ $formatLocalizedNumber($completedOrders, 0) }}</strong>
                </div>

                <div class="scp-account-stat-card">
                    <div class="scp-account-stat-icon">⚠️</div>
                    <span>{{ __('storefront.account_dashboard.unpaid_orders') }}</span>
                    <strong>{{ $formatLocalizedNumber($unpaidOrders, 0) }}</strong>
                </div>
            </div>

            <div class="scp-account-grid">

                <div class="scp-account-main">

                    <div class="scp-account-panel">
                        <div class="scp-account-panel-head">
                            <div>
                                <h2>{{ __('storefront.account_dashboard.recent_orders') }}</h2>
                                <p>{{ __('storefront.account_dashboard.recent_orders_hint') }}</p>
                            </div>

                            <a href="{{ route('storefront.orders.history', ['lang' => $locale]) }}">
                                {{ __('storefront.account_dashboard.view_all') }}
                            </a>
                        </div>

                        @if($recentOrders->count() > 0)
                            <div class="scp-account-orders-list">
                                @foreach($recentOrders as $order)
                                    <article class="scp-account-order-card">
                                        <div>
                                            <span class="scp-account-order-label">
                                                {{ __('storefront.order_details.order_number') }}
                                            </span>

                                            <strong>{{ $order->order_number }}</strong>

                                            <small>
                                                {{ optional($order->ordered_at ?? $order->created_at)->format('Y-m-d H:i') }}
                                            </small>
                                        </div>

                                        <div>
                                            <span class="scp-account-order-label">
                                                {{ __('storefront.order_details.order_status') }}
                                            </span>

                                            <em>{{ $resolveStatus($order->status) }}</em>
                                        </div>

                                        <div>
                                            <span class="scp-account-order-label">
                                                {{ __('storefront.order_details.payment_status') }}
                                            </span>

                                            <em class="scp-account-payment-status">
                                                {{ $resolveStatus($order->payment_status) }}
                                            </em>
                                        </div>

                                        <div>
                                            <span class="scp-account-order-label">
                                                {{ __('storefront.cart.grand_total') }}
                                            </span>

                                            <strong>{{ $formatLocalizedMoney((float) $order->grand_total) }}</strong>
                                        </div>

                                        <a
                                            href="{{ URL::signedRoute('storefront.orders.show', ['order' => $order->id, 'lang' => $locale]) }}"
                                            class="scp-account-view-order"
                                        >
                                            {{ __('storefront.order_history.view_details') }}
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="scp-account-empty">
                                <div>🛒</div>
                                <h3>{{ __('storefront.account_dashboard.no_orders_title') }}</h3>
                                <p>{{ __('storefront.account_dashboard.no_orders_text') }}</p>
                                <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}">
                                    {{ __('storefront.account_dashboard.start_shopping') }}
                                </a>
                            </div>
                        @endif
                    </div>

                </div>

                <aside class="scp-account-sidebar">

                    <div class="scp-account-panel">
                        <div class="scp-account-panel-head">
                            <div>
                                <h2>{{ __('storefront.account_dashboard.latest_order') }}</h2>
                                <p>{{ __('storefront.account_dashboard.latest_order_hint') }}</p>
                            </div>
                        </div>

                        @if($latestOrder)
                            <div class="scp-account-latest-order">
                                <span>{{ $latestOrder->order_number }}</span>

                                <strong>
                                    {{ $formatLocalizedMoney((float) $latestOrder->grand_total) }}
                                </strong>

                                <div>
                                    <small>{{ __('storefront.order_details.order_status') }}</small>
                                    <em>{{ $resolveStatus($latestOrder->status) }}</em>
                                </div>

                                <div>
                                    <small>{{ __('storefront.order_details.payment_status') }}</small>
                                    <em>{{ $resolveStatus($latestOrder->payment_status) }}</em>
                                </div>

                                <a href="{{ URL::signedRoute('storefront.orders.show', ['order' => $latestOrder->id, 'lang' => $locale]) }}">
                                    {{ __('storefront.order_history.view_details') }}
                                </a>
                            </div>
                        @else
                            <div class="scp-account-mini-empty">
                                {{ __('storefront.account_dashboard.no_latest_order') }}
                            </div>
                        @endif
                    </div>

                    <div class="scp-account-panel">
                        <div class="scp-account-panel-head">
                            <div>
                                <h2>{{ __('storefront.account_dashboard.quick_actions') }}</h2>
                                <p>{{ __('storefront.account_dashboard.quick_actions_hint') }}</p>
                            </div>
                        </div>

                        <div class="scp-account-actions-list">
                            <a href="{{ route('storefront.orders.history', ['lang' => $locale]) }}">
                                <span>🧾</span>
                                {{ __('storefront.order_history.my_orders') }}
                            </a>

                            <a href="{{ route('storefront.orders.track', ['lang' => $locale]) }}">
                                <span>📦</span>
                                {{ __('storefront.order_tracking.track_order') }}
                            </a>

                            <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}">
                                <span>🛍️</span>
                                {{ __('storefront.account_dashboard.browse_products') }}
                            </a>

                            <a href="{{ route('profile.edit', ['lang' => $locale]) }}">
                                <span>⚙️</span>
                                {{ __('storefront.account_dashboard.profile_settings') }}
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="scp-account-logout-form">
                                @csrf

                                <input type="hidden" name="lang" value="{{ $locale }}">

                                <button type="submit" class="scp-account-logout-action">
                                    <span>🚪</span>
                                    <strong>{{ $logoutText }}</strong>
                                    <small>{{ $logoutHint }}</small>
                                </button>
                            </form>
                        </div>
                    </div>

                </aside>

            </div>

        </div>
    </section>
@endsection
