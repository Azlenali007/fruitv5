<?php
/**
 * Admin Single Customer Detail & History View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Customer #' . ($customer['name'] ?? 'Details') . ' - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'customers';
require_once __DIR__ . '/layout-header.php';

$customerId = (int)$customer['id'];
$orders = Order::getByCustomerId($customerId);
$totalOrders = count($orders);
$totalSpent = 0;
foreach ($orders as $o) {
    if ($o['order_status'] !== 'cancelled') {
        $totalSpent += (float)$o['total_amount'];
    }
}
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/admin/customers" class="p-1 text-gray-500 hover:text-gray-900 transition" aria-label="Back to customers">
                &larr; Back to Customers
            </a>
            <h1 class="text-2xl font-black text-gray-900">
                Customer: <?= e($customer['name']) ?>
            </h1>
        </div>
        <span class="text-xs text-gray-400">Account ID #<?= $customerId ?></span>
    </div>

    <!-- Customer Overview Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Contact Details</span>
            <p class="font-bold text-gray-900 text-sm"><?= e($customer['name']) ?></p>
            <p class="text-xs text-gray-600 font-mono mt-0.5"><?= e($customer['phone']) ?></p>
            <p class="text-xs text-gray-500 mt-0.5"><?= !empty($customer['email']) ? e($customer['email']) : 'No email provided' ?></p>
        </div>

        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Saved Delivery Address</span>
            <p class="text-xs text-gray-700 leading-relaxed">
                <?= !empty($customer['address']) ? e($customer['address']) : 'No address saved' ?>
            </p>
            <p class="text-[11px] text-gray-400 mt-1">
                <?= e($customer['city'] ?? '') ?>, <?= e($customer['state'] ?? '') ?> - <?= e($customer['pincode'] ?? '') ?>
            </p>
        </div>

        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Account Status</span>
            <div class="space-y-1 text-xs">
                <div>
                    Status: 
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $customer['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' ?>">
                        <?= ucfirst($customer['status']) ?>
                    </span>
                </div>
                <div class="text-gray-500">Registered: <?= date('d M Y, h:i A', strtotime($customer['created_at'])) ?></div>
                <div class="text-gray-500">Last Login: <?= !empty($customer['last_login_at']) ? date('d M Y, h:i A', strtotime($customer['last_login_at'])) : 'Never logged in' ?></div>
            </div>
        </div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total Lifetime Orders</span>
            <p class="text-2xl font-black text-gray-900"><?= $totalOrders ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total Lifetime Revenue</span>
            <p class="text-2xl font-black text-brand-600">₹<?= number_format($totalSpent, 2) ?></p>
        </div>
    </div>

    <!-- Orders Placed by Customer -->
    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Orders by this Customer (<?= $totalOrders ?>)</h2>

        <?php if (empty($orders)): ?>
        <p class="text-xs text-gray-400 py-6 text-center">No orders have been recorded for this customer yet.</p>
        <?php else: ?>
        <div class="divide-y divide-gray-100 text-xs">
            <?php foreach ($orders as $order): ?>
            <div class="py-3.5 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                <div>
                    <a href="/admin/orders/view?id=<?= $order['id'] ?>" class="font-bold text-brand-600 hover:underline text-sm">
                        Order #<?= e($order['order_number']) ?>
                    </a>
                    <span class="text-[11px] text-gray-400 block"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                    <span class="text-gray-600 block text-[11px] mt-0.5">
                        <?php foreach ($order['items'] as $it): ?>
                            <?= e($it['product_name']) ?> &times; <?= (int)$it['quantity'] ?>;
                        <?php endforeach; ?>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <?php
                    $statusColors = [
                        'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'confirmed' => 'bg-blue-50 text-blue-800 border-blue-200',
                        'delivered' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        'cancelled' => 'bg-red-50 text-red-800 border-red-200'
                    ];
                    $badgeClass = $statusColors[$order['order_status']] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                    ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border <?= $badgeClass ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                    <span class="font-black text-gray-950 text-sm">₹<?= number_format($order['total_amount'], 2) ?></span>
                    <a href="/admin/orders/view?id=<?= $order['id'] ?>" class="text-xs font-bold text-brand-600 hover:text-brand-800 ml-2">
                        Inspect &rarr;
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
