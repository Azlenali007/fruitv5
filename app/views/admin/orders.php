<?php
/**
 * Admin Orders Management View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Manage Orders - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'orders';
require_once __DIR__ . '/layout-header.php';

$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$totalOrdersCount = Order::countAll($filters);
$orders = Order::getAll($filters, $limit, $offset);
$totalPages = ceil($totalOrdersCount / $limit);
?>

<div class="space-y-6">
    
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Order Management</h1>
            <p class="text-xs text-gray-500">Filter, inspect, and update customer order statuses</p>
        </div>
        <div class="text-xs text-gray-500">
            Total Orders: <span class="font-bold text-gray-900"><?= $totalOrdersCount ?></span>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs">
        <form action="/admin/orders" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end text-xs">
            
            <!-- Search -->
            <div>
                <label class="block font-semibold text-gray-500 uppercase mb-1">Search</label>
                <input 
                    type="text" 
                    name="search" 
                    value="<?= e($filters['search']) ?>" 
                    placeholder="Order #, name, phone" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-semibold text-gray-500 uppercase mb-1">Status</label>
                <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $filters['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="delivered" <?= $filters['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block font-semibold text-gray-500 uppercase mb-1">From Date</label>
                <input 
                    type="date" 
                    name="date_from" 
                    value="<?= e($filters['date_from']) ?>" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <!-- Date To -->
            <div>
                <label class="block font-semibold text-gray-500 uppercase mb-1">To Date</label>
                <input 
                    type="date" 
                    name="date_to" 
                    value="<?= e($filters['date_to']) ?>" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-bold py-2 px-3 rounded-lg transition text-xs">
                    Apply Filter
                </button>
                <a href="/admin/orders" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-3 rounded-lg transition text-xs text-center">
                    Reset
                </a>
            </div>

        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase text-[10px] tracking-wider font-bold">
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Items</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Fulfilment</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" class="py-12 text-center text-gray-400">
                            No orders found matching the filter criteria.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="py-3.5 px-4 font-bold text-gray-900">
                            #<?= e($o['order_number']) ?>
                        </td>
                        <td class="py-3.5 px-4 text-gray-500 whitespace-nowrap">
                            <?= date('d M Y, h:i A', strtotime($o['created_at'])) ?>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-gray-900 block"><?= e($o['customer_name']) ?></span>
                            <span class="text-[11px] text-gray-400"><?= e($o['customer_phone']) ?></span>
                        </td>
                        <td class="py-3.5 px-4 text-gray-700">
                            <?php foreach ($o['items'] as $it): ?>
                            <div><?= (int)$it['quantity'] ?>x <?= e($it['product_name']) ?></div>
                            <?php endforeach; ?>
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-gray-950">
                            ₹<?= number_format($o['total_amount']) ?>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="capitalize text-gray-700 font-medium"><?= e($o['fulfilment_method']) ?></span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="uppercase text-[11px] font-bold text-gray-800 block"><?= e($o['payment_method']) ?></span>
                            <span class="text-[10px] text-gray-400 capitalize"><?= e($o['payment_status']) ?></span>
                        </td>
                        <td class="py-3.5 px-4">
                            <?php
                            $badge = [
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'delivered' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                            ][$o['order_status']] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border <?= $badge ?>">
                                <?= e($o['order_status']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="/admin/orders/view?id=<?= $o['id'] ?>" class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 px-3 py-1 rounded-lg transition">
                                <span>Manage</span>
                                &rarr;
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="p-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Showing page <?= $page ?> of <?= $totalPages ?></span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                <a href="/admin/orders?page=<?= $page - 1 ?>&status=<?= urlencode($filters['status']) ?>&search=<?= urlencode($filters['search']) ?>" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                <a href="/admin/orders?page=<?= $page + 1 ?>&status=<?= urlencode($filters['status']) ?>&search=<?= urlencode($filters['search']) ?>" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
