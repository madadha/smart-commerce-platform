<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class StorefrontCompareController extends Controller
{
    private int $maxCompareItems = 4;

    public function index(Request $request): View
    {
        $locale = $this->resolveLocale($request);

        $compareIds = $this->getCompareIds($request);

        $products = Product::query()
            ->with([
                'brand',
                'currency',
                'categories',
                'variants',
                'approvedReviews',
            ])
            ->whereIn('id', $compareIds)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $compareIds, true))
            ->values();

        return view('storefront.compare.index', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'products' => $products,
            'compareCount' => $products->count(),
            'maxCompareItems' => $this->maxCompareItems,
            'pageTitle' => __('storefront.compare.page_title') . ' - Smart Commerce Platform',
            'pageDescription' => __('storefront.compare.page_description'),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        if (! $product->is_active) {
            return back()->with('error', __('storefront.compare.product_not_available'));
        }

        $compareIds = $this->getCompareIds($request);

        if (in_array($product->id, $compareIds, true)) {
            return back()->with('success', __('storefront.compare.already_added'));
        }

        if (count($compareIds) >= $this->maxCompareItems) {
            return back()->with('error', __('storefront.compare.max_limit_reached'));
        }

        $compareIds[] = $product->id;

        $request->session()->put('storefront_compare_products', array_values(array_unique($compareIds)));

        return back()
            ->with('success', __('storefront.compare.added_successfully'))
            ->withInput(['lang' => $locale]);
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        $compareIds = collect($this->getCompareIds($request))
            ->reject(fn (int $id) => $id === $product->id)
            ->values()
            ->all();

        $request->session()->put('storefront_compare_products', $compareIds);

        return back()
            ->with('success', __('storefront.compare.removed_successfully'))
            ->withInput(['lang' => $locale]);
    }

    public function clear(Request $request): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        $request->session()->forget('storefront_compare_products');

        return redirect()
            ->route('storefront.compare.index', ['lang' => $locale])
            ->with('success', __('storefront.compare.cleared_successfully'));
    }

    private function getCompareIds(Request $request): array
    {
        return collect($request->session()->get('storefront_compare_products', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take($this->maxCompareItems)
            ->values()
            ->all();
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