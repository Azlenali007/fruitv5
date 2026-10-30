<?php
/**
 * Your Cart View - Exact Match to Reference Image Bottom Left Screen
 * N.A Fresh Fruits & Coconuts
 */
$pageTitle = 'Your Cart - N.A Fresh Fruits & Coconuts';
$activePage = 'cart';
require_once __DIR__ . '/layouts/header.php';

$cartData = CartService::getCart();
$items = $cartData['items'];
$customer = CustomerAuthService::user();
$customerId = $customer ? (int)$customer['id'] : null;
$phone = $customer['phone'] ?? '';

$totals = CartService::calculateCheckoutTotals('delivery', $customerId, $phone);
$subtotal = $totals['subtotal'];
$originalDelivery = $totals['original_delivery_charge'];
$deliveryCharge = $totals['delivery_charge'];
$discountAmount = $totals['discount_amount'];
$deliveryDiscount = $totals['delivery_discount'];
$appliedCoupon = $totals['coupon'];
$couponCalc = $totals['coupon_calculation'];
$total = $totals['total'];
?>

<div class="max-w-md mx-auto px-4 py-4 sm:py-6">
    
    <!-- Header with Back Arrow and Clear All -->
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <a href="/shop" class="p-1 text-gray-700 hover:text-brand-600 transition" aria-label="Back to shop">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-lg font-bold text-gray-900 tracking-tight">Your Cart</h1>
        </div>

        <?php if (!empty($items)): ?>
        <form action="/cart/clear" method="POST" onsubmit="return confirm('Clear all items from your cart?');">
            <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
            <button type="submit" class="text-xs font-semibold text-gray-500 hover:text-red-600 transition">
                Clear All
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Flash Notification Banner -->
    <?php $flash = get_flash(); if ($flash): ?>
    <div class="mb-4 p-3 rounded-xl text-xs font-semibold flex items-center gap-2 <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : ($flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200') ?>">
        <?php if ($flash['type'] === 'error'): ?>
        <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <?php elseif ($flash['type'] === 'success'): ?>
        <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <?php else: ?>
        <svg class="w-4 h-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <?php endif; ?>
        <span><?= e($flash['message']) ?></span>
    </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
    <!-- Empty Cart State -->
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm text-center space-y-4 my-6">
        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-brand-600 flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Your Cart is Empty</h2>
        <p class="text-sm text-gray-500 max-w-xs mx-auto">
            You don't have any fresh coconut water in your cart yet. Stay healthy and hydrated!
        </p>
        <div class="pt-2">
            <a href="/shop" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm px-6 py-3 rounded-xl shadow-xs transition">
                <span>Shop Fresh Coconuts</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>
    <?php else: ?>

    <!-- Cart Item List -->
    <div class="space-y-4 mb-6">
        <?php foreach ($items as $item): ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs relative">
            <div class="flex items-center gap-4">
                
                <!-- Product Thumbnail -->
                <div class="w-20 h-20 rounded-xl bg-emerald-50/70 p-1 shrink-0 overflow-hidden border border-gray-100">
                    <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-full object-cover rounded-lg">
                </div>

                <!-- Product Details & Controls -->
                <div class="flex-grow min-w-0 pr-8">
                    <h3 class="font-bold text-gray-900 text-sm sm:text-base truncate">
                        <?= e($item['name']) ?>
                    </h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">
                        ₹<?= number_format($item['price']) ?> <span class="text-gray-400 font-normal">/pc</span>
                    </p>

                    <!-- Stepper in Cart -->
                    <div class="flex items-center gap-3 mt-3">
                        <form action="/cart/update" method="POST" class="inline">
                            <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <input type="hidden" name="quantity" value="<?= $item['quantity'] - 1 ?>">
                            <button type="submit" class="w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold flex items-center justify-center text-sm transition">
                                -
                            </button>
                        </form>

                        <span class="font-extrabold text-sm text-gray-900 w-4 text-center">
                            <?= $item['quantity'] ?>
                        </span>

                        <form action="/cart/update" method="POST" class="inline">
                            <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                            <button 
                                type="submit" 
                                <?= $item['quantity'] >= $item['max_stock'] ? 'disabled class="w-7 h-7 rounded-md bg-gray-50 text-gray-300 cursor-not-allowed flex items-center justify-center text-sm"' : 'class="w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold flex items-center justify-center text-sm transition"' ?>
                            >
                                +
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Delete Trash Icon (Top Right of Card) -->
                <form action="/cart/remove" method="POST" class="absolute top-4 right-4">
                    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                    <button type="submit" class="text-gray-400 hover:text-red-500 p-1 transition" title="Remove item">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>

                <!-- Line Total (Bottom Right) -->
                <div class="absolute bottom-4 right-4 text-right">
                    <span class="font-extrabold text-base text-gray-900">₹<?= number_format($item['subtotal']) ?></span>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- COUPON CODE MODULE -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs mb-4">
        <div class="flex items-center gap-2 mb-2.5">
            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <span class="text-xs font-bold text-gray-800 uppercase tracking-wide">Promotional Coupon</span>
        </div>

        <?php if ($appliedCoupon): ?>
            <!-- Currently Applied Coupon Banner -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between gap-3">
                <div class="space-y-0.5 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-xs text-brand-900 tracking-wider bg-white px-2 py-0.5 rounded border border-emerald-300">
                            <?= e($appliedCoupon['code']) ?>
                        </span>
                        <span class="text-[11px] font-bold text-emerald-800">
                            <?= e($couponCalc['benefit_message'] ?? 'Applied') ?>
                        </span>
                    </div>
                    <p class="text-[10px] text-emerald-700 truncate"><?= e($appliedCoupon['name']) ?></p>
                </div>
                <form action="/cart/remove-coupon" method="POST" class="shrink-0">
                    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                    <input type="hidden" name="redirect_to" value="/cart">
                    <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700 hover:underline">
                        Remove
                    </button>
                </form>
            </div>
        <?php else: ?>
            <!-- Coupon Input Form -->
            <form action="/cart/apply-coupon" method="POST" class="flex gap-2">
                <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="redirect_to" value="/cart">
                <input type="hidden" name="fulfilment_method" value="delivery">
                <div class="relative flex-grow">
                    <input 
                        type="text" 
                        name="coupon_code" 
                        placeholder="Enter coupon (e.g. FREEDELIVERY)" 
                        required 
                        oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_\-]/g, '')"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono font-bold tracking-wider uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    >
                </div>
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow-xs shrink-0">
                    Apply
                </button>
            </form>
            <p class="text-[10px] text-gray-400 mt-1.5">Try: <code class="text-brand-700 font-bold">FREEDELIVERY</code>, <code class="text-brand-700 font-bold">SAVE10</code>, or <code class="text-brand-700 font-bold">FLAT50</code></p>
        <?php endif; ?>
    </div>

    <!-- Pricing Summary Card -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs space-y-3 mb-6">
        <div class="flex justify-between items-center text-sm text-gray-500">
            <span>Subtotal</span>
            <span class="font-bold text-gray-900">₹<?= number_format($subtotal, 2) ?></span>
        </div>

        <?php if ($discountAmount > 0): ?>
        <div class="flex justify-between items-center text-sm text-emerald-700 font-semibold">
            <span>Coupon Discount (<?= e($appliedCoupon['code']) ?>)</span>
            <span>-₹<?= number_format($discountAmount, 2) ?></span>
        </div>
        <?php endif; ?>

        <div class="flex justify-between items-center text-sm text-gray-500">
            <span>Delivery Charge</span>
            <?php if ($deliveryDiscount > 0): ?>
                <div class="space-x-1">
                    <span class="line-through text-gray-400">₹<?= number_format($originalDelivery, 2) ?></span>
                    <span class="font-bold text-emerald-700">FREE</span>
                </div>
            <?php else: ?>
                <span class="font-bold text-gray-900">₹<?= number_format($deliveryCharge, 2) ?></span>
            <?php endif; ?>
        </div>

        <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
            <div>
                <span class="font-extrabold text-base text-gray-900 block leading-tight">Total</span>
                <?php if ($totals['total_savings'] > 0): ?>
                <span class="text-[11px] font-bold text-emerald-600">Saved ₹<?= number_format($totals['total_savings'], 2) ?> with coupon!</span>
                <?php endif; ?>
            </div>
            <span class="font-black text-2xl text-gray-950">₹<?= number_format($total, 2) ?></span>
        </div>
    </div>

    <!-- Proceed to Checkout Button -->
    <div class="mb-6">
        <a href="/checkout" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-base py-3.5 px-6 rounded-xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition active:scale-[0.99]">
            <span>Proceed to Checkout</span>
        </a>
    </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
