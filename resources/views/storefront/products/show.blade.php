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

        $productTypeValue = function ($product) {
            $type = $product->product_type ?? null;

            if ($type instanceof \BackedEnum) {
                return $type->value;
            }

            return (string) $type;
        };

        $productStatusValue = function ($product) {
            $status = $product->status ?? null;

            if ($status instanceof \BackedEnum) {
                return $status->value;
            }

            return (string) $status;
        };

        $description = method_exists($product, 'getDescription')
            ? $product->getDescription($locale)
            : ($product->description[$locale] ?? '');

        $shortDescription = method_exists($product, 'getShortDescription')
            ? $product->getShortDescription($locale)
            : ($product->short_description[$locale] ?? '');

        $specifications = $product->specifications ?? [];
        $notes = $product->notes ?? [];
    @endphp

    <section class="scp-product-details-section">
        <div class="scp-container">

            <div class="scp-breadcrumbs">
                <a href="{{ route('storefront.home', ['lang' => $locale]) }}">
                    {{ __('storefront.nav.home') }}
                </a>
                <span>/</span>
                <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}">
                    {{ __('storefront.nav.products') }}
                </a>
                <span>/</span>
                <strong>{{ $product->getName($locale) }}</strong>
            </div>

            <div class="scp-product-details-layout">

                <div class="scp-product-gallery">
                    <div class="scp-product-main-image">
                        @if($productImage($product))
                            <img src="{{ $productImage($product) }}" alt="{{ $product->getName($locale) }}">
                        @else
                            <div class="scp-product-placeholder big">
                                {{ mb_substr($product->getName($locale), 0, 1) }}
                            </div>
                        @endif

                        @if(! empty($product->sale_price))
                            <span class="scp-product-badge">
                                {{ __('storefront.product.sale') }}
                            </span>
                        @endif

                        @if($productTypeValue($product) === 'digital')
                            <span class="scp-product-type-badge">
                                {{ __('storefront.products_page.digital') }}
                            </span>
                        @endif
                    </div>

                    <div class="scp-product-thumbs">
                        @if($productImage($product))
                            <div class="scp-product-thumb active">
                                <img src="{{ $productImage($product) }}" alt="{{ $product->getName($locale) }}">
                            </div>
                        @endif

                   @if($product->relationLoaded('media') && $product->media->count())
    @foreach($product->media->take(4) as $mediaItem)
        @php
            $mediaPath = $mediaItem->file_path
                ?? $mediaItem->path
                ?? $mediaItem->url
                ?? $mediaItem->mediaFile?->file_path
                ?? $mediaItem->mediaFile?->path
                ?? null;
        @endphp

        @if($mediaPath)
            <div class="scp-product-thumb">
                <img src="{{ asset('storage/' . $mediaPath) }}" alt="{{ $product->getName($locale) }}">
            </div>
        @endif
    @endforeach
@endif
                    </div>
                </div>

                <div class="scp-product-info">
                    <div class="scp-product-meta-line">
                        <span>{{ $product->brand?->getName($locale) ?? __('storefront.product.default_brand') }}</span>

                        @if(! empty($product->sku))
                            <span>SKU: {{ $product->sku }}</span>
                        @endif
                    </div>

                    <h1>{{ $product->getName($locale) }}</h1>

                    @include('storefront.products.partials.rating-summary', [
                        'product' => $product,
                    ])

                    @if($shortDescription)
                        <p class="scp-product-short-description">
                            {{ $shortDescription }}
                        </p>
                    @endif

                    <div class="scp-product-detail-price">
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

                    <div class="scp-product-tags">
                        <span>{{ __('storefront.product_details.type') }}: {{ $productTypeValue($product) ?: '-' }}</span>
                        <span>{{ __('storefront.product_details.status') }}: {{ $productStatusValue($product) ?: '-' }}</span>

                        @if(isset($product->track_stock) && $product->track_stock)
                            <span>
                                {{ __('storefront.product_details.stock') }}:
                                {{ $product->stock_quantity ?? 0 }}
                            </span>
                        @endif
                    </div>

                    @if(isset($product->categories) && $product->categories->count())
                        <div class="scp-product-categories">
                            <strong>{{ __('storefront.product_details.categories') }}</strong>

                            <div>
                                @foreach($product->categories as $category)
                                    <a href="{{ route('storefront.products.index', ['lang' => $locale, 'category' => $category->id]) }}">
                                        {{ $category->getName($locale) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(isset($product->variants) && $product->variants->count())
                        <div class="scp-product-variants">
                            <strong>{{ __('storefront.product_details.variants') }}</strong>

                            <div class="scp-variant-list">
                                @foreach($product->variants as $variant)
                                    <div class="scp-variant-card">
                                        <span>{{ $variant->getName($locale) }}</span>

                                        @if(! empty($variant->sku))
                                            <small>{{ $variant->sku }}</small>
                                        @endif

                                        @if(method_exists($variant, 'finalPrice'))
                                            <strong>
                                                {{ $product->currency?->symbol ?? '₪' }}
                                                {{ number_format((float) $variant->finalPrice(), 2) }}
                                            </strong>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

<div class="scp-product-detail-actions">
    <form method="POST" action="{{ route('storefront.cart.add') }}" class="scp-detail-cart-form">
        @csrf

        <input type="hidden" name="lang" value="{{ $locale }}">
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        <div class="scp-quantity-box">
            <label>{{ __('storefront.cart.quantity') }}</label>

            <input
                type="number"
                name="quantity"
                value="1"
                min="1"
                max="99"
            >
        </div>

        <button type="submit" class="scp-detail-add-to-cart">
            {{ __('storefront.product.add_to_cart') }}
        </button>
    </form>

    @if(auth()->check())
        <form
            method="POST"
            action="{{ route('storefront.wishlist.toggle', ['product' => $product->id, 'lang' => $locale]) }}"
            class="scp-product-details-wishlist-form"
        >
            @csrf

            <button type="submit">
                ♥ {{ __('storefront.wishlist.toggle') }}
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="scp-product-details-wishlist-link">
            ♡ {{ __('storefront.wishlist.login_required') }}
        </a>
    @endif


    <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-detail-secondary-btn">
        {{ __('storefront.product_details.back_to_products') }}
    </a>
</div>
                </div>

            </div>

            <div class="scp-product-content-grid">
                <div class="scp-product-content-card">
                    <h2>{{ __('storefront.product_details.description') }}</h2>

                    @if($description)
                        <div class="scp-rich-text">
                            {!! $description !!}
                        </div>
                    @else
                        <p class="scp-muted-text">
                            {{ __('storefront.product_details.no_description') }}
                        </p>
                    @endif
                </div>

                <div class="scp-product-content-card">
                    <h2>{{ __('storefront.product_details.specifications') }}</h2>

                    @if(is_array($specifications) && count($specifications))
                        <div class="scp-spec-list">
                            @foreach($specifications as $key => $value)
                                <div>
                                    <span>{{ $key }}</span>
                                    <strong>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="scp-muted-text">
                            {{ __('storefront.product_details.no_specifications') }}
                        </p>
                    @endif
                </div>
            </div>

            @if(is_array($notes) && count($notes))
                <div class="scp-product-content-card">
                    <h2>{{ __('storefront.product_details.notes') }}</h2>

                    <div class="scp-spec-list">
                        @foreach($notes as $key => $value)
                            <div>
                                <span>{{ $key }}</span>
                                <strong>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <section class="scp-section">
                <div class="scp-section-heading">
                    <div>
                        <h2>{{ __('storefront.product_details.related_products') }}</h2>
                        <p>{{ __('storefront.product_details.related_subtitle') }}</p>
                    </div>
                </div>

                <div class="scp-product-grid">
                    @forelse($relatedProducts as $relatedProduct)
                        <article class="scp-product-card">
                            @if(auth()->check())
                                <form
                                    method="POST"
                                    action="{{ route('storefront.wishlist.toggle', ['product' => $relatedProduct->id, 'lang' => $locale]) }}"
                                    class="scp-product-wishlist-form"
                                >
                                    @csrf

                                    <button type="submit" title="{{ __('storefront.wishlist.toggle') }}">
                                        ♥
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="scp-product-wishlist-form">
                                    <button type="button" title="{{ __('storefront.wishlist.login_required') }}">
                                        ♡
                                    </button>
                                </a>
                            @endif

                            <div class="scp-product-image">
                                @if($productImage($relatedProduct))
                                    <img src="{{ $productImage($relatedProduct) }}" alt="{{ $relatedProduct->getName($locale) }}">
                                @else
                                    <div class="scp-product-placeholder">
                                        {{ mb_substr($relatedProduct->getName($locale), 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <div class="scp-product-body">
                                <div class="scp-product-brand">
                                    {{ $relatedProduct->brand?->getName($locale) ?? __('storefront.product.default_brand') }}
                                </div>

                                <h3>{{ $relatedProduct->getName($locale) }}</h3>

                                @include('storefront.products.partials.rating-summary', [
                                    'product' => $relatedProduct,
                                ])

                                <div class="scp-product-price">
                                    <strong>
                                        {{ $relatedProduct->currency?->symbol ?? '₪' }}
                                        {{ number_format((float) $productPrice($relatedProduct), 2) }}
                                    </strong>
                                </div>

                                <div class="scp-product-actions">
                                    <a href="{{ route('storefront.products.show', ['slug' => $relatedProduct->slug, 'lang' => $locale]) }}" class="scp-btn-small">
                                        {{ __('storefront.product.details') }}
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="scp-empty">
                            {{ __('storefront.product_details.no_related_products') }}
                        </div>
                    @endforelse
                </div>
            </section>

        </div>
    </section>

    @include('storefront.products.partials.reviews', [
        'product' => $product,
        'locale' => $locale,
    ])


@endsection