<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontOrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $locale = $this->resolveLocale($request);

        $order->load([
            'items.product.brand',
            'items.product.currency',
            'items.productVariant',
            'currency',
            'customer',
            'shippingMethod',
        ]);

        $digitalCodesByItem = $this->loadDigitalCodesByOrderItem($order);

        return view('storefront.orders.show', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'order' => $order,
            'digitalCodesByItem' => $digitalCodesByItem,
            'pageTitle' => __('storefront.order_details.page_title') . ' - ' . $order->order_number,
            'pageDescription' => __('storefront.order_details.page_description'),
        ]);
    }

    private function loadDigitalCodesByOrderItem(Order $order): array
    {
        if (! Schema::hasTable('product_digital_codes')) {
            return [];
        }

        if (! Schema::hasColumn('product_digital_codes', 'order_item_id')) {
            return [];
        }

        $query = DB::table('product_digital_codes')
            ->whereIn('order_item_id', $order->items->pluck('id')->filter()->values());

        $codes = $query->get();

        return $codes
            ->groupBy('order_item_id')
            ->map(function ($items) {
                return $items->map(function ($code) {
                    return [
                        'id' => $code->id ?? null,
                        'status' => $code->status ?? null,
                        'code' => $this->resolveDigitalCodeDisplay($code),
                        'sold_at' => $code->sold_at ?? null,
                    ];
                })->values()->toArray();
            })
            ->toArray();
    }

    private function resolveDigitalCodeDisplay(object $code): string
    {
        foreach (['masked_code', 'code_masked', 'display_code'] as $column) {
            if (property_exists($code, $column) && ! empty($code->{$column})) {
                return (string) $code->{$column};
            }
        }

        foreach (['code', 'digital_code', 'serial', 'serial_number'] as $column) {
            if (property_exists($code, $column) && ! empty($code->{$column})) {
                $value = (string) $code->{$column};

                if (mb_strlen($value) <= 6) {
                    return str_repeat('*', mb_strlen($value));
                }

                return mb_substr($value, 0, 3) . str_repeat('*', max(mb_strlen($value) - 6, 4)) . mb_substr($value, -3);
            }
        }

        return '********';
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

    private function direction(string $locale): string
    {
        return in_array($locale, ['ar', 'he'], true) ? 'rtl' : 'ltr';
    }
}