@extends('storefront.layout')

@section('content')
    @php
        $productImage = function ($product) {
            if (! empty($product->main_image)) {
                return asset('storage/' . $product->main_image);
            }

            return null;
        };

        $productPrice = function ($product) {
            if (method_exists($product, 'finalPrice')) {
                return $product->finalPrice();
            }

            return $product->sale_price ?: $product->price;
        };
    @endphp

    <section class="scp-hero-section">
        <div class="scp-container">
            <div class="scp-hero-layout">

                <div class="scp-hero-content">
                    <div class="scp-hero-badge">
                        {{ __('storefront.hero.badge') }}
                    </div>

                    <h1>
                        {{ __('storefront.hero.title') }}
                    </h1>

                    <p>
                        {{ __('storefront.hero.text') }}
                    </p>

                    <div class="scp-hero-buttons">
                        <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar']) }}" class="scp-btn scp-btn-primary">
                            {{ __('storefront.hero.shop_now') }}
                        </a>

                        <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'on_sale' => 1]) }}" class="scp-btn scp-btn-light">
                            {{ __('storefront.hero.view_deals') }}
                        </a>
                    </div>

                    <div class="scp-hero-stats">
                        <div>
                            <strong>{{ $featuredProducts->count() + $latestProducts->count() }}+</strong>
                            <span>{{ __('storefront.hero.products') }}</span>
                        </div>

                        <div>
                            <strong>{{ $featuredCategories->count() }}+</strong>
                            <span>{{ __('storefront.hero.categories') }}</span>
                        </div>

                        <div>
                            <strong>3</strong>
                            <span>{{ __('storefront.hero.languages') }}</span>
                        </div>
                    </div>
                </div>

                <div class="scp-hero-showcase">
                    <div class="scp-showcase-card main">
                        <span>{{ __('storefront.hero.showcase_small') }}</span>
                        <strong>{{ __('storefront.hero.showcase_title') }}</strong>
                    </div>

                    <div class="scp-showcase-card floating one">
                        <span>{{ __('storefront.hero.coupons') }}</span>
                        <strong>{{ __('storefront.hero.dynamic_deals') }}</strong>
                    </div>

                    <div class="scp-showcase-card floating two">
                        <span>{{ __('storefront.hero.checkout') }}</span>
                        <strong>{{ __('storefront.hero.cart_to_order') }}</strong>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="scp-section">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ __('storefront.sections.categories') }}</h2>
                    <p>{{ __('storefront.sections.categories_subtitle') }}</p>
                </div>
            </div>

            <div class="scp-category-grid">
                @forelse($featuredCategories as $category)
                    <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'category' => $category->id]) }}" class="scp-category-card">
                        <div class="scp-category-icon">
                            @if(! empty($category->icon))
                                {{ $category->icon }}
                            @else
                                📁
                            @endif
                        </div>

                        <h3>{{ $category->getName($locale) }}</h3>

                        @if(method_exists($category, 'getDescription'))
                            <p>{{ $category->getDescription($locale) }}</p>
                        @endif
                    </a>
                @empty
                    <div class="scp-empty">
                        {{ __('storefront.empty.categories') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="scp-section scp-section-muted">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ __('storefront.sections.featured') }}</h2>
                    <p>{{ __('storefront.sections.featured_subtitle') }}</p>
                </div>

                <a href="#" class="scp-link-more">
                    {{ __('storefront.sections.view_all') }} →
                </a>
            </div>

            <div class="scp-product-grid">
                @forelse($featuredProducts as $product)
                    @php
                                $stockValue = null;

                                foreach (['stock_quantity', 'quantity', 'stock'] as $stockColumn) {
                                    if (isset($product->{$stockColumn}) && $product->{$stockColumn} !== null) {
                                        $stockValue = (int) $product->{$stockColumn};
                                        break;
                                    }
                                }

                                $isOutOfStock = $stockValue !== null && $stockValue <= 0;
                    @endphp
                    <article class="scp-product-card">
                        <div class="scp-product-image">
                            @if($productImage($product))
                                <img src="{{ $productImage($product) }}" alt="{{ $product->getName($locale) }}">
                            @else
                                <div class="scp-product-placeholder">
                                    {{ mb_substr($product->getName($locale), 0, 1) }}
                                </div>
                            @endif

                            @if(! empty($product->sale_price))
                                <span class="scp-product-badge">
                                    {{ __('storefront.product.sale') }}
                                </span>
                            @endif
                        </div>

                        <div class="scp-product-body">
                            <div class="scp-product-brand">
                                {{ $product->brand?->getName($locale) ?? __('storefront.product.default_brand') }}
                            </div>

                            <h3>{{ $product->getName($locale) }}</h3>

                            @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.rating-summary'))
                                @include('storefront.products.partials.rating-summary', [
                                    'product' => $product,
                                ])
                            @endif

                            @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.stock-status'))
                                @include('storefront.products.partials.stock-status', [
                                    'product' => $product,
                                ])
                            @endif

                            <div class="scp-product-price">
                                <strong>
                                    {{ $product->currency?->symbol ?? '₪' }}
                                    {{ number_format((float) $productPrice($product), 2) }}
                                </strong>

                                @if(! empty($product->sale_price) && (float) $product->sale_price < (float) $product->price)
                                    <span>
                                        {{ $product->currency?->symbol ?? '₪' }}
                                        {{ number_format((float) $product->price, 2) }}
                                    </span>
                                @endif
                            </div>

                            <div class="scp-product-actions">
                              <a href="{{ route('storefront.products.show', ['slug' => $product->slug, 'lang' => $locale]) }}" class="scp-btn-small">
    {{ __('storefront.product.details') }}
</a>

                           <form method="POST" action="{{ route('storefront.cart.add') }}" class="scp-card-cart-form">
    @csrf

    <input type="hidden" name="lang" value="{{ $locale }}">
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <input type="hidden" name="quantity" value="1">

    <button
        type="submit"
        class="scp-btn-small primary"
        @disabled($isOutOfStock)
    >
        {{ $isOutOfStock ? (\Illuminate\Support\Facades\Lang::has('storefront.stock.out_of_stock') ? __('storefront.stock.out_of_stock') : 'نفذ المخزون') : __('storefront.product.add_to_cart') }}
    </button>
</form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="scp-empty">
                        {{ __('storefront.product.no_featured') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="scp-section">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ __('storefront.sections.latest') }}</h2>
                    <p>{{ __('storefront.sections.latest_subtitle') }}</p>
                </div>
            </div>

            <div class="scp-product-grid compact">
                @forelse($latestProducts as $product)
                    @php
                                $stockValue = null;

                                foreach (['stock_quantity', 'quantity', 'stock'] as $stockColumn) {
                                    if (isset($product->{$stockColumn}) && $product->{$stockColumn} !== null) {
                                        $stockValue = (int) $product->{$stockColumn};
                                        break;
                                    }
                                }

                                $isOutOfStock = $stockValue !== null && $stockValue <= 0;
                    @endphp
                    <article class="scp-product-card">
                        <div class="scp-product-image">
                            @if($productImage($product))
                                <img src="{{ $productImage($product) }}" alt="{{ $product->getName($locale) }}">
                            @else
                                <div class="scp-product-placeholder">
                                    {{ mb_substr($product->getName($locale), 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <div class="scp-product-body">
                            <div class="scp-product-brand">
                                {{ $product->brand?->getName($locale) ?? __('storefront.product.default_brand') }}
                            </div>

                            <h3>{{ $product->getName($locale) }}</h3>

                            @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.rating-summary'))
                                @include('storefront.products.partials.rating-summary', [
                                    'product' => $product,
                                ])
                            @endif

                            @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.stock-status'))
                                @include('storefront.products.partials.stock-status', [
                                    'product' => $product,
                                ])
                            @endif

                            <div class="scp-product-price">
                                <strong>
                                    {{ $product->currency?->symbol ?? '₪' }}
                                    {{ number_format((float) $productPrice($product), 2) }}
                                </strong>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="scp-empty">
                        {{ __('storefront.product.no_latest') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="scp-section scp-section-muted">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ __('storefront.sections.brands') }}</h2>
                    <p>{{ __('storefront.sections.brands_subtitle') }}</p>
                </div>
            </div>

            <div class="scp-brand-grid">
                @forelse($brands as $brand)
                    <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'brand' => $brand->id]) }}" class="scp-brand-card">
                        @if(! empty($brand->logo))
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->getName($locale) }}">
                        @else
                            <strong>{{ $brand->getName($locale) }}</strong>
                        @endif
                    </a>
                @empty
                    <div class="scp-empty">
                        {{ __('storefront.empty.brands') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection