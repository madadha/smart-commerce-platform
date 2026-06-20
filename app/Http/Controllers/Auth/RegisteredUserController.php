<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $customerMode = (string) config('customer-types.mode', 'regular');

        $allowResellerRequest = (bool) config('customer-types.allow_reseller_requests', false)
            || in_array($customerMode, ['reseller', 'b2b'], true);

        $allowCompanyRequest = (bool) config('customer-types.allow_company_requests', false)
            || $customerMode === 'b2b';

        $allowVipRequest = (bool) config('customer-types.allow_vip_requests', false)
            || $customerMode === 'vip';

        $allowedRequestedTypes = [];

        if ($allowResellerRequest) {
            $allowedRequestedTypes[] = CustomerType::Reseller->value;
        }

        if ($allowCompanyRequest) {
            $allowedRequestedTypes[] = CustomerType::Company->value;
        }

        if ($allowVipRequest) {
            $allowedRequestedTypes[] = CustomerType::Vip->value;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'requested_customer_type' => ['nullable', 'string', 'in:'.implode(',', $allowedRequestedTypes ?: ['none'])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $nameParts = preg_split('/\s+/', trim($validated['name']), 2);
        $firstName = $nameParts[0] ?? $validated['name'];
        $lastName = $nameParts[1] ?? null;

        $requestedType = $validated['requested_customer_type'] ?? null;
        $requestedAt = $requestedType ? now() : null;

        Customer::create([
            'user_id' => $user->id,
            'customer_type' => CustomerType::Regular->value,
            'requested_customer_type' => $requestedType,
            'customer_type_requested_at' => $requestedAt,
            'status' => CustomerStatus::Active->value,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'internal_notes' => $requestedType
                ? 'Customer requested account type: ' . $requestedType . ' from registration form.'
                : null,
            'accepts_marketing' => false,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('storefront.account.dashboard', [
            'lang' => $request->input('lang', 'ar'),
        ], absolute: false));
    }
}
