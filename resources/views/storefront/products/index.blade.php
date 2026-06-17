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
    @endphp

    <section class="scp-products-hero">
        <div class="scp-container">
            <div class="scp-products-hero-inner">
                <div>
                    <div class="scp-hero-badge">
                        {{ __('storefront.products_page.badge') }}
                    </div>

                    <h1>{{ __('storefront.products_page.title') }}</h1>

                    <p>
                        {{ __('storefront.products_page.subtitle') }}
                    </p>
                </div>

                <div class="scp-products-hero-stats">
                    <div>
                        <strong>{{ $products->total() }}</strong>
                        <span>{{ __('storefront.products_page.results') }}</span>
                    </div>

                    <div>
                        <strong>{{ $categories->count() }}</strong>
                        <span>{{ __('storefront.hero.categories') }}</span>
                    </div>

                    <div>
                        <strong>{{ $brands->count() }}</strong>
                        <span>{{ __('storefront.nav.brands') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="scp-products-section">
        <div class="scp-container">

            <form method="GET" action="{{ route('storefront.products.index') }}" class="scp-products-toolbar">
                <input type="hidden" name="lang" value="{{ $locale }}">

                <div class="scp-products-search">
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="{{ __('storefront.products_page.search_placeholder') }}"
                    >
                </div>

                <select name="category">
                    <option value="">{{ __('storefront.products_page.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $filters['category'] === (string) $category->id)>
                            {{ $category->getName($locale) }}
                        </option>
                    @endforeach
                </select>

                <select name="brand">
                    <option value="">{{ __('storefront.products_page.all_brands') }}</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected((string) $filters['brand'] === (string) $brand->id)>
                            {{ $brand->getName($locale) }}
                        </option>
                    @endforeach
                </select>

                <select name="type">
                    <option value="">{{ __('storefront.products_page.all_types') }}</option>
                    <option value="physical" @selected($filters['type'] === 'physical')>
                        {{ __('storefront.products_page.physical') }}
                    </option>
                    <option value="digital" @selected($filters['type'] === 'digital')>
                        {{ __('storefront.products_page.digital') }}
                    </option>
                    <option value="service" @selected($filters['type'] === 'service')>
                        {{ __('storefront.products_page.service') }}
                    </option>
                </select>

                <select name="sort">
                    <option value="latest" @selected($filters['sort'] === 'latest')>
                        {{ __('storefront.products_page.sort_latest') }}
                    </option>
                    <option value="name_asc" @selected($filters['sort'] === 'name_asc')>
                        {{ __('storefront.products_page.sort_name') }}
                    </option>
                    <option value="price_low" @selected($filters['sort'] === 'price_low')>
                        {{ __('storefront.products_page.sort_price_low') }}
                    </option>
                    <option value="price_high" @selected($filters['sort'] === 'price_high')>
                        {{ __('storefront.products_page.sort_price_high') }}
                    </option>
                </select>

                <button type="submit">
                    {{ __('storefront.products_page.apply') }}
                </button>

                <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}" class="scp-products-reset">
                    {{ __('storefront.products_page.reset') }}
                </a>
            </form>

            <div class="scp-products-layout">
                <aside class="scp-products-sidebar">
                    <div class="scp-sidebar-card">
                        <h3>{{ __('storefront.products_page.categories_filter') }}</h3>

                        <a
                            href="{{ route('storefront.products.index', array_filter(['lang' => $locale, 'q' => $filters['q'], 'brand' => $filters['brand'], 'type' => $filters['type'], 'sort' => $filters['sort']])) }}"
                            class="{{ empty($filters['category']) ? 'active' : '' }}"
                        >
                            {{ __('storefront.products_page.all_categories') }}
                        </a>

                        @foreach($categories as $category)
                            <a
                                href="{{ route('storefront.products.index', array_filter(['lang' => $locale, 'q' => $filters['q'], 'category' => $category->id, 'brand' => $filters['brand'], 'type' => $filters['type'], 'sort' => $filters['sort']])) }}"
                                class="{{ (string) $filters['category'] === (string) $category->id ? 'active' : '' }}"
                            >
                                {{ $category->getName($locale) }}
                            </a>
                        @endforeach
                    </div>

                    <div class="scp-sidebar-card">
                        <h3>{{ __('storefront.products_page.brands_filter') }}</h3>

                        <a
                            href="{{ route('storefront.products.index', array_filter(['lang' => $locale, 'q' => $filters['q'], 'category' => $filters['category'], 'type' => $filters['type'], 'sort' => $filters['sort']])) }}"
                            class="{{ empty($filters['brand']) ? 'active' : '' }}"
                        >
                            {{ __('storefront.products_page.all_brands') }}
                        </a>

                        @foreach($brands as $brand)
                            <a
                                href="{{ route('storefront.products.index', array_filter(['lang' => $locale, 'q' => $filters['q'], 'category' => $filters['category'], 'brand' => $brand->id, 'type' => $filters['type'], 'sort' => $filters['sort']])) }}"
                                class="{{ (string) $filters['brand'] === (string) $brand->id ? 'active' : '' }}"
                            >
                                {{ $brand->getName($locale) }}
                            </a>
                        @endforeach
                    </div>
                </aside>

                <div class="scp-products-main">
                    <div class="scp-products-summary">
                        <div>
                            <strong>{{ $products->total() }}</strong>
                            <span>{{ __('storefront.products_page.products_found') }}</span>
                        </div>

                        @if($filters['q'])
                            <div>
                                {{ __('storefront.products_page.searching_for') }}
                                <strong>"{{ $filters['q'] }}"</strong>
                            </div>
                        @endif
                    </div>

                    <div class="scp-product-grid">
                        @forelse($products as $product)
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
                                @if(auth()->check())
                                    <form
                                        method="POST"
                                        action="{{ route('storefront.wishlist.toggle', ['product' => $product->id, 'lang' => $locale]) }}"
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

                                    @if($productTypeValue($product) === 'digital')
                                        <span class="scp-product-type-badge">
                                            {{ __('storefront.products_page.digital') }}
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
                            <div class="scp-empty scp-products-empty">
                                {{ __('storefront.products_page.no_products') }}
                            </div>
                        @endforelse
                    </div>

                    <div class="scp-pagination">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection