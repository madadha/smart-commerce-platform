<!DOCTYPE html>
@php
    $languageRegistry = app(\App\Support\Localization\ActiveLanguageRegistry::class);
    $activeLanguages = $languageRegistry->active();
    $currentLocale = $languageRegistry->resolve($locale ?? request('lang', session('storefront_locale', app()->getLocale() ?? 'ar')));
    $currentDirection = $languageRegistry->direction($currentLocale);

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

    $storefrontSettings = $storefrontSettings ?? (\App\Models\StorefrontSetting::current());
    $storeName = $storefrontSettings?->localized('store_name', $currentLocale, 'Smart Commerce') ?: 'Smart Commerce';
    $storeTagline = $storefrontSettings?->localized('store_tagline', $currentLocale, 'Marketplace Platform') ?: 'Marketplace Platform';
    $topbarText = $storefrontSettings?->localized('topbar_text', $currentLocale, __('storefront.topbar')) ?: __('storefront.topbar');
    $footerDescription = $storefrontSettings?->localized('footer_description', $currentLocale, __('storefront.footer.description')) ?: __('storefront.footer.description');
    $footerRights = $storefrontSettings?->localized('footer_rights_text', $currentLocale, __('storefront.footer.rights')) ?: __('storefront.footer.rights');
    $footerAddress = $storefrontSettings?->localized('address', $currentLocale, '') ?: '';
    $sessionCartId = session('storefront_cart_id');
    $cartCount = $sessionCartId
        ? (int) \App\Models\CartItem::query()
            ->where('cart_id', $sessionCartId)
            ->sum('quantity')
        : 0;
    $floatingWhatsappUrl = $storefrontSettings?->whatsapp
        ? 'https://wa.me/'.preg_replace('/\D+/', '', $storefrontSettings->whatsapp)
        : null;
    $footerContactItems = collect([
        [
            'icon' => '✉',
            'label' => __('storefront.footer.email'),
            'value' => $storefrontSettings?->contact_email,
            'url' => $storefrontSettings?->contact_email ? 'mailto:'.$storefrontSettings->contact_email : null,
        ],
        [
            'icon' => '☎',
            'label' => __('storefront.footer.phone'),
            'value' => $storefrontSettings?->contact_phone,
            'url' => $storefrontSettings?->contact_phone ? 'tel:'.preg_replace('/\s+/', '', $storefrontSettings->contact_phone) : null,
        ],
        [
            'icon' => '☘',
            'label' => __('storefront.footer.whatsapp'),
            'value' => $storefrontSettings?->whatsapp,
            'url' => $storefrontSettings?->whatsapp ? 'https://wa.me/'.preg_replace('/\D+/', '', $storefrontSettings->whatsapp) : null,
        ],
        [
            'icon' => '⌖',
            'label' => __('storefront.footer.address'),
            'value' => $footerAddress,
            'url' => null,
        ],
    ])->filter(fn (array $item) => filled($item['value']));
    $footerSocialLinks = collect([
        ['icon' => 'f', 'label' => 'Facebook', 'url' => $storefrontSettings?->facebook_url],
        ['icon' => '◎', 'label' => 'Instagram', 'url' => $storefrontSettings?->instagram_url],
        ['icon' => '♪', 'label' => 'TikTok', 'url' => $storefrontSettings?->tiktok_url],
        ['icon' => '▶', 'label' => 'YouTube', 'url' => $storefrontSettings?->youtube_url],
    ])->filter(fn (array $item) => filled($item['url']));
    $footerSocialLinks = $footerSocialLinks->map(function (array $item) use ($storefrontSettings): array {
        $customIcons = [
            'Facebook' => $storefrontSettings?->facebook_icon ?: 'f',
            'Instagram' => $storefrontSettings?->instagram_icon ?: '◎',
            'TikTok' => $storefrontSettings?->tiktok_icon ?: '♪',
            'YouTube' => $storefrontSettings?->youtube_icon ?: '▶',
        ];

        $item['icon'] = $customIcons[$item['label']] ?? $item['icon'];

        return $item;
    });
    $logoUrl = $storefrontSettings?->logoUrl();
    $faviconUrl = $storefrontSettings?->faviconUrl();
    $themeVariables = $storefrontSettings?->cssVariables();
@endphp
<html lang="{{ $currentLocale }}" dir="{{ $currentDirection }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? $storeName }}</title>

    <meta name="description" content="{{ $pageDescription ?? $storeTagline }}">

    <link rel="stylesheet" href="{{ asset('css/storefront/storefront.css') }}?v={{ filemtime(public_path('css/storefront/storefront.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/storefront/design-overrides.css') }}?v={{ filemtime(public_path('css/storefront/design-overrides.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/storefront/customer-profile.css') }}?v={{ file_exists(public_path('css/storefront/customer-profile.css')) ? filemtime(public_path('css/storefront/customer-profile.css')) : time() }}">
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif

    @if(! empty($themeVariables))
        <style>
            :root { {!! $themeVariables !!} }
        </style>
    @endif
</head>

<body class="scp-storefront {{ $currentDirection === 'rtl' ? 'is-rtl' : 'is-ltr' }}">

<header class="scp-header">
    <div class="scp-container">

        <div class="scp-topbar">
            <div class="scp-topbar-text">
                {{ $topbarText }}
            </div>

            <div class="scp-language-switcher">
                @forelse($activeLanguages as $language)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $language->code]) }}" class="{{ $currentLocale === $language->code ? 'active' : '' }}">
                        {{ strtoupper($language->code) }}
                    </a>
                @empty
                    @foreach(\App\Support\Localization\ActiveLanguageRegistry::SUPPORTED_CODES as $languageCode)
                        <a href="{{ request()->fullUrlWithQuery(['lang' => $languageCode]) }}" class="{{ $currentLocale === $languageCode ? 'active' : '' }}">
                            {{ strtoupper($languageCode) }}
                        </a>
                    @endforeach
                @endforelse
            </div>
        </div>

        <div class="scp-main-header">
            <a href="{{ route('storefront.home', ['lang' => $currentLocale]) }}" class="scp-logo">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="scp-logo-image">
                @else
                    <span class="scp-logo-mark">{{ mb_substr($storeName, 0, 1) }}</span>
                @endif
                <span>
                    <strong>{{ $storeName }}</strong>
                    <small>{{ $storeTagline }}</small>
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

                <a href="{{ route('storefront.cart.index', ['lang' => $currentLocale]) }}" class="scp-header-action scp-header-cart-link">
                    <span>🛒</span>
                    @if($cartCount > 0)
                        <strong class="scp-cart-count-badge" aria-label="{{ $cartCount }} {{ $authText['cart'] }}">{{ $cartCount }}</strong>
                    @endif
                    <small>{{ $authText['cart'] }}</small>
                </a>
            </div>

            <button
                type="button"
                class="scp-mobile-menu-toggle"
                data-scp-mobile-menu-toggle
                aria-label="{{ $currentLocale === 'he' ? 'פתיחת תפריט' : ($currentLocale === 'en' ? 'Open menu' : 'فتح القائمة') }}"
                aria-expanded="false"
                aria-controls="scp-mobile-navigation"
            >
                <span class="scp-mobile-menu-icon" aria-hidden="true">
                    <i></i>
                    <i></i>
                    <i></i>
                </span>
                <small>{{ $currentLocale === 'he' ? 'תפריט' : ($currentLocale === 'en' ? 'Menu' : 'القائمة') }}</small>
            </button>
        </div>

        <div class="scp-mobile-menu-backdrop" data-scp-mobile-menu-close></div>

        <nav id="scp-mobile-navigation" class="scp-nav" data-scp-mobile-menu aria-label="{{ $currentLocale === 'he' ? 'ניווט ראשי' : ($currentLocale === 'en' ? 'Main navigation' : 'التنقل الرئيسي') }}">
            <div class="scp-mobile-menu-head">
                <strong>{{ $currentLocale === 'he' ? 'תפריט החנות' : ($currentLocale === 'en' ? 'Store Menu' : 'قائمة المتجر') }}</strong>
                <button type="button" data-scp-mobile-menu-close aria-label="{{ $currentLocale === 'he' ? 'סגירת תפריט' : ($currentLocale === 'en' ? 'Close menu' : 'إغلاق القائمة') }}">×</button>
            </div>

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
            <div class="scp-footer-brand-block">
                <div class="scp-footer-brand">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $storeName }}">
                    @else
                        <span>{{ mb_substr($storeName, 0, 1) }}</span>
                    @endif

                    <div>
                        <h3>{{ $storeName }}</h3>
                        <small>{{ $storeTagline }}</small>
                    </div>
                </div>

                <p>
                    {{ $footerDescription }}
                </p>

                @if($footerSocialLinks->isNotEmpty())
                    <div class="scp-footer-socials">
                        @foreach($footerSocialLinks as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}">
                                <span>{{ $social['icon'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="scp-footer-links-block">
                <h4>{{ __('storefront.footer.quick_links') }}</h4>

                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">
                    {{ __('storefront.footer.products') }}
                </a>
                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">{{ __('storefront.footer.categories') }}</a>
                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">{{ __('storefront.footer.brands') }}</a>
                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale, 'on_sale' => 1]) }}">{{ __('storefront.footer.deals') }}</a>
            </div>

            <div class="scp-footer-links-block">
                <h4>{{ __('storefront.footer.support') }}</h4>

                <a href="{{ route('storefront.orders.track', ['lang' => $currentLocale]) }}">{{ __('storefront.order_tracking.track_order') }}</a>
                <a href="{{ route('storefront.cart.index', ['lang' => $currentLocale]) }}">{{ __('storefront.nav.cart') }}</a>
                <a href="{{ route('storefront.products.index', ['lang' => $currentLocale]) }}">{{ __('storefront.footer.products') }}</a>
            </div>

            @if($footerContactItems->isNotEmpty())
                <div class="scp-footer-contact-block">
                    <h4>{{ __('storefront.footer.contact') }}</h4>

                    <div class="scp-footer-contact-list">
                        @foreach($footerContactItems as $item)
                            @if($item['url'])
                                <a href="{{ $item['url'] }}" class="scp-footer-contact-item" @if(str_starts_with($item['url'], 'https://')) target="_blank" rel="noopener noreferrer" @endif>
                                    <span>{{ $item['icon'] }}</span>
                                    <div>
                                        <small>{{ $item['label'] }}</small>
                                        <strong>{{ $item['value'] }}</strong>
                                    </div>
                                </a>
                            @else
                                <div class="scp-footer-contact-item">
                                    <span>{{ $item['icon'] }}</span>
                                    <div>
                                        <small>{{ $item['label'] }}</small>
                                        <strong>{{ $item['value'] }}</strong>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="scp-footer-bottom">
            &copy; {{ date('Y') }} {{ $storeName }}. {{ $footerRights }}
        </div>
    </div>
</footer>

@if($floatingWhatsappUrl && ($storefrontSettings?->show_floating_whatsapp ?? true))
    <a
        href="{{ $floatingWhatsappUrl }}"
        class="scp-floating-whatsapp"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="{{ __('storefront.footer.whatsapp') }}"
    >
        <span>{{ $storefrontSettings?->whatsapp_floating_icon ?: '☎' }}</span>
    </a>
@endif

@if(request()->is('store') || request()->is('store/*'))
    <x-cookie-consent :locale="$currentLocale" :settings="$storefrontSettings" />
@endif


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var body = document.body;
        var toggles = document.querySelectorAll('[data-scp-mobile-menu-toggle]');
        var closers = document.querySelectorAll('[data-scp-mobile-menu-close]');
        var menu = document.querySelector('[data-scp-mobile-menu]');

        function openMenu() {
            body.classList.add('scp-mobile-menu-open');
            toggles.forEach(function (toggle) {
                toggle.setAttribute('aria-expanded', 'true');
            });
        }

        function closeMenu() {
            body.classList.remove('scp-mobile-menu-open');
            toggles.forEach(function (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            });
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                if (body.classList.contains('scp-mobile-menu-open')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
        });

        closers.forEach(function (closer) {
            closer.addEventListener('click', closeMenu);
        });

        if (menu) {
            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    });
</script>

@stack('scripts')

</body>
</html>
