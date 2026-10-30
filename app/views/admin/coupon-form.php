<?php
/**
 * Admin Coupon Create / Edit Form View
 * N.A Fresh Fruits & Coconuts
 */
$isEdit = !empty($coupon['id']);
$adminTitle = ($isEdit ? 'Edit Coupon #' . $coupon['id'] : 'Create New Coupon') . ' - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'coupons';
require_once __DIR__ . '/layout-header.php';

$couponData = $coupon ?? [
    'id' => null,
    'code' => '',
    'name' => '',
    'description' => '',
    'type' => 'percentage',
    'discount_value' => '10.00',
    'min_order_amount' => '100.00',
    'max_discount_amount' => '50.00',
    'start_date' => '',
    'expiry_date' => '',
    'max_uses' => '',
    'max_uses_per_customer' => '1',
    'is_active' => 1
];

// Format datetime strings for HTML5 datetime-local input
$formattedStartDate = !empty($couponData['start_date']) ? date('Y-m-d\TH:i', strtotime($couponData['start_date'])) : '';
$formattedExpiryDate = !empty($couponData['expiry_date']) ? date('Y-m-d\TH:i', strtotime($couponData['expiry_date'])) : '';
?>

<div class="max-w-4xl mx-auto space-y-6" x-data="{
    code: '<?= e($couponData['code']) ?>',
    name: '<?= e($couponData['name']) ?>',
    type: '<?= e($couponData['type']) ?>',
    discountValue: '<?= e($couponData['discount_value']) ?>',
    minOrder: '<?= e($couponData['min_order_amount']) ?>',
    maxDiscount: '<?= e($couponData['max_discount_amount'] ?? '') ?>',
    updateCode(val) {
        this.code = val.toUpperCase().replace(/[^A-Z0-9_\-]/g, '');
    }
}">

    <!-- Breadcrumb & Title -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/admin/coupons" class="p-2 text-gray-500 hover:text-brand-600 hover:bg-gray-100 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">
                    <?= $isEdit ? 'Edit Coupon: ' . e($couponData['code']) : 'Create New Promotional Coupon' ?>
                </h1>
                <p class="text-xs text-gray-500">Configure discount criteria, cart thresholds, customer usage caps, and validity</p>
            </div>
        </div>
        <a href="/admin/coupons" class="text-xs font-bold text-gray-500 hover:text-gray-800">
            Cancel
        </a>
    </div>

    <form action="<?= $isEdit ? '/admin/coupons/update' : '/admin/coupons/store' ?>" method="POST" class="space-y-6">
        <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
        <?php if ($isEdit): ?>
        <input type="hidden" name="coupon_id" value="<?= $couponData['id'] ?>">
        <?php endif; ?>

        <!-- Coupon Main Details Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Coupon Identification</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                
                <!-- Code -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Coupon Code <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="code" 
                        x-model="code"
                        @input="updateCode($event.target.value)"
                        placeholder="e.g. SUMMERFRESH, SAVE20" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 font-mono font-bold tracking-wider text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none uppercase"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Letters, numbers, underscores and hyphens only (case-insensitive)</span>
                </div>

                <!-- Title / Name -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Coupon Title / Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="name"
                        value="<?= e($couponData['name']) ?>" 
                        placeholder="e.g. Free Delivery on Orders Over ₹80" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 font-bold text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Human-readable label shown to customers</span>
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Offer Description</label>
                    <textarea 
                        name="description" 
                        rows="2" 
                        placeholder="Explain the offer terms, e.g. Get 10% off on all tender coconut orders up to ₹50 discount." 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    ><?= e($couponData['description']) ?></textarea>
                </div>

            </div>
        </div>

        <!-- Discount Benefit & Type Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Discount Benefit & Type</h2>

            <!-- Type Selector Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                
                <label 
                    class="p-4 rounded-2xl border-2 transition cursor-pointer flex flex-col justify-between"
                    :class="type === 'free_delivery' ? 'border-brand-600 bg-brand-50/50 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300'"
                >
                    <div class="flex items-center gap-2 mb-2">
                        <input type="radio" name="type" value="free_delivery" x-model="type" class="w-4 h-4 text-brand-600">
                        <span class="font-bold text-xs text-gray-900">Free Delivery</span>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        Waives the delivery fee (₹40) on the order. Subtotal remains unchanged.
                    </p>
                </label>

                <label 
                    class="p-4 rounded-2xl border-2 transition cursor-pointer flex flex-col justify-between"
                    :class="type === 'percentage' ? 'border-brand-600 bg-brand-50/50 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300'"
                >
                    <div class="flex items-center gap-2 mb-2">
                        <input type="radio" name="type" value="percentage" x-model="type" class="w-4 h-4 text-brand-600">
                        <span class="font-bold text-xs text-gray-900">Percentage Discount</span>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        Applies a percentage discount (e.g. 10%) on cart subtotal with optional max cap.
                    </p>
                </label>

                <label 
                    class="p-4 rounded-2xl border-2 transition cursor-pointer flex flex-col justify-between"
                    :class="type === 'fixed' ? 'border-brand-600 bg-brand-50/50 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300'"
                >
                    <div class="flex items-center gap-2 mb-2">
                        <input type="radio" name="type" value="fixed" x-model="type" class="w-4 h-4 text-brand-600">
                        <span class="font-bold text-xs text-gray-900">Fixed ₹ Amount</span>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        Deducts a flat rupee amount (e.g. ₹50) from the cart subtotal.
                    </p>
                </label>

            </div>

            <!-- Discount Value inputs based on type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                
                <div x-show="type !== 'free_delivery'">
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        <span x-text="type === 'percentage' ? 'Discount Percentage (%)' : 'Fixed Discount Amount (₹)'"></span>
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 font-bold" x-text="type === 'percentage' ? '%' : '₹'"></span>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="discount_value" 
                            x-model="discountValue"
                            :required="type !== 'free_delivery'"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-2.5 font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block" x-text="type === 'percentage' ? 'Example: 10 for 10% discount' : 'Example: 50 for ₹50 discount'"></span>
                </div>

                <div x-show="type === 'percentage'" x-cloak>
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Maximum Discount Cap (₹) <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 font-bold">₹</span>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="max_discount_amount" 
                            x-model="maxDiscount"
                            placeholder="e.g. 50" 
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-2.5 font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Prevents huge discounts on very large bulk orders</span>
                </div>

            </div>

        </div>

        <!-- Rules, Limits & Thresholds Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Usage Rules & Conditions</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                
                <!-- Minimum Order Amount -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Minimum Order Amount (₹)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 font-bold">₹</span>
                        <input 
                            type="number" 
                            step="1" 
                            name="min_order_amount" 
                            x-model="minOrder"
                            value="<?= (float)$couponData['min_order_amount'] ?>" 
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-2.5 font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Subtotal must reach this value to apply coupon (0 for no min)</span>
                </div>

                <!-- Per Customer Max Uses -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Max Uses Per Customer
                    </label>
                    <input 
                        type="number" 
                        name="max_uses_per_customer" 
                        value="<?= $couponData['max_uses_per_customer'] ?? 1 ?>" 
                        min="1" 
                        placeholder="e.g. 1" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Limit how many times an individual customer phone/account can redeem</span>
                </div>

                <!-- Global Max Uses -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Total Global Redemptions Cap <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <input 
                        type="number" 
                        name="max_uses" 
                        value="<?= $couponData['max_uses'] ?? '' ?>" 
                        placeholder="e.g. 500 (leave blank for unlimited)" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Coupon automatically deactivates once this total is reached</span>
                </div>

                <!-- Active Status Toggle -->
                <div class="flex items-center pt-5">
                    <label class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-2xl cursor-pointer w-full">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1" 
                            <?= (int)$couponData['is_active'] === 1 ? 'checked' : '' ?> 
                            class="w-4 h-4 text-brand-600 rounded-sm focus:ring-brand-500"
                        >
                        <div>
                            <span class="font-bold text-xs text-gray-900 block">Enable Coupon (Active)</span>
                            <span class="text-[11px] text-gray-500">Allow customers to apply this coupon code at checkout</span>
                        </div>
                    </label>
                </div>

            </div>

            <!-- Start and Expiry Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Start Date & Time <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        name="start_date" 
                        value="<?= $formattedStartDate ?>" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Leave empty to activate immediately</span>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Expiry Date & Time <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        name="expiry_date" 
                        value="<?= $formattedExpiryDate ?>" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Leave empty for no expiry date</span>
                </div>
            </div>

        </div>

        <!-- Live Customer Card Preview -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl p-6 border border-emerald-100 space-y-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 block">Customer Storefront Preview</span>
            <div class="bg-white rounded-2xl p-4 border border-emerald-200/60 shadow-xs flex items-center justify-between gap-4 max-w-md">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-brand-800 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-md text-xs tracking-wider" x-text="code || 'PROMOCODE'"></span>
                        <span class="text-[11px] font-bold text-emerald-700" x-text="type === 'free_delivery' ? 'Free Delivery' : (type === 'percentage' ? (discountValue || '10') + '% OFF' : '₹' + (discountValue || '50') + ' OFF')"></span>
                    </div>
                    <p class="text-xs font-bold text-gray-900" x-text="name || 'Coupon Title'"></p>
                    <p class="text-[11px] text-gray-500" x-text="'Valid on orders over ₹' + (minOrder || '0')"></p>
                </div>
                <div class="text-right">
                    <span class="bg-brand-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-xs">APPLY</span>
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex justify-end gap-3 pt-2">
            <a href="/admin/coupons" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs py-3 px-6 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-xs transition">
                <?= $isEdit ? 'Save Changes' : 'Create Coupon' ?>
            </button>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
