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
                        <a href="#" class="scp-btn scp-btn-primary">
                            {{ __('storefront.hero.shop_now') }}
                        </a>

                        <a href="#" class="scp-btn scp-btn-light">
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
                    <a href="#" class="scp-category-card">
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

                                <button type="button" class="scp-btn-small primary">
                                    {{ __('storefront.product.add_to_cart') }}
                                </button>
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
                    <a href="#" class="scp-brand-card">
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