@php
    $locale = request('lang', session('storefront_locale', 'ar'));
    $locale = in_array($locale, ['ar', 'he', 'en'], true) ? $locale : 'ar';

    $labels = [
        'ar' => [
            'section' => 'الحساب',
            'account' => 'حسابي',
            'profile' => 'بياناتي',
            'logout' => 'تسجيل الخروج',
        ],
        'he' => [
            'section' => 'החשבון',
            'account' => 'החשבון שלי',
            'profile' => 'הפרופיל שלי',
            'logout' => 'התנתקות',
        ],
        'en' => [
            'section' => 'Account',
            'account' => 'My Account',
            'profile' => 'Profile',
            'logout' => 'Logout',
        ],
    ][$locale];
@endphp

<nav class="scp-mini-account-nav">
    <div class="scp-container">
        <div class="scp-mini-account-panel">
            <span class="scp-mini-account-title">{{ $labels['section'] }}</span>

            <div class="scp-mini-account-links">
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
    </div>
</nav>
