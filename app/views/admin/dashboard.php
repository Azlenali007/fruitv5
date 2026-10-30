<?php
/**
 * Admin Dashboard View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Admin Dashboard - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'dashboard';
require_once __DIR__ . '/layout-header.php';

$stats = Order::getDashboardStats();
$recentOrders = Order::getAll([], 10, 0);
$product = Product::getPrimary();
?>

<div class="space-y-8">
    
    <!-- Top Welcome & Low Stock Warning -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Store Dashboard</h1>
            <p class="text-xs text-gray-500">Live operational overview for N.A Fresh Fruits & Coconuts</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold px-3 py-1.5 rounded-full <?= ($product['stock_quantity'] <= 20) ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                Coconut Stock: <?= (int)$product['stock_quantity'] ?> left
            </span>
            <a href="/admin/products" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                Manage Stock
            </a>
        </div>
    </div>

    <!-- 6 KPI Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- 1. Sales Today -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sales Today</span>
            <p class="text-2xl sm:text-3xl font-black text-gray-950">₹<?= number_format($stats['sales_today']) ?></p>
            <span class="text-[11px] text-emerald-600 font-semibold">Real-time non-cancelled</span>
        </div>

        <!-- 2. Orders Today -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Orders Today</span>
            <p class="text-2xl sm:text-3xl font-black text-gray-950"><?= $stats['orders_today'] ?></p>
            <span class="text-[11px] text-gray-400 font-medium"><?= $stats['pending_orders'] ?> currently pending</span>
        </div>

        <!-- 3. Total Revenue -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Sales</span>
            <p class="text-2xl sm:text-3xl font-black text-brand-700">₹<?= number_format($stats['total_revenue']) ?></p>
            <span class="text-[11px] text-gray-400 font-medium">All completed orders</span>
        </div>

        <!-- 4. Coconuts Sold -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Coconuts Sold</span>
            <p class="text-2xl sm:text-3xl font-black text-gray-950"><?= $stats['coconuts_sold'] ?></p>
            <span class="text-[11px] text-gray-400 font-medium">Tender units fulfilled</span>
        </div>

    </div>

    <!-- Status Breakdown Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-amber-50/70 border border-amber-200/60 p-3.5 rounded-xl text-center">
            <span class="text-[11px] font-bold uppercase text-amber-700 block">Pending</span>
            <span class="text-xl font-extrabold text-amber-900"><?= $stats['pending_orders'] ?></span>
        </div>
        <div class="bg-emerald-50/70 border border-emerald-200/60 p-3.5 rounded-xl text-center">
            <span class="text-[11px] font-bold uppercase text-emerald-700 block">Confirmed</span>
            <span class="text-xl font-extrabold text-emerald-900"><?= $stats['confirmed_orders'] ?></span>
        </div>
        <div class="bg-blue-50/70 border border-blue-200/60 p-3.5 rounded-xl text-center">
            <span class="text-[11px] font-bold uppercase text-blue-700 block">Delivered</span>
            <span class="text-xl font-extrabold text-blue-900"><?= $stats['delivered_orders'] ?></span>
        </div>
        <div class="bg-red-50/70 border border-red-200/60 p-3.5 rounded-xl text-center">
            <span class="text-[11px] font-bold uppercase text-red-700 block">Cancelled</span>
            <span class="text-xl font-extrabold text-red-900"><?= $stats['cancelled_orders'] ?></span>
        </div>
    </div>

    <!-- 7-Day Sales Trend Chart (Chart.js) -->
    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-base font-bold text-gray-900">7-Day Sales Performance</h2>
                <p class="text-xs text-gray-400">Daily revenue from fresh coconut water sales</p>
            </div>
            <a href="/admin/reports" class="text-xs font-semibold text-brand-600 hover:underline">
                View Full Reports &rarr;
            </a>
        </div>
        
        <div class="h-64 w-full">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-base font-bold text-gray-900">Recent Customer Orders</h2>
            <a href="/admin/orders" class="text-xs font-semibold text-brand-600 hover:underline">
                View All Orders &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-3">Order</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3">Items</th>
                        <th class="py-3 px-3">Total</th>
                        <th class="py-3 px-3">Fulfilment</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-400">No orders placed yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recentOrders as $ro): ?>
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="py-3 px-3 font-bold text-gray-900">
                            #<?= e($ro['order_number']) ?>
                            <span class="block text-[10px] text-gray-400 font-normal"><?= date('M j, g:i A', strtotime($ro['created_at'])) ?></span>
                        </td>
                        <td class="py-3 px-3 font-medium text-gray-800">
                            <?= e($ro['customer_name']) ?>
                            <span class="block text-[10px] text-gray-400"><?= e($ro['customer_phone']) ?></span>
                        </td>
                        <td class="py-3 px-3 text-gray-600">
                            <?php foreach ($ro['items'] as $item): ?>
                                <div><?= (int)$item['quantity'] ?>x <?= e($item['product_name']) ?></div>
                            <?php endforeach; ?>
                        </td>
                        <td class="py-3 px-3 font-bold text-gray-900">
                            ₹<?= number_format($ro['total_amount']) ?>
                            <span class="block text-[10px] uppercase font-semibold text-gray-400"><?= e($ro['payment_method']) ?></span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="capitalize text-gray-700 font-medium"><?= e($ro['fulfilment_method']) ?></span>
                        </td>
                        <td class="py-3 px-3">
                            <?php
                            $badge = [
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'delivered' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                            ][$ro['order_status']] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border <?= $badge ?>">
                                <?= e($ro['order_status']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="/admin/orders/view?id=<?= $ro['id'] ?>" class="text-xs font-bold text-brand-600 hover:text-brand-800 underline">
                                Manage
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Setup Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    const dates = <?= json_encode($stats['trend_dates']) ?>;
    const sales = <?= json_encode($stats['trend_sales']) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Sales (₹)',
                data: sales,
                backgroundColor: 'rgba(35, 93, 58, 0.75)',
                borderColor: '#235d3a',
                borderWidth: 1.5,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) { return '₹' + val; }
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
