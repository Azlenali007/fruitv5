<?php
/**
 * Admin Single Order Detail & Status Management
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Order #' . ($order['order_number'] ?? '') . ' - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'orders';
require_once __DIR__ . '/layout-header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/admin/orders" class="p-1 text-gray-500 hover:text-gray-900 transition">
                &larr; Back to Orders
            </a>
            <h1 class="text-2xl font-black text-gray-900">
                Order #<?= e($order['order_number']) ?>
            </h1>
        </div>
        <span class="text-xs text-gray-400">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Order Summary & Items -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- Items Snapshot Card -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Ordered Items Snapshot</h2>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($order['items'] as $item): ?>
                    <div class="py-3 flex justify-between items-center text-sm">
                        <div>
                            <span class="font-bold text-gray-900"><?= e($item['product_name']) ?></span>
                            <span class="text-xs text-gray-400 block">Unit Price: ₹<?= number_format($item['unit_price'], 2) ?> &times; <?= (int)$item['quantity'] ?> units</span>
                        </div>
                        <span class="font-extrabold text-gray-900">₹<?= number_format($item['subtotal'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-t border-gray-100 pt-3 space-y-2 text-xs">
                    <div class="flex justify-between text-gray-600">
                        <span>Items Subtotal:</span>
                        <span class="font-bold">₹<?= number_format($order['subtotal'], 2) ?></span>
                    </div>
                    <?php if (!empty($order['coupon_code'])): ?>
                    <div class="flex justify-between text-emerald-700 font-semibold">
                        <span>Coupon (<?= e($order['coupon_code']) ?>):</span>
                        <span>-₹<?= number_format((float)($order['discount_amount'] ?? 0) + (float)($order['delivery_discount'] ?? 0), 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-gray-600">
                        <span>Delivery Fee:</span>
                        <?php if ((float)($order['delivery_discount'] ?? 0) > 0): ?>
                            <span class="space-x-1">
                                <span class="line-through text-gray-400">₹<?= number_format($order['original_delivery_charge'] ?? 40, 2) ?></span>
                                <span class="font-bold text-emerald-700">FREE</span>
                            </span>
                        <?php else: ?>
                            <span class="font-bold">₹<?= number_format($order['delivery_charge'], 2) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex justify-between text-base font-black text-gray-950 pt-2 border-t border-gray-100">
                        <span>Total Payable:</span>
                        <span class="text-brand-700">₹<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Customer & Delivery Information -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Customer & Delivery Info</h2>
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-400 font-semibold block">Customer Name</span>
                        <span class="font-bold text-gray-900 text-sm"><?= e($order['customer_name']) ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-semibold block">Contact Phone</span>
                        <span class="font-bold text-gray-900 text-sm"><?= e($order['customer_phone']) ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-semibold block">Fulfilment Type</span>
                        <span class="capitalize font-bold text-gray-800"><?= e($order['fulfilment_method']) ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-semibold block">Payment Status</span>
                        <span class="uppercase font-bold text-gray-800"><?= e($order['payment_method']) ?> (<?= e(ucfirst($order['payment_status'])) ?>)</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-gray-400 font-semibold block">Delivery Address</span>
                        <p class="font-medium text-gray-700 mt-1"><?= e($order['delivery_address']) ?></p>
                    </div>
                    <?php if (!empty($order['delivery_area_name'])): ?>
                    <div>
                        <span class="text-gray-400 font-semibold block">Delivery Locality / Zone</span>
                        <span class="font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200 text-[11px] inline-block mt-1">
                            📍 <?= e($order['delivery_area_name']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['latitude']) && !empty($order['longitude'])): ?>
                    <div>
                        <span class="text-gray-400 font-semibold block">Map Coordinates</span>
                        <a href="https://www.google.com/maps?q=<?= $order['latitude'] ?>,<?= $order['longitude'] ?>" target="_blank" class="text-brand-600 font-mono font-bold hover:underline inline-flex items-center gap-1 text-[11px] mt-1">
                            <span><?= number_format((float)$order['latitude'], 5) ?>, <?= number_format((float)$order['longitude'], 5) ?></span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['notes'])): ?>
                    <div class="col-span-2">
                        <span class="text-gray-400 font-semibold block">Order Notes</span>
                        <p class="font-medium text-gray-700 italic mt-1"><?= e($order['notes']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Audit Logs -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-3">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Status Change History</h2>
                <div class="space-y-3 text-xs">
                    <?php foreach ($order['history'] as $h): ?>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                        <div class="w-2 h-2 rounded-full bg-brand-600 mt-1.5 shrink-0"></div>
                        <div class="flex-grow">
                            <span class="font-bold uppercase text-[10px] text-gray-900"><?= e($h['new_status']) ?></span>
                            <span class="text-gray-500 font-medium ml-2"><?= e($h['comment']) ?></span>
                            <div class="text-[10px] text-gray-400 mt-0.5">By <?= e($h['changed_by']) ?> on <?= date('d M Y, h:i:s A', strtotime($h['created_at'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Right Col: Change Order Status Form -->
        <div class="space-y-6">
            
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Update Order Status</h2>
                
                <form action="/admin/orders/update-status" method="POST" class="space-y-4 text-xs">
                    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                    <div>
                        <label class="block font-semibold text-gray-600 uppercase mb-1">Current Status</label>
                        <select name="new_status" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none">
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $order['order_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-600 uppercase mb-1">Status Note / Reason</label>
                        <input type="text" name="comment" placeholder="e.g. Prepared and dispatched with delivery partner" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    <div class="p-3 bg-amber-50 text-amber-800 rounded-xl text-[11px] leading-relaxed">
                        <strong>Inventory Policy:</strong> If an order is marked <span class="font-bold">Cancelled</span>, items are automatically and atomically returned to product stock.
                    </div>

                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 rounded-xl shadow-xs transition">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Customer Tracking Link Helper -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-xs space-y-2 text-xs">
                <span class="font-bold text-gray-900">Secure Tracking URL:</span>
                <p class="text-gray-400 text-[11px] break-all">
                    /track?num=<?= urlencode($order['order_number']) ?>&h=<?= urlencode($order['tracking_hash']) ?>
                </p>
                <a href="/track?num=<?= urlencode($order['order_number']) ?>&h=<?= urlencode($order['tracking_hash']) ?>" target="_blank" class="text-brand-600 font-bold hover:underline inline-block mt-1">
                    Preview Customer View &rarr;
                </a>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
