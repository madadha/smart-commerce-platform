<!DOCTYPE html>
@php
    $currentLocale = $locale ?? request('lang', session('storefront_locale', app()->getLocale() ?? 'ar'));
    $allowedLocales = ['ar', 'he', 'en'];

    if (! in_array($currentLocale, $allowedLocales, true)) {
        $currentLocale = 'ar';
    }

    $currentDirection = in_array($currentLocale, ['ar', 'he'], true) ? 'rtl' : 'ltr';

    $authLabels = [
        'ar' => [
            'login' => 'تسجيل الدخول',
            'register' => 'إنشاء حساب',
            'account' => 'حسابي',
            'wishlist' => 'المفضلة',
            'cart' => 'السلة',
        ],
        'he' => [
            'login' => 'התחברות',
            'register' => 'הרשמה',
            'account' => 'החשבון שלי',
            'wishlist' => 'מועדפים',
            'cart' => 'עגלה',
        ],
        'en' => [
            'login' => 'Login',
            'register' => 'Register',
            'account' => 'My Account',
            'wishlist' => 'Wishlist',
            'cart' => 'Cart',
        ],
    ];

    $authText = $authLabels[$currentLocale] ?? $authLabels['ar'];
@endphp
<html lang="{{ $currentLocale }}" dir="{{ $currentDirection }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Smart Commerce Platform' }}</title>

    <meta name="description" content="{{ $pageDescription ?? 'Smart Commerce Platform - Modern dynamic e-commerce platform.' }}">

    <link rel="stylesheet" href="{{ asset('css/storefront/storefront.css') }}?v={{ filemtime(public_path('css/storefront/storefront.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/storefront/design-overrides.css') }}?v={{ filemtime(public_path('css/storefront/design-overrides.css')) }}">
</head>

<body class="scp-storefront {{ $currentDirection === 'rtl' ? 'is-rtl' : 'is-ltr' }}">

<header class="scp-header">
    <div class="scp-container">

        <div class="scp-topbar">
            <div class="scp-topbar-text">
                {{ __('storefront.topbar') }}
            </div>

            <div class="scp-language-switcher">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="{{ $currentLocale === 'ar' ? 'active' : '' }}">AR</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'he']) }}" class="{{ $currentLocale === 'he' ? 'active' : '' }}">HE</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="{{ $currentLocale === 'en' ? 'active' : '' }}">EN</a>
            </div>
        </div>

        <div class="scp-main-header">
            <a href="{{ route('storefront.home', ['lang' => $currentLocale]) }}" class="scp-logo">
                <span class="scp-logo-mark">S</span>
                <span>
                    <strong>Smart Commerce</strong>
                    <small>Marketplace Platform</small>
                </span>
            </a>

            <form class="scp-search" action="{{ route('storefront.products.index') }}" method="GET">
                <input type="hidden" name="lang" value="{{ $currentLocale }}">

                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ __('storefront.nav.search_placeholder') }}"
                >

                <button type="submit">
                    {{ __('storefront.nav.search') }}
                </button>
            </form>

            <div class="scp-header-actions">
                @auth
                    <a href="{{ route('storefront.account.dashboard', ['lang' => $currentLocale]) }}" class="scp-header-action">
                        <span>👤</span>
                        <small>{{ $authText['account'] }}</small>
                    </a>

                    <a href="{{ route('storefront.wishlist.index', ['lang' => $currentLocale]) }}" class="scp-header-action">
                        <span>♡</span>
                        <small>{{ $authText['wishlist'] }}</small>
                    </a>
                @else
                    <a href="{{ route('login', ['lang' => $currentLocale]) }}" class="scp-header-action scp-header-auth-link">
                        <span>👤</span>
                        <small>{{ $authText['login'] }}</small>
                    </a>

                    <a href="{{ route('register', ['lang' => $currentLocale]) }}" class="scp-header-action scp-header-auth-primary">
                        <span>＋</span>
                        <small>{{ $authText['register'] }}</small>
                    </a>
                @endauth

                <a href="{{ route('storefront.cart.index', ['lang' => $currentLocale]) }}" class="scp-header-action">
                    <span>🛒</span>
                    <small>{{ $authText['cart'] }}</small>
                </a>
            </div>
        </div>

        <nav class="scp-nav">
            <a href="{{ route('storefront.home', ['lang' => $currentLocale]) }}">
                {{ __('storefront.nav.home') }}
            </a>

            <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">
                {{ __('storefront.nav.products') }}
            </a>

            <a href="{{ route('storefront.products.index', ['lang' => $currentLocale, 'type' => 'digital']) }}">
                {{ __('storefront.nav.digital_codes') }}
            </a>

            <a href="{{ route('storefront.products.index', ['lang' => $currentLocale, 'on_sale' => 1]) }}">
                {{ __('storefront.nav.deals') }}
            </a>

            <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">
                {{ __('storefront.nav.brands') }}
            </a>

            <a href="{{ route('storefront.orders.track', ['lang' => $currentLocale]) }}">
                {{ __('storefront.order_tracking.track_order') }}
            </a>

            @auth
                <a href="{{ route('storefront.orders.history', ['lang' => $currentLocale]) }}">
                    {{ __('storefront.order_history.my_orders') }}
                </a>

                <a href="{{ route('storefront.account.dashboard', ['lang' => $currentLocale]) }}">
                    {{ __('storefront.account_dashboard.my_account') }}
                </a>

                <a href="{{ route('storefront.wishlist.index', ['lang' => $currentLocale]) }}">
                    {{ __('storefront.wishlist.my_wishlist') }}
                </a>
            @else
                <a href="{{ route('login', ['lang' => $currentLocale]) }}" class="scp-nav-login-link">
                    {{ $authText['login'] }}
                </a>

                <a href="{{ route('register', ['lang' => $currentLocale]) }}" class="scp-nav-register-link">
                    {{ $authText['register'] }}
                </a>
            @endauth

            <a href="{{ route('storefront.compare.index', ['lang' => $currentLocale]) }}">
                {{ __('storefront.compare.compare') }}
            </a>
        </nav>

    </div>
</header>

<main>
    @if(session('success'))
        <div class="scp-container">
            <div class="scp-alert-success">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="scp-container">
            <div class="scp-alert-error">
                {{ session('error') }}
            </div>
        </div>
    @endif

    @yield('content')
</main>

<footer class="scp-footer">
    <div class="scp-container">
        <div class="scp-footer-grid">
            <div>
                <h3>Smart Commerce Platform</h3>
                <p>
                    {{ __('storefront.footer.description') }}
                </p>
            </div>

            <div>
                <h4>{{ __('storefront.footer.quick_links') }}</h4>

                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">
                    {{ __('storefront.footer.products') }}
                </a>
                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">{{ __('storefront.footer.categories') }}</a>
                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">{{ __('storefront.footer.brands') }}</a>
                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale, 'on_sale' => 1]) }}">{{ __('storefront.footer.deals') }}</a>
            </div>

            <div>
                <h4>{{ __('storefront.footer.support') }}</h4>

                <a href="{{ route('storefront.orders.track', ['lang' => $currentLocale]) }}">{{ __('storefront.order_tracking.track_order') }}</a>
                <a href="{{ route('storefront.cart.index', ['lang' => $currentLocale]) }}">{{ __('storefront.nav.cart') }}</a>
                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">{{ __('storefront.footer.products') }}</a>
            </div>
        </div>

        <div class="scp-footer-bottom">
            © {{ date('Y') }} Smart Commerce Platform. {{ __('storefront.footer.rights') }}
        </div>
    </div>
</footer>

</body>
</html>
