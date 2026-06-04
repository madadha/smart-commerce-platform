<x-filament-panels::page>
    @once
        <link rel="stylesheet" href="{{ asset('css/admin/reports-dashboard.css') }}">
    @endonce

    @php
        $stats = $this->getStats();
        $latestOrders = $this->getLatestOrders();
        $latestPayments = $this->getLatestPayments();
        $lowStockProducts = $this->getLowStockProducts();
        $lowStockVariants = $this->getLowStockVariants();
        $digitalCodes = $this->getDigitalCodeSummary();

        $salesChart = $this->getSalesChartData();
        $ordersStatusChart = $this->getOrdersByStatusChartData();
        $paymentsMethodChart = $this->getPaymentsByMethodChartData();
        $topProductsChart = $this->getTopProductsChartData();
        $topCustomersChart = $this->getTopCustomersChartData();

        $mainCurrency = '₪';

        $enumValue = function ($status): string {
            if ($status instanceof \BackedEnum) {
                return strtolower((string) $status->value);
            }

            if (is_object($status) && property_exists($status, 'value')) {
                return strtolower((string) $status->value);
            }

            return strtolower((string) $status);
        };

        $enumLabel = function ($status): string {
            if (is_object($status) && method_exists($status, 'label')) {
                return $status->label();
            }

            if ($status instanceof \BackedEnum) {
                return ucfirst(str_replace('_', ' ', (string) $status->value));
            }

            return ucfirst(str_replace('_', ' ', (string) $status));
        };

        $badgeClass = function ($status) use ($enumValue): string {
            return match ($enumValue($status)) {
                'paid', 'completed', 'active' => 'badge-success',
                'pending', 'processing', 'partially_paid' => 'badge-warning',
                'unpaid', 'failed', 'cancelled' => 'badge-danger',
                'refunded' => 'badge-purple',
                default => 'badge-gray',
            };
        };
    @endphp

    <div class="scp-dashboard">

        <div class="scp-hero">
            <div class="scp-hero-inner">
                <div>
                    <h1 class="scp-hero-title">Reports Dashboard</h1>
                    <div class="scp-hero-text">
                        Professional business overview for sales, payments, customers, stock, and digital codes.
                    </div>
                </div>

                <div class="scp-hero-mini">
                    <div class="scp-hero-card">
                        <span>Total Sales</span>
                        <strong>{{ $this->money($stats['total_sales'], $mainCurrency) }}</strong>
                    </div>

                    <div class="scp-hero-card">
                        <span>Orders</span>
                        <strong>{{ $stats['orders_count'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="scp-filters">
            <div>
                <div class="scp-filter-title">Date Range Filter</div>
                <div class="scp-filter-subtitle">
                    Current range: {{ $this->getDateRangeLabel() }}
                </div>
            </div>

            <div class="scp-filter-actions">
                @foreach($this->getDateRangeOptions() as $rangeKey => $rangeLabel)
                    <button
                        type="button"
                        wire:click="$set('dateRange', '{{ $rangeKey }}')"
                        class="scp-filter-btn {{ $this->dateRange === $rangeKey ? 'active' : '' }}"
                    >
                        {{ $rangeLabel }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="scp-grid-4">
            <div class="scp-card scp-kpi">
                <div>
                    <div class="scp-kpi-label">Total Sales</div>
                    <div class="scp-kpi-value">{{ $this->money($stats['total_sales'], $mainCurrency) }}</div>
                    <div class="scp-kpi-note">Business revenue overview</div>
                </div>
                <div class="scp-icon green">💰</div>
            </div>

            <div class="scp-card scp-kpi">
                <div>
                    <div class="scp-kpi-label">Paid Payments</div>
                    <div class="scp-kpi-value">{{ $this->money($stats['paid_payments'], $mainCurrency) }}</div>
                    <div class="scp-kpi-note">Successfully collected payments</div>
                </div>
                <div class="scp-icon blue">💳</div>
            </div>

            <div class="scp-card scp-kpi">
                <div>
                    <div class="scp-kpi-label">Orders</div>
                    <div class="scp-kpi-value">{{ $stats['orders_count'] }}</div>
                    <div class="scp-kpi-note">Completed: {{ $stats['completed_orders_count'] }}</div>
                </div>
                <div class="scp-icon indigo">📦</div>
            </div>

            <div class="scp-card scp-kpi">
                <div>
                    <div class="scp-kpi-label">Customers</div>
                    <div class="scp-kpi-value">{{ $stats['customers_count'] }}</div>
                    <div class="scp-kpi-note">Registered customer base</div>
                </div>
                <div class="scp-icon pink">👥</div>
            </div>
        </div>

        <div class="scp-grid-4">
            <div class="scp-card scp-kpi">
                <div>
                    <div class="scp-kpi-label">Products</div>
                    <div class="scp-kpi-value">{{ $stats['products_count'] }}</div>
                    <div class="scp-kpi-note">Products in catalog</div>
                </div>
                <div class="scp-icon gray">🛍️</div>
            </div>

            <div class="scp-card scp-kpi">
                <div>
                    <div class="scp-kpi-label">Active Carts</div>
                    <div class="scp-kpi-value">{{ $stats['active_carts_count'] }}</div>
                    <div class="scp-kpi-note">Potential pending purchases</div>
                </div>
                <div class="scp-icon amber">🛒</div>
            </div>

            <div class="scp-card scp-kpi">
                <div>
                    <div class="scp-kpi-label">Invoices</div>
                    <div class="scp-kpi-value">{{ $stats['invoices_count'] }}</div>
                    <div class="scp-kpi-note">Generated invoices</div>
                </div>
                <div class="scp-icon cyan">🧾</div>
            </div>

            <div class="scp-card scp-kpi">
                <div>
                    <div class="scp-kpi-label">Low Stock</div>
                    <div class="scp-kpi-value" style="color:#dc2626;">
                        {{ $stats['low_stock_products_count'] + $stats['low_stock_variants_count'] }}
                    </div>
                    <div class="scp-kpi-note">Products or variants needing refill</div>
                </div>
                <div class="scp-icon red">⚠️</div>
            </div>
        </div>

        <div class="scp-grid-3">
            <div class="scp-card">
                <div class="scp-section-head">
                    <div>
                        <h2 class="scp-section-title">Digital Codes Summary</h2>
                        <div class="scp-section-subtitle">Availability and lifecycle overview of digital inventory</div>
                    </div>
                    <div class="scp-pill">Digital Inventory</div>
                </div>

                <div class="scp-code-grid">
                    <div class="scp-code-card available"><span>Available</span><strong>{{ $digitalCodes['available'] }}</strong></div>
                    <div class="scp-code-card reserved"><span>Reserved</span><strong>{{ $digitalCodes['reserved'] }}</strong></div>
                    <div class="scp-code-card sold"><span>Sold</span><strong>{{ $digitalCodes['sold'] }}</strong></div>
                    <div class="scp-code-card cancelled"><span>Cancelled</span><strong>{{ $digitalCodes['cancelled'] }}</strong></div>
                    <div class="scp-code-card expired"><span>Expired</span><strong>{{ $digitalCodes['expired'] }}</strong></div>
                </div>
            </div>

            <div class="scp-card">
                <h2 class="scp-section-title">Business Overview</h2>
                <div class="scp-section-subtitle">Quick snapshot for investor-friendly presentation</div>

                <div class="scp-overview-list">
                    <div class="scp-overview-row">
                        <span>Average Sales / Order</span>
                        <strong>{{ $stats['orders_count'] > 0 ? $this->money($stats['total_sales'] / $stats['orders_count'], $mainCurrency) : $this->money(0, $mainCurrency) }}</strong>
                    </div>

                    <div class="scp-overview-row">
                        <span>Completed Orders</span>
                        <strong>{{ $stats['completed_orders_count'] }}</strong>
                    </div>

                    <div class="scp-overview-row">
                        <span>Available Digital Codes</span>
                        <strong>{{ $stats['available_codes_count'] }}</strong>
                    </div>

                    <div class="scp-overview-row">
                        <span>Sold Digital Codes</span>
                        <strong>{{ $stats['sold_codes_count'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="scp-chart-grid">
            <div class="scp-chart-card">
                <div class="scp-section-head">
                    <div>
                        <h2 class="scp-section-title">Sales Performance</h2>
                        <div class="scp-section-subtitle">
                            Sales grouped by day for selected range: {{ $this->getDateRangeLabel() }}
                        </div>
                    </div>
                    <div class="scp-pill">Sales Chart</div>
                </div>

                @if(count($salesChart) > 0)
                    <div class="scp-bars">
                        @foreach($salesChart as $point)
                            <div class="scp-bar-item">
                                <div class="scp-bar-value">{{ $this->money($point['sales']) }}</div>
                                <div
                                    class="scp-bar"
                                    style="height: {{ max($point['percent'], 5) }}%;"
                                    title="{{ $point['label'] }} - {{ $this->money($point['sales']) }}"
                                ></div>
                                <div class="scp-bar-label">{{ $point['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="scp-chart-empty">No sales data for this range.</div>
                @endif
            </div>

            <div class="scp-chart-card">
                <div class="scp-section-head">
                    <div>
                        <h2 class="scp-section-title">Orders by Status</h2>
                        <div class="scp-section-subtitle">Distribution of orders by current status</div>
                    </div>
                </div>

                @if(count($ordersStatusChart) > 0)
                    <div class="scp-horizontal-chart">
                        @foreach($ordersStatusChart as $row)
                            <div class="scp-horizontal-row">
                                <div class="scp-horizontal-label">{{ $row['label'] }}</div>
                                <div class="scp-horizontal-track">
                                    <div class="scp-horizontal-fill" style="width: {{ max($row['percent'], 3) }}%;"></div>
                                </div>
                                <div class="scp-horizontal-value">{{ $row['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="scp-chart-empty">No orders found for this range.</div>
                @endif
            </div>
        </div>

        <div class="scp-chart-grid-2">
            <div class="scp-chart-card">
                <div class="scp-section-head">
                    <div>
                        <h2 class="scp-section-title">Payments by Method</h2>
                        <div class="scp-section-subtitle">Paid amounts grouped by payment method</div>
                    </div>
                </div>

                @if(count($paymentsMethodChart) > 0)
                    <div class="scp-horizontal-chart">
                        @foreach($paymentsMethodChart as $row)
                            <div class="scp-horizontal-row">
                                <div class="scp-horizontal-label">{{ $row['label'] }}</div>
                                <div class="scp-horizontal-track">
                                    <div class="scp-horizontal-fill" style="width: {{ max($row['percent'], 3) }}%;"></div>
                                </div>
                                <div class="scp-horizontal-value">{{ $this->money($row['amount']) }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="scp-chart-empty">No paid payments for this range.</div>
                @endif
            </div>

            <div class="scp-chart-card">
                <div class="scp-section-head">
                    <div>
                        <h2 class="scp-section-title">Top Customers</h2>
                        <div class="scp-section-subtitle">Customers ranked by total purchases</div>
                    </div>
                </div>

                @if(count($topCustomersChart) > 0)
                    <div class="scp-horizontal-chart">
                        @foreach($topCustomersChart as $row)
                            <div class="scp-horizontal-row">
                                <div class="scp-horizontal-label">{{ $row['label'] }}</div>
                                <div class="scp-horizontal-track">
                                    <div class="scp-horizontal-fill" style="width: {{ max($row['percent'], 3) }}%;"></div>
                                </div>
                                <div class="scp-horizontal-value">{{ $this->money($row['sales']) }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="scp-chart-empty">No customer sales for this range.</div>
                @endif
            </div>
        </div>

        <div class="scp-chart-card" style="margin-bottom: 20px;">
            <div class="scp-section-head">
                <div>
                    <h2 class="scp-section-title">Top Products</h2>
                    <div class="scp-section-subtitle">Best selling products by quantity</div>
                </div>
                <div class="scp-pill">Best Sellers</div>
            </div>

            @if(count($topProductsChart) > 0)
                <div class="scp-horizontal-chart">
                    @foreach($topProductsChart as $row)
                        <div class="scp-horizontal-row">
                            <div class="scp-horizontal-label">{{ $row['label'] }}</div>
                            <div class="scp-horizontal-track">
                                <div class="scp-horizontal-fill" style="width: {{ max($row['percent'], 3) }}%;"></div>
                            </div>
                            <div class="scp-horizontal-value">{{ $row['quantity'] }} sold</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="scp-chart-empty">No product sales for this range.</div>
            @endif
        </div>

        <div class="scp-table-card">
            <div class="scp-table-header">
                <h2 class="scp-section-title">Latest Orders</h2>
                <div class="scp-section-subtitle">Recent sales and their order/payment statuses</div>
            </div>

            <div class="scp-table-wrap">
                <table class="scp-table">
                    <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($latestOrders as $order)
                        <tr>
                            <td class="strong">{{ $order->order_number }}</td>
                            <td>{{ $order->customer?->getDisplayName() ?? '-' }}</td>
                            <td><span class="badge {{ $badgeClass($order->status) }}">{{ $enumLabel($order->status) }}</span></td>
                            <td><span class="badge {{ $badgeClass($order->payment_status) }}">{{ $enumLabel($order->payment_status) }}</span></td>
                            <td class="strong">{{ $this->money($order->grand_total, $order->currency?->symbol ?? $mainCurrency) }}</td>
                            <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No orders found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="scp-table-card">
            <div class="scp-table-header">
                <h2 class="scp-section-title">Latest Payments</h2>
                <div class="scp-section-subtitle">Track payment activity and collection status</div>
            </div>

            <div class="scp-table-wrap">
                <table class="scp-table">
                    <thead>
                    <tr>
                        <th>Payment</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($latestPayments as $payment)
                        <tr>
                            <td class="strong">{{ $payment->payment_number }}</td>
                            <td>{{ $payment->order?->order_number ?? '-' }}</td>
                            <td>{{ $payment->customer?->getDisplayName() ?? $payment->order?->customer?->getDisplayName() ?? '-' }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td><span class="badge {{ $badgeClass($payment->status) }}">{{ $enumLabel($payment->status) }}</span></td>
                            <td class="strong">{{ $this->money($payment->amount, $payment->currency?->symbol ?? $payment->order?->currency?->symbol ?? $mainCurrency) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No payments found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="scp-grid-2">
            <div class="scp-table-card">
                <div class="scp-table-header">
                    <h2 class="scp-section-title">Low Stock Products</h2>
                    <div class="scp-section-subtitle">Products that reached minimum stock threshold</div>
                </div>

                <div class="scp-table-wrap">
                    <table class="scp-table">
                        <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Stock</th>
                            <th>Min</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($lowStockProducts as $product)
                            <tr>
                                <td class="strong">{{ $product->getName('ar') }}</td>
                                <td>{{ $product->sku ?? '-' }}</td>
                                <td style="color:#dc2626;font-weight:800;">{{ $product->stock_quantity }}</td>
                                <td>{{ $product->min_stock_quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">No low stock products.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="scp-table-card">
                <div class="scp-table-header">
                    <h2 class="scp-section-title">Low Stock Variants</h2>
                    <div class="scp-section-subtitle">Variants that need restocking soon</div>
                </div>

                <div class="scp-table-wrap">
                    <table class="scp-table">
                        <thead>
                        <tr>
                            <th>Variant</th>
                            <th>SKU</th>
                            <th>Stock</th>
                            <th>Min</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($lowStockVariants as $variant)
                            <tr>
                                <td class="strong">{{ $variant->getName('ar') }}</td>
                                <td>{{ $variant->sku ?? '-' }}</td>
                                <td style="color:#dc2626;font-weight:800;">{{ $variant->stock_quantity }}</td>
                                <td>{{ $variant->min_stock_quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">No low stock variants.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>