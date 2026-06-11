@php
    $badgeText = function (string $key, string $fallback) {
        $translationKey = 'storefront.badges.' . $key;

        return \Illuminate\Support\Facades\Lang::has($translationKey)
            ? __($translationKey)
            : $fallback;
    };

    $type = $product->product_type ?? null;

    if ($type instanceof \BackedEnum) {
        $type = $type->value;
    }

    $type = (string) $type;

    $isOnSale = ! empty($product->sale_price)
        && ! empty($product->price)
        && (float) $product->sale_price > 0
        && (float) $product->sale_price < (float) $product->price;

    $isFeatured = isset($product->is_featured) && (bool) $product->is_featured;

    $isNew = isset($product->created_at)
        && $product->created_at
        && method_exists($product->created_at, 'greaterThanOrEqualTo')
        && $product->created_at->greaterThanOrEqualTo(now()->subDays(14));

    $stockValue = null;

    foreach (['stock_quantity', 'quantity', 'stock'] as $stockColumn) {
        if (isset($product->{$stockColumn}) && $product->{$stockColumn} !== null) {
            $stockValue = (int) $product->{$stockColumn};
            break;
        }
    }

    $isOutOfStock = $stockValue !== null && $stockValue <= 0;

    $approvedReviews = $product->approvedReviews ?? collect();

    $reviewsCount = $approvedReviews->count();

    $averageRating = $reviewsCount > 0
        ? round((float) $approvedReviews->avg('rating'), 1)
        : 0;

    $isTopRated = $reviewsCount >= 3 && $averageRating >= 4.5;

    $badges = [];

    if ($isOutOfStock) {
        $badges[] = [
            'class' => 'out-of-stock',
            'text' => $badgeText('out_of_stock', 'نفذ المخزون'),
        ];
    }

    if ($isOnSale) {
        $badges[] = [
            'class' => 'sale',
            'text' => $badgeText('sale', 'خصم'),
        ];
    }

    if ($isNew) {
        $badges[] = [
            'class' => 'new',
            'text' => $badgeText('new', 'جديد'),
        ];
    }

    if ($isFeatured) {
        $badges[] = [
            'class' => 'featured',
            'text' => $badgeText('featured', 'مميز'),
        ];
    }

    if ($isTopRated) {
        $badges[] = [
            'class' => 'top-rated',
            'text' => $badgeText('top_rated', 'الأعلى تقييمًا'),
        ];
    }

    if ($type === 'digital') {
        $badges[] = [
            'class' => 'digital',
            'text' => $badgeText('digital', 'رقمي'),
        ];
    }

    if ($type === 'service') {
        $badges[] = [
            'class' => 'service',
            'text' => $badgeText('service', 'خدمة'),
        ];
    }
@endphp

@if(count($badges) > 0)
    <div class="scp-product-badges">
        @foreach($badges as $badge)
            <span class="scp-smart-badge {{ $badge['class'] }}">
                {{ $badge['text'] }}
            </span>
        @endforeach
    </div>
@endif
