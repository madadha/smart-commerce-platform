<x-guest-layout>
    @php $locale = request('lang', session('storefront_locale', 'ar')); $locale = in_array($locale, ['ar','he','en'], true) ? $locale : 'ar'; @endphp
    <div class="scp-auth-heading">
        <h2>{{ $locale === 'ar' ? 'تأكيد البريد الإلكتروني' : ($locale === 'he' ? 'אימות אימייל' : 'Verify email') }}</h2>
        <p>{{ $locale === 'ar' ? 'يرجى تأكيد بريدك الإلكتروني من الرابط الذي تم إرساله إليك.' : ($locale === 'he' ? 'אנא אמת את האימייל באמצעות הקישור שנשלח אליך.' : 'Please verify your email using the link sent to you.') }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="scp-auth-alert success">
            {{ $locale === 'ar' ? 'تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني.' : ($locale === 'he' ? 'קישור אימות חדש נשלח לאימייל שלך.' : 'A new verification link has been sent.') }}
        </div>
    @endif

    <div class="scp-auth-actions-inline">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="scp-auth-submit">{{ $locale === 'ar' ? 'إعادة إرسال الرابط' : ($locale === 'he' ? 'שליחה מחדש' : 'Resend link') }}</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="scp-auth-link-button">{{ $locale === 'ar' ? 'تسجيل خروج' : ($locale === 'he' ? 'יציאה' : 'Logout') }}</button>
        </form>
    </div>
</x-guest-layout>
