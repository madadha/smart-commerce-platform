<x-guest-layout>
    @php $locale = app(\App\Support\Localization\ActiveLanguageRegistry::class)->resolve(request('lang', session('storefront_locale', 'ar'))); @endphp
    <div class="scp-auth-heading">
        <h2>{{ $locale === 'ar' ? 'تعيين كلمة مرور جديدة' : ($locale === 'he' ? 'הגדרת סיסמה חדשה' : 'Reset password') }}</h2>
        <p>{{ $locale === 'ar' ? 'اختر كلمة مرور جديدة وآمنة لحسابك.' : ($locale === 'he' ? 'בחר סיסמה חדשה ומאובטחת.' : 'Choose a new secure password.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="scp-auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <input type="hidden" name="lang" value="{{ $locale }}">

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'البريد الإلكتروني' : ($locale === 'he' ? 'אימייל' : 'Email') }}</span>
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="scp-auth-error" />
        </label>

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'كلمة المرور الجديدة' : ($locale === 'he' ? 'סיסמה חדשה' : 'New password') }}</span>
            <input type="password" name="password" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="scp-auth-error" />
        </label>

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'تأكيد كلمة المرور' : ($locale === 'he' ? 'אישור סיסמה' : 'Confirm password') }}</span>
            <input type="password" name="password_confirmation" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="scp-auth-error" />
        </label>

        <button type="submit" class="scp-auth-submit">{{ $locale === 'ar' ? 'حفظ كلمة المرور' : ($locale === 'he' ? 'שמירה' : 'Reset password') }}</button>
    </form>
</x-guest-layout>
