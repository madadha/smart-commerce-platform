<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class StorefrontProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        $validated = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:120'],
            'reviewer_email' => ['nullable', 'email', 'max:180'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1500'],
            'lang' => ['nullable', 'string', 'max:5'],
        ]);

        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'reviewer_name' => $validated['reviewer_name'],
            'reviewer_email' => $validated['reviewer_email'] ?? auth()->user()?->email,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'status' => 'pending',
            'locale' => $locale,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return back()
            ->with('success', __('storefront.reviews.submitted_successfully'));
    }

    private function resolveLocale(Request $request): string
    {
        $allowedLocales = ['ar', 'he', 'en'];

        $locale = $request->input('lang')
            ?? $request->query('lang')
            ?? session('storefront_locale')
            ?? app()->getLocale()
            ?? 'ar';

        if (! in_array($locale, $allowedLocales, true)) {
            $locale = 'ar';
        }

        session(['storefront_locale' => $locale]);

        App::setLocale($locale);

        return $locale;
    }
}