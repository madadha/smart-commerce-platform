<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\StorefrontPromotion;
use App\Models\StorefrontSetting;
use App\Models\StorefrontSlide;
use App\Support\Localization\ActiveLanguageRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class StorefrontController extends Controller
{
    public function home(Request $request)
    {
        $locale = $this->resolveLocale($request);

        $storefrontSettings = StorefrontSetting::current();
        $storefrontSlides = StorefrontSlide::activeSlides();
        $homePromotions = StorefrontPromotion::activeForPlacement('home_after_hero', 6);
        $midPromotions = StorefrontPromotion::activeForPlacement('home_between_products', 3);

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
            'storefrontSettings' => $storefrontSettings,
            'storefrontSlides' => $storefrontSlides,
            'homePromotions' => $homePromotions,
            'midPromotions' => $midPromotions,
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

        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $rating = $request->query('rating');
        $inStock = $request->boolean('in_stock');
        $onSale = $request->boolean('on_sale');
        $optionFilters = $this->normalizeOptionFilters($request->input('options', []));

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

        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice) && Schema::hasColumn('products', 'price')) {
            $productsQuery->where(function (Builder $query) use ($minPrice) {
                if (Schema::hasColumn('products', 'sale_price')) {
                    $query->whereRaw('COALESCE(NULLIF(sale_price, 0), price) >= ?', [(float) $minPrice]);
                } else {
                    $query->where('price', '>=', (float) $minPrice);
                }
            });
        }

        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice) && Schema::hasColumn('products', 'price')) {
            $productsQuery->where(function (Builder $query) use ($maxPrice) {
                if (Schema::hasColumn('products', 'sale_price')) {
                    $query->whereRaw('COALESCE(NULLIF(sale_price, 0), price) <= ?', [(float) $maxPrice]);
                } else {
                    $query->where('price', '<=', (float) $maxPrice);
                }
            });
        }

        if ($inStock) {
            $this->applyStockFilter($productsQuery);
        }

        if ($onSale && Schema::hasColumn('products', 'sale_price') && Schema::hasColumn('products', 'price')) {
            $productsQuery
                ->whereNotNull('sale_price')
                ->where('sale_price', '>', 0)
                ->whereColumn('sale_price', '<', 'price');
        }

        $this->applyOptionFilters($productsQuery, $optionFilters);

        $needsRatingAverage = ($rating !== null && $rating !== '')
            || in_array($sort, ['rating_high', 'rating_low'], true);

        if ($needsRatingAverage) {
            $productsQuery->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating');
        }

        if ($rating !== null && $rating !== '' && is_numeric($rating)) {
            $productsQuery
                ->whereHas('approvedReviews')
                ->having('approved_reviews_avg_rating', '>=', (float) $rating);
        }

        match ($sort) {
            'name_asc' => $productsQuery->orderBy("name->{$locale}"),
            'price_low' => $this->applyPriceSort($productsQuery, 'asc'),
            'price_high' => $this->applyPriceSort($productsQuery, 'desc'),
            'rating_high' => $this->applyRatingSort($productsQuery, 'desc'),
            'rating_low' => $this->applyRatingSort($productsQuery, 'asc'),
            default => $productsQuery->latest(),
        };

        $priceBoundsQuery = Product::query()->where('is_active', true);

        if (Schema::hasColumn('products', 'sale_price') && Schema::hasColumn('products', 'price')) {
            $priceBounds = $priceBoundsQuery->selectRaw(
                'MIN(COALESCE(NULLIF(sale_price, 0), price)) as min_price, MAX(COALESCE(NULLIF(sale_price, 0), price)) as max_price'
            )->first();
        } else {
            $priceBounds = $priceBoundsQuery->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        }

        $availableOptionFilters = $this->buildAvailableOptionFilters(
            (clone $productsQuery)
                ->reorder()
                ->select('products.id')
                ->distinct()
                ->pluck('id')
                ->all(),
            $locale
        );

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

        $productAdSlides = StorefrontPromotion::activeForPlacement('products_ads_hero', 8);
        $productAdTiles = StorefrontPromotion::activeForPlacement('products_ads_strip', 8);

        return view('storefront.products.index', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'productAdSlides' => $productAdSlides,
            'productAdTiles' => $productAdTiles,
            'storefrontSettings' => StorefrontSetting::current(),
            'filters' => [
                'q' => $search,
                'category' => $categoryId,
                'brand' => $brandId,
                'type' => $productType,
                'sort' => $sort,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'rating' => $rating,
                'in_stock' => $inStock,
                'on_sale' => $onSale,
            ],
            'optionFilters' => $optionFilters,
            'availableOptionFilters' => $availableOptionFilters,
            'priceBounds' => [
                'min' => (float) ($priceBounds->min_price ?? 0),
                'max' => (float) ($priceBounds->max_price ?? 0),
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
                'options' => fn ($query) => $query->where('is_active', true),
                'variants' => fn ($query) => $query->where('is_active', true)->with('mediaFile'),
                'media' => fn ($query) => $query->where('is_active', true)->with('mediaFile'),
                'approvedReviews',
                'approvedQuestions',
            ])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $this->storeRecentlyViewedProduct($request, $product->id);

        $recentlyViewedProducts = $this->getRecentlyViewedProducts($request, $product->id);

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
            'recentlyViewedProducts' => $recentlyViewedProducts,
            'pageTitle' => $product->getName($locale) . ' - Smart Commerce Platform',
            'pageDescription' => method_exists($product, 'getShortDescription')
                ? $product->getShortDescription($locale)
                : $product->getName($locale),
        ]);
    }

    private function storeRecentlyViewedProduct(Request $request, int $productId): void
    {
        $recentlyViewed = collect(
            $request->session()->get('storefront_recently_viewed_products', [])
        );

        $recentlyViewed = $recentlyViewed
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $id === $productId)
            ->prepend($productId)
            ->unique()
            ->take(8)
            ->values()
            ->all();

        $request->session()->put('storefront_recently_viewed_products', $recentlyViewed);
    }

    private function getRecentlyViewedProducts(Request $request, int $currentProductId)
    {
        $recentlyViewedIds = collect(
            $request->session()->get('storefront_recently_viewed_products', [])
        )
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $id === $currentProductId)
            ->unique()
            ->take(8)
            ->values()
            ->all();

        if (empty($recentlyViewedIds)) {
            return collect();
        }

        return Product::query()
            ->with([
                'brand',
                'currency',
                'approvedReviews',
            ])
            ->whereIn('id', $recentlyViewedIds)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $recentlyViewedIds, true))
            ->values();
    }

    private function applyStockFilter(Builder $query): Builder
    {
        foreach ([
            'stock_quantity',
            'quantity',
            'stock',
            'inventory_quantity',
            'available_quantity',
        ] as $column) {
            if (Schema::hasColumn('products', $column)) {
                return $query->where($column, '>', 0);
            }
        }

        return $query;
    }

    private function applyRatingSort(Builder $query, string $direction): Builder
    {
        return $query
            ->orderBy('approved_reviews_avg_rating', $direction)
            ->orderByDesc('id');
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

    private function normalizeOptionFilters(mixed $filters): array
    {
        if (! is_array($filters)) {
            return [];
        }

        return collect($filters)
            ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
            ->mapWithKeys(fn ($value, $slug) => [trim((string) $slug) => trim((string) $value)])
            ->all();
    }

    private function applyOptionFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $slug => $value) {
            if ($value === '') {
                continue;
            }

            $query->whereHas('variants', function (Builder $variantQuery) use ($slug, $value) {
                $variantQuery->where('is_active', true)
                    ->whereJsonContains("option_values->{$slug}", $value);
            });
        }
    }

    private function buildAvailableOptionFilters(array $productIds, string $locale): array
    {
        if ($productIds === []) {
            return [];
        }

        return ProductOption::query()
            ->where('is_active', true)
            ->whereIn('product_id', $productIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('slug')
            ->map(function ($options, string $slug) use ($locale): array {
                $first = $options->first();
                $values = collect();

                foreach ($options as $option) {
                    foreach ($option->getValues() as $value) {
                        $technicalValue = trim((string) ($value['value'] ?? ''));

                        if ($technicalValue === '') {
                            continue;
                        }

                        $label = $value[$locale] ?? $value['en'] ?? $value['ar'] ?? $technicalValue;

                        $values[$technicalValue] = [
                            'value' => $technicalValue,
                            'label' => $label,
                            'color' => $value['color'] ?? null,
                        ];
                    }
                }

                return [
                    'slug' => $slug,
                    'name' => $first?->getName($locale) ?? $slug,
                    'type' => $first?->type ?? 'select',
                    'values' => $values->values()->sortBy('label')->values()->all(),
                ];
            })
            ->filter(fn (array $option) => $option['values'] !== [])
            ->values()
            ->all();
    }

    private function resolveLocale(Request $request): string
    {
        $locale = $request->input('lang')
            ?? $request->query('lang')
            ?? session('storefront_locale')
            ?? app()->getLocale()
            ?? 'ar';

        $locale = app(ActiveLanguageRegistry::class)->resolve($locale);

        session(['storefront_locale' => $locale]);

        App::setLocale($locale);

        return $locale;
    }

    private function direction(string $locale): string
    {
        return app(ActiveLanguageRegistry::class)->direction($locale);
    }
}
