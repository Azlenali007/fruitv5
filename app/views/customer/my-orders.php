<?php
/**
 * Customer My Orders View
 * N.A Fresh Fruits & Coconuts
 * Shows strictly authenticated customer's own orders.
 */
$pageTitle = 'My Orders - N.A Fresh Fruits & Coconuts';
$activePage = 'track';
require_once __DIR__ . '/../layouts/header.php';

$customer = CustomerAuthService::user();
$customerId = (int)$customer['id'];
$orders = Order::getByCustomerId($customerId);
?>

<div class="max-w-3xl mx-auto px-4 py-8 sm:py-10 space-y-6">
    <!-- Header with Back to Account link -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/account" class="p-1 text-gray-700 hover:text-brand-600 transition" aria-label="Back to Account">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">My Order History</h1>
                <p class="text-xs text-gray-500">All past and active coconut water orders for <?= e($customer['name']) ?></p>
            </div>
        </div>

        <a href="/shop" class="bg-brand-600 text-white text-xs font-bold py-2 px-3.5 rounded-xl hover:bg-brand-700 transition">
            + New Order
        </a>
    </div>

    <?php if (empty($orders)): ?>
    <div class="bg-white rounded-3xl p-12 border border-gray-100 shadow-sm text-center space-y-4">
        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-brand-600 flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        </div>
        <h2 class="text-lg font-bold text-gray-900">No Orders Found</h2>
        <p class="text-xs text-gray-500 max-w-xs mx-auto">You haven't placed any coconut water orders yet.</p>
        <div class="pt-2">
            <a href="/shop" class="bg-brand-600 text-white text-xs font-bold py-2.5 px-6 rounded-xl inline-block hover:bg-brand-700 transition">
                Start Shopping
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($orders as $order): ?>
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm space-y-4 hover:border-gray-200 transition">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 border-b border-gray-100 pb-3">
                <div>
                    <span class="font-black text-gray-900 text-base">Order #<?= e($order['order_number']) ?></span>
                    <span class="text-xs text-gray-400 block mt-0.5">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
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
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase border <?= $badgeClass ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                    <span class="font-black text-gray-950 text-base">₹<?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>

            <!-- Items Snapshot -->
            <div class="divide-y divide-gray-50 text-xs">
                <?php foreach ($order['items'] as $item): ?>
                <div class="py-2 flex justify-between items-center text-gray-700">
                    <div>
                        <span class="font-bold text-gray-900"><?= e($item['product_name']) ?></span>
                        <span class="text-gray-400 text-[11px] block">Unit price ₹<?= number_format($item['unit_price'], 2) ?> &times; <?= (int)$item['quantity'] ?></span>
                    </div>
                    <span class="font-bold text-gray-900">₹<?= number_format($item['subtotal'], 2) ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Delivery & Payment Footer -->
            <div class="bg-gray-50 rounded-2xl p-3.5 text-xs text-gray-600 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                <div>
                    <span class="font-semibold text-gray-800 block text-[11px]">
                        <?= $order['fulfilment_method'] === 'delivery' ? 'Delivery to: ' . e($order['delivery_address']) : 'Shop Pickup' ?>
                    </span>
                    <span class="text-[11px] text-gray-400">
                        Payment: <?= strtoupper($order['payment_method']) ?> (<?= ucfirst($order['payment_status']) ?>)
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="/track?h=<?= urlencode($order['tracking_hash']) ?>" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-2 px-4 rounded-xl transition inline-flex items-center gap-1 shadow-xs">
                        <span>Track Order</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
