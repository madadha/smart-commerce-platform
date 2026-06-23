@php
    $locale = request('lang', session('storefront_locale', 'ar'));
    $locale = in_array($locale, ['ar', 'he', 'en'], true) ? $locale : 'ar';

    $labels = [
        'ar' => [
            'account' => 'حسابي',
            'profile' => 'بياناتي',
            'logout' => 'تسجيل الخروج',
        ],
        'he' => [
            'account' => 'החשבון שלי',
            'profile' => 'הפרופיל שלי',
            'logout' => 'התנתקות',
        ],
        'en' => [
            'account' => 'My Account',
            'profile' => 'Profile',
            'logout' => 'Logout',
        ],
    ][$locale];
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
                {{ $labels['account'] }}
            </a>
            <a href="{{ route('profile.edit', ['lang' => $locale]) }}">
                {{ $labels['profile'] }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    {{ $labels['logout'] }}
                </button>
            </form>
        </div>
    </div>
</nav>
