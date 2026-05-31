<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $latestOrders = $this->getLatestOrders();
        $latestPayments = $this->getLatestPayments();
        $lowStockProducts = $this->getLowStockProducts();
        $lowStockVariants = $this->getLowStockVariants();
        $digitalCodes = $this->getDigitalCodeSummary();
    @endphp

    <div class="space-y-6">

        {{-- Main Stats --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm text-gray-500">Total Sales</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ $this->money($stats['total_sales']) }}
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm text-gray-500">Paid Payments</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ $this->money($stats['paid_payments']) }}
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm text-gray-500">Orders</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ $stats['orders_count'] }}
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm text-gray-500">Customers</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ $stats['customers_count'] }}
                </div>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm text-gray-500">Products</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ $stats['products_count'] }}
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm text-gray-500">Active Carts</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ $stats['active_carts_count'] }}
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm text-gray-500">Invoices</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ $stats['invoices_count'] }}
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm text-gray-500">Low Stock</div>
                <div class="mt-2 text-2xl font-bold text-red-600">
                    {{ $stats['low_stock_products_count'] + $stats['low_stock_variants_count'] }}
                </div>
            </div>
        </div>

        {{-- Digital Codes --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h2 class="text-lg font-bold text-gray-950 dark:text-white">Digital Codes Summary</h2>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-5">
                <div class="rounded-lg bg-green-50 p-4 dark:bg-green-950/30">
                    <div class="text-sm text-green-700 dark:text-green-300">Available</div>
                    <div class="mt-1 text-xl font-bold">{{ $digitalCodes['available'] }}</div>
                </div>

                <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-950/30">
                    <div class="text-sm text-yellow-700 dark:text-yellow-300">Reserved</div>
                    <div class="mt-1 text-xl font-bold">{{ $digitalCodes['reserved'] }}</div>
                </div>

                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-950/30">
                    <div class="text-sm text-blue-700 dark:text-blue-300">Sold</div>
                    <div class="mt-1 text-xl font-bold">{{ $digitalCodes['sold'] }}</div>
                </div>

                <div class="rounded-lg bg-red-50 p-4 dark:bg-red-950/30">
                    <div class="text-sm text-red-700 dark:text-red-300">Cancelled</div>
                    <div class="mt-1 text-xl font-bold">{{ $digitalCodes['cancelled'] }}</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                    <div class="text-sm text-gray-700 dark:text-gray-300">Expired</div>
                    <div class="mt-1 text-xl font-bold">{{ $digitalCodes['expired'] }}</div>
                </div>
            </div>
        </div>

        {{-- Latest Orders --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h2 class="text-lg font-bold text-gray-950 dark:text-white">Latest Orders</h2>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b text-left text-gray-500">
                        <th class="py-2">Order</th>
                        <th class="py-2">Customer</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Payment</th>
                        <th class="py-2">Total</th>
                        <th class="py-2">Date</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($latestOrders as $order)
                        <tr class="border-b last:border-b-0">
                            <td class="py-2 font-medium">{{ $order->order_number }}</td>
                            <td class="py-2">{{ $order->customer?->getDisplayName() ?? '-' }}</td>
                            <td class="py-2">{{ $order->status?->label() ?? $order->status }}</td>
                            <td class="py-2">{{ $order->payment_status?->label() ?? $order->payment_status }}</td>
                            <td class="py-2">{{ $this->money($order->grand_total, $order->currency?->symbol ?? '₪') }}</td>
                            <td class="py-2">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">No orders found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Latest Payments --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h2 class="text-lg font-bold text-gray-950 dark:text-white">Latest Payments</h2>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b text-left text-gray-500">
                        <th class="py-2">Payment</th>
                        <th class="py-2">Order</th>
                        <th class="py-2">Customer</th>
                        <th class="py-2">Method</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Amount</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($latestPayments as $payment)
                        <tr class="border-b last:border-b-0">
                            <td class="py-2 font-medium">{{ $payment->payment_number }}</td>
                            <td class="py-2">{{ $payment->order?->order_number ?? '-' }}</td>
                            <td class="py-2">{{ $payment->customer?->getDisplayName() ?? $payment->order?->customer?->getDisplayName() ?? '-' }}</td>
                            <td class="py-2">{{ $payment->payment_method }}</td>
                            <td class="py-2">{{ $payment->status?->label() ?? $payment->status }}</td>
                            <td class="py-2">{{ $this->money($payment->amount, $payment->currency?->symbol ?? $payment->order?->currency?->symbol ?? '₪') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">No payments found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Low Stock --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <h2 class="text-lg font-bold text-gray-950 dark:text-white">Low Stock Products</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b text-left text-gray-500">
                            <th class="py-2">Product</th>
                            <th class="py-2">SKU</th>
                            <th class="py-2">Stock</th>
                            <th class="py-2">Min</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($lowStockProducts as $product)
                            <tr class="border-b last:border-b-0">
                                <td class="py-2 font-medium">{{ $product->getName('ar') }}</td>
                                <td class="py-2">{{ $product->sku ?? '-' }}</td>
                                <td class="py-2 text-red-600">{{ $product->stock_quantity }}</td>
                                <td class="py-2">{{ $product->min_stock_quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">No low stock products.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <h2 class="text-lg font-bold text-gray-950 dark:text-white">Low Stock Variants</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b text-left text-gray-500">
                            <th class="py-2">Variant</th>
                            <th class="py-2">SKU</th>
                            <th class="py-2">Stock</th>
                            <th class="py-2">Min</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($lowStockVariants as $variant)
                            <tr class="border-b last:border-b-0">
                                <td class="py-2 font-medium">{{ $variant->getName('ar') }}</td>
                                <td class="py-2">{{ $variant->sku ?? '-' }}</td>
                                <td class="py-2 text-red-600">{{ $variant->stock_quantity }}</td>
                                <td class="py-2">{{ $variant->min_stock_quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">No low stock variants.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>