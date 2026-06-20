@php
    $locale = request('lang', session('storefront_locale', 'ar'));
    $locale = in_array($locale, ['ar', 'he', 'en'], true) ? $locale : 'ar';
@endphp

<nav class="scp-mini-account-nav">
    <div class="scp-container">
        <a href="{{ route('storefront.home', ['lang' => $locale]) }}" class="scp-logo">
            <span class="scp-logo-mark">S</span>
            <span>
                <strong>Smart Commerce</strong>
                <small>Marketplace Platform</small>
            </span>
        </a>

        <div>
            <a href="{{ route('storefront.account.dashboard', ['lang' => $locale]) }}">
                {{ __('storefront.account_dashboard.my_account') }}
            </a>
            <a href="{{ route('profile.edit', ['lang' => $locale]) }}">
                {{ $locale === 'ar' ? 'بياناتي' : ($locale === 'he' ? 'הפרופיל שלי' : 'Profile') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    {{ $locale === 'ar' ? 'تسجيل خروج' : ($locale === 'he' ? 'יציאה' : 'Logout') }}
                </button>
            </form>
        </div>
    </div>
</nav>
