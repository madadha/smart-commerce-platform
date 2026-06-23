<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class StorefrontProductQuestionController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'email', 'max:180'],
            'question' => ['required', 'string', 'min:5', 'max:1500'],
            'lang' => ['nullable', 'string', 'max:5'],
        ]);

        ProductQuestion::query()->create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? auth()->user()?->email,
            'question' => $validated['question'],
            'answer' => null,
            'status' => 'pending',
            'locale' => $locale,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return back()
            ->with('success', __('storefront.questions.submitted_successfully'));
    }

    private function resolveLocale(Request $request): string
    {
        $locale = $request->input('lang')
            ?? $request->query('lang')
            ?? session('storefront_locale')
            ?? app()->getLocale()
            ?? 'ar';

        $locale = app(\App\Support\Localization\ActiveLanguageRegistry::class)->resolve($locale);

        session(['storefront_locale' => $locale]);

        App::setLocale($locale);

        return $locale;
    }
}
