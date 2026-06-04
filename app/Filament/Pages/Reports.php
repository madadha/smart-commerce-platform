<?php

namespace App\Filament\Pages;

use App\Enums\DigitalCodeStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentTransactionStatus;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

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

        $totalSales = (float) $salesQuery->sum('grand_total');

        $paidPayments = (float) $paymentsQuery->sum('amount');

        return [
            'total_sales' => $totalSales,
            'paid_payments' => $paidPayments,

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