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


    @php
        $storefrontSettings = $storefrontSettings ?? (\App\Models\StorefrontSetting::current());
        $storefrontSlides = $storefrontSlides ?? collect();
        $homePromotions = $homePromotions ?? collect();
        $midPromotions = $midPromotions ?? collect();
        $homeAdSlides = $homeAdSlides ?? collect();
        $homeAdTiles = $homeAdTiles ?? collect();
        $featuredGames = $featuredGames ?? collect();
        $showCategoriesSection = $storefrontSettings?->show_categories_section ?? true;
        $showFeaturedSection = $storefrontSettings?->show_featured_section ?? true;
        $showLatestSection = $storefrontSettings?->show_latest_section ?? true;
        $showBrandsSection = $storefrontSettings?->show_brands_section ?? true;

        $settingText = function (string $field, string $fallback) use ($storefrontSettings, $locale) {
            return $storefrontSettings?->localized($field, $locale, $fallback) ?: $fallback;
        };

        $resolveStoreUrl = function (?string $url) use ($locale) {
            if (empty($url)) {
                return route('storefront.products.index', ['lang' => $locale ?? 'ar']);
            }

            if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) {
                return $url;
            }

            return url($url) . (str_contains($url, '?') ? '&' : '?') . 'lang=' . ($locale ?? 'ar');
        };
    @endphp

    <section class="scp-hero-section">
        <div class="scp-container">
            @if($storefrontSlides->isNotEmpty())
                <div class="scp-dynamic-slider" data-scp-slider data-autoplay="6000">
                    @foreach($storefrontSlides as $slideIndex => $slide)
                        <article class="scp-dynamic-slide {{ $slideIndex === 0 ? 'is-active' : '' }}" data-scp-slide>
                            <div class="scp-dynamic-slide-content">
                                <div class="scp-hero-badge">
                                    {{ $slide->localized('badge', $locale, __('storefront.hero.badge')) }}
                                </div>

                                <h1>
                                    {{ $slide->localized('title', $locale, __('storefront.hero.title')) }}
                                </h1>

                                <p>
                                    {{ $slide->localized('description', $locale, __('storefront.hero.text')) }}
                                </p>

                                <div class="scp-hero-buttons">
                                    <a href="{{ $resolveStoreUrl($slide->primary_button_url) }}" class="scp-btn scp-btn-primary">
                                        {{ $slide->localized('primary_button_text', $locale, __('storefront.hero.shop_now')) }}
                                    </a>

                                    <a href="{{ $resolveStoreUrl($slide->secondary_button_url) }}" class="scp-btn scp-btn-light">
                                        {{ $slide->localized('secondary_button_text', $locale, __('storefront.hero.view_deals')) }}
                                    </a>
                                </div>
                            </div>

                            <div class="scp-dynamic-slide-media">
                                @if($slide->imageUrl())
                                    <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->localized('title', $locale, __('storefront.hero.title')) }}">
                                @else
                                    <div class="scp-showcase-card main">
                                        <span>{{ __('storefront.hero.showcase_small') }}</span>
                                        <strong>{{ __('storefront.hero.showcase_title') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach

                    @if($storefrontSlides->count() > 1)
                        <button type="button" class="scp-slider-arrow scp-slider-prev" data-scp-prev aria-label="Previous slide">‹</button>
                        <button type="button" class="scp-slider-arrow scp-slider-next" data-scp-next aria-label="Next slide">›</button>

                        <div class="scp-slider-dots" aria-label="Slider navigation">
                            @foreach($storefrontSlides as $slideIndex => $slide)
                                <button
                                    type="button"
                                    class="scp-slider-dot {{ $slideIndex === 0 ? 'is-active' : '' }}"
                                    data-scp-dot="{{ $slideIndex }}"
                                    aria-label="Go to slide {{ $slideIndex + 1 }}"
                                ></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="scp-hero-layout">
                    <div class="scp-hero-content">
                        <div class="scp-hero-badge">
                            {{ $settingText('hero_badge', __('storefront.hero.badge')) }}
                        </div>

                        <h1>
                            {{ $settingText('hero_title', __('storefront.hero.title')) }}
                        </h1>

                        <p>
                            {{ $settingText('hero_text', __('storefront.hero.text')) }}
                        </p>

                        <div class="scp-hero-buttons">
                            <a href="{{ $resolveStoreUrl($storefrontSettings?->hero_primary_button_url ?: '/store/products') }}" class="scp-btn scp-btn-primary">
                                {{ $settingText('hero_primary_button_text', __('storefront.hero.shop_now')) }}
                            </a>

                            <a href="{{ $resolveStoreUrl($storefrontSettings?->hero_secondary_button_url ?: '/store/products?on_sale=1') }}" class="scp-btn scp-btn-light">
                                {{ $settingText('hero_secondary_button_text', __('storefront.hero.view_deals')) }}
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
            @endif
        </div>
    </section>

    @if($homeAdSlides->isNotEmpty() || $homeAdTiles->isNotEmpty())
        <section class="scp-products-ad-showcase" data-scp-home-ads>
            <div class="scp-container">
                @if($homeAdSlides->isNotEmpty())
                    <div class="scp-products-ad-slider">
                        <div class="scp-products-ad-track" data-scp-home-ad-track>
                            @foreach($homeAdSlides as $promotion)
                                <a
                                    href="{{ $resolveStoreUrl($promotion->button_url) }}"
                                    class="scp-products-ad-slide"
                                    @if($promotion->background_color || $promotion->text_color)
                                        style="{{ $promotion->background_color ? '--scp-ad-bg: '.$promotion->background_color.';' : '' }} {{ $promotion->text_color ? '--scp-ad-text: '.$promotion->text_color.';' : '' }}"
                                    @endif
                                >
                                    @if($promotion->imageUrl())
                                        <img src="{{ $promotion->imageUrl() }}" alt="{{ $promotion->localized('title', $locale) }}">
                                    @endif

                                    <span class="scp-products-ad-overlay">
                                        @if($promotion->localized('eyebrow', $locale))
                                            <small>{{ $promotion->localized('eyebrow', $locale) }}</small>
                                        @endif

                                        <strong>{{ $promotion->localized('title', $locale) }}</strong>

                                        @if($promotion->localized('description', $locale))
                                            <em>{{ $promotion->localized('description', $locale) }}</em>
                                        @endif

                                        <b>
                                            {{ $promotion->localized('button_text', $locale, __('storefront.sections.view_all')) }}
                                            {{ $direction === 'rtl' ? '<' : '>' }}
                                        </b>
                                    </span>
                                </a>
                            @endforeach
                        </div>

                        @if($homeAdSlides->count() > 1)
                            <button type="button" class="scp-products-ad-nav prev" data-scp-home-ad-prev aria-label="Previous ad">‹</button>
                            <button type="button" class="scp-products-ad-nav next" data-scp-home-ad-next aria-label="Next ad">›</button>
                        @endif
                    </div>
                @endif

                @if($homeAdTiles->isNotEmpty())
                    <div class="scp-products-ad-tiles">
                        @foreach($homeAdTiles as $promotion)
                            <a
                                href="{{ $resolveStoreUrl($promotion->button_url) }}"
                                class="scp-products-ad-tile"
                                @if($promotion->background_color || $promotion->text_color)
                                    style="{{ $promotion->background_color ? '--scp-ad-bg: '.$promotion->background_color.';' : '' }} {{ $promotion->text_color ? '--scp-ad-text: '.$promotion->text_color.';' : '' }}"
                                @endif
                            >
                                @if($promotion->imageUrl())
                                    <img src="{{ $promotion->imageUrl() }}" alt="{{ $promotion->localized('title', $locale) }}">
                                @endif

                                <span>
                                    <strong>{{ $promotion->localized('title', $locale) }}</strong>

                                    @if($promotion->localized('button_text', $locale))
                                        <small>{{ $promotion->localized('button_text', $locale) }}</small>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if($homePromotions->isNotEmpty())
        <section class="scp-promo-section">
            <div class="scp-container">
                <div class="scp-promo-grid">
                    @foreach($homePromotions as $promotion)
                        <a
                            href="{{ $resolveStoreUrl($promotion->button_url) }}"
                            class="scp-promo-card scp-promo-{{ $promotion->style ?: 'gradient' }}"
                            @if($promotion->background_color || $promotion->text_color)
                                style="{{ $promotion->background_color ? '--scp-promo-bg: '.$promotion->background_color.';' : '' }} {{ $promotion->text_color ? '--scp-promo-text: '.$promotion->text_color.';' : '' }}"
                            @endif
                        >
                            <div class="scp-promo-content">
                                @if($promotion->localized('eyebrow', $locale))
                                    <span>{{ $promotion->localized('eyebrow', $locale) }}</span>
                                @endif

                                <h2>{{ $promotion->localized('title', $locale) }}</h2>

                                @if($promotion->localized('description', $locale))
                                    <p>{{ $promotion->localized('description', $locale) }}</p>
                                @endif

                                <strong>
                                    {{ $promotion->localized('button_text', $locale, __('storefront.sections.view_all')) }}
                                    {{ $direction === 'rtl' ? '←' : '→' }}
                                </strong>
                            </div>

                            @if($promotion->imageUrl())
                                <div class="scp-promo-media">
                                    <img src="{{ $promotion->imageUrl() }}" alt="{{ $promotion->localized('title', $locale) }}">
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($showCategoriesSection)
    <section class="scp-section">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ $settingText('categories_section_title', __('storefront.sections.categories')) }}</h2>
                    <p>{{ $settingText('categories_section_subtitle', __('storefront.sections.categories_subtitle')) }}</p>
                </div>
            </div>

            <div class="scp-category-grid">
                @forelse($featuredCategories as $category)
                    <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'category' => $category->id]) }}" class="scp-category-card">
                    <div class="scp-category-icon">
    @php
        $categoryIcon = $category->icon ?? null;

        $isCategoryIconImage = is_string($categoryIcon)
            && preg_match('/\.(png|jpg|jpeg|svg|webp|gif)$/i', $categoryIcon);

        $categoryIconUrl = null;

        if ($isCategoryIconImage) {
            $cleanIconPath = ltrim(str_replace('storage/', '', $categoryIcon), '/');

            $categoryIconUrl = \Illuminate\Support\Str::startsWith($categoryIcon, ['http://', 'https://'])
                ? $categoryIcon
                : asset('storage/' . $cleanIconPath);
        }
    @endphp

    @if($categoryIconUrl)
        <img
            src="{{ $categoryIconUrl }}"
            alt="{{ $category->getName($locale) }}"
            class="scp-category-icon-image"
        >
    @elseif(! empty($categoryIcon))
        <span>{{ $categoryIcon }}</span>
    @else
        <span>📁</span>
    @endif
</div>

                        <h3>{{ $category->getName($locale) }}</h3>

                        @if(method_exists($category, 'getDescription'))
                            <p>{{ $category->getDescription($locale) }}</p>
                        @endif

                        <strong class="scp-category-cta">
                            {{ __('storefront.sections.category_cta') }} {{ $direction === 'rtl' ? '<' : '>' }}
                        </strong>
                    </a>
                @empty
                    <div class="scp-empty">
                        {{ __('storefront.empty.categories') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @endif

    @if(($storefrontSettings?->enable_game_topups ?? true) && $featuredGames->isNotEmpty())
    <section class="scp-section scp-gaming-section">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ __('storefront.sections.game_topups') }}</h2>
                    <p>{{ __('storefront.sections.game_topups_subtitle') }}</p>
                </div>

                <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'type' => 'game_topup']) }}" class="scp-link-more">
                    {{ __('storefront.sections.view_all') }} {{ $direction === 'rtl' ? '←' : '→' }}
                </a>
            </div>

            <div class="scp-game-grid">
                @foreach($featuredGames as $game)
                    @php
                        $gameBanner = method_exists($game, 'bannerUrl') ? $game->bannerUrl() : null;
                        $gameIcon = method_exists($game, 'iconUrl') ? $game->iconUrl() : null;
                    @endphp

                    <a
                        href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'type' => 'game_topup']) }}"
                        class="scp-game-card"
                        @if($gameBanner)
                            style="--scp-game-bg: url('{{ $gameBanner }}')"
                        @endif
                    >
                        <span class="scp-game-card-glow" aria-hidden="true"></span>

                        <div class="scp-game-card-media">
                            @if($gameIcon)
                                <img src="{{ $gameIcon }}" alt="{{ $game->getName($locale) }}">
                            @else
                                <strong>{{ mb_substr($game->getName($locale), 0, 1) }}</strong>
                            @endif
                        </div>

                        <div class="scp-game-card-content">
                            <span>{{ __('storefront.game_topup.badge') }}</span>
                            <h3>{{ $game->getName($locale) }}</h3>
                            <p>{{ $game->getDescription($locale) ?: __('storefront.sections.game_topups_card_hint') }}</p>

                            <div class="scp-game-card-meta">
                                @if(($game->active_regions_count ?? 0) > 0)
                                    <small>{{ trans_choice('storefront.sections.game_regions_count', $game->active_regions_count, ['count' => $game->active_regions_count]) }}</small>
                                @endif

                                @if($game->supports_player_validation)
                                    <small>{{ __('storefront.sections.player_validation') }}</small>
                                @endif
                            </div>
                        </div>

                        <b>
                            {{ __('storefront.sections.recharge_now') }}
                            {{ $direction === 'rtl' ? '←' : '→' }}
                        </b>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($showFeaturedSection)
    <section class="scp-section scp-section-muted">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ $settingText('featured_section_title', __('storefront.sections.featured')) }}</h2>
                    <p>{{ $settingText('featured_section_subtitle', __('storefront.sections.featured_subtitle')) }}</p>
                </div>

                <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar']) }}" class="scp-link-more">
                    {{ __('storefront.sections.view_all') }} {{ $direction === 'rtl' ? '←' : '→' }}
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

                            <div class="scp-product-utility-row">
                                @if(auth()->check())
                                    <form
                                        method="POST"
                                        action="{{ route('storefront.wishlist.toggle', ['product' => $product->id, 'lang' => $locale]) }}"
                                        class="scp-product-utility-form"
                                    >
                                        @csrf
                                        <button type="submit" title="{{ __('storefront.wishlist.toggle') }}">
                                            ♡ <span>{{ __('storefront.wishlist.toggle') }}</span>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login', ['lang' => $locale]) }}" class="scp-product-utility-form">
                                        <button type="button" title="{{ __('storefront.wishlist.login_required') }}">
                                            ♡ <span>{{ __('storefront.wishlist.login_required') }}</span>
                                        </button>
                                    </a>
                                @endif

                                @if(Route::has('storefront.compare.add'))
                                    <form
                                        method="POST"
                                        action="{{ route('storefront.compare.add', ['product' => $product->id, 'lang' => $locale]) }}"
                                        class="scp-product-utility-form"
                                    >
                                        @csrf
                                        <button type="submit" title="{{ __('storefront.compare.add_to_compare') }}">
                                            ⇄ <span>{{ __('storefront.compare.add_to_compare') }}</span>
                                        </button>
                                    </form>
                                @endif

                                @if($productTypeValue($product) === 'digital')
                                    <span class="scp-product-utility-chip">{{ __('storefront.products_page.digital') }}</span>
                                @endif
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

    @endif

    @if($midPromotions->isNotEmpty())
        <section class="scp-promo-section scp-promo-section-compact">
            <div class="scp-container">
                <div class="scp-promo-grid compact">
                    @foreach($midPromotions as $promotion)
                        <a
                            href="{{ $resolveStoreUrl($promotion->button_url) }}"
                            class="scp-promo-card scp-promo-{{ $promotion->style ?: 'gradient' }}"
                            @if($promotion->background_color || $promotion->text_color)
                                style="{{ $promotion->background_color ? '--scp-promo-bg: '.$promotion->background_color.';' : '' }} {{ $promotion->text_color ? '--scp-promo-text: '.$promotion->text_color.';' : '' }}"
                            @endif
                        >
                            <div class="scp-promo-content">
                                @if($promotion->localized('eyebrow', $locale))
                                    <span>{{ $promotion->localized('eyebrow', $locale) }}</span>
                                @endif

                                <h2>{{ $promotion->localized('title', $locale) }}</h2>

                                @if($promotion->localized('description', $locale))
                                    <p>{{ $promotion->localized('description', $locale) }}</p>
                                @endif

                                <strong>
                                    {{ $promotion->localized('button_text', $locale, __('storefront.sections.view_all')) }}
                                    {{ $direction === 'rtl' ? '←' : '→' }}
                                </strong>
                            </div>

                            @if($promotion->imageUrl())
                                <div class="scp-promo-media">
                                    <img src="{{ $promotion->imageUrl() }}" alt="{{ $promotion->localized('title', $locale) }}">
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($showLatestSection)
    <section class="scp-section">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ $settingText('latest_section_title', __('storefront.sections.latest')) }}</h2>
                    <p>{{ $settingText('latest_section_subtitle', __('storefront.sections.latest_subtitle')) }}</p>
                </div>

                <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'sort' => 'latest']) }}" class="scp-link-more">
                    {{ __('storefront.sections.view_all') }} {{ $direction === 'rtl' ? '←' : '→' }}
                </a>
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

                            <div class="scp-product-utility-row">
                                @if(auth()->check())
                                    <form
                                        method="POST"
                                        action="{{ route('storefront.wishlist.toggle', ['product' => $product->id, 'lang' => $locale]) }}"
                                        class="scp-product-utility-form"
                                    >
                                        @csrf
                                        <button type="submit" title="{{ __('storefront.wishlist.toggle') }}">
                                            ♡ <span>{{ __('storefront.wishlist.toggle') }}</span>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login', ['lang' => $locale]) }}" class="scp-product-utility-form">
                                        <button type="button" title="{{ __('storefront.wishlist.login_required') }}">
                                            ♡ <span>{{ __('storefront.wishlist.login_required') }}</span>
                                        </button>
                                    </a>
                                @endif

                                @if(Route::has('storefront.compare.add'))
                                    <form
                                        method="POST"
                                        action="{{ route('storefront.compare.add', ['product' => $product->id, 'lang' => $locale]) }}"
                                        class="scp-product-utility-form"
                                    >
                                        @csrf
                                        <button type="submit" title="{{ __('storefront.compare.add_to_compare') }}">
                                            ⇄ <span>{{ __('storefront.compare.add_to_compare') }}</span>
                                        </button>
                                    </form>
                                @endif
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

    @endif

    @if($showBrandsSection)
    <section class="scp-section scp-section-muted">
        <div class="scp-container">
            <div class="scp-section-heading">
                <div>
                    <h2>{{ $settingText('brands_section_title', __('storefront.sections.brands')) }}</h2>
                    <p>{{ $settingText('brands_section_subtitle', __('storefront.sections.brands_subtitle')) }}</p>
                </div>
            </div>

            @if($brands->isNotEmpty())
                <div class="scp-brand-carousel" data-scp-brand-carousel>
                    <div class="scp-brand-track" data-scp-brand-track>
                        @foreach($brands as $brand)
                            <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'brand' => $brand->id]) }}" class="scp-brand-card scp-brand-carousel-card">
                                @if(! empty($brand->logo))
                                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->getName($locale) }}">
                                @else
                                    <strong>{{ $brand->getName($locale) }}</strong>
                                @endif
                            </a>
                        @endforeach

                        @foreach($brands as $brand)
                            <a href="{{ route('storefront.products.index', ['lang' => $locale ?? 'ar', 'brand' => $brand->id]) }}" class="scp-brand-card scp-brand-carousel-card" aria-hidden="true" tabindex="-1">
                                @if(! empty($brand->logo))
                                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="">
                                @else
                                    <strong>{{ $brand->getName($locale) }}</strong>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="scp-empty">
                    {{ __('storefront.empty.brands') }}
                </div>
            @endif
        </div>
    </section>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-scp-slider]').forEach(function (slider) {
                var slides = Array.from(slider.querySelectorAll('[data-scp-slide]'));
                var dots = Array.from(slider.querySelectorAll('[data-scp-dot]'));
                var prev = slider.querySelector('[data-scp-prev]');
                var next = slider.querySelector('[data-scp-next]');
                var delay = parseInt(slider.dataset.autoplay || '6000', 10);
                var current = 0;
                var timer = null;

                if (slides.length <= 1) {
                    return;
                }

                function show(index) {
                    current = (index + slides.length) % slides.length;

                    slides.forEach(function (slide, slideIndex) {
                        slide.classList.toggle('is-active', slideIndex === current);
                    });

                    dots.forEach(function (dot, dotIndex) {
                        dot.classList.toggle('is-active', dotIndex === current);
                    });
                }

                function start() {
                    stop();
                    timer = window.setInterval(function () {
                        show(current + 1);
                    }, delay);
                }

                function stop() {
                    if (timer) {
                        window.clearInterval(timer);
                    }
                }

                if (prev) {
                    prev.addEventListener('click', function () {
                        show(current - 1);
                        start();
                    });
                }

                if (next) {
                    next.addEventListener('click', function () {
                        show(current + 1);
                        start();
                    });
                }

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () {
                        show(parseInt(dot.dataset.scpDot || '0', 10));
                        start();
                    });
                });

                slider.addEventListener('mouseenter', stop);
                slider.addEventListener('mouseleave', start);

                show(0);
                start();
            });

            var homeAdTrack = document.querySelector('[data-scp-home-ad-track]');
            var homeAdPrev = document.querySelector('[data-scp-home-ad-prev]');
            var homeAdNext = document.querySelector('[data-scp-home-ad-next]');

            if (homeAdTrack) {
                var scrollHomeAd = function (direction) {
                    var slide = homeAdTrack.querySelector('.scp-products-ad-slide');
                    var distance = slide ? slide.getBoundingClientRect().width + 14 : homeAdTrack.clientWidth;

                    homeAdTrack.scrollBy({
                        left: direction * distance,
                        behavior: 'smooth',
                    });
                };

                if (homeAdPrev) {
                    homeAdPrev.addEventListener('click', function () {
                        scrollHomeAd(-1);
                    });
                }

                if (homeAdNext) {
                    homeAdNext.addEventListener('click', function () {
                        scrollHomeAd(1);
                    });
                }
            }
        });
    </script>

@endsection
