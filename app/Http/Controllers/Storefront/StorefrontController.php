<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class StorefrontController extends Controller
{
    public function home(Request $request)
    {
        $locale = $this->resolveLocale($request);

        $featuredCategories = Category::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('show_in_menu', true)
                    ->orWhereNull('show_in_menu');
            })
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(10)
            ->get();

        $featuredProductsQuery = Product::query()
            ->with(['brand', 'currency'])
            ->where('is_active', true);

        if (Schema::hasColumn('products', 'is_featured')) {
            $featuredProductsQuery->where(function ($query) {
                $query->where('is_featured', true)
                    ->orWhereNull('is_featured');
            });
        }

        $featuredProducts = $featuredProductsQuery
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $latestProducts = Product::query()
            ->with(['brand', 'currency'])
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();

        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(12)
            ->get();

        return view('storefront.home', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'featuredCategories' => $featuredCategories,
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'brands' => $brands,
        ]);
    }

    private function resolveLocale(Request $request): string
    {
        $allowedLocales = ['ar', 'he', 'en'];

        $locale = $request->query('lang')
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