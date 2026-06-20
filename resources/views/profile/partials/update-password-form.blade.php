@php $locale = $locale ?? request('lang', session('storefront_locale', 'ar')); @endphp

<section>
    <div class="scp-profile-card-head">
        <div>
            <h2>{{ $locale === 'ar' ? 'تحديث كلمة المرور' : ($locale === 'he' ? 'עדכון סיסמה' : 'Update password') }}</h2>
            <p>{{ $locale === 'ar' ? 'استخدم كلمة مرور قوية لحماية حسابك وطلباتك.' : ($locale === 'he' ? 'השתמש בסיסמה חזקה כדי להגן על החשבון.' : 'Use a strong password to protect your account.') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="scp-profile-form">
        @csrf
        @method('put')

        <div class="scp-profile-form-grid">
            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'كلمة المرور الحالية' : ($locale === 'he' ? 'סיסמה נוכחית' : 'Current password') }}</span>
                <input name="current_password" type="password" autocomplete="current-password">
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'كلمة المرور الجديدة' : ($locale === 'he' ? 'סיסמה חדשה' : 'New password') }}</span>
                <input name="password" type="password" autocomplete="new-password">
                <x-input-error :messages="$errors->updatePassword->get('password')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'تأكيد كلمة المرور' : ($locale === 'he' ? 'אישור סיסמה' : 'Confirm password') }}</span>
                <input name="password_confirmation" type="password" autocomplete="new-password">
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="scp-profile-error" />
            </label>
        </div>

        <div class="scp-profile-submit-row">
            <button type="submit" class="scp-profile-submit secondary">
                {{ $locale === 'ar' ? 'تحديث كلمة المرور' : ($locale === 'he' ? 'עדכון סיסמה' : 'Update password') }}
            </button>
        </div>
    </form>
</section>
