<?php
/**
 * Admin Coupon Usage Detail & Orders History View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Coupon Details: ' . $coupon['code'] . ' - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'coupons';
require_once __DIR__ . '/layout-header.php';

$usage = Coupon::getUsageDetails($coupon['id']);
$stats = $usage['stats'];
$orders = $usage['orders'];
?>

<div class="space-y-6">

    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="/admin/coupons" class="p-2 text-gray-500 hover:text-brand-600 hover:bg-gray-100 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono font-black text-brand-800 bg-brand-50 border border-brand-200 px-2.5 py-0.5 rounded-md text-sm tracking-wider">
                        <?= e($coupon['code']) ?>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold <?= $coupon['is_active'] ? 'bg-emerald-50 text-emerald-800' : 'bg-gray-100 text-gray-600' ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $coupon['is_active'] ? 'bg-emerald-500' : 'bg-gray-400' ?>"></span>
                        <?= $coupon['is_active'] ? 'Active' : 'Disabled' ?>
                    </span>
                </div>
                <h1 class="text-xl font-black text-gray-900 mt-1"><?= e($coupon['name']) ?></h1>
                <?php if (!empty($coupon['description'])): ?>
                <p class="text-xs text-gray-500 mt-0.5"><?= e($coupon['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="/admin/coupons/edit?id=<?= $coupon['id'] ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs py-2 px-3.5 rounded-xl transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit Coupon</span>
            </a>
            <form action="/admin/coupons/toggle-status" method="POST" class="inline">
                <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="coupon_id" value="<?= $coupon['id'] ?>">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-2 px-3.5 rounded-xl shadow-xs transition">
                    <?= $coupon['is_active'] ? 'Deactivate' : 'Activate' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Usage Statistics Overview -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase block">Times Redeemed</span>
            <span class="text-2xl font-black text-brand-700 mt-1 block"><?= (int)($stats['total_uses'] ?? 0) ?></span>
            <span class="text-[10px] text-gray-500">of <?= $coupon['max_uses'] ? (int)$coupon['max_uses'] : '∞' ?> max allowed</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase block">Unique Customers</span>
            <span class="text-2xl font-black text-gray-900 mt-1 block"><?= (int)($stats['unique_customers'] ?? 0) ?></span>
            <span class="text-[10px] text-gray-500">distinct phone numbers</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase block">Product Discounts</span>
            <span class="text-2xl font-black text-blue-600 mt-1 block">₹<?= number_format($stats['total_product_discount'] ?? 0, 2) ?></span>
            <span class="text-[10px] text-gray-500">direct price cuts</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase block">Delivery Discounts</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">₹<?= number_format($stats['total_delivery_discount'] ?? 0, 2) ?></span>
            <span class="text-[10px] text-gray-500">free delivery savings</span>
        </div>
    </div>

    <!-- Coupon Configuration Summary Card -->
    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Coupon Rules & Configuration</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-gray-400 font-semibold block">Type</span>
                <span class="font-bold text-gray-900 capitalize text-sm">
                    <?= $coupon['type'] === 'free_delivery' ? 'Free Delivery' : ($coupon['type'] === 'percentage' ? $coupon['discount_value'] . '% Discount' : '₹' . number_format($coupon['discount_value']) . ' Flat') ?>
                </span>
            </div>
            <div>
                <span class="text-gray-400 font-semibold block">Min Order Required</span>
                <span class="font-bold text-gray-900 text-sm">₹<?= number_format($coupon['min_order_amount']) ?></span>
            </div>
            <div>
                <span class="text-gray-400 font-semibold block">Per-Customer Limit</span>
                <span class="font-bold text-gray-900 text-sm"><?= $coupon['max_uses_per_customer'] ? $coupon['max_uses_per_customer'] . 'x' : 'Unlimited' ?></span>
            </div>
            <div>
                <span class="text-gray-400 font-semibold block">Expiry Date</span>
                <span class="font-bold text-gray-900 text-sm"><?= !empty($coupon['expiry_date']) ? date('d M Y, h:i A', strtotime($coupon['expiry_date'])) : 'Never Expires' ?></span>
            </div>
        </div>
    </div>

    <!-- Orders that Redeemed this Coupon -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden space-y-2">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Orders Redeemed With This Coupon</h2>
            <span class="text-xs text-gray-400 font-semibold"><?= count($orders) ?> order(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Discount Given</th>
                        <th class="py-3 px-4">Order Total</th>
                        <th class="py-3 px-4">Order Status</th>
                        <th class="py-3 px-4">Date Redeemed</th>
                        <th class="py-3 px-4 text-right">View Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400">
                            No orders have redeemed this coupon yet.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="py-3 px-4 font-mono font-bold text-brand-700">
                            #<?= e($ord['order_number']) ?>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-gray-900 block"><?= e($ord['customer_name']) ?></span>
                            <span class="text-[11px] text-gray-400"><?= e($ord['customer_phone']) ?></span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-emerald-700">
                                ₹<?= number_format((float)$ord['discount_amount'] + (float)$ord['delivery_discount'], 2) ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 font-extrabold text-gray-900">
                            ₹<?= number_format((float)$ord['total_amount'], 2) ?>
                        </td>
                        <td class="py-3 px-4">
                            <span class="capitalize px-2 py-0.5 rounded-full text-[10px] font-bold <?= $ord['order_status'] === 'delivered' ? 'bg-emerald-50 text-emerald-800' : ($ord['order_status'] === 'confirmed' ? 'bg-blue-50 text-blue-800' : 'bg-gray-100 text-gray-700') ?>">
                                <?= e($ord['order_status']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-500 text-[11px]">
                            <?= date('d M Y, h:i A', strtotime($ord['used_at'])) ?>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="/admin/orders/view?id=<?= $ord['order_id'] ?>" class="text-brand-600 hover:text-brand-700 font-bold hover:underline">
                                View Order &rarr;
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

<?php require_once __DIR__ . '/layout-footer.php'; ?>
