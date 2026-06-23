@php
    $languageRegistry = app(\App\Support\Localization\ActiveLanguageRegistry::class);
    $locale = $languageRegistry->resolve(request('lang', session('storefront_locale', app()->getLocale() ?: 'ar')));
    $direction = $languageRegistry->direction($locale);
    $activeLanguages = $languageRegistry->active();
    $storefrontSettings = \App\Models\StorefrontSetting::current();
    $storeName = $storefrontSettings?->localized('store_name', $locale, 'Smart Commerce') ?: 'Smart Commerce';
    $storeTagline = $storefrontSettings?->localized('store_tagline', $locale, 'Marketplace Platform') ?: 'Marketplace Platform';
    $logoUrl = $storefrontSettings?->logoUrl();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $storeName }} — {{ $locale === 'ar' ? 'حساب العميل' : ($locale === 'he' ? 'חשבון לקוח' : 'Customer account') }}</title>
    <meta name="description" content="{{ $storeTagline }}">

    <link rel="stylesheet" href="{{ asset('css/storefront/storefront.css') }}?v={{ file_exists(public_path('css/storefront/storefront.css')) ? filemtime(public_path('css/storefront/storefront.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/storefront/design-overrides.css') }}?v={{ file_exists(public_path('css/storefront/design-overrides.css')) ? filemtime(public_path('css/storefront/design-overrides.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/storefront/auth.css') }}?v={{ file_exists(public_path('css/storefront/auth.css')) ? filemtime(public_path('css/storefront/auth.css')) : time() }}">
</head>
<body class="scp-storefront scp-auth-page {{ $direction === 'rtl' ? 'is-rtl' : 'is-ltr' }}">
    <div class="scp-auth-shell">
        <div class="scp-auth-brand-panel">
            <a href="{{ route('storefront.home', ['lang' => $locale]) }}" class="scp-auth-logo">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="scp-auth-logo-image">
                @else
                    <span class="scp-logo-mark">{{ mb_substr($storeName, 0, 1) }}</span>
                @endif
                <span>
                    <strong>{{ $storeName }}</strong>
                    <small>{{ $storeTagline }}</small>
                </span>
            </a>

            <div class="scp-auth-marketing">
                <span class="scp-auth-badge">
                    {{ $locale === 'ar' ? 'منصة تجارة ذكية' : ($locale === 'he' ? 'פלטפורמת מסחר חכמה' : 'Smart commerce platform') }}
                </span>

                <h1>
                    {{ $locale === 'ar' ? 'ادخل إلى حسابك وتابع طلباتك بسهولة.' : ($locale === 'he' ? 'התחבר לחשבון ועקוב אחרי ההזמנות שלך.' : 'Access your account and manage your orders easily.') }}
                </h1>

                <p>
                    {{ $locale === 'ar' ? 'تجربة متجر حديثة تدعم العربية والعبرية والإنجليزية، مع تتبع الطلبات والفواتير والمفضلة.' : ($locale === 'he' ? 'חוויית חנות מודרנית עם מעקב הזמנות, חשבוניות ורשימת מועדפים.' : 'A modern storefront experience with order tracking, invoices, and wishlist support.') }}
                </p>
            </div>

            <div class="scp-auth-language-switcher">
                @forelse($activeLanguages as $language)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $language->code]) }}" class="{{ $locale === $language->code ? 'active' : '' }}">{{ strtoupper($language->code) }}</a>
                @empty
                    @foreach(\App\Support\Localization\ActiveLanguageRegistry::SUPPORTED_CODES as $languageCode)
                        <a href="{{ request()->fullUrlWithQuery(['lang' => $languageCode]) }}" class="{{ $locale === $languageCode ? 'active' : '' }}">{{ strtoupper($languageCode) }}</a>
                    @endforeach
                @endforelse
            </div>
        </div>

        <div class="scp-auth-card-wrap">
            <div class="scp-auth-card">
                {{ $slot }}
            </div>

            <div class="scp-auth-back">
                <a href="{{ route('storefront.home', ['lang' => $locale]) }}">
                    {{ $locale === 'ar' ? 'العودة إلى المتجر' : ($locale === 'he' ? 'חזרה לחנות' : 'Back to storefront') }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>
