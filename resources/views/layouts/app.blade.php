@php
    $languageRegistry = app(\App\Support\Localization\ActiveLanguageRegistry::class);
    $locale = $languageRegistry->resolve(request('lang', session('storefront_locale', app()->getLocale() ?: 'ar')));
    $direction = $languageRegistry->direction($locale);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Smart Commerce Platform') }}</title>

    <link rel="stylesheet" href="{{ asset('css/storefront/storefront.css') }}?v={{ file_exists(public_path('css/storefront/storefront.css')) ? filemtime(public_path('css/storefront/storefront.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/storefront/design-overrides.css') }}?v={{ file_exists(public_path('css/storefront/design-overrides.css')) ? filemtime(public_path('css/storefront/design-overrides.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/storefront/customer-profile.css') }}?v={{ file_exists(public_path('css/storefront/customer-profile.css')) ? filemtime(public_path('css/storefront/customer-profile.css')) : time() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="scp-storefront {{ $direction === 'rtl' ? 'is-rtl' : 'is-ltr' }}">
    <main>
        {{ $slot }}
    </main>
</body>
</html>
