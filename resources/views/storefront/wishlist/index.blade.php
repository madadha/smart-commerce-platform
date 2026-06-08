@extends('storefront.layout')

@section('content')
    @php
        $resolveProductName = function ($product) use ($locale) {
            if ($product && method_exists($product, 'getName')) {
                return $product->getName($locale);
            }

            $name = $product->name ?? null;

            if (is_array($name)) {
                return $name[$locale] ?? $name['ar'] ?? $name['en'] ?? reset($name);
            }

            if (is_string($name)) {
                $decoded = json_decode($name, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded[$locale] ?? $decoded['ar'] ?? $decoded['en'] ?? reset($decoded);
                }

                return $name;
            }

            return '-';
        };

        $resolveProductDescription = function ($product) use ($locale) {
            if (! $product) {
                return '';
            }

            if (method_exists($product, 'getDescription')) {
                return $product->getDescription($locale);
            }

            $description = $product->description ?? null;

            if (is_array($description)) {
                return $description[$locale] ?? $description['ar'] ?? $description['en'] ?? reset($description);
            }

            if (is_string($description)) {
                $decoded = json_decode($description, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded[$locale] ?? $decoded['ar'] ?? $decoded['en'] ?? reset($decoded);
                }

                return $description;
            }

            return '';
        };

        $resolveProductImage = function ($product) {
            if (! $product) {
                return null;
            }

            if (! empty($product->main_image)) {
                return asset('storage/' . ltrim($product->main_image, '/'));
            }

            if (! empty($product->image)) {
                return asset('storage/' . ltrim($product->image, '/'));
            }

            return null;
        };

        $resolveProductPrice = function ($product) {
            if (! $product) {
                return 0;
            }

            foreach (['sale_price', 'price', 'regular_price'] as $column) {
                if (isset($product->{$column}) && $product->{$column} !== null) {
                    return (float) $product->{$column};
                }
            }

            return 0;
        };
    @endphp

    <section class="scp-wishlist-page">
        <div class="scp-container">

            <div class="scp-wishlist-hero">
                <div>
                    <span class="scp-wishlist-badge">
                        {{ __('storefront.wishlist.badge') }}
                    </span>

                    <h1>{{ __('storefront.wishlist.page_title') }}</h1>

                    <p>{{ __('storefront.wishlist.page_description') }}</p>
                </div>

                <div class="scp-wishlist-count-card">
                    <span>{{ __('storefront.wishlist.total_items') }}</span>
                    <strong>{{ $wishlistItems->total() }}</strong>
                </div>
            </div>

            @if($wishlistItems->count() > 0)
                <div class="scp-wishlist-grid">
                    @foreach($wishlistItems as $wishlistItem)
                        @php
                            $product = $wishlistItem->product;
                            $productName = $resolveProductName($product);
                            $productDescription = $resolveProductDescription($product);
                            $productImage = $resolveProductImage($product);
                            $productPrice = $resolveProductPrice($product);
                            $currencySymbol = $product?->currency?->symbol ?? '₪';
                        @endphp

                        @if($product)
                            <article class="scp-wishlist-card">
                                <form
                                    method="POST"
                                    action="{{ route('storefront.wishlist.destroy', ['product' => $product->id, 'lang' => $locale]) }}"
                                    class="scp-wishlist-remove-form"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" title="{{ __('storefront.wishlist.remove') }}">
                                        ♥
                                    </button>
                                </form>

                                <a
                                    href="{{ route('storefront.products.show', ['slug' => $product->slug, 'lang' => $locale]) }}"
                                    class="scp-wishlist-image"
                                >
                                    @if($productImage)
                                        <img src="{{ $productImage }}" alt="{{ $productName }}">
                                    @else
                                        <div class="scp-wishlist-placeholder">
                                            {{ mb_substr($productName, 0, 1) }}
                                        </div>
                                    @endif
                                </a>

                                <div class="scp-wishlist-content">
                                    <div class="scp-wishlist-meta">
                                        @if($product->brand)
                                            <span>{{ $product->brand->getName($locale) ?? '-' }}</span>
                                        @else
                                            <span>{{ __('storefront.wishlist.saved_item') }}</span>
                                        @endif

                                        @if(! empty($product->sku))
                                            <small>SKU: {{ $product->sku }}</small>
                                        @endif
                                    </div>

                                    <h2>
                                        <a href="{{ route('storefront.products.show', ['slug' => $product->slug, 'lang' => $locale]) }}">
                                            {{ $productName }}
                                        </a>
                                    </h2>

                                    @if($productDescription)
                                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($productDescription), 110) }}</p>
                                    @endif

                                    <div class="scp-wishlist-footer">
                                        <strong>
                                            {{ $currencySymbol }} {{ number_format($productPrice, 2) }}
                                        </strong>

                                        <form method="POST" action="{{ route('storefront.cart.add') }}">
                                            @csrf

                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="lang" value="{{ $locale }}">

                                            <button type="submit">
                                                {{ __('storefront.wishlist.add_to_cart') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>

                <div class="scp-wishlist-pagination">
                    {{ $wishlistItems->links() }}
                </div>
            @else
                <div class="scp-wishlist-empty">
                    <div class="scp-wishlist-empty-icon">♡</div>

                    <h2>{{ __('storefront.wishlist.empty_title') }}</h2>

                    <p>{{ __('storefront.wishlist.empty_text') }}</p>

                    <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}">
                        {{ __('storefront.wishlist.browse_products') }}
                    </a>
                </div>
            @endif

        </div>
    </section>
@endsection