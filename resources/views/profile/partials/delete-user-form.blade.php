@php $locale = $locale ?? request('lang', session('storefront_locale', 'ar')); @endphp

<section>
    <div class="scp-profile-card-head">
        <div>
            <h2>{{ $locale === 'ar' ? 'حذف الحساب' : ($locale === 'he' ? 'מחיקת חשבון' : 'Delete account') }}</h2>
            <p>{{ $locale === 'ar' ? 'هذا الإجراء نهائي. سيتم حذف الحساب بعد إدخال كلمة المرور.' : ($locale === 'he' ? 'פעולה זו סופית. החשבון יימחק לאחר הזנת הסיסמה.' : 'This action is permanent. Enter your password to delete the account.') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('profile.destroy') }}" class="scp-profile-form" onsubmit="return confirm('{{ $locale === 'ar' ? 'هل أنت متأكد من حذف الحساب؟' : ($locale === 'he' ? 'האם למחוק את החשבון?' : 'Are you sure you want to delete your account?') }}')">
        @csrf
        @method('delete')

        <label class="scp-profile-field">
            <span>{{ $locale === 'ar' ? 'كلمة المرور' : ($locale === 'he' ? 'סיסמה' : 'Password') }}</span>
            <input name="password" type="password" placeholder="••••••••">
            <x-input-error :messages="$errors->userDeletion->get('password')" class="scp-profile-error" />
        </label>

        <button type="submit" class="scp-profile-delete">
            {{ $locale === 'ar' ? 'حذف الحساب' : ($locale === 'he' ? 'מחיקת חשבון' : 'Delete account') }}
        </button>
    </form>
</section>
