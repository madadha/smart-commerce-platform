@extends('storefront.layout')

@section('content')
    @php
        $productImage = function ($product) {
            return method_exists($product, 'getImageUrl') ? $product->getImageUrl() : null;
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

        $stockValue = null;

        foreach (['stock_quantity', 'quantity', 'stock'] as $stockColumn) {
            if (isset($product->{$stockColumn}) && $product->{$stockColumn} !== null) {
                $stockValue = (int) $product->{$stockColumn};
                break;
            }
        }

        $galleryImages = collect();

        if ($productImage($product)) {
            $galleryImages->push([
                'url' => $productImage($product),
                'alt' => $product->getName($locale),
            ]);
        }

        if ($product->relationLoaded('media')) {
            foreach ($product->media as $mediaItem) {
                $mediaUrl = method_exists($mediaItem, 'getUrl') ? $mediaItem->getUrl() : null;

                if ($mediaUrl) {
                    $galleryImages->push([
                        'url' => $mediaUrl,
                        'alt' => method_exists($mediaItem, 'getAltText')
                            ? $mediaItem->getAltText($locale)
                            : $product->getName($locale),
                    ]);
                }
            }
        }

        $galleryImages = $galleryImages->unique('url')->values();
        $activeVariants = $product->variants->where('is_active', true)->values();
        $selectedVariant = $activeVariants->firstWhere('is_default', true) ?? $activeVariants->first();
        $selectedStockOwner = $selectedVariant ?: $product;
        $selectedStock = $selectedStockOwner->track_stock ? (int) $selectedStockOwner->stock_quantity : null;
        $isOutOfStock = $selectedStock !== null && $selectedStock <= 0;

        $variantPayload = $activeVariants->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->getName($locale),
            'sku' => $variant->sku,
            'options' => $variant->getOptionValues(),
            'price' => $variant->finalPrice(),
            'regular_price' => $variant->price ? (float) $variant->price : (float) $product->price,
            'image' => $variant->getImageUrl(),
            'in_stock' => $variant->isInStock(),
            'stock' => $variant->track_stock ? (int) $variant->stock_quantity : null,
        ])->values();
        $youtubeEmbedUrl = method_exists($product, 'getYouTubeEmbedUrl') ? $product->getYouTubeEmbedUrl() : null;
        $storefrontSettings = $storefrontSettings ?? \App\Models\StorefrontSetting::current();
        $gameTopUpsEnabled = $storefrontSettings?->enable_game_topups ?? true;
        $isGameTopUp = $productTypeValue($product) === 'game_topup';
        $availableGameRegions = $isGameTopUp && method_exists($product, 'availableGameRegions')
            ? $product->availableGameRegions()
            : collect();
        $gameServerOptions = $isGameTopUp && method_exists($product, 'gameServerOptions')
            ? $product->gameServerOptions()
            : [];
        $localizedProductField = function ($value, string $fallback = '') use ($locale): string {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                $value = json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : $value;
            }

            if (is_array($value)) {
                return (string) ($value[$locale] ?? $value['ar'] ?? $value['en'] ?? $value['he'] ?? $fallback);
            }

            return (string) ($value ?: $fallback);
        };
        $gameTitle = $product->game
            ? $product->game->getName($locale)
            : $localizedProductField($product->game_title, $product->getName($locale));
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
                        @if($galleryImages->isNotEmpty())
                            <img
                                src="{{ $galleryImages->first()['url'] }}"
                                alt="{{ $galleryImages->first()['alt'] }}"
                                data-scp-product-main-image
                            >
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
                        @foreach($galleryImages as $index => $galleryImage)
                            <button
                                type="button"
                                class="scp-product-thumb {{ $index === 0 ? 'active' : '' }}"
                                data-scp-product-thumb
                                data-image="{{ $galleryImage['url'] }}"
                                data-alt="{{ $galleryImage['alt'] }}"
                                aria-label="{{ __('storefront.product_details.view_image', ['number' => $index + 1]) }}"
                            >
                                <img src="{{ $galleryImage['url'] }}" alt="{{ $galleryImage['alt'] }}">
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="scp-product-info">
                    <div class="scp-product-meta-line">
                        <span>{{ $product->brand?->getName($locale) ?? __('storefront.product.default_brand') }}</span>

                        @if(! empty($product->sku) || $selectedVariant?->sku)
                            <span>SKU: <b data-scp-product-sku>{{ $selectedVariant?->sku ?? $product->sku }}</b></span>
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

                    <div class="scp-product-detail-price" data-scp-product-price>
                        <strong data-scp-current-price>
                            {{ $product->currency?->symbol ?? '₪' }}
                            {{ number_format((float) ($selectedVariant?->finalPrice() ?? $productPrice($product)), 2) }}
                        </strong>

                        @php
                            $displayRegularPrice = $selectedVariant?->price ?: $product->price;
                            $displayCurrentPrice = $selectedVariant?->finalPrice() ?? $productPrice($product);
                        @endphp
                        @if((float) $displayRegularPrice > (float) $displayCurrentPrice)
                            <span data-scp-regular-price>
                                {{ $product->currency?->symbol ?? '₪' }}
                                {{ number_format((float) $displayRegularPrice, 2) }}
                            </span>
                        @else
                            <span data-scp-regular-price hidden></span>
                        @endif
                    </div>

                    @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.stock-status'))
                        @include('storefront.products.partials.stock-status', [
                            'product' => $product,
                        ])
                    @endif

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

                    @if($activeVariants->isNotEmpty())
                        <div class="scp-product-variants" data-scp-product-configurator>
                            <strong>{{ __('storefront.product_details.variants') }}</strong>

                            @if($product->options->isNotEmpty())
                                <div class="scp-option-groups">
                                    @foreach($product->options as $option)
                                        <fieldset class="scp-option-group" data-option-slug="{{ $option->slug }}">
                                            <legend>{{ $option->getName($locale) }}</legend>
                                            <div class="scp-option-values">
                                                @foreach($option->getValues() as $optionValue)
                                                    @php
                                                        $rawValue = (string) ($optionValue['value'] ?? $optionValue['en'] ?? $optionValue['ar'] ?? '');
                                                        $optionLabel = $optionValue[$locale] ?? $optionValue['en'] ?? $optionValue['ar'] ?? $rawValue;
                                                    @endphp
                                                    <button type="button" class="scp-option-value" data-option-value="{{ $rawValue }}" @if(! empty($optionValue['color'])) style="--option-color: {{ $optionValue['color'] }}" @endif>
                                                        @if(! empty($optionValue['color']))<i aria-hidden="true"></i>@endif
                                                        {{ $optionLabel }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </fieldset>
                                    @endforeach
                                </div>
                            @endif

                            <div class="scp-variant-list">
                                @foreach($activeVariants as $variant)
                                    <button type="button" class="scp-variant-card {{ $selectedVariant?->id === $variant->id ? 'active' : '' }}" data-scp-variant-id="{{ $variant->id }}" @disabled(! $variant->isInStock())>
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
                                        @if(! $variant->isInStock())<em>{{ __('storefront.stock.out_of_stock') }}</em>@endif
                                    </button>
                                @endforeach
                            </div>

                            <p class="scp-variant-selection-status" data-scp-variant-status>
                                {{ __('storefront.product_details.selected_variant') }}:
                                <strong>{{ $selectedVariant?->getName($locale) }}</strong>
                            </p>
                        </div>
                    @endif

<div class="scp-product-detail-actions">
    <form method="POST" action="{{ route('storefront.cart.add') }}" class="scp-detail-cart-form">
        @csrf

        <input type="hidden" name="lang" value="{{ $locale }}">
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="product_variant_id" value="{{ $selectedVariant?->id }}" data-scp-selected-variant>

        @if($isGameTopUp)
            <div class="scp-game-topup-box">
                <div>
                    <span>{{ __('storefront.game_topup.badge') }}</span>
                    <strong>{{ $gameTitle }}</strong>
                    @if($localizedProductField($product->game_currency_name))
                        <small>{{ __('storefront.game_topup.currency') }}: {{ $localizedProductField($product->game_currency_name) }}</small>
                    @endif
                </div>

                @if($localizedProductField($product->game_topup_instructions))
                    <p>{{ $localizedProductField($product->game_topup_instructions) }}</p>
                @else
                    <p>{{ __('storefront.game_topup.default_instructions') }}</p>
                @endif

                @if(! $gameTopUpsEnabled)
                    <div class="scp-game-topup-disabled">
                        {{ __('storefront.game_topup.disabled') }}
                    </div>
                @else
                    <div class="scp-game-topup-fields">
                        @if($product->game_requires_player_id)
                            <label>
                                <span>{{ $localizedProductField($product->game_player_id_label, __('storefront.game_topup.player_id')) }}</span>
                                <input
                                    type="text"
                                    name="game_player_id"
                                    value="{{ old('game_player_id') }}"
                                    placeholder="{{ __('storefront.game_topup.player_id_placeholder') }}"
                                    required
                                >
                            </label>
                        @endif

                        @if($product->game_requires_region)
                            <label>
                                <span>{{ $localizedProductField($product->game_region_label, __('storefront.game_topup.region')) }}</span>
                                @if($availableGameRegions->isNotEmpty())
                                    <select name="game_region_id" required>
                                        <option value="">{{ __('storefront.game_topup.region_placeholder') }}</option>
                                        @foreach($availableGameRegions as $region)
                                            <option value="{{ $region->id }}" @selected((string) old('game_region_id') === (string) $region->id)>
                                                {{ $region->getName($locale) }}{{ $region->code ? ' - '.$region->code : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        type="text"
                                        name="game_region"
                                        value="{{ old('game_region') }}"
                                        placeholder="{{ __('storefront.game_topup.region_placeholder') }}"
                                        required
                                    >
                                @endif
                            </label>
                        @endif

                        @if($product->game_requires_server)
                            <label>
                                <span>{{ $localizedProductField($product->game_server_label, __('storefront.game_topup.server')) }}</span>
                                @if(! empty($gameServerOptions))
                                    <select name="game_server_key" required>
                                        <option value="">{{ __('storefront.game_topup.server_placeholder') }}</option>
                                        @foreach($gameServerOptions as $serverKey => $serverLabel)
                                            <option value="{{ $serverKey }}" @selected((string) old('game_server_key') === (string) $serverKey)>
                                                {{ $serverLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        type="text"
                                        name="game_server"
                                        value="{{ old('game_server') }}"
                                        placeholder="{{ __('storefront.game_topup.server_placeholder') }}"
                                        required
                                    >
                                @endif
                            </label>
                        @endif
                    </div>

                    @if($product->game_can_validate_player)
                        <div class="scp-game-topup-validation-note">
                            {{ __('storefront.game_topup.validation_available') }}
                        </div>
                    @else
                        <div class="scp-game-topup-validation-note">
                            {{ __('storefront.game_topup.manual_check_note') }}
                        </div>
                    @endif
                @endif
            </div>
        @endif

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

        <button
            type="submit"
            class="scp-detail-add-to-cart"
            data-scp-add-to-cart
            @disabled($isOutOfStock || ($isGameTopUp && ! $gameTopUpsEnabled))
        >
            {{ $isOutOfStock ? (\Illuminate\Support\Facades\Lang::has('storefront.stock.out_of_stock') ? __('storefront.stock.out_of_stock') : 'نفذ المخزون') : __('storefront.product.add_to_cart') }}
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

            @if($youtubeEmbedUrl)
                <section class="scp-product-video-card" aria-labelledby="scp-product-video-title">
                    <div class="scp-product-video-copy">
                        <span>{{ __('storefront.product_details.video_badge') }}</span>
                        <h2 id="scp-product-video-title">{{ __('storefront.product_details.video_title') }}</h2>
                        <p>{{ __('storefront.product_details.video_description') }}</p>
                    </div>
                    <div class="scp-product-video-frame">
                        <iframe
                            src="{{ $youtubeEmbedUrl }}"
                            title="{{ $product->getName($locale) }} — {{ __('storefront.product_details.video_title') }}"
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                </section>
            @endif

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

                                @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.rating-summary'))
                                    @include('storefront.products.partials.rating-summary', [
                                        'product' => $relatedProduct,
                                    ])
                                @endif

                                @if(\Illuminate\Support\Facades\View::exists('storefront.products.partials.stock-status'))
                                    @include('storefront.products.partials.stock-status', [
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var mainImage = document.querySelector('[data-scp-product-main-image]');
            var thumbnails = Array.from(document.querySelectorAll('[data-scp-product-thumb]'));

            thumbnails.forEach(function (thumbnail) {
                thumbnail.addEventListener('click', function () {
                    if (! mainImage) return;
                    mainImage.src = thumbnail.dataset.image;
                    mainImage.alt = thumbnail.dataset.alt || '';
                    thumbnails.forEach(function (item) { item.classList.toggle('active', item === thumbnail); });
                });
            });

            var variants = @json($variantPayload);
            if (! variants.length) return;

            var currency = @json($product->currency?->symbol ?? '');
            var hiddenVariant = document.querySelector('[data-scp-selected-variant]');
            var currentPrice = document.querySelector('[data-scp-current-price]');
            var regularPrice = document.querySelector('[data-scp-regular-price]');
            var sku = document.querySelector('[data-scp-product-sku]');
            var addToCart = document.querySelector('[data-scp-add-to-cart]');
            var status = document.querySelector('[data-scp-variant-status] strong');
            var variantButtons = Array.from(document.querySelectorAll('[data-scp-variant-id]'));
            var optionGroups = Array.from(document.querySelectorAll('[data-option-slug]'));
            var selectedOptions = {};

            function formatPrice(value) {
                return [currency, Number(value || 0).toFixed(2)].filter(Boolean).join(' ');
            }

            function applyVariant(variant) {
                if (! variant) return;

                hiddenVariant.value = variant.id;
                if (currentPrice) currentPrice.textContent = formatPrice(variant.price);
                if (regularPrice) {
                    regularPrice.textContent = formatPrice(variant.regular_price);
                    regularPrice.hidden = Number(variant.regular_price) <= Number(variant.price);
                }
                if (sku) sku.textContent = variant.sku || '-';
                if (status) status.textContent = variant.name;
                if (addToCart) addToCart.disabled = ! variant.in_stock;

                variantButtons.forEach(function (button) {
                    button.classList.toggle('active', Number(button.dataset.scpVariantId) === Number(variant.id));
                });

                selectedOptions = Object.assign({}, variant.options || {});
                optionGroups.forEach(function (group) {
                    var value = selectedOptions[group.dataset.optionSlug];
                    group.querySelectorAll('[data-option-value]').forEach(function (button) {
                        button.classList.toggle('active', button.dataset.optionValue === String(value));
                    });
                });

                if (variant.image && mainImage) {
                    mainImage.src = variant.image;
                    mainImage.alt = variant.name;
                    thumbnails.forEach(function (item) {
                        item.classList.toggle('active', item.dataset.image === variant.image);
                    });
                }
            }

            variantButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    applyVariant(variants.find(function (variant) {
                        return Number(variant.id) === Number(button.dataset.scpVariantId);
                    }));
                });
            });

            optionGroups.forEach(function (group) {
                group.querySelectorAll('[data-option-value]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        selectedOptions[group.dataset.optionSlug] = button.dataset.optionValue;
                        var exact = variants.find(function (variant) {
                            return Object.keys(selectedOptions).every(function (key) {
                                return String((variant.options || {})[key]) === String(selectedOptions[key]);
                            });
                        });
                        applyVariant(exact);
                    });
                });
            });

            applyVariant(variants.find(function (variant) {
                return Number(variant.id) === Number(hiddenVariant.value);
            }) || variants[0]);
        });
    </script>

@endsection
