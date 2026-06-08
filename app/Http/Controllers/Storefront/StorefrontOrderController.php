<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
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

    public function trackingForm(Request $request): View
    {
        $locale = $this->resolveLocale($request);

        return view('storefront.orders.track', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'pageTitle' => __('storefront.order_tracking.page_title') . ' - Smart Commerce Platform',
            'pageDescription' => __('storefront.order_tracking.page_description'),
        ]);
    }

    public function trackingResult(Request $request): View
    {
        $locale = $this->resolveLocale($request);

        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'lang' => ['nullable', 'string', 'max:5'],
        ]);

        $orderNumber = trim($validated['order_number']);
        $phone = $this->normalizePhone($validated['customer_phone']);

        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where(function (Builder $query) use ($phone) {
                $this->applyPhoneSearch($query, $phone);
            })
            ->first();

        if (! $order) {
            return view('storefront.orders.track', [
                'locale' => $locale,
                'direction' => $this->direction($locale),
                'orderNotFound' => true,
                'oldOrderNumber' => $orderNumber,
                'oldCustomerPhone' => $validated['customer_phone'],
                'pageTitle' => __('storefront.order_tracking.page_title') . ' - Smart Commerce Platform',
                'pageDescription' => __('storefront.order_tracking.page_description'),
            ]);
        }

        $order->load([
            'items.product.brand',
            'items.product.currency',
            'items.productVariant',
            'currency',
            'customer',
            'shippingMethod',
        ]);

        $digitalCodesByItem = $this->loadDigitalCodesByOrderItem($order);

        return view('storefront.orders.tracking-result', [
            'locale' => $locale,
            'direction' => $this->direction($locale),
            'order' => $order,
            'digitalCodesByItem' => $digitalCodesByItem,
            'pageTitle' => __('storefront.order_tracking.result_title') . ' - ' . $order->order_number,
            'pageDescription' => __('storefront.order_tracking.result_description'),
        ]);
    }

    private function applyPhoneSearch(Builder $query, string $phone): void
    {
        $hasAnyCondition = false;

        foreach (['customer_phone', 'phone', 'mobile'] as $column) {
            if (Schema::hasColumn('orders', $column)) {
                if ($hasAnyCondition) {
                    $query->orWhereRaw($this->phoneNormalizeSql($column) . ' = ?', [$phone]);
                } else {
                    $query->whereRaw($this->phoneNormalizeSql($column) . ' = ?', [$phone]);
                    $hasAnyCondition = true;
                }
            }
        }

        if (Schema::hasTable('customers')) {
            if ($hasAnyCondition) {
                $query->orWhereHas('customer', function (Builder $customerQuery) use ($phone) {
                    $this->applyCustomerPhoneSearch($customerQuery, $phone);
                });
            } else {
                $query->whereHas('customer', function (Builder $customerQuery) use ($phone) {
                    $this->applyCustomerPhoneSearch($customerQuery, $phone);
                });
            }
        }
    }

    private function applyCustomerPhoneSearch(Builder $query, string $phone): void
    {
        $hasAnyCondition = false;

        foreach (['phone', 'mobile', 'customer_phone', 'whatsapp'] as $column) {
            if (Schema::hasColumn('customers', $column)) {
                if ($hasAnyCondition) {
                    $query->orWhereRaw($this->phoneNormalizeSql($column) . ' = ?', [$phone]);
                } else {
                    $query->whereRaw($this->phoneNormalizeSql($column) . ' = ?', [$phone]);
                    $hasAnyCondition = true;
                }
            }
        }

        if (! $hasAnyCondition) {
            $query->whereRaw('1 = 0');
        }
    }

    private function phoneNormalizeSql(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$column}, '-', ''), ' ', ''), '+', ''), '(', ''), ')', '')";
    }

    private function loadDigitalCodesByOrderItem(Order $order): array
    {
        if (! Schema::hasTable('product_digital_codes')) {
            return [];
        }

        if (! Schema::hasColumn('product_digital_codes', 'order_item_id')) {
            return [];
        }

        $itemIds = $order->items
            ->pluck('id')
            ->filter()
            ->values();

        if ($itemIds->isEmpty()) {
            return [];
        }

        $codes = DB::table('product_digital_codes')
            ->whereIn('order_item_id', $itemIds)
            ->get();

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

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone) ?? '';
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