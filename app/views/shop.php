<?php
/**
 * Shop & Product Detail View - Exact Match to Reference Image Top Right Screen
 * N.A Fresh Fruits & Coconuts
 */
$pageTitle = 'Fresh Coconut Water - N.A Fresh Fruits & Coconuts';
$activePage = 'shop';
require_once __DIR__ . '/layouts/header.php';

$unitPrice = (float)($product['price'] ?? 40);
$stock = (int)($product['stock_quantity'] ?? 100);
$gallery = $product['gallery'] ?? ['/assets/images/product-main.png'];
?>

<div class="max-w-2xl mx-auto px-4 py-4 sm:py-8" x-data="{
    qty: 1,
    unitPrice: <?= $unitPrice ?>,
    maxStock: <?= $stock ?>,
    activeImage: '<?= e($gallery[0] ?? '/assets/images/product-main.png') ?>',
    get lineTotal() {
        return this.qty * this.unitPrice;
    },
    decrease() {
        if (this.qty > 1) this.qty--;
    },
    increase() {
        if (this.qty < this.maxStock) this.qty++;
    }
}">

    <!-- Top Sub-header with Back Arrow (matches reference top right) -->
    <div class="flex items-center gap-3 mb-4 sm:mb-6">
        <a href="/" class="p-1 text-gray-700 hover:text-brand-600 transition" aria-label="Back">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <h1 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">
            Fresh Coconut Water
        </h1>
    </div>

    <!-- Product Card Container -->
    <div class="bg-white rounded-3xl p-5 sm:p-7 border border-gray-100 shadow-sm space-y-6">
        
        <!-- Main Product Image -->
        <div class="w-full aspect-[4/3] sm:aspect-square rounded-2xl overflow-hidden bg-emerald-50/50 flex items-center justify-center border border-gray-100">
            <img 
                :src="activeImage" 
                alt="Fresh Coconut Water" 
                class="w-full h-full object-cover transition-all duration-300"
            >
        </div>

        <!-- Thumbnails Row (4 thumbnails matching reference image) -->
        <div class="grid grid-cols-4 gap-3">
            <?php foreach ($gallery as $index => $thumb): ?>
            <button 
                type="button"
                @click="activeImage = '<?= e($thumb) ?>'"
                :class="activeImage === '<?= e($thumb) ?>' ? 'ring-2 ring-brand-600 border-transparent shadow-xs' : 'border-gray-200 hover:border-brand-400 opacity-80 hover:opacity-100'"
                class="aspect-square rounded-xl overflow-hidden border bg-gray-50 p-1 transition"
            >
                <img src="<?= e($thumb) ?>" alt="Thumbnail <?= $index + 1 ?>" class="w-full h-full object-cover rounded-lg">
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Title, Tagline & Price -->
        <div class="space-y-1.5 pt-1">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-950 tracking-tight">
                <?= e($product['name'] ?? 'Fresh Coconut Water') ?>
            </h2>
            <p class="text-xs sm:text-sm font-semibold text-gray-400">
                <?= e($product['tagline'] ?? 'Pure • Natural • Healthy') ?>
            </p>
            <div class="pt-2 flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black text-gray-900">₹<?= number_format($unitPrice) ?></span>
                <span class="text-sm font-medium text-gray-400">/pc</span>
            </div>
        </div>

        <!-- Quantity Stepper & Add to Cart Action Row (Matches reference image) -->
        <form action="/cart/add" method="POST" class="space-y-4 pt-1">
            <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="product_id" value="<?= (int)($product['id'] ?? 1) ?>">
            <input type="hidden" name="quantity" :value="qty">

            <?php if ($stock > 0): ?>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                
                <!-- Stepper: [-] qty [+] (₹total) -->
                <div class="flex items-center justify-between bg-emerald-50/70 border border-emerald-100/80 rounded-xl px-3 py-2 sm:py-2.5 sm:w-44">
                    <button 
                        type="button" 
                        @click="decrease()" 
                        :disabled="qty <= 1"
                        :class="qty <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white text-gray-800'"
                        class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-lg transition"
                        aria-label="Decrease quantity"
                    >
                        -
                    </button>
                    
                    <span class="font-extrabold text-base text-gray-900" x-text="qty"></span>
                    
                    <button 
                        type="button" 
                        @click="increase()" 
                        :disabled="qty >= maxStock"
                        :class="qty >= maxStock ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white text-gray-800'"
                        class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-lg transition"
                        aria-label="Increase quantity"
                    >
                        +
                    </button>

                    <span class="text-xs font-semibold text-gray-500 pl-1" x-text="'(₹' + lineTotal + ')'"></span>
                </div>

                <!-- Add to Cart Button -->
                <button 
                    type="submit" 
                    class="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm sm:text-base py-3.5 px-6 rounded-xl flex items-center justify-center gap-2.5 shadow-md hover:shadow-lg transition active:scale-[0.99]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span>Add to Cart</span>
                </button>

            </div>
            <?php else: ?>
            <div class="p-4 bg-red-50 text-red-700 rounded-xl text-center font-semibold text-sm">
                This item is currently sold out. Please check back later!
            </div>
            <?php endif; ?>
        </form>

        <!-- Product Details Section -->
        <div class="pt-3 border-t border-gray-100 space-y-2">
            <h3 class="font-bold text-gray-900 text-sm sm:text-base">Product Details</h3>
            <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                <?= e($product['description'] ?? 'Fresh tender coconut water, straight from nature. No added sugar, no preservatives, no chemicals. Just pure and natural hydration.') ?>
            </p>
        </div>

        <!-- 4 Attribute Icons Row (100% Natural, No Chemicals, Rich in Electrolytes, Keeps You Hydrated) -->
        <div class="grid grid-cols-4 gap-2 pt-4 border-t border-gray-100 text-center">
            
            <!-- 1. 100% Natural -->
            <div class="flex flex-col items-center space-y-2">
                <div class="w-11 h-11 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 0 0 9-9c0-4.97-4.03-9-9-9-4.97 0-9 4.03-9 9 0 4.97 4.03 9 9 9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9m0 0l4 4m-4-4l-4 4"></path>
                    </svg>
                </div>
                <span class="text-[11px] font-semibold text-gray-700 leading-tight">100%<br>Natural</span>
            </div>

            <!-- 2. No Chemicals -->
            <div class="flex flex-col items-center space-y-2">
                <div class="w-11 h-11 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="7" r="4"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 21v-2a6 6 0 0 1 12 0v2"></path>
                    </svg>
                </div>
                <span class="text-[11px] font-semibold text-gray-700 leading-tight">No<br>Chemicals</span>
            </div>

            <!-- 3. Rich in Electrolytes -->
            <div class="flex flex-col items-center space-y-2">
                <div class="w-11 h-11 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <span class="text-[11px] font-semibold text-gray-700 leading-tight">Rich in<br>Electrolytes</span>
            </div>

            <!-- 4. Keeps You Hydrated -->
            <div class="flex flex-col items-center space-y-2">
                <div class="w-11 h-11 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                    </svg>
                </div>
                <span class="text-[11px] font-semibold text-gray-700 leading-tight">Keeps You<br>Hydrated</span>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
