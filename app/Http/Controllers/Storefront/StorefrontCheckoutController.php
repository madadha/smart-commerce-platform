<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\CartStatus;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Http\Controllers\Controller;
use App\Mail\StorefrontOrderCreatedMail;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Payments\PaymentGatewayManager;
use App\Services\Checkout\CartCheckoutService;
use App\Services\Shipping\ShippingQuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class StorefrontCheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayManager $paymentGatewayManager,
    ) {}

    public function index(Request $request): View
    {
        $locale = $this->resolveLocale($request);

        $cart = $this->getCurrentCart();

        if ($cart) {
            $cart->load([
                'items.product.brand',
                'items.product.currency',
                'items.productVariant',
                'currency',
                'shippingMethod',
            ]);
        }

        $shippingMethods = ShippingMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $countries = Country::query()->active()->ordered()->get();

        return view('storefront.checkout.index', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'cart' => $cart,
            'shippingMethods' => $shippingMethods,
            'countries' => $countries,
            'pageTitle' => __('storefront.checkout.page_title').' - Smart Commerce Platform',
            'pageDescription' => __('storefront.checkout.page_description'),
            'checkoutDefaults' => $this->checkoutDefaults(),
            'paymentMethods' => $this->enabledPaymentMethods(),
        ]);
    }

    public function shippingQuotes(Request $request, ShippingQuoteService $shippingQuoteService): JsonResponse
    {
        $validated = $request->validate([
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'city' => ['required', 'string', 'max:255'],
        ]);
        $cart = $this->getCurrentCart();
        if (! $cart) {
            return response()->json(['quotes' => []]);
        }

        $quotes = $shippingQuoteService->quoteCart($cart, $validated['country_id'] ?? null, $validated['city'])
            ->map(fn (array $quote) => [
                'id' => $quote['method']->id,
                'name' => $quote['method']->getName(app()->getLocale()),
                'cost' => $quote['cost'],
                'min_delivery_days' => $quote['min_delivery_days'],
                'max_delivery_days' => $quote['max_delivery_days'],
            ])->all();

        return response()->json(['quotes' => $quotes]);
    }

    public function placeOrder(Request $request, CartCheckoutService $checkoutService): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        $cart = $this->getCurrentCart();

        if (! $cart || $cart->items()->count() === 0) {
            return redirect()
                ->route('storefront.cart.index', ['lang' => $locale])
                ->with('error', __('storefront.checkout.empty_cart_error'));
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'address' => ['required', 'string', 'max:500'],
            'shipping_method_id' => [Rule::requiredIf(ShippingMethod::query()->active()->exists()), 'nullable', 'integer', 'exists:shipping_methods,id'],
            'payment_method' => [
                'required',
                'string',
                Rule::in(array_keys($this->enabledPaymentMethods())),
            ],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'lang' => ['nullable', 'string', 'max:5'],
        ]);

        try {
            $cart->load([
                'items.product',
                'items.productVariant',
                'currency',
                'shippingMethod',
            ]);

            $order = $checkoutService->convertCartToOrder(
                cart: $cart,
                data: $validated,
                userId: auth()->id()
            );

            $this->saveCustomerProfileFromCheckout($validated);

            session()->forget('storefront_cart_id');

            $this->sendOrderCreatedEmail($order, $locale);

            $checkoutPayment = $order->payments()
                ->whereNotNull('checkout_url')
                ->latest('id')
                ->first();

            if ($checkoutPayment?->checkout_url) {
                return redirect()->away($checkoutPayment->checkout_url);
            }

            return redirect()
                ->to(URL::signedRoute('storefront.orders.show', [
                    'order' => $order->id,
                    'lang' => $locale,
                ]))
                ->with('success', __('storefront.checkout.order_created_successfully'));
        } catch (Throwable $exception) {
            if (! $exception instanceof RuntimeException) {
                report($exception);
            }

            return back()
                ->withInput()
                ->with('error', $exception->getMessage() ?: __('storefront.checkout.order_failed'));
        }
    }

    public function success(Request $request, Order $order): View
    {
        $locale = $this->resolveLocale($request);

        $order->load([
            'items.product',
            'currency',
            'customer',
        ]);

        return view('storefront.checkout.success', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'order' => $order,
            'pageTitle' => __('storefront.checkout.success_title').' - Smart Commerce Platform',
            'pageDescription' => __('storefront.checkout.success_text'),
        ]);
    }

    private function sendOrderCreatedEmail(Order $order, string $locale): void
    {
        try {
            $order->loadMissing([
                'items.product.brand',
                'items.product.currency',
                'items.productVariant',
                'currency',
                'customer',
                'shippingMethod',
            ]);

            $email = $order->customer_email
                ?? $order->customer?->email
                ?? null;

            if (! $email) {
                return;
            }

            Mail::to($email)->send(new StorefrontOrderCreatedMail($order, $locale));
        } catch (Throwable $mailException) {
            report($mailException);
        }
    }

    private function checkoutDefaults(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $customer = Customer::query()
            ->where('user_id', $user->id)
            ->first();

        return [
            'customer_name' => $user->name ?: $customer?->getDisplayName(),
            'customer_email' => $user->email ?: $customer?->email,
            'customer_phone' => $customer?->phone,
            'city' => $customer?->city,
            'address' => $customer?->street ?: $customer?->getFullAddress(),
            'customer_notes' => $customer?->address_notes,
        ];
    }

    private function enabledPaymentMethods(): array
    {
        return collect($this->paymentGatewayManager->enabledMethods())
            ->mapWithKeys(function (array $config, string $method): array {
                $displayName = $config['display_name'] ?? null;
                $locale = app()->getLocale();

                if (is_array($displayName)) {
                    return [$method => $displayName[$locale] ?? $displayName['en'] ?? ucfirst($method)];
                }

                $translationKey = $config['translation_key'] ?? null;

                return [$method => $translationKey ? __($translationKey) : ucfirst(str_replace('_', ' ', $method))];
            })
            ->all();
    }

    private function saveCustomerProfileFromCheckout(array $validated): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        [$firstName, $lastName] = $this->splitName((string) ($validated['customer_name'] ?? $user->name));

        Customer::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $validated['customer_email'] ?? $user->email,
                'phone' => $validated['customer_phone'] ?? null,
                'city' => $validated['city'] ?? null,
                'street' => $validated['address'] ?? null,
                'address_notes' => $validated['customer_notes'] ?? null,
                'customer_type' => CustomerType::Regular->value,
                'status' => CustomerStatus::Active->value,
                'is_active' => true,
            ]
        );
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

    private function getCurrentCart(): ?Cart
    {
        $cartId = session('storefront_cart_id');

        if (! $cartId) {
            return null;
        }

        return Cart::query()
            ->where('id', $cartId)
            ->where('status', CartStatus::Active->value)
            ->where('is_active', true)
            ->first();
    }

    private function resolveLocale(Request $request): string
    {
        $allowedLocales = ['ar', 'he', 'en'];

        $locale = $request->input('lang')
            ?? $request->query('lang')
            ?? session('storefront_locale')
            ?? app()->getLocale()
            ?? 'ar';

        if (! in_array($locale, $allowedLocales, true)) {
            $locale = 'ar';
        }

        session(['storefront_locale' => $locale]);

        App::setLocale($locale);

        return $locale;
    }

    private function direction(string $locale): string
    {
        return in_array($locale, ['ar', 'he'], true) ? 'rtl' : 'ltr';
    }
}
