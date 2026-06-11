@extends('storefront.layout')

@section('content')
    @php
        $toLocalizedString = function ($value) use ($locale) {
            if ($value instanceof \BackedEnum) {
                return (string) $value->value;
            }

            if (is_array($value)) {
                $localized = $value[$locale] ?? $value['ar'] ?? $value['en'] ?? $value['he'] ?? null;

                if (is_array($localized)) {
                    return collect($localized)
                        ->map(fn ($item, $key) => is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : (string) $item)
                        ->implode("\n");
                }

                if ($localized !== null) {
                    return (string) $localized;
                }

                return collect($value)
                    ->map(fn ($item, $key) => is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : (string) $item)
                    ->implode("\n");
            }

            if (is_object($value)) {
                if (method_exists($value, '__toString')) {
                    return (string) $value;
                }

                return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
            }

            return (string) ($value ?? '');
        };

        $productImage = function ($product) {
            if (! empty($product->main_image)) {
                return asset('storage/' . ltrim($product->main_image, '/'));
            }

            if (! empty($product->image)) {
                return asset('storage/' . ltrim($product->image, '/'));
            }

            return null;
        };

        $productPrice = function ($product) {
            if (method_exists($product, 'finalPrice')) {
                return $product->finalPrice();
            }

            return $product->sale_price ?: ($product->price ?? 0);
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

        $productName = method_exists($product, 'getName')
            ? $toLocalizedString($product->getName($locale))
            : $toLocalizedString($product->name ?? '');

        $descriptionRaw = method_exists($product, 'getDescription')
            ? $product->getDescription($locale)
            : ($product->description ?? '');

        $shortDescriptionRaw = method_exists($product, 'getShortDescription')
            ? $product->getShortDescription($locale)
            : ($product->short_description ?? '');

        $description = $toLocalizedString($descriptionRaw);
        $shortDescription = $toLocalizedString($shortDescriptionRaw);
        $specifications = is_array($product->specifications ?? null) ? $product->specifications : [];
        $notes = is_array($product->notes ?? null) ? $product->notes : [];
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
                <strong>{{ $productName }}</strong>
            </div>

            @if(session('success'))
                <div class="scp-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="scp-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <div class="scp-product-details-layout">

                <div class="scp-product-gallery">
                    <div class="scp-product-main-image">
                        @if($productImage($product))
                            <img src="{{ $productImage($product) }}" alt="{{ $productName }}">
                        @else
                            <div class="scp-product-placeholder big">
                                {{ mb_substr($productName, 0, 1) }}
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
                                <img src="{{ $productImage($product) }}" alt="{{ $productName }}">
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
                                        <img src="{{ asset('storage/' . ltrim($mediaPath, '/')) }}" alt="{{ $productName }}">
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

                    <h1>{{ $productName }}</h1>

                    @if($shortDescription !== '')
                        <p class="scp-product-short-description">
                            {{ $shortDescription }}
                        </p>
                    @endif

                    @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.rating-summary'))
                        @include('storefront.products.partials.rating-summary', ['product' => $product])
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
                                        {{ method_exists($category, 'getName') ? $category->getName($locale) : $toLocalizedString($category->name ?? '') }}
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
                                        <span>{{ method_exists($variant, 'getName') ? $variant->getName($locale) : $toLocalizedString($variant->name ?? '') }}</span>

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
                        @if(\Illuminate\Support\Facades\Route::has('storefront.cart.add'))
                            <form method="POST" action="{{ route('storefront.cart.add') }}" class="scp-detail-cart-form">
                                @csrf

                                <input type="hidden" name="lang" value="{{ $locale }}">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <div class="scp-quantity-box">
                                    <label>{{ __('storefront.cart.quantity') }}</label>

                                    <input type="number" name="quantity" value="1" min="1" max="99">
                                </div>

                                <button type="submit" class="scp-detail-add-to-cart">
                                    {{ __('storefront.product.add_to_cart') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-detail-secondary-btn">
                            {{ __('storefront.product_details.back_to_products') }}
                        </a>

                        @if(auth()->check() && \Illuminate\Support\Facades\Route::has('storefront.wishlist.toggle'))
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
                        @elseif(\Illuminate\Support\Facades\Route::has('login'))
                            <a href="{{ route('login') }}" class="scp-product-details-wishlist-link">
                                ♡ {{ __('storefront.wishlist.login_required') }}
                            </a>
                        @endif

                        @if(\Illuminate\Support\Facades\Route::has('storefront.compare.add'))
                            <form
                                method="POST"
                                action="{{ route('storefront.compare.add', ['product' => $product->id, 'lang' => $locale]) }}"
                                class="scp-product-details-compare-form"
                            >
                                @csrf

                                <button type="submit">
                                    ⇄ {{ __('storefront.compare.add_to_compare') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>

            <div class="scp-product-content-grid">
                <div class="scp-product-content-card">
                    <h2>{{ __('storefront.product_details.description') }}</h2>

                    @if($description !== '')
                        <div class="scp-rich-text">
                            {!! nl2br(e(strip_tags($description))) !!}
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
                                    <span>{{ $toLocalizedString($key) }}</span>
                                    <strong>{{ $toLocalizedString($value) }}</strong>
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
                                <span>{{ $toLocalizedString($key) }}</span>
                                <strong>{{ $toLocalizedString($value) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($relatedProducts) && $relatedProducts->count() > 0)
                <section class="scp-section">
                    <div class="scp-section-heading">
                        <div>
                            <h2>{{ __('storefront.product_details.related_products') }}</h2>
                            <p>{{ __('storefront.product_details.related_subtitle') }}</p>
                        </div>
                    </div>

                    <div class="scp-product-grid">
                        @foreach($relatedProducts as $relatedProduct)
                            @php
                                $relatedName = method_exists($relatedProduct, 'getName')
                                    ? $toLocalizedString($relatedProduct->getName($locale))
                                    : $toLocalizedString($relatedProduct->name ?? '');
                            @endphp

                            <article class="scp-product-card">
                                @if(\Illuminate\Support\Facades\Route::has('storefront.compare.add'))
                                    <form
                                        method="POST"
                                        action="{{ route('storefront.compare.add', ['product' => $relatedProduct->id, 'lang' => $locale]) }}"
                                        class="scp-product-compare-form"
                                    >
                                        @csrf

                                        <button type="submit" title="{{ __('storefront.compare.add_to_compare') }}">
                                            ⇄
                                        </button>
                                    </form>
                                @endif

                                <a
                                    href="{{ route('storefront.products.show', ['slug' => $relatedProduct->slug, 'lang' => $locale]) }}"
                                    class="scp-product-image"
                                >
                                    @if($productImage($relatedProduct))
                                        <img src="{{ $productImage($relatedProduct) }}" alt="{{ $relatedName }}">
                                    @else
                                        <div class="scp-product-placeholder">
                                            {{ mb_substr($relatedName, 0, 1) }}
                                        </div>
                                    @endif
                                </a>

                                <div class="scp-product-body">
                                    <div class="scp-product-brand">
                                        {{ $relatedProduct->brand?->getName($locale) ?? __('storefront.product.default_brand') }}
                                    </div>

                                    <h3>
                                        <a href="{{ route('storefront.products.show', ['slug' => $relatedProduct->slug, 'lang' => $locale]) }}">
                                            {{ $relatedName }}
                                        </a>
                                    </h3>

                                    @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.rating-summary'))
                                        @include('storefront.products.partials.rating-summary', [
                                            'product' => $relatedProduct,
                                        ])
                                    @endif

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

                                        @if(\Illuminate\Support\Facades\Route::has('storefront.cart.add'))
                                            <form method="POST" action="{{ route('storefront.cart.add') }}" class="scp-card-cart-form">
                                                @csrf

                                                <input type="hidden" name="lang" value="{{ $locale }}">
                                                <input type="hidden" name="product_id" value="{{ $relatedProduct->id }}">
                                                <input type="hidden" name="quantity" value="1">

                                                <button type="submit" class="scp-btn-small primary">
                                                    {{ __('storefront.product.add_to_cart') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.questions'))
                @include('storefront.products.partials.questions', [
                    'product' => $product,
                    'locale' => $locale,
                ])
            @endif

            @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.reviews'))
                @include('storefront.products.partials.reviews', [
                    'product' => $product,
                    'locale' => $locale,
                ])
            @endif

            @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.recently-viewed'))
                @include('storefront.products.partials.recently-viewed', [
                    'recentlyViewedProducts' => $recentlyViewedProducts ?? collect(),
                    'locale' => $locale,
                ])
            @endif

        </div>
    </section>
@endsection
