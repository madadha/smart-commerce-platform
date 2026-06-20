@php
    $locale = $locale ?? request('lang', session('storefront_locale', 'ar'));
    $customer = $customer ?? null;
@endphp

<section>
    <div class="scp-profile-card-head">
        <div>
            <h2>{{ $locale === 'ar' ? 'معلومات الحساب والزبون' : ($locale === 'he' ? 'פרטי חשבון ולקוח' : 'Account & customer information') }}</h2>
            <p>{{ $locale === 'ar' ? 'هذه المعلومات ستظهر تلقائيًا في صفحة إتمام الطلب.' : ($locale === 'he' ? 'הפרטים יופיעו אוטומטית בעמוד התשלום.' : 'These details will be used automatically during checkout.') }}</p>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update', ['lang' => $locale]) }}" class="scp-profile-form">
        @csrf
        @method('patch')

        <div class="scp-profile-form-grid">
            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'الاسم الكامل' : ($locale === 'he' ? 'שם מלא' : 'Full name') }}</span>
                <input name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">
                <x-input-error :messages="$errors->get('name')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'البريد الإلكتروني' : ($locale === 'he' ? 'אימייל' : 'Email') }}</span>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                <x-input-error :messages="$errors->get('email')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'رقم الهاتف' : ($locale === 'he' ? 'טלפון' : 'Phone') }}</span>
                <input name="phone" type="text" value="{{ old('phone', $customer?->phone) }}" autocomplete="tel" placeholder="05x-xxxxxxx">
                <x-input-error :messages="$errors->get('phone')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>WhatsApp</span>
                <input name="whatsapp" type="text" value="{{ old('whatsapp', $customer?->whatsapp) }}" autocomplete="tel">
                <x-input-error :messages="$errors->get('whatsapp')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'المدينة' : ($locale === 'he' ? 'עיר' : 'City') }}</span>
                <input name="city" type="text" value="{{ old('city', $customer?->city) }}">
                <x-input-error :messages="$errors->get('city')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'المنطقة' : ($locale === 'he' ? 'אזור' : 'Area') }}</span>
                <input name="area" type="text" value="{{ old('area', $customer?->area) }}">
                <x-input-error :messages="$errors->get('area')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'الشارع' : ($locale === 'he' ? 'רחוב' : 'Street') }}</span>
                <input name="street" type="text" value="{{ old('street', $customer?->street) }}">
                <x-input-error :messages="$errors->get('street')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'البناية' : ($locale === 'he' ? 'בניין' : 'Building') }}</span>
                <input name="building" type="text" value="{{ old('building', $customer?->building) }}">
                <x-input-error :messages="$errors->get('building')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'الشقة' : ($locale === 'he' ? 'דירה' : 'Apartment') }}</span>
                <input name="apartment" type="text" value="{{ old('apartment', $customer?->apartment) }}">
                <x-input-error :messages="$errors->get('apartment')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field">
                <span>{{ $locale === 'ar' ? 'الرمز البريدي' : ($locale === 'he' ? 'מיקוד' : 'Postal code') }}</span>
                <input name="postal_code" type="text" value="{{ old('postal_code', $customer?->postal_code) }}">
                <x-input-error :messages="$errors->get('postal_code')" class="scp-profile-error" />
            </label>

            <label class="scp-profile-field full">
                <span>{{ $locale === 'ar' ? 'ملاحظات العنوان' : ($locale === 'he' ? 'הערות כתובת' : 'Address notes') }}</span>
                <textarea name="address_notes" rows="4">{{ old('address_notes', $customer?->address_notes) }}</textarea>
                <x-input-error :messages="$errors->get('address_notes')" class="scp-profile-error" />
            </label>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="scp-profile-warning">
                {{ $locale === 'ar' ? 'بريدك الإلكتروني غير مؤكد.' : ($locale === 'he' ? 'האימייל שלך לא מאומת.' : 'Your email address is unverified.') }}
                <button form="send-verification">
                    {{ $locale === 'ar' ? 'إعادة إرسال رابط التحقق' : ($locale === 'he' ? 'שליחת קישור אימות' : 'Resend verification link') }}
                </button>
            </div>
        @endif

        <div class="scp-profile-submit-row">
            <button type="submit" class="scp-profile-submit">
                {{ $locale === 'ar' ? 'حفظ البيانات' : ($locale === 'he' ? 'שמירת פרטים' : 'Save details') }}
            </button>
        </div>
    </form>
</section>
