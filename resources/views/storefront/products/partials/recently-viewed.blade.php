@if(isset($recentlyViewedProducts) && $recentlyViewedProducts->count() > 0)
    @php
        $viewDetailsText = \Illuminate\Support\Facades\Lang::has('storefront.products.view_details')
            ? __('storefront.products.view_details')
            : 'عرض التفاصيل';

        $addToCartText = \Illuminate\Support\Facades\Lang::has('storefront.cart.add_to_cart')
            ? __('storefront.cart.add_to_cart')
            : 'أضف للسلة';

        $wishlistToggleText = \Illuminate\Support\Facades\Lang::has('storefront.wishlist.toggle')
            ? __('storefront.wishlist.toggle')
            : 'إضافة / إزالة من المفضلة';

        $wishlistLoginText = \Illuminate\Support\Facades\Lang::has('storefront.wishlist.login_required')
            ? __('storefront.wishlist.login_required')
            : 'سجّل الدخول لإضافة المنتج للمفضلة';

        $compareText = \Illuminate\Support\Facades\Lang::has('storefront.compare.add_to_compare')
            ? __('storefront.compare.add_to_compare')
            : 'أضف للمقارنة';

        $recentlyViewedTitle = \Illuminate\Support\Facades\Lang::has('storefront.recently_viewed.title')
            ? __('storefront.recently_viewed.title')
            : 'شاهدتها مؤخرًا';

        $recentlyViewedSubtitle = \Illuminate\Support\Facades\Lang::has('storefront.recently_viewed.subtitle')
            ? __('storefront.recently_viewed.subtitle')
            : 'منتجات قمت بزيارتها مؤخرًا لتعود إليها بسرعة.';

        $browseMoreText = \Illuminate\Support\Facades\Lang::has('storefront.recently_viewed.browse_more')
            ? __('storefront.recently_viewed.browse_more')
            : 'تصفح المزيد';

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

        $resolveOldPrice = function ($product) {
            if (! $product) {
                return null;
            }

            foreach (['compare_at_price', 'old_price'] as $column) {
                if (isset($product->{$column}) && $product->{$column} !== null) {
                    return (float) $product->{$column};
                }
            }

            return null;
        };
    @endphp

    <section class="scp-section scp-recently-viewed-section">
        <div class="scp-section-heading">
            <div>
                <h2>{{ $recentlyViewedTitle }}</h2>
                <p>{{ $recentlyViewedSubtitle }}</p>
            </div>

            <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-link-more">
                {{ $browseMoreText }}
            </a>
        </div>

        <div class="scp-product-grid compact">
            @foreach($recentlyViewedProducts as $product)
                @php
                    $productName = $resolveProductName($product);
                    $productImage = $resolveProductImage($product);
                    $productPrice = $resolveProductPrice($product);
                    $oldPrice = $resolveOldPrice($product);
                    $currencySymbol = $product?->currency?->symbol ?? '₪';
                @endphp

                <article class="scp-product-card">
                    @if(auth()->check() && Route::has('storefront.wishlist.toggle'))
                        <form
                            method="POST"
                            action="{{ route('storefront.wishlist.toggle', ['product' => $product->id, 'lang' => $locale]) }}"
                            class="scp-product-wishlist-form"
                        >
                            @csrf

                            <button type="submit" title="{{ $wishlistToggleText }}">
                                ♥
                            </button>
                        </form>
                    @elseif(Route::has('login'))
                        <a href="{{ route('login') }}" class="scp-product-wishlist-form">
                            <button type="button" title="{{ $wishlistLoginText }}">
                                ♡
                            </button>
                        </a>
                    @endif

                    @if(Route::has('storefront.compare.add'))
                        <form
                            method="POST"
                            action="{{ route('storefront.compare.add', ['product' => $product->id, 'lang' => $locale]) }}"
                            class="scp-product-compare-form"
                        >
                            @csrf

                            <button type="submit" title="{{ $compareText }}">
                                ⇄
                            </button>
                        </form>
                    @endif

                    <a
                        href="{{ route('storefront.products.show', ['slug' => $product->slug, 'lang' => $locale]) }}"
                        class="scp-product-image"
                    >
                        @if($productImage)
                            <img src="{{ $productImage }}" alt="{{ $productName }}">
                        @else
                            <div class="scp-product-placeholder">
                                {{ mb_substr($productName, 0, 1) }}
                            </div>
                        @endif

                        @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.badges'))
                            @include('storefront.products.partials.badges', [
                                'product' => $product,
                            ])
                        @endif
                    </a>

                    <div class="scp-product-body">
                        @if($product->brand)
                            <div class="scp-product-brand">
                                {{ method_exists($product->brand, 'getName') ? $product->brand->getName($locale) : ($product->brand->name ?? '') }}
                            </div>
                        @endif

                        <h3>
                            <a href="{{ route('storefront.products.show', ['slug' => $product->slug, 'lang' => $locale]) }}">
                                {{ $productName }}
                            </a>
                        </h3>

                        @if(View::exists('storefront.products.partials.rating-summary'))
                            @include('storefront.products.partials.rating-summary', [
                                'product' => $product,
                            ])
                        @endif

                        <div class="scp-product-price">
                            <strong>
                                {{ $currencySymbol }} {{ number_format($productPrice, 2) }}
                            </strong>

                            @if($oldPrice && $oldPrice > $productPrice)
                                <span>
                                    {{ $currencySymbol }} {{ number_format($oldPrice, 2) }}
                                </span>
                            @endif
                        </div>

                        <div class="scp-product-actions">
                            @if(Route::has('storefront.cart.add'))
                                <form method="POST" action="{{ route('storefront.cart.add') }}" class="scp-card-cart-form">
                                    @csrf

                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="lang" value="{{ $locale }}">

                                    <button type="submit" class="scp-btn-small primary">
                                        {{ $addToCartText }}
                                    </button>
                                </form>
                            @endif

                            <a
                                href="{{ route('storefront.products.show', ['slug' => $product->slug, 'lang' => $locale]) }}"
                                class="scp-btn-small"
                            >
                                {{ $viewDetailsText }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif