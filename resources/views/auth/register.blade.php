<x-guest-layout>
    @php
        $locale = app(\App\Support\Localization\ActiveLanguageRegistry::class)->resolve(request('lang', session('storefront_locale', 'ar')));

        $customerMode = config('customer-types.mode', 'regular');

        $allowResellerRequest = (bool) config('customer-types.allow_reseller_requests', false)
            || in_array($customerMode, ['reseller', 'b2b'], true);

        $allowCompanyRequest = (bool) config('customer-types.allow_company_requests', false)
            || $customerMode === 'b2b';

        $allowVipRequest = (bool) config('customer-types.allow_vip_requests', false)
            || $customerMode === 'vip';

        $hasTypeRequest = $allowResellerRequest || $allowCompanyRequest || $allowVipRequest;
    @endphp

    <div class="scp-auth-heading">
        <h2>{{ $locale === 'ar' ? 'إنشاء حساب' : ($locale === 'he' ? 'יצירת חשבון' : 'Create account') }}</h2>
        <p>{{ $locale === 'ar' ? 'سجّل حسابك واحفظ بياناتك لتسهيل الطلبات القادمة.' : ($locale === 'he' ? 'צור חשבון ושמור את הפרטים להזמנות הבאות.' : 'Create an account and save your details for future orders.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="scp-auth-form">
        @csrf
        <input type="hidden" name="lang" value="{{ $locale }}">

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'الاسم الكامل' : ($locale === 'he' ? 'שם מלא' : 'Full name') }}</span>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" class="scp-auth-error" />
        </label>

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'البريد الإلكتروني' : ($locale === 'he' ? 'אימייל' : 'Email') }}</span>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="example@email.com">
            <x-input-error :messages="$errors->get('email')" class="scp-auth-error" />
        </label>

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'رقم الهاتف' : ($locale === 'he' ? 'טלפון' : 'Phone') }}</span>
            <input type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="05x-xxxxxxx">
            <x-input-error :messages="$errors->get('phone')" class="scp-auth-error" />
        </label>

        @if($hasTypeRequest)
            <div class="scp-auth-request-box">
                <strong>
                    {{ $locale === 'ar' ? 'طلب نوع حساب خاص' : ($locale === 'he' ? 'בקשת סוג חשבון מיוחד' : 'Special account request') }}
                </strong>

                <p>
                    {{ $locale === 'ar'
                        ? 'الحساب يتم إنشاؤه كزبون عادي. يمكن للإدارة مراجعة الطلب وتفعيل حساب وكيل أو شركة أو VIP لاحقًا.'
                        : ($locale === 'he'
                            ? 'החשבון נוצר כלקוח רגיל. ההנהלה יכולה לאשר חשבון משווק, חברה או VIP בהמשך.'
                            : 'The account is created as Regular. Admin can approve Reseller, Company, or VIP later.') }}
                </p>

                <label class="scp-auth-field">
                    <span>{{ $locale === 'ar' ? 'نوع الحساب المطلوب' : ($locale === 'he' ? 'סוג חשבון מבוקש' : 'Requested account type') }}</span>

                    <select name="requested_customer_type">
                        <option value="">{{ $locale === 'ar' ? 'حساب عادي' : ($locale === 'he' ? 'חשבון רגיל' : 'Regular account') }}</option>

                        @if($allowResellerRequest)
                            <option value="reseller" @selected(old('requested_customer_type') === 'reseller')>
                                {{ $locale === 'ar' ? 'طلب حساب وكيل / موزع' : ($locale === 'he' ? 'בקשת חשבון משווק' : 'Request Reseller account') }}
                            </option>
                        @endif

                        @if($allowCompanyRequest)
                            <option value="company" @selected(old('requested_customer_type') === 'company')>
                                {{ $locale === 'ar' ? 'طلب حساب شركة' : ($locale === 'he' ? 'בקשת חשבון חברה' : 'Request Company account') }}
                            </option>
                        @endif

                        @if($allowVipRequest)
                            <option value="vip" @selected(old('requested_customer_type') === 'vip')>
                                {{ $locale === 'ar' ? 'طلب حساب VIP' : ($locale === 'he' ? 'בקשת חשבון VIP' : 'Request VIP account') }}
                            </option>
                        @endif
                    </select>

                    <x-input-error :messages="$errors->get('requested_customer_type')" class="scp-auth-error" />
                </label>

                <label class="scp-auth-field">
                    <span>{{ $locale === 'ar' ? 'اسم الشركة / النشاط' : ($locale === 'he' ? 'שם חברה / עסק' : 'Company / Business name') }}</span>
                    <input type="text" name="company_name" value="{{ old('company_name') }}">
                    <x-input-error :messages="$errors->get('company_name')" class="scp-auth-error" />
                </label>
            </div>
        @endif

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'كلمة المرور' : ($locale === 'he' ? 'סיסמה' : 'Password') }}</span>
            <input type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="scp-auth-error" />
        </label>

        <label class="scp-auth-field">
            <span>{{ $locale === 'ar' ? 'تأكيد كلمة المرور' : ($locale === 'he' ? 'אישור סיסמה' : 'Confirm password') }}</span>
            <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password_confirmation')" class="scp-auth-error" />
        </label>

        <button type="submit" class="scp-auth-submit">
            {{ $locale === 'ar' ? 'تسجيل الحساب' : ($locale === 'he' ? 'הרשמה' : 'Register') }}
        </button>
    </form>

    <div class="scp-auth-switch">
        <span>{{ $locale === 'ar' ? 'لديك حساب بالفعل؟' : ($locale === 'he' ? 'כבר יש לך חשבון?' : 'Already registered?') }}</span>
        <a href="{{ route('login', ['lang' => $locale]) }}">
            {{ $locale === 'ar' ? 'تسجيل الدخول' : ($locale === 'he' ? 'כניסה' : 'Login') }}
        </a>
    </div>
</x-guest-layout>
