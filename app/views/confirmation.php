<?php
/**
 * Order Placed Successfully - Exact Match to Reference Image Bottom Right Screen
 * N.A Fresh Fruits & Coconuts
 */
$pageTitle = 'Order Placed Successfully! - N.A Fresh Fruits & Coconuts';
$activePage = 'cart';
require_once __DIR__ . '/layouts/header.php';
?>

<div class="max-w-md mx-auto px-4 py-8 sm:py-12 text-center">
    
    <!-- Big Green Checkmark with Confetti Sparkles -->
    <div class="relative w-24 h-24 mx-auto mb-6 flex items-center justify-center">
        <!-- Confetti / Sparkle SVGs around circle -->
        <span class="absolute -top-1 left-2 w-2 h-3.5 bg-yellow-400 rounded-xs rotate-45 transform"></span>
        <span class="absolute top-2 -right-1 w-2.5 h-1.5 bg-blue-400 rounded-xs -rotate-12 transform"></span>
        <span class="absolute bottom-2 -left-3 w-3 h-2 bg-emerald-400 rounded-xs rotate-25 transform"></span>
        <span class="absolute -bottom-1 right-3 w-2 h-2 bg-pink-400 rounded-xs rotate-45 transform"></span>
        <span class="absolute top-6 left-0 w-1.5 h-1.5 rounded-full bg-orange-400"></span>
        <span class="absolute bottom-6 right-0 w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

        <!-- Center Green Circle with Checkmark -->
        <div class="w-20 h-20 rounded-full bg-brand-600 text-white flex items-center justify-center shadow-lg transform transition hover:scale-105">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="3.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
    </div>

    <!-- Heading & Message -->
    <div class="space-y-2.5 mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-950 tracking-tight">
            Order Placed Successfully!
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 max-w-sm mx-auto leading-relaxed">
            Thank you for choosing <?= e(Setting::get('store_name', 'N.A Fresh Fruits & Coconuts')) ?>.
            Your order has been confirmed and will be delivered soon.
        </p>
    </div>

    <!-- Order ID & Delivery Card (Matches reference bottom right card) -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs mb-8 text-left">
        <div class="flex items-center gap-4">
            
            <!-- Delivery Truck Icon Container -->
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-brand-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8h4l3 4v4h-2"></path>
                </svg>
            </div>

            <!-- Details -->
            <div class="flex-grow space-y-1">
                <div class="flex justify-between items-baseline">
                    <span class="text-xs text-gray-400 font-semibold">Order ID</span>
                    <span class="text-sm font-extrabold text-gray-900 tracking-wide">#<?= e($order['order_number'] ?? 'NA1024') ?></span>
                </div>
                <div>
                    <span class="text-[11px] text-gray-400 font-medium block">Estimated Delivery</span>
                    <span class="text-xs font-bold text-gray-800"><?= e($order['estimated_delivery'] ?? 'Today, 4:30 PM - 6:30 PM') ?></span>
                </div>
            </div>

        </div>

        <!-- Ordered Items Summary -->
        <?php if (!empty($order['items'])): ?>
        <div class="border-t border-gray-100 mt-4 pt-3 space-y-2 text-xs">
            <?php foreach ($order['items'] as $item): ?>
            <div class="flex justify-between items-center text-gray-600">
                <span><?= (int)$item['quantity'] ?>x <?= e($item['product_name']) ?></span>
                <span class="font-bold text-gray-900">₹<?= number_format($item['subtotal']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (!empty($order['coupon_code'])): ?>
            <div class="flex justify-between items-center text-emerald-700 font-semibold pt-1">
                <span>Coupon Applied (<?= e($order['coupon_code']) ?>):</span>
                <span>-₹<?= number_format((float)($order['discount_amount'] ?? 0) + (float)($order['delivery_discount'] ?? 0), 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between items-center text-gray-500 pt-1">
                <span>Delivery:</span>
                <?php if ((float)($order['delivery_discount'] ?? 0) > 0): ?>
                    <span class="text-emerald-700 font-bold">FREE (Saved ₹<?= number_format($order['delivery_discount'], 2) ?>)</span>
                <?php else: ?>
                    <span>₹<?= number_format($order['delivery_charge'] ?? 40, 2) ?></span>
                <?php endif; ?>
            </div>
            <div class="flex justify-between items-center text-sm font-extrabold text-gray-950 pt-1 border-t border-gray-100">
                <span>Total Amount:</span>
                <span class="text-brand-700">₹<?= number_format($order['total_amount'] ?? 120, 2) ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Track Order Button -->
    <div class="space-y-3">
        <a 
            href="/track?num=<?= urlencode($order['order_number'] ?? '') ?>&h=<?= urlencode($order['tracking_hash'] ?? '') ?>" 
            class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-base py-3.5 px-6 rounded-xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition active:scale-[0.99]"
        >
            <span>Track Order</span>
        </a>

        <a href="/" class="block text-xs font-semibold text-gray-500 hover:text-brand-600 transition">
            Continue Shopping
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
