@extends('storefront.layout')

@section('content')
@php
    $locale = $locale ?? request('lang', session('storefront_locale', app()->getLocale() ?: 'ar'));
    $locale = in_array($locale, ['ar', 'he', 'en'], true) ? $locale : 'ar';
    $customer = $customer ?? auth()->user()?->customer ?? null;
    $user = $user ?? auth()->user();

    $labels = [
        'ar' => [
            'badge' => 'حساب العميل',
            'title' => 'بياناتي وحسابي',
            'desc' => 'حدّث بياناتك الشخصية وعنوانك حتى يتم تعبئة الطلبات تلقائياً في كل مرة.',
            'home' => 'الرئيسية', 'account' => 'حسابي', 'orders' => 'طلباتي', 'wishlist' => 'المفضلة', 'logout' => 'تسجيل الخروج',
            'profile_info' => 'البيانات الأساسية', 'profile_hint' => 'هذه البيانات تُستخدم لتسجيل الدخول والتواصل معك بخصوص الطلبات.',
            'address_info' => 'بيانات التوصيل المحفوظة', 'address_hint' => 'سيتم استخدامها تلقائياً أثناء إتمام الطلب.',
            'security' => 'الأمان وكلمة المرور', 'security_hint' => 'حدّث كلمة المرور لحماية حسابك.',
            'danger' => 'حذف الحساب', 'danger_hint' => 'حذف الحساب نهائي ولا يمكن التراجع عنه.',
            'name' => 'الاسم الكامل', 'email' => 'البريد الإلكتروني', 'phone' => 'رقم الهاتف', 'whatsapp' => 'واتساب',
            'city' => 'المدينة', 'area' => 'المنطقة', 'street' => 'الشارع / العنوان', 'building' => 'البناية', 'apartment' => 'الشقة', 'postal_code' => 'الرمز البريدي', 'address_notes' => 'ملاحظات العنوان',
            'save' => 'حفظ البيانات', 'saved' => 'تم حفظ البيانات بنجاح.',
            'current_password' => 'كلمة المرور الحالية', 'new_password' => 'كلمة المرور الجديدة', 'confirm_password' => 'تأكيد كلمة المرور', 'update_password' => 'تحديث كلمة المرور',
            'delete_account' => 'حذف الحساب', 'summary' => 'ملخص بياناتك', 'not_set' => 'غير محدد',
        ],
        'he' => [
            'badge' => 'חשבון לקוח',
            'title' => 'החשבון שלי והפרטים שלי',
            'desc' => 'עדכן את הפרטים האישיים וכתובת המשלוח כדי שההזמנות יתמלאו אוטומטית.',
            'home' => 'ראשי', 'account' => 'החשבון שלי', 'orders' => 'ההזמנות שלי', 'wishlist' => 'מועדפים', 'logout' => 'התנתקות',
            'profile_info' => 'פרטים בסיסיים', 'profile_hint' => 'הפרטים משמשים להתחברות וליצירת קשר לגבי הזמנות.',
            'address_info' => 'פרטי משלוח שמורים', 'address_hint' => 'ישמשו אוטומטית בזמן התשלום.',
            'security' => 'אבטחה וסיסמה', 'security_hint' => 'עדכן סיסמה כדי להגן על החשבון.',
            'danger' => 'מחיקת חשבון', 'danger_hint' => 'מחיקת החשבון היא פעולה סופית.',
            'name' => 'שם מלא', 'email' => 'אימייל', 'phone' => 'טלפון', 'whatsapp' => 'וואטסאפ',
            'city' => 'עיר', 'area' => 'אזור', 'street' => 'רחוב / כתובת', 'building' => 'בניין', 'apartment' => 'דירה', 'postal_code' => 'מיקוד', 'address_notes' => 'הערות כתובת',
            'save' => 'שמירת נתונים', 'saved' => 'הנתונים נשמרו בהצלחה.',
            'current_password' => 'סיסמה נוכחית', 'new_password' => 'סיסמה חדשה', 'confirm_password' => 'אימות סיסמה', 'update_password' => 'עדכון סיסמה',
            'delete_account' => 'מחיקת חשבון', 'summary' => 'סיכום הפרטים שלך', 'not_set' => 'לא הוגדר',
        ],
        'en' => [
            'badge' => 'Customer Account',
            'title' => 'My Profile & Account',
            'desc' => 'Keep your profile and delivery details updated so checkout can be filled automatically.',
            'home' => 'Home', 'account' => 'My Account', 'orders' => 'My Orders', 'wishlist' => 'Wishlist', 'logout' => 'Logout',
            'profile_info' => 'Basic Information', 'profile_hint' => 'These details are used for login and order communication.',
            'address_info' => 'Saved Delivery Details', 'address_hint' => 'Used automatically during checkout.',
            'security' => 'Security & Password', 'security_hint' => 'Update your password to keep the account safe.',
            'danger' => 'Delete Account', 'danger_hint' => 'Deleting your account is permanent.',
            'name' => 'Full Name', 'email' => 'Email', 'phone' => 'Phone', 'whatsapp' => 'WhatsApp',
            'city' => 'City', 'area' => 'Area', 'street' => 'Street / Address', 'building' => 'Building', 'apartment' => 'Apartment', 'postal_code' => 'Postal Code', 'address_notes' => 'Address Notes',
            'save' => 'Save Details', 'saved' => 'Details saved successfully.',
            'current_password' => 'Current Password', 'new_password' => 'New Password', 'confirm_password' => 'Confirm Password', 'update_password' => 'Update Password',
            'delete_account' => 'Delete Account', 'summary' => 'Your Details Summary', 'not_set' => 'Not set',
        ],
    ][$locale];

    $displayName = $user?->name ?? $customer?->getDisplayName() ?? $labels['not_set'];
    $initial = mb_substr($displayName, 0, 1);
@endphp

<section class="scp-profile-page">
    <div class="scp-container">
        @include('layouts.navigation')

        <div class="scp-profile-hero">
            <div>
                <span class="scp-profile-badge">{{ $labels['badge'] }}</span>
                <h1>{{ $labels['title'] }}</h1>
                <p>{{ $labels['desc'] }}</p>
            </div>

            <aside class="scp-profile-user-card">
                <div class="scp-profile-avatar">{{ $initial }}</div>
                <div>
                    <strong>{{ $displayName }}</strong>
                    <span>{{ $user?->email }}</span>
                    <small>{{ $customer?->phone ?: $labels['not_set'] }}</small>
                </div>
            </aside>
        </div>

        @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
            <div class="scp-profile-success">{{ $labels['saved'] }}</div>
        @endif

        <div class="scp-profile-grid">
            <main class="scp-profile-main">
                <div class="scp-profile-card">
                    <div class="scp-profile-card-head">
                        <div>
                            <h2>{{ $labels['profile_info'] }}</h2>
                            <p>{{ $labels['profile_hint'] }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.update', ['lang' => $locale]) }}" class="scp-profile-form">
                        @csrf
                        @method('patch')

                        <div class="scp-profile-form-grid">
                            <label class="scp-profile-field">
                                <span>{{ $labels['name'] }}</span>
                                <input type="text" name="name" value="{{ old('name', $user?->name) }}" required>
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['email'] }}</span>
                                <input type="email" name="email" value="{{ old('email', $user?->email) }}" required>
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['phone'] }}</span>
                                <input type="text" name="phone" value="{{ old('phone', $customer?->phone) }}" placeholder="05x-xxxxxxx">
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['whatsapp'] }}</span>
                                <input type="text" name="whatsapp" value="{{ old('whatsapp', $customer?->whatsapp) }}" placeholder="05x-xxxxxxx">
                            </label>
                        </div>

                        <div class="scp-profile-card-head scp-profile-card-head-tight">
                            <div>
                                <h3>{{ $labels['address_info'] }}</h3>
                                <p>{{ $labels['address_hint'] }}</p>
                            </div>
                        </div>

                        <div class="scp-profile-form-grid">
                            <label class="scp-profile-field">
                                <span>{{ $labels['city'] }}</span>
                                <input type="text" name="city" value="{{ old('city', $customer?->city) }}">
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['area'] }}</span>
                                <input type="text" name="area" value="{{ old('area', $customer?->area) }}">
                            </label>

                            <label class="scp-profile-field full">
                                <span>{{ $labels['street'] }}</span>
                                <input type="text" name="street" value="{{ old('street', $customer?->street) }}">
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['building'] }}</span>
                                <input type="text" name="building" value="{{ old('building', $customer?->building) }}">
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['apartment'] }}</span>
                                <input type="text" name="apartment" value="{{ old('apartment', $customer?->apartment) }}">
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['postal_code'] }}</span>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $customer?->postal_code) }}">
                            </label>

                            <label class="scp-profile-field full">
                                <span>{{ $labels['address_notes'] }}</span>
                                <textarea name="address_notes" rows="4">{{ old('address_notes', $customer?->address_notes) }}</textarea>
                            </label>
                        </div>

                        <div class="scp-profile-submit-row">
                            <button type="submit" class="scp-profile-submit">{{ $labels['save'] }}</button>
                        </div>
                    </form>
                </div>

                <div class="scp-profile-card">
                    <div class="scp-profile-card-head">
                        <div>
                            <h2>{{ $labels['security'] }}</h2>
                            <p>{{ $labels['security_hint'] }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="scp-profile-form">
                        @csrf
                        @method('put')
                        <input type="hidden" name="lang" value="{{ $locale }}">

                        <div class="scp-profile-form-grid">
                            <label class="scp-profile-field full">
                                <span>{{ $labels['current_password'] }}</span>
                                <input type="password" name="current_password" autocomplete="current-password">
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['new_password'] }}</span>
                                <input type="password" name="password" autocomplete="new-password">
                            </label>

                            <label class="scp-profile-field">
                                <span>{{ $labels['confirm_password'] }}</span>
                                <input type="password" name="password_confirmation" autocomplete="new-password">
                            </label>
                        </div>

                        <div class="scp-profile-submit-row">
                            <button type="submit" class="scp-profile-submit secondary">{{ $labels['update_password'] }}</button>
                        </div>
                    </form>
                </div>
            </main>

            <aside class="scp-profile-sidebar">
                <div class="scp-profile-card">
                    <h3>{{ $labels['summary'] }}</h3>
                    <div class="scp-profile-summary">
                        <div><span>{{ $labels['phone'] }}</span><strong>{{ $customer?->phone ?: $labels['not_set'] }}</strong></div>
                        <div><span>{{ $labels['city'] }}</span><strong>{{ $customer?->city ?: $labels['not_set'] }}</strong></div>
                        <div><span>{{ $labels['street'] }}</span><strong>{{ $customer?->street ?: $labels['not_set'] }}</strong></div>
                    </div>
                </div>

                <div class="scp-profile-card danger">
                    <h3>{{ $labels['danger'] }}</h3>
                    <p>{{ $labels['danger_hint'] }}</p>
                    <form method="POST" action="{{ route('profile.destroy') }}" class="scp-profile-form" onsubmit="return confirm('{{ $locale === 'ar' ? 'هل أنت متأكد من حذف الحساب؟' : 'Are you sure?' }}')">
                        @csrf
                        @method('delete')
                        <label class="scp-profile-field">
                            <span>{{ $labels['current_password'] }}</span>
                            <input type="password" name="password" autocomplete="current-password">
                        </label>
                        <button type="submit" class="scp-profile-delete">{{ $labels['delete_account'] }}</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
