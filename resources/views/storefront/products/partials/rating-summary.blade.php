@php
    $approvedReviews = $product->approvedReviews ?? collect();

    $reviewsCount = $approvedReviews->count();

    $averageRating = $reviewsCount > 0
        ? round((float) $approvedReviews->avg('rating'), 1)
        : 0;
@endphp

<div class="scp-product-card-rating">
    <div class="scp-product-card-stars">
        @for($i = 1; $i <= 5; $i++)
            <span class="{{ $i <= round($averageRating) ? 'is-active' : '' }}">★</span>
        @endfor
    </div>

    <span>
        @if($reviewsCount > 0)
            {{ number_format($averageRating, 1) }}
            ·
            {{ $reviewsCount }}
            {{ __('storefront.reviews.reviews_count') }}
        @else
            {{ __('storefront.reviews.empty_title') }}
        @endif
    </span>
</div>