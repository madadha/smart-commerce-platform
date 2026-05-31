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
use Filament\Pages\Page;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Reports Dashboard';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected string $view = 'filament.pages.reports';

    public function getStats(): array
    {
        $totalSales = (float) Order::query()
            ->where('status', OrderStatus::Completed->value)
            ->orWhere('payment_status', 'paid')
            ->sum('grand_total');

        $paidPayments = (float) Payment::query()
            ->where('status', PaymentTransactionStatus::Paid->value)
            ->sum('amount');

        return [
            'total_sales' => $totalSales,
            'paid_payments' => $paidPayments,
            'orders_count' => Order::query()->count(),
            'completed_orders_count' => Order::query()->where('status', OrderStatus::Completed->value)->count(),
            'customers_count' => Customer::query()->count(),
            'products_count' => Product::query()->count(),
            'active_carts_count' => Cart::query()->where('status', 'active')->count(),
            'invoices_count' => Invoice::query()->count(),
            'available_codes_count' => ProductDigitalCode::query()->where('status', DigitalCodeStatus::Available->value)->count(),
            'sold_codes_count' => ProductDigitalCode::query()->where('status', DigitalCodeStatus::Sold->value)->count(),
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
        return Order::query()
            ->with(['customer', 'currency'])
            ->latest()
            ->limit(10)
            ->get();
    }

    public function getLatestPayments()
    {
        return Payment::query()
            ->with(['order', 'customer', 'currency'])
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
            'available' => ProductDigitalCode::query()->where('status', DigitalCodeStatus::Available->value)->count(),
            'reserved' => ProductDigitalCode::query()->where('status', DigitalCodeStatus::Reserved->value)->count(),
            'sold' => ProductDigitalCode::query()->where('status', DigitalCodeStatus::Sold->value)->count(),
            'cancelled' => ProductDigitalCode::query()->where('status', DigitalCodeStatus::Cancelled->value)->count(),
            'expired' => ProductDigitalCode::query()->where('status', DigitalCodeStatus::Expired->value)->count(),
        ];
    }

    public function money(float|int|string|null $amount, ?string $symbol = '₪'): string
    {
        return ($symbol ?: '₪') . ' ' . number_format((float) $amount, 2);
    }
}