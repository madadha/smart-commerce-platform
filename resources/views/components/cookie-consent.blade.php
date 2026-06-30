@props([
    'locale' => app()->getLocale(),
    'settings' => null,
])

@php
    $language = in_array($locale, ['ar', 'he', 'en'], true) ? $locale : 'ar';
    $direction = in_array($language, ['ar', 'he'], true) ? 'rtl' : 'ltr';

    $copy = [
        'ar' => [
            'text' => 'نستخدم في هذا الموقع ملفات تعريف الارتباط (Cookies) وأدوات مشابهة لتحسين تجربة التصفح، وتحليل استخدام الموقع، وتخصيص المحتوى. لمزيد من المعلومات يمكنك الاطلاع على سياسة الخصوصية الخاصة بنا.',
            'accept' => 'موافق',
            'privacy' => 'سياسة الخصوصية',
        ],
        'he' => [
            'text' => 'באתר זה נעשה שימוש ב"קבצי עוגיות" (cookies) וכלים דומים אחרים על מנת לספק לכם חווית גלישה טובה יותר, וכן להתאים אישית את ביצוע ניתוחים סטטיסטיים. למידע נוסף ניתן לעיין במדיניות הפרטיות שלנו.',
            'accept' => 'קראתי',
            'privacy' => 'מדיניות הפרטיות שלנו',
        ],
        'en' => [
            'text' => 'This website uses cookies and similar technologies to improve your browsing experience, analyze website usage, and personalize content. For more information, please read our Privacy Policy.',
            'accept' => 'Got it',
            'privacy' => 'Privacy Policy',
        ],
    ][$language];

    $settings = $settings ?? \App\Models\StorefrontSetting::current();

    if ($settings && $settings->show_cookie_consent === false) {
        return;
    }

    $text = $settings?->localized('cookie_consent_text', $language, $copy['text']) ?: $copy['text'];
    $acceptText = $settings?->localized('cookie_consent_button_text', $language, $copy['accept']) ?: $copy['accept'];
    $privacyText = $settings?->localized('cookie_consent_privacy_text', $language, $copy['privacy']) ?: $copy['privacy'];
    $privacyUrl = filled($settings?->cookie_consent_privacy_url) ? $settings->cookie_consent_privacy_url : '#';
    $storageKey = filled($settings?->cookie_consent_storage_key) ? $settings->cookie_consent_storage_key : 'smart_commerce_cookie_consent';
    $backgroundColor = filled($settings?->cookie_consent_background_color) ? $settings->cookie_consent_background_color : null;
    $buttonColor = filled($settings?->cookie_consent_button_color) ? $settings->cookie_consent_button_color : null;

    $customStyle = trim(implode(' ', array_filter([
        $backgroundColor ? '--scp-cookie-bg: '.$backgroundColor.';' : null,
        $buttonColor ? '--scp-cookie-button-bg: '.$buttonColor.';' : null,
    ])));
@endphp

<div
    class="scp-cookie-consent"
    dir="{{ $direction }}"
    data-scp-cookie-consent
    data-storage-key="{{ $storageKey }}"
    @if($customStyle !== '') style="{{ $customStyle }}" @endif
    hidden
>
    <div class="scp-cookie-consent-inner">
        <div class="scp-cookie-consent-icon" aria-hidden="true">🍪</div>

        <p>
            {{ $text }}
            <a href="{{ $privacyUrl }}" class="scp-cookie-consent-link">{{ $privacyText }}</a>
        </p>

        <button type="button" class="scp-cookie-consent-button" data-scp-cookie-accept>
            {{ $acceptText }}
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var banner = document.querySelector('[data-scp-cookie-consent]');

        if (!banner) {
            return;
        }

        var storageKey = banner.dataset.storageKey || 'smart_commerce_cookie_consent';

        try {
            if (window.localStorage.getItem(storageKey) === 'accepted') {
                return;
            }
        } catch (error) {
            // If storage is blocked, show the banner for the current page only.
        }

        banner.hidden = false;
        window.requestAnimationFrame(function () {
            banner.classList.add('is-visible');
        });

        var acceptButton = banner.querySelector('[data-scp-cookie-accept]');

        if (acceptButton) {
            acceptButton.addEventListener('click', function () {
                try {
                    window.localStorage.setItem(storageKey, 'accepted');
                } catch (error) {
                    // Ignore blocked storage and still close the banner.
                }

                banner.classList.remove('is-visible');
                banner.classList.add('is-hiding');

                window.setTimeout(function () {
                    banner.hidden = true;
                }, 220);
            });
        }
    });
</script>
