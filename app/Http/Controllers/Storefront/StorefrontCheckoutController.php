<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\CartStatus;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Services\Checkout\CartCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Throwable;

class StorefrontCheckoutController extends Controller
{
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

        return view('storefront.checkout.index', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'cart' => $cart,
            'shippingMethods' => $shippingMethods,
            'pageTitle' => __('storefront.checkout.page_title') . ' - Smart Commerce Platform',
            'pageDescription' => __('storefront.checkout.page_description'),
        ]);
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
            'address' => ['required', 'string', 'max:500'],
            'shipping_method_id' => ['nullable', 'integer', 'exists:shipping_methods,id'],
            'payment_method' => ['required', 'string', 'max:50'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'lang' => ['nullable', 'string', 'max:5'],
        ]);

        try {
            $order = $checkoutService->convertCartToOrder(
                cart: $cart,
                data: $validated,
                userId: auth()->id()
            );

            session()->forget('storefront_cart_id');

            return redirect()
                ->to(URL::signedRoute('storefront.orders.show', [
                    'order' => $order->id,
                    'lang' => $locale,
                ]))
                ->with('success', __('storefront.checkout.order_created_successfully'));
        } catch (Throwable $exception) {
            report($exception);

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
            'pageTitle' => __('storefront.checkout.success_title') . ' - Smart Commerce Platform',
            'pageDescription' => __('storefront.checkout.success_text'),
        ]);
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