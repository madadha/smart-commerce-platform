<x-guest-layout>
    @php $locale = request('lang', session('storefront_locale', 'ar')); $locale = in_array($locale, ['ar','he','en'], true) ? $locale : 'ar'; @endphp
    <div class="scp-auth-heading">
        <h2>{{ $locale === 'ar' ? 'استعادة كلمة المرور' : ($locale === 'he' ? 'איפוס סיסמה' : 'Forgot password') }}</h2>
        <p>{{ $locale === 'ar' ? 'اكتب بريدك الإلكتروني وسنرسل لك رابط إعادة تعيين كلمة المرور.' : ($locale === 'he' ? 'הזן אימייל ונשלח קישור לאיפוס הסיסמה.' : 'Enter your email and we will send a password reset link.') }}</p>
    </div>

    <x-auth-session-status class="scp-auth-alert success" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="scp-auth-form">
        @csrf
        <input type="hidden" name="lang" value="{{ $locale }}">
        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'البريد الإلكتروني' : ($locale === 'he' ? 'אימייל' : 'Email') }}</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="example@email.com">
            <x-input-error :messages="$errors->get('email')" class="scp-auth-error" />
        </label>
        <button type="submit" class="scp-auth-submit">{{ $locale === 'ar' ? 'إرسال رابط الاستعادة' : ($locale === 'he' ? 'שלח קישור איפוס' : 'Send reset link') }}</button>
    </form>
</x-guest-layout>
