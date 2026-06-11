<!DOCTYPE html>
<html lang="{{ $locale ?? app()->getLocale() }}" dir="{{ $direction ?? 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Smart Commerce Platform' }}</title>

    <meta name="description" content="{{ $pageDescription ?? 'Smart Commerce Platform - Modern dynamic e-commerce platform.' }}">

<link rel="stylesheet" href="{{ asset('css/storefront/storefront.css') }}?v={{ filemtime(public_path('css/storefront/storefront.css')) }}">

<link rel="stylesheet" href="{{ asset('css/storefront/design-overrides.css') }}?v={{ filemtime(public_path('css/storefront/design-overrides.css')) }}">
</head>

<body class="scp-storefront {{ ($direction ?? 'rtl') === 'rtl' ? 'is-rtl' : 'is-ltr' }}">

<header class="scp-header">
    <div class="scp-container">

        <div class="scp-topbar">
            <div class="scp-topbar-text">
                {{ __('storefront.topbar') }}
            </div>

            <div class="scp-language-switcher">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="{{ ($locale ?? 'ar') === 'ar' ? 'active' : '' }}">AR</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'he']) }}" class="{{ ($locale ?? 'ar') === 'he' ? 'active' : '' }}">HE</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="{{ ($locale ?? 'ar') === 'en' ? 'active' : '' }}">EN</a>
            </div>
        </div>

        <div class="scp-main-header">
            <a href="{{ route('storefront.home', ['lang' => $locale ?? 'ar']) }}" class="scp-logo">
                <span class="scp-logo-mark">S</span>
                <span>
                    <strong>Smart Commerce</strong>
                    <small>Marketplace Platform</small>
                </span>
            </a>

            <form class="scp-search" action="#" method="GET">
                <input type="hidden" name="lang" value="{{ $locale ?? 'ar' }}">

                <input
                    type="search"
                    name="q"
                    placeholder="{{ __('storefront.nav.search_placeholder') }}"
                >

                <button type="submit">
                    {{ __('storefront.nav.search') }}
                </button>
            </form>

            <div class="scp-header-actions">
                <a href="#" class="scp-header-action">
                    <span>♡</span>
                    <small>{{ __('storefront.nav.wishlist') }}</small>
                </a>

             <a href="{{ route('storefront.cart.index', ['lang' => $locale ?? 'ar']) }}" class="scp-header-action">
    <span>🛒</span>
    <small>{{ __('storefront.nav.cart') }}</small>
</a>
            </div>
        </div>

        <nav class="scp-nav">
            <a href="{{ route('storefront.home', ['lang' => $locale ?? 'ar']) }}">
                {{ __('storefront.nav.home') }}
            </a>

           <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar']) }}">
    {{ __('storefront.nav.products') }}
</a>

            <a href="#">
                {{ __('storefront.nav.digital_codes') }}
            </a>

            <a href="#">
                {{ __('storefront.nav.deals') }}
            </a>

            <a href="#">
                {{ __('storefront.nav.brands') }}
            </a>

            <a href="{{ route('storefront.orders.track', ['lang' => $locale ?? 'ar']) }}">
    {{ __('storefront.order_tracking.track_order') }}
</a>

@if(auth()->check())
    <a href="{{ route('storefront.orders.history', ['lang' => $locale ?? 'ar']) }}">
        {{ __('storefront.order_history.my_orders') }}
    </a>
@endif

@if(auth()->check())
    <a href="{{ route('storefront.account.dashboard', ['lang' => $locale ?? 'ar']) }}">
        {{ __('storefront.account_dashboard.my_account') }}
    </a>
@endif

@if(auth()->check())
    <a href="{{ route('storefront.wishlist.index', ['lang' => $locale ?? 'ar']) }}">
        {{ __('storefront.wishlist.my_wishlist') }}
    </a>
@endif

<a href="{{ route('storefront.compare.index', ['lang' => $locale ?? 'ar']) }}">
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

                <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar']) }}">
    {{ __('storefront.footer.products') }}
</a>
                <a href="#">{{ __('storefront.footer.categories') }}</a>
                <a href="#">{{ __('storefront.footer.brands') }}</a>
                <a href="#">{{ __('storefront.footer.deals') }}</a>
            </div>

            <div>
                <h4>{{ __('storefront.footer.support') }}</h4>

                <a href="#">{{ __('storefront.footer.contact') }}</a>
                <a href="#">{{ __('storefront.footer.shipping') }}</a>
                <a href="#">{{ __('storefront.footer.returns') }}</a>
                <a href="#">{{ __('storefront.footer.faq') }}</a>
            </div>
        </div>

        <div class="scp-footer-bottom">
            © {{ date('Y') }} Smart Commerce Platform. {{ __('storefront.footer.rights') }}
        </div>
    </div>
</footer>

</body>
</html>