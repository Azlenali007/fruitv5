<?php
/**
 * Admin Product & Inventory Management
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Product & Stock Management - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'products';
require_once __DIR__ . '/layout-header.php';

$product = Product::getPrimary();
?>

<div class="max-w-3xl mx-auto space-y-6">
    
    <div>
        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Product & Stock Management</h1>
        <p class="text-xs text-gray-500">Update selling prices, inventory stock, and availability for Fresh Coconut Water</p>
    </div>

    <!-- Main Product Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-6">
        
        <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 overflow-hidden border border-gray-100 shrink-0">
                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" class="w-full h-full object-cover">
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900"><?= e($product['name']) ?></h2>
                <span class="text-xs text-gray-400"><?= e($product['tagline']) ?></span>
                <div class="mt-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $product['is_available'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                        <?= $product['is_available'] ? 'Active in Store' : 'Hidden from Customers' ?>
                    </span>
                </div>
            </div>
        </div>

        <form action="/admin/products/update" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
            <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Product Name -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Product Title</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="<?= e($product['name']) ?>" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>

                <!-- Tagline -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Tagline</label>
                    <input 
                        type="text" 
                        name="tagline" 
                        value="<?= e($product['tagline']) ?>" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>

                <!-- Selling Price -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Selling Price (₹ per piece)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 font-bold">₹</span>
                        <input 
                            type="number" 
                            step="1" 
                            min="1" 
                            name="price" 
                            value="<?= (int)$product['price'] ?>" 
                            required 
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-7 pr-3 py-2.5 text-xs font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Live pricing updated immediately in customer cart</span>
                </div>

                <!-- Stock Quantity -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Available Stock (Units)</label>
                    <input 
                        type="number" 
                        min="0" 
                        name="stock_quantity" 
                        value="<?= (int)$product['stock_quantity'] ?>" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Automatically decremented upon customer orders</span>
                </div>

                <!-- Is Available Switch -->
                <div class="sm:col-span-2">
                    <label class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="is_available" 
                            value="1" 
                            <?= $product['is_available'] ? 'checked' : '' ?>
                            class="w-4 h-4 text-brand-600 rounded-sm focus:ring-brand-500"
                        >
                        <div>
                            <span class="font-bold text-gray-900 block">Product Available for Sale</span>
                            <span class="text-gray-400 text-[11px]">When unchecked, product shows as "Out of stock / Unavailable"</span>
                        </div>
                    </label>
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Detailed Description</label>
                    <textarea 
                        name="description" 
                        rows="3" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none leading-relaxed"
                    ><?= e($product['description']) ?></textarea>
                </div>

                <!-- Image URL or Upload -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Primary Product Image Path</label>
                    <input 
                        type="text" 
                        name="image_url" 
                        value="<?= e($product['image_url']) ?>" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-mono focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>

                <!-- File upload for replacement -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Or Upload Replacement Photo</label>
                    <input 
                        type="file" 
                        name="product_image" 
                        accept="image/png,image/jpeg,image/webp" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Accepts PNG, JPG, WEBP up to 5MB</span>
                </div>

            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-xs transition">
                    Save Product Changes
                </button>
            </div>

        </form>

    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
