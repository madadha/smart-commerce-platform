<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $latestOrders = $this->getLatestOrders();
        $latestPayments = $this->getLatestPayments();
        $lowStockProducts = $this->getLowStockProducts();
        $lowStockVariants = $this->getLowStockVariants();
        $digitalCodes = $this->getDigitalCodeSummary();

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

    <style>
        .scp-dashboard {
            width: 100%;
            max-width: 100%;
            padding-bottom: 30px;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .scp-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%);
            border-radius: 24px;
            padding: 30px;
            color: white;
            box-shadow: 0 20px 45px rgba(79, 70, 229, 0.22);
            margin-bottom: 24px;
            overflow: hidden;
            position: relative;
        }

        .scp-hero::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            right: -80px;
            top: -90px;
        }

        .scp-hero-inner {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
        }

        .scp-hero-title {
            font-size: 32px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.04em;
        }

        .scp-hero-text {
            margin-top: 8px;
            color: #ede9fe;
            font-size: 15px;
            max-width: 650px;
        }

        .scp-hero-mini {
            display: grid;
            grid-template-columns: repeat(2, minmax(150px, 1fr));
            gap: 12px;
            min-width: 330px;
        }

        .scp-hero-card {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(12px);
            border-radius: 18px;
            padding: 16px;
        }

        .scp-hero-card span {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #ddd6fe;
            margin-bottom: 6px;
        }

        .scp-hero-card strong {
            font-size: 21px;
            font-weight: 800;
            color: white;
        }

        .scp-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 20px;
        }

        .scp-grid-3 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .scp-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .scp-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 22px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .dark .scp-card {
            background: #111827;
            border-color: #1f2937;
        }

        .scp-kpi {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            min-height: 145px;
        }

        .scp-kpi-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .scp-kpi-value {
            font-size: 30px;
            line-height: 1.1;
            font-weight: 850;
            color: #111827;
            letter-spacing: -0.04em;
        }

        .dark .scp-kpi-value {
            color: white;
        }

        .scp-kpi-note {
            margin-top: 10px;
            font-size: 12px;
            color: #6b7280;
        }

        .scp-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            flex-shrink: 0;
        }

        .scp-icon.green { background: #dcfce7; color: #166534; }
        .scp-icon.blue { background: #dbeafe; color: #1d4ed8; }
        .scp-icon.indigo { background: #e0e7ff; color: #4338ca; }
        .scp-icon.pink { background: #fce7f3; color: #be185d; }
        .scp-icon.gray { background: #f1f5f9; color: #475569; }
        .scp-icon.amber { background: #fef3c7; color: #b45309; }
        .scp-icon.cyan { background: #cffafe; color: #0e7490; }
        .scp-icon.red { background: #fee2e2; color: #b91c1c; }

        .scp-section-title {
            font-size: 19px;
            font-weight: 800;
            color: #111827;
            margin: 0;
        }

        .dark .scp-section-title {
            color: white;
        }

        .scp-section-subtitle {
            margin-top: 5px;
            font-size: 13px;
            color: #6b7280;
        }

        .scp-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .scp-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
        }

        .scp-code-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .scp-code-card {
            border-radius: 18px;
            padding: 16px;
            border: 1px solid #e5e7eb;
        }

        .scp-code-card span {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .scp-code-card strong {
            font-size: 28px;
            font-weight: 850;
        }

        .scp-code-card.available { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .scp-code-card.reserved { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .scp-code-card.sold { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .scp-code-card.cancelled { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .scp-code-card.expired { background: #f9fafb; border-color: #e5e7eb; color: #374151; }

        .scp-overview-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 18px;
        }

        .scp-overview-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            border-radius: 16px;
            background: #f9fafb;
            border: 1px solid #eef2f7;
        }

        .dark .scp-overview-row {
            background: #1f2937;
            border-color: #374151;
        }

        .scp-overview-row span {
            color: #6b7280;
            font-size: 13px;
        }

        .scp-overview-row strong {
            color: #111827;
            font-size: 14px;
            font-weight: 800;
        }

        .dark .scp-overview-row strong {
            color: white;
        }

        .scp-table-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .dark .scp-table-card {
            background: #111827;
            border-color: #1f2937;
        }

        .scp-table-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e5e7eb;
        }

        .dark .scp-table-header {
            border-color: #1f2937;
        }

        .scp-table-wrap {
            overflow-x: auto;
        }

        .scp-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .scp-table thead {
            background: #f9fafb;
        }

        .dark .scp-table thead {
            background: #1f2937;
        }

        .scp-table th {
            text-align: left;
            padding: 14px 18px;
            font-size: 12px;
            color: #6b7280;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #e5e7eb;
        }

        .scp-table td {
            padding: 15px 18px;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .dark .scp-table td {
            color: #d1d5db;
            border-color: #1f2937;
        }

        .scp-table tr:hover td {
            background: #fafafa;
        }

        .dark .scp-table tr:hover td {
            background: #172033;
        }

        .scp-table .strong {
            font-weight: 800;
            color: #111827;
        }

        .dark .scp-table .strong {
            color: white;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 800;
            border: 1px solid transparent;
        }

        .badge-success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .badge-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .badge-danger { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; border-color: #e9d5ff; }
        .badge-gray { background: #f3f4f6; color: #374151; border-color: #e5e7eb; }

        .empty-state {
            padding: 24px;
            text-align: center;
            color: #6b7280;
        }

        @media (max-width: 1200px) {
            .scp-grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .scp-grid-3,
            .scp-grid-2 {
                grid-template-columns: 1fr;
            }

            .scp-code-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .scp-hero-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .scp-hero-mini {
                width: 100%;
                min-width: 0;
            }

            .scp-grid-4 {
                grid-template-columns: 1fr;
            }

            .scp-code-grid {
                grid-template-columns: 1fr;
            }

            .scp-hero-title {
                font-size: 25px;
            }
        }
    </style>

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
                    <div class="scp-code-card available">
                        <span>Available</span>
                        <strong>{{ $digitalCodes['available'] }}</strong>
                    </div>

                    <div class="scp-code-card reserved">
                        <span>Reserved</span>
                        <strong>{{ $digitalCodes['reserved'] }}</strong>
                    </div>

                    <div class="scp-code-card sold">
                        <span>Sold</span>
                        <strong>{{ $digitalCodes['sold'] }}</strong>
                    </div>

                    <div class="scp-code-card cancelled">
                        <span>Cancelled</span>
                        <strong>{{ $digitalCodes['cancelled'] }}</strong>
                    </div>

                    <div class="scp-code-card expired">
                        <span>Expired</span>
                        <strong>{{ $digitalCodes['expired'] }}</strong>
                    </div>
                </div>
            </div>

            <div class="scp-card">
                <h2 class="scp-section-title">Business Overview</h2>
                <div class="scp-section-subtitle">Quick snapshot for investor-friendly presentation</div>

                <div class="scp-overview-list">
                    <div class="scp-overview-row">
                        <span>Average Sales / Order</span>
                        <strong>
                            {{ $stats['orders_count'] > 0 ? $this->money($stats['total_sales'] / $stats['orders_count'], $mainCurrency) : $this->money(0, $mainCurrency) }}
                        </strong>
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
                            <td>
                                <span class="badge {{ $badgeClass($order->status) }}">
                                    {{ $enumLabel($order->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass($order->payment_status) }}">
                                    {{ $enumLabel($order->payment_status) }}
                                </span>
                            </td>
                            <td class="strong">
                                {{ $this->money($order->grand_total, $order->currency?->symbol ?? $mainCurrency) }}
                            </td>
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
                            <td>
                                {{ $payment->customer?->getDisplayName() ?? $payment->order?->customer?->getDisplayName() ?? '-' }}
                            </td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>
                                <span class="badge {{ $badgeClass($payment->status) }}">
                                    {{ $enumLabel($payment->status) }}
                                </span>
                            </td>
                            <td class="strong">
                                {{ $this->money($payment->amount, $payment->currency?->symbol ?? $payment->order?->currency?->symbol ?? $mainCurrency) }}
                            </td>
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