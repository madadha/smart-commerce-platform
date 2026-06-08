@php
    $approvedReviews = $product->approvedReviews ?? collect();

    $reviewsCount = $approvedReviews->count();

    $averageRating = $reviewsCount > 0
        ? round((float) $approvedReviews->avg('rating'), 1)
        : 0;

    $userName = auth()->user()?->name ?? '';
    $userEmail = auth()->user()?->email ?? '';
@endphp

<section class="scp-product-reviews-section" id="reviews">
    <div class="scp-product-reviews-head">
        <div>
            <span class="scp-product-reviews-badge">
                {{ __('storefront.reviews.badge') }}
            </span>

            <h2>{{ __('storefront.reviews.title') }}</h2>

            <p>{{ __('storefront.reviews.subtitle') }}</p>
        </div>

        <div class="scp-product-rating-summary">
            <strong>{{ number_format($averageRating, 1) }}</strong>

            <div>
                <div class="scp-rating-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= round($averageRating) ? 'is-active' : '' }}">★</span>
                    @endfor
                </div>

                <small>
                    {{ $reviewsCount }}
                    {{ __('storefront.reviews.reviews_count') }}
                </small>
            </div>
        </div>
    </div>

    <div class="scp-product-reviews-grid">
        <div class="scp-product-review-form-card">
            <h3>{{ __('storefront.reviews.write_review') }}</h3>

            <p>{{ __('storefront.reviews.approval_notice') }}</p>

            <form
                method="POST"
                action="{{ route('storefront.products.reviews.store', ['product' => $product->id, 'lang' => $locale]) }}"
                class="scp-product-review-form"
            >
                @csrf

                <input type="hidden" name="lang" value="{{ $locale }}">

                <div class="scp-review-field">
                    <label>{{ __('storefront.reviews.name') }}</label>
                    <input
                        type="text"
                        name="reviewer_name"
                        value="{{ old('reviewer_name', $userName) }}"
                        required
                        maxlength="120"
                    >
                </div>

                <div class="scp-review-field">
                    <label>{{ __('storefront.reviews.email') }}</label>
                    <input
                        type="email"
                        name="reviewer_email"
                        value="{{ old('reviewer_email', $userEmail) }}"
                        maxlength="180"
                    >
                </div>

                <div class="scp-review-field">
                    <label>{{ __('storefront.reviews.rating') }}</label>

                    <div class="scp-review-rating-input">
                        @for($i = 5; $i >= 1; $i--)
                            <input
                                type="radio"
                                id="review-rating-{{ $i }}"
                                name="rating"
                                value="{{ $i }}"
                                {{ (int) old('rating', 5) === $i ? 'checked' : '' }}
                            >
                            <label for="review-rating-{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="scp-review-field">
                    <label>{{ __('storefront.reviews.comment') }}</label>
                    <textarea
                        name="comment"
                        rows="4"
                        maxlength="1500"
                        placeholder="{{ __('storefront.reviews.comment_placeholder') }}"
                    >{{ old('comment') }}</textarea>
                </div>

                <button type="submit" class="scp-review-submit-btn">
                    {{ __('storefront.reviews.submit') }}
                </button>
            </form>
        </div>

        <div class="scp-product-approved-reviews-card">
            <div class="scp-product-approved-reviews-head">
                <h3>{{ __('storefront.reviews.approved_reviews') }}</h3>
                <span>{{ $reviewsCount }}</span>
            </div>

            @if($reviewsCount > 0)
                <div class="scp-approved-reviews-list">
                    @foreach($approvedReviews->take(8) as $review)
                        <article class="scp-approved-review-item">
                            <div class="scp-approved-review-top">
                                <div class="scp-review-avatar">
                                    {{ mb_substr($review->reviewer_name, 0, 1) }}
                                </div>

                                <div>
                                    <strong>{{ $review->reviewer_name }}</strong>

                                    <div class="scp-rating-stars small">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= (int) $review->rating ? 'is-active' : '' }}">★</span>
                                        @endfor
                                    </div>
                                </div>

                                <small>{{ optional($review->approved_at ?? $review->created_at)->format('Y-m-d') }}</small>
                            </div>

                            @if($review->comment)
                                <p>{{ $review->comment }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="scp-reviews-empty">
                    <div>★</div>
                    <h4>{{ __('storefront.reviews.empty_title') }}</h4>
                    <p>{{ __('storefront.reviews.empty_text') }}</p>
                </div>
            @endif
        </div>
    </div>
</section>