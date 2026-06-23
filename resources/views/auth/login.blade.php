<x-guest-layout>
    @php
        $locale = request('lang', session('storefront_locale', 'ar'));
        $locale = in_array($locale, ['ar', 'he', 'en'], true) ? $locale : 'ar';
    @endphp

    <div class="scp-auth-heading">
        <h2>{{ $locale === 'ar' ? 'تسجيل الدخول' : ($locale === 'he' ? 'כניסה לחשבון' : 'Login') }}</h2>
        <p>{{ $locale === 'ar' ? 'ادخل إلى حسابك لمتابعة الطلبات والمفضلة والفواتير.' : ($locale === 'he' ? 'התחבר כדי לעקוב אחרי הזמנות, מועדפים וחשבוניות.' : 'Access your orders, wishlist, and invoices.') }}</p>
    </div>

    <x-auth-session-status class="scp-auth-alert success" :status="session('status')" />

    <form method="POST" action="{{ route('login', ['lang' => $locale]) }}" class="scp-auth-form">
        @csrf
        <input type="hidden" name="lang" value="{{ $locale }}">

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'البريد الإلكتروني' : ($locale === 'he' ? 'אימייל' : 'Email') }}</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="example@email.com">
            <x-input-error :messages="$errors->get('email')" class="scp-auth-error" />
        </label>

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'كلمة المرور' : ($locale === 'he' ? 'סיסמה' : 'Password') }}</span>
            <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="scp-auth-error" />
        </label>

        <div class="scp-auth-row">
            <label class="scp-auth-check">
                <input type="checkbox" name="remember">
                <span>{{ $locale === 'ar' ? 'تذكرني' : ($locale === 'he' ? 'זכור אותי' : 'Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request', ['lang' => $locale]) }}">
                    {{ $locale === 'ar' ? 'نسيت كلمة المرور؟' : ($locale === 'he' ? 'שכחת סיסמה?' : 'Forgot password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="scp-auth-submit">
            {{ $locale === 'ar' ? 'دخول' : ($locale === 'he' ? 'כניסה' : 'Login') }}
        </button>
    </form>

    <div class="scp-auth-switch">
        <span>{{ $locale === 'ar' ? 'ليس لديك حساب؟' : ($locale === 'he' ? 'אין לך חשבון?' : 'No account yet?') }}</span>
        <a href="{{ route('register', ['lang' => $locale]) }}">
            {{ $locale === 'ar' ? 'إنشاء حساب جديد' : ($locale === 'he' ? 'הרשמה' : 'Create account') }}
        </a>
    </div>
</x-guest-layout>
