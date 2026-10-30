<?php
/**
 * Admin Sales Reports & Analytics
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Sales Reports - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'reports';
require_once __DIR__ . '/layout-header.php';

$pdo = Database::getConnection();

// Date range filters
$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// 1. Total revenue in range
$stmtRev = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_revenue,
        COALESCE(AVG(total_amount), 0) as avg_order_val
    FROM `orders`
    WHERE DATE(created_at) BETWEEN ? AND ? AND order_status != 'cancelled'
");
$stmtRev->execute([$dateFrom, $dateTo]);
$summary = $stmtRev->fetch();

// 2. Breakdown by Payment Method
$stmtPay = $pdo->prepare("
    SELECT 
        payment_method, 
        COUNT(*) as cnt, 
        COALESCE(SUM(total_amount), 0) as total 
    FROM `orders` 
    WHERE DATE(created_at) BETWEEN ? AND ? AND order_status != 'cancelled'
    GROUP BY payment_method
");
$stmtPay->execute([$dateFrom, $dateTo]);
$payments = $stmtPay->fetchAll();

$codTotal = 0; $codCount = 0;
$onlineTotal = 0; $onlineCount = 0;
foreach ($payments as $p) {
    if ($p['payment_method'] === 'cod') {
        $codTotal = (float)$p['total'];
        $codCount = (int)$p['cnt'];
    } else {
        $onlineTotal = (float)$p['total'];
        $onlineCount = (int)$p['cnt'];
    }
}

// 3. Status Breakdown
$stmtStatus = $pdo->prepare("
    SELECT order_status, COUNT(*) as cnt 
    FROM `orders` 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY order_status
");
$stmtStatus->execute([$dateFrom, $dateTo]);
$statusRows = $stmtStatus->fetchAll();

$statusMap = ['pending' => 0, 'confirmed' => 0, 'delivered' => 0, 'cancelled' => 0];
foreach ($statusRows as $sr) {
    if (isset($statusMap[$sr['order_status']])) {
        $statusMap[$sr['order_status']] = (int)$sr['cnt'];
    }
}
?>

<div class="space-y-6">
    
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Sales Reports & Financial Analytics</h1>
            <p class="text-xs text-gray-500">Database-backed accounting reports for N.A Fresh Fruits & Coconuts</p>
        </div>

        <!-- Date Range Filter Form -->
        <form action="/admin/reports" method="GET" class="flex items-center gap-2 text-xs">
            <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-brand-600 focus:outline-none">
            <span class="text-gray-400">to</span>
            <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-brand-600 focus:outline-none">
            <button type="submit" class="bg-brand-600 text-white font-bold px-3 py-1.5 rounded-lg hover:bg-brand-700 transition">Filter</button>
        </form>
    </div>

    <!-- 4 High Level Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase">Period Revenue</span>
            <p class="text-2xl sm:text-3xl font-black text-brand-700">₹<?= number_format($summary['total_revenue']) ?></p>
            <span class="text-[10px] text-gray-400">Net valid sales</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase">Valid Orders</span>
            <p class="text-2xl sm:text-3xl font-black text-gray-950"><?= $summary['total_orders'] ?></p>
            <span class="text-[10px] text-gray-400">Excludes cancellations</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase">Avg. Order Value</span>
            <p class="text-2xl sm:text-3xl font-black text-gray-950">₹<?= number_format($summary['avg_order_val']) ?></p>
            <span class="text-[10px] text-gray-400">Per transaction</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase">Cash on Delivery</span>
            <p class="text-2xl sm:text-3xl font-black text-gray-950">₹<?= number_format($codTotal) ?></p>
            <span class="text-[10px] text-gray-400"><?= $codCount ?> COD orders</span>
        </div>

    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Status Distribution Chart (Doughnut) -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Order Status Breakdown</h2>
            <div class="h-60 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="grid grid-cols-4 gap-2 pt-2 text-center text-xs border-t border-gray-50">
                <div>
                    <span class="text-gray-400 block text-[10px]">Pending</span>
                    <span class="font-bold text-amber-600"><?= $statusMap['pending'] ?></span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px]">Confirmed</span>
                    <span class="font-bold text-emerald-600"><?= $statusMap['confirmed'] ?></span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px]">Delivered</span>
                    <span class="font-bold text-blue-600"><?= $statusMap['delivered'] ?></span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px]">Cancelled</span>
                    <span class="font-bold text-red-600"><?= $statusMap['cancelled'] ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Method Comparison (Bar) -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Payment Collection Channel</h2>
            <div class="h-60 flex items-center justify-center">
                <canvas id="paymentChart"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 text-center text-xs border-t border-gray-50">
                <div>
                    <span class="text-gray-400 block text-[10px]">COD Collection</span>
                    <span class="font-bold text-gray-800">₹<?= number_format($codTotal) ?> (<?= $codCount ?> orders)</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px]">Online Payment</span>
                    <span class="font-bold text-gray-800">₹<?= number_format($onlineTotal) ?> (<?= $onlineCount ?> orders)</span>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Status Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Confirmed', 'Delivered', 'Cancelled'],
            datasets: [{
                data: [<?= $statusMap['pending'] ?>, <?= $statusMap['confirmed'] ?>, <?= $statusMap['delivered'] ?>, <?= $statusMap['cancelled'] ?>],
                backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 2. Payment Chart
    const ctxPayment = document.getElementById('paymentChart').getContext('2d');
    new Chart(ctxPayment, {
        type: 'bar',
        data: {
            labels: ['Cash on Delivery', 'Online Payment'],
            datasets: [{
                label: 'Revenue (₹)',
                data: [<?= $codTotal ?>, <?= $onlineTotal ?>],
                backgroundColor: ['#235d3a', '#3b82f6'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(val) { return '₹' + val; } }
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
