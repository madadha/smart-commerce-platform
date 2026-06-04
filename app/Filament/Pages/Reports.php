<?php

namespace App\Filament\Pages;

use App\Enums\DigitalCodeStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentTransactionStatus;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductVariant;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Reports Dashboard';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected string $view = 'filament.pages.reports';

    public string $dateRange = 'all_time';

    public function getDateRangeOptions(): array
    {
        return [
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'all_time' => 'All Time',
        ];
    }

    public function getDateRangeLabel(): string
    {
        return $this->getDateRangeOptions()[$this->dateRange] ?? 'All Time';
    }

    private function getDateRangeDates(): array
    {
        return match ($this->dateRange) {
            'today' => [
                now()->startOfDay(),
                now()->endOfDay(),
            ],

            'this_week' => [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ],

            'this_month' => [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ],

            default => [
                null,
                null,
            ],
        };
    }

    private function applyDateRange(Builder $query, string $column = 'created_at'): Builder
    {
        [$startDate, $endDate] = $this->getDateRangeDates();

        if ($startDate && $endDate) {
            $query->whereBetween($column, [$startDate, $endDate]);
        }

        return $query;
    }

    public function getStats(): array
    {
        $ordersQuery = $this->applyDateRange(Order::query());

        $completedOrdersQuery = $this->applyDateRange(
            Order::query()->where('status', OrderStatus::Completed->value)
        );

        $salesQuery = $this->applyDateRange(
            Order::query()->where(function (Builder $query) {
                $query->where('status', OrderStatus::Completed->value)
                    ->orWhere('payment_status', 'paid');
            })
        );

        $paymentsQuery = $this->applyDateRange(
            Payment::query()->where('status', PaymentTransactionStatus::Paid->value)
        );

        $customersQuery = $this->applyDateRange(Customer::query());

        $productsQuery = $this->applyDateRange(Product::query());

        $activeCartsQuery = $this->applyDateRange(
            Cart::query()->where('status', 'active')
        );

        $invoicesQuery = $this->applyDateRange(Invoice::query());

        return [
            'total_sales' => (float) $salesQuery->sum('grand_total'),
            'paid_payments' => (float) $paymentsQuery->sum('amount'),

            'orders_count' => $ordersQuery->count(),
            'completed_orders_count' => $completedOrdersQuery->count(),

            'customers_count' => $customersQuery->count(),
            'products_count' => $productsQuery->count(),
            'active_carts_count' => $activeCartsQuery->count(),
            'invoices_count' => $invoicesQuery->count(),

            'available_codes_count' => ProductDigitalCode::query()
                ->where('status', DigitalCodeStatus::Available->value)
                ->count(),

            'sold_codes_count' => ProductDigitalCode::query()
                ->where('status', DigitalCodeStatus::Sold->value)
                ->count(),

            'low_stock_products_count' => Product::query()
                ->where('track_stock', true)
                ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
                ->count(),

            'low_stock_variants_count' => ProductVariant::query()
                ->where('track_stock', true)
                ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
                ->count(),
        ];
    }

    public function getSalesChartData(): array
    {
        $query = Order::query()
            ->selectRaw('DATE(created_at) as date_label')
            ->selectRaw('SUM(grand_total) as total_sales')
            ->selectRaw('COUNT(*) as orders_count')
            ->where(function (Builder $query) {
                $query->where('status', OrderStatus::Completed->value)
                    ->orWhere('payment_status', 'paid');
            })
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)');

        $this->applyDateRange($query);

        $rows = $query->get();

        $maxSales = max((float) $rows->max('total_sales'), 1);

        return $rows->map(function ($row) use ($maxSales) {
            return [
                'label' => $row->date_label,
                'sales' => (float) $row->total_sales,
                'orders' => (int) $row->orders_count,
                'percent' => round(((float) $row->total_sales / $maxSales) * 100, 1),
            ];
        })->toArray();
    }

    public function getOrdersByStatusChartData(): array
    {
        $query = Order::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status');

        $this->applyDateRange($query);

        $rows = $query->get();

        $max = max((int) $rows->max('total'), 1);

        return $rows->map(function ($row) use ($max) {
            $label = $row->status;

            if ($label instanceof \BackedEnum) {
                $label = $label->value;
            }

            return [
                'label' => ucfirst(str_replace('_', ' ', (string) $label)),
                'value' => (int) $row->total,
                'percent' => round(((int) $row->total / $max) * 100, 1),
            ];
        })->toArray();
    }

    public function getPaymentsByMethodChartData(): array
    {
        $query = Payment::query()
            ->select('payment_method')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('COUNT(*) as payments_count')
            ->where('status', PaymentTransactionStatus::Paid->value)
            ->groupBy('payment_method');

        $this->applyDateRange($query);

        $rows = $query->get();

        $max = max((float) $rows->max('total_amount'), 1);

        return $rows->map(function ($row) use ($max) {
            return [
                'label' => ucfirst(str_replace('_', ' ', (string) $row->payment_method)),
                'amount' => (float) $row->total_amount,
                'count' => (int) $row->payments_count,
                'percent' => round(((float) $row->total_amount / $max) * 100, 1),
            ];
        })->toArray();
    }

    public function getTopProductsChartData(): array
    {
        $query = OrderItem::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('SUM(line_total) as total_sales')
            ->with('product')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(7);

        $query->whereHas('order', function (Builder $orderQuery) {
            $this->applyDateRange($orderQuery);
        });

        $rows = $query->get();

        $max = max((int) $rows->max('total_quantity'), 1);

        return $rows->map(function ($row) use ($max) {
            return [
                'label' => $row->product?->getName('ar') ?? 'Product #' . $row->product_id,
                'quantity' => (int) $row->total_quantity,
                'sales' => (float) $row->total_sales,
                'percent' => round(((int) $row->total_quantity / $max) * 100, 1),
            ];
        })->toArray();
    }

    public function getTopCustomersChartData(): array
    {
        $query = Order::query()
            ->select('customer_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(grand_total) as total_sales')
            ->with('customer')
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderByDesc('total_sales')
            ->limit(7);

        $this->applyDateRange($query);

        $rows = $query->get();

        $max = max((float) $rows->max('total_sales'), 1);

        return $rows->map(function ($row) use ($max) {
            return [
                'label' => $row->customer?->getDisplayName() ?? 'Customer #' . $row->customer_id,
                'orders' => (int) $row->orders_count,
                'sales' => (float) $row->total_sales,
                'percent' => round(((float) $row->total_sales / $max) * 100, 1),
            ];
        })->toArray();
    }

    public function getLatestOrders()
    {
        return $this->applyDateRange(
            Order::query()->with(['customer', 'currency'])
        )
            ->latest()
            ->limit(10)
            ->get();
    }

    public function getLatestPayments()
    {
        return $this->applyDateRange(
            Payment::query()->with(['order', 'customer', 'currency'])
        )
            ->latest()
            ->limit(10)
            ->get();
    }

    public function getLowStockProducts()
    {
        return Product::query()
            ->with(['brand'])
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();
    }

    public function getLowStockVariants()
    {
        return ProductVariant::query()
            ->with(['product'])
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();
    }

    public function getDigitalCodeSummary(): array
    {
        return [
            'available' => ProductDigitalCode::query()
                ->where('status', DigitalCodeStatus::Available->value)
                ->count(),

            'reserved' => ProductDigitalCode::query()
                ->where('status', DigitalCodeStatus::Reserved->value)
                ->count(),

            'sold' => ProductDigitalCode::query()
                ->where('status', DigitalCodeStatus::Sold->value)
                ->count(),

            'cancelled' => ProductDigitalCode::query()
                ->where('status', DigitalCodeStatus::Cancelled->value)
                ->count(),

            'expired' => ProductDigitalCode::query()
                ->where('status', DigitalCodeStatus::Expired->value)
                ->count(),
        ];
    }

    public function money(float|int|string|null $amount, ?string $symbol = '₪'): string
    {
        return ($symbol ?: '₪') . ' ' . number_format((float) $amount, 2);
    }
}