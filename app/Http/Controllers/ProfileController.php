<?php

namespace App\Http\Controllers;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $locale = $this->resolveLocale($request);
        $user = $request->user();
        $customer = Customer::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $user->name,
                'email' => $user->email,
                'customer_type' => CustomerType::Regular->value,
                'status' => CustomerStatus::Active->value,
                'is_active' => true,
            ]
        );

        return view('profile.edit', [
            'user' => $user,
            'customer' => $customer,
            'locale' => $locale,
            'direction' => in_array($locale, ['ar', 'he'], true) ? 'rtl' : 'ltr',
            'pageTitle' => 'Profile - Smart Commerce Platform',
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $locale = $this->resolveLocale($request);
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $extra = $request->validate([
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:500'],
            'building' => ['nullable', 'string', 'max:100'],
            'apartment' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'address_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        [$firstName, $lastName] = $this->splitName((string) $user->name);

        Customer::query()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($extra, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'customer_type' => CustomerType::Regular->value,
                'status' => CustomerStatus::Active->value,
                'is_active' => true,
            ])
        );

        return Redirect::route('profile.edit', ['lang' => $locale])->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function resolveLocale(Request $request): string
    {
        $locale = $request->input('lang')
            ?? $request->query('lang')
            ?? session('storefront_locale')
            ?? app()->getLocale()
            ?? 'ar';

        if (! in_array($locale, ['ar', 'he', 'en'], true)) {
            $locale = 'ar';
        }

        session(['storefront_locale' => $locale]);

        return $locale;
    }

    private function splitName(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $name, 2);

        return [$parts[0] ?? $name, $parts[1] ?? null];
    }
}
