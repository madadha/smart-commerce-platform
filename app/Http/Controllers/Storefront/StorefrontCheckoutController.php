<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\CartStatus;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

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