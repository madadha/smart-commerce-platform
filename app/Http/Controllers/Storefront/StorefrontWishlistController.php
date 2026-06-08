<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerWishlist;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class StorefrontWishlistController extends Controller
{
    public function index(Request $request): View
    {
        $locale = $this->resolveLocale($request);

        $wishlistItems = CustomerWishlist::query()
            ->with([
                'product.brand',
                'product.currency',
            ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('storefront.wishlist.index', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'wishlistItems' => $wishlistItems,
            'pageTitle' => __('storefront.wishlist.page_title') . ' - Smart Commerce Platform',
            'pageDescription' => __('storefront.wishlist.page_description'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        CustomerWishlist::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $validated['product_id'],
        ]);

        return back()
            ->with('success', __('storefront.wishlist.added_successfully'))
            ->withInput(['lang' => $locale]);
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        CustomerWishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return back()
            ->with('success', __('storefront.wishlist.removed_successfully'))
            ->withInput(['lang' => $locale]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        $wishlistItem = CustomerWishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();

            return back()
                ->with('success', __('storefront.wishlist.removed_successfully'))
                ->withInput(['lang' => $locale]);
        }

        CustomerWishlist::query()->create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return back()
            ->with('success', __('storefront.wishlist.added_successfully'))
            ->withInput(['lang' => $locale]);
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