@extends('storefront.layout')

@section('content')
    @php
        $resolveProductName = function ($product) use ($locale) {
            if (method_exists($product, 'getName')) {
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
            if (! empty($product->main_image)) {
                return asset('storage/' . ltrim($product->main_image, '/'));
            }

            if (! empty($product->image)) {
                return asset('storage/' . ltrim($product->image, '/'));
            }

            return null;
        };

        $resolveProductPrice = function ($product) {
            foreach (['sale_price', 'price', 'regular_price'] as $column) {
                if (isset($product->{$column}) && $product->{$column} !== null) {
                    return (float) $product->{$column};
                }
            }

            return 0;
        };

        $resolveStock = function ($product) {
            foreach (['stock_quantity', 'quantity', 'stock'] as $column) {
                if (isset($product->{$column}) && $product->{$column} !== null) {
                    return $product->{$column};
                }
            }

            return '-';
        };

        $resolveType = function ($product) {
            return $product->product_type ?? $product->type ?? '-';
        };

        $compareRows = [
            'price' => __('storefront.compare.price'),
            'brand' => __('storefront.compare.brand'),
            'type' => __('storefront.compare.product_type'),
            'sku' => __('storefront.compare.sku'),
            'stock' => __('storefront.compare.stock'),
            'rating' => __('storefront.compare.rating'),
            'categories' => __('storefront.compare.categories'),
        ];
    @endphp

    <section class="scp-compare-page">
        <div class="scp-container">

            <div class="scp-compare-hero">
                <div>
                    <span class="scp-compare-badge">
                        {{ __('storefront.compare.badge') }}
                    </span>

                    <h1>{{ __('storefront.compare.page_title') }}</h1>

                    <p>{{ __('storefront.compare.page_description') }}</p>
                </div>

                <div class="scp-compare-count-card">
                    <span>{{ __('storefront.compare.total_items') }}</span>
                    <strong>{{ $compareCount }}/{{ $maxCompareItems }}</strong>
                </div>
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

            @if($products->count() > 0)
                <div class="scp-compare-actions-top">
                    <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}">
                        {{ __('storefront.compare.add_more_products') }}
                    </a>

                    <form method="POST" action="{{ route('storefront.compare.clear', ['lang' => $locale]) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit">
                            {{ __('storefront.compare.clear_all') }}
                        </button>
                    </form>
                </div>

                <div class="scp-compare-table-card">
                    <div class="scp-compare-table-wrap">
                        <table class="scp-compare-table">
                            <thead>
                                <tr>
                                    <th>{{ __('storefront.compare.feature') }}</th>

                                    @foreach($products as $product)
                                        @php
                                            $productName = $resolveProductName($product);
                                            $productImage = $resolveProductImage($product);
                                        @endphp

                                        <th>
                                            <div class="scp-compare-product-head">
                                                <form
                                                    method="POST"
                                                    action="{{ route('storefront.compare.remove', ['product' => $product->id, 'lang' => $locale]) }}"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit">×</button>
                                                </form>

                                                <a href="{{ route('storefront.products.show', ['slug' => $product->slug, 'lang' => $locale]) }}">
                                                    <div class="scp-compare-product-image">
                                                        @if($productImage)
                                                            <img src="{{ $productImage }}" alt="{{ $productName }}">
                                                        @else
                                                            <span>{{ mb_substr($productName, 0, 1) }}</span>
                                                        @endif
                                                    </div>

                                                    <strong>{{ $productName }}</strong>
                                                </a>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($compareRows as $key => $label)
                                    <tr>
                                        <td class="scp-compare-feature-name">
                                            {{ $label }}
                                        </td>

                                        @foreach($products as $product)
                                            <td>
                                                @if($key === 'price')
                                                    {{ $product?->currency?->symbol ?? '₪' }}
                                                    {{ number_format($resolveProductPrice($product), 2) }}

                                                @elseif($key === 'brand')
                                                    {{ $product?->brand?->getName($locale) ?? '-' }}

                                                @elseif($key === 'type')
                                                    {{ $resolveType($product) }}

                                                @elseif($key === 'sku')
                                                    {{ $product->sku ?? '-' }}

                                                @elseif($key === 'stock')
                                                    {{ $resolveStock($product) }}

                                                @elseif($key === 'rating')
                                                    @include('storefront.products.partials.rating-summary', [
                                                        'product' => $product,
                                                    ])

                                                @elseif($key === 'categories')
                                                    @if($product->categories && $product->categories->count() > 0)
                                                        <div class="scp-compare-categories">
                                                            @foreach($product->categories->take(4) as $category)
                                                                <span>{{ $category->getName($locale) }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach

                                <tr>
                                    <td class="scp-compare-feature-name">
                                        {{ __('storefront.compare.actions') }}
                                    </td>

                                    @foreach($products as $product)
                                        <td>
                                            <div class="scp-compare-row-actions">
                                                <a href="{{ route('storefront.products.show', ['slug' => $product->slug, 'lang' => $locale]) }}">
                                                    {{ __('storefront.compare.view_product') }}
                                                </a>

                                                <form method="POST" action="{{ route('storefront.cart.add') }}">
                                                    @csrf

                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <input type="hidden" name="lang" value="{{ $locale }}">

                                                    <button type="submit">
                                                        {{ __('storefront.compare.add_to_cart') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="scp-compare-empty">
                    <div>⇄</div>

                    <h2>{{ __('storefront.compare.empty_title') }}</h2>

                    <p>{{ __('storefront.compare.empty_text') }}</p>

                    <a href="{{ route('storefront.products.index', ['lang' => $locale]) }}">
                        {{ __('storefront.compare.browse_products') }}
                    </a>
                </div>
            @endif

        </div>
    </section>
@endsection