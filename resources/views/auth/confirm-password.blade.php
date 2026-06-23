<x-guest-layout>
    @php $locale = app(\App\Support\Localization\ActiveLanguageRegistry::class)->resolve(request('lang', session('storefront_locale', 'ar'))); @endphp
    <div class="scp-auth-heading">
        <h2>{{ $locale === 'ar' ? 'تأكيد كلمة المرور' : ($locale === 'he' ? 'אישור סיסמה' : 'Confirm password') }}</h2>
        <p>{{ $locale === 'ar' ? 'هذه منطقة آمنة، يرجى تأكيد كلمة المرور للمتابعة.' : ($locale === 'he' ? 'זהו אזור מאובטח, אשר סיסמה להמשך.' : 'This is a secure area. Please confirm your password.') }}</p>
    </div>
    <form method="POST" action="{{ route('password.confirm') }}" class="scp-auth-form">
        @csrf
        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'كلمة المرور' : ($locale === 'he' ? 'סיסמה' : 'Password') }}</span>
            <input type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="scp-auth-error" />
        </label>
        <button type="submit" class="scp-auth-submit">{{ $locale === 'ar' ? 'تأكيد' : ($locale === 'he' ? 'אישור' : 'Confirm') }}</button>
    </form>
</x-guest-layout>
