<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
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
            ->with([
                'brand',
                'currency',
                'approvedReviews',
            ])
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
            ->with([
                'brand',
                'currency',
                'approvedReviews',
            ])
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

    public function products(Request $request)
    {
        $locale = $this->resolveLocale($request);

        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');
        $brandId = $request->query('brand');
        $productType = $request->query('type');
        $sort = $request->query('sort', 'latest');

        $productsQuery = Product::query()
            ->with([
                'brand',
                'currency',
                'approvedReviews',
            ])
            ->where('is_active', true);

        if ($search !== '') {
            $productsQuery->where(function (Builder $query) use ($search) {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%")
                    ->orWhere('name->he', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('description->ar', 'like', "%{$search}%")
                    ->orWhere('description->he', 'like', "%{$search}%")
                    ->orWhere('description->en', 'like', "%{$search}%");
            });
        }

        if ($categoryId && method_exists(Product::class, 'categories')) {
            $productsQuery->whereHas('categories', function (Builder $query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            });
        }

        if ($brandId && Schema::hasColumn('products', 'brand_id')) {
            $productsQuery->where('brand_id', $brandId);
        }

        if ($productType && Schema::hasColumn('products', 'product_type')) {
            $productsQuery->where('product_type', $productType);
        }

        match ($sort) {
            'name_asc' => $productsQuery->orderBy("name->{$locale}"),
            'price_low' => $this->applyPriceSort($productsQuery, 'asc'),
            'price_high' => $this->applyPriceSort($productsQuery, 'desc'),
            default => $productsQuery->latest(),
        };

        $products = $productsQuery
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('storefront.products.index', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => [
                'q' => $search,
                'category' => $categoryId,
                'brand' => $brandId,
                'type' => $productType,
                'sort' => $sort,
            ],
        ]);
    }

    public function productShow(Request $request, string $slug)
    {
        $locale = $this->resolveLocale($request);

        $product = Product::query()
            ->with([
                'brand',
                'currency',
                'categories',
                'variants',
                'media',
                'approvedReviews',
            ])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->with([
                'brand',
                'currency',
                'approvedReviews',
            ])
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when(
                method_exists($product, 'categories') && $product->categories->isNotEmpty(),
                function (Builder $query) use ($product) {
                    $query->whereHas('categories', function (Builder $categoryQuery) use ($product) {
                        $categoryQuery->whereIn(
                            'categories.id',
                            $product->categories->pluck('id')->toArray()
                        );
                    });
                }
            )
            ->latest()
            ->limit(4)
            ->get();

        return view('storefront.products.show', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'pageTitle' => $product->getName($locale) . ' - Smart Commerce Platform',
            'pageDescription' => method_exists($product, 'getShortDescription')
                ? $product->getShortDescription($locale)
                : $product->getName($locale),
        ]);
    }

    private function applyPriceSort(Builder $query, string $direction): Builder
    {
        if (Schema::hasColumn('products', 'sale_price') && Schema::hasColumn('products', 'price')) {
            return $query->orderByRaw("COALESCE(NULLIF(sale_price, 0), price) {$direction}");
        }

        if (Schema::hasColumn('products', 'price')) {
            return $query->orderBy('price', $direction);
        }

        return $query->latest();
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
