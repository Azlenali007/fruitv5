<?php
$currentStep = 5;
$installerTitle = 'Step 5: Business Settings - N.A Installer';
ob_start();

$prod = Product::getPrimary();
$initialPrice = $prod['price'] ?? 40.00;
$initialStock = $prod['stock_quantity'] ?? 150;
?>

<div class="space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold text-brand-600 uppercase tracking-wider">Step 5 of 6</span>
        <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight mt-0.5">Initial Store & Delivery Settings</h2>
        <p class="text-xs text-gray-500 mt-1">
            Configure initial product pricing, inventory counts, and delivery rules. These can be adjusted anytime via the store admin dashboard.
        </p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="p-3.5 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium">
        <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form action="/install?step=5" method="POST" class="space-y-4 text-xs">
        <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Store / Brand Name</label>
                <input 
                    type="text" 
                    name="store_name" 
                    value="<?= e(Setting::get('store_name', 'N.A Fresh Fruits & Coconuts')) ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Currency Symbol</label>
                <input 
                    type="text" 
                    name="currency_symbol" 
                    value="<?= e(Setting::get('currency_symbol', '₹')) ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>
        </div>

        <!-- Product Price & Stock -->
        <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl space-y-3">
            <span class="font-bold text-gray-900 text-xs block">Fresh Coconut Water — Initial Pricing & Stock</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Selling Price (₹ / piece)</label>
                    <input 
                        type="number" 
                        step="1" 
                        name="product_price" 
                        value="<?= (int)$initialPrice ?>" 
                        required 
                        class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Starting Stock Quantity (Units)</label>
                    <input 
                        type="number" 
                        step="1" 
                        name="product_stock" 
                        value="<?= (int)$initialStock ?>" 
                        required 
                        class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>
            </div>
        </div>

        <!-- Delivery & Pickup Settings -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Delivery Charge (₹)</label>
                <input 
                    type="number" 
                    step="1" 
                    name="delivery_charge" 
                    value="<?= (int)Setting::get('delivery_charge', '40') ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Min Order for Delivery (₹)</label>
                <input 
                    type="number" 
                    step="1" 
                    name="min_order_amount" 
                    value="<?= (int)Setting::get('min_order_amount', '40') ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Free Delivery Above (₹)</label>
                <input 
                    type="number" 
                    step="1" 
                    name="free_delivery_above" 
                    value="<?= (int)Setting::get('free_delivery_above', '500') ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>
        </div>

        <!-- Fulfilment Checkboxes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
            <label class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
                <input type="checkbox" name="local_delivery_enabled" value="1" checked class="w-4 h-4 text-brand-600 rounded-sm">
                <span class="font-bold text-gray-800">Enable Local Doorstep Delivery</span>
            </label>

            <label class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
                <input type="checkbox" name="shop_pickup_enabled" value="1" checked class="w-4 h-4 text-brand-600 rounded-sm">
                <span class="font-bold text-gray-800">Enable Store Pickup</span>
            </label>
        </div>

        <!-- Store Contact & Timezone -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Support Phone</label>
                <input 
                    type="text" 
                    name="store_phone" 
                    value="<?= e(Setting::get('store_phone', '+91 98765 43210')) ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Timezone</label>
                <input 
                    type="text" 
                    name="timezone" 
                    value="Asia/Kolkata" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
            <a href="/install?step=4" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                &larr; Back
            </a>

            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-xs transition flex items-center gap-2">
                <span>Save Settings & Finish</span>
                &rarr;
            </button>
        </div>
    </form>
</div>

<?php
$stepContent = ob_get_clean();
require __DIR__ . '/layout.php';
