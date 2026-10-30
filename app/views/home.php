<?php
/**
 * Customer Home Page - Exact Match to Reference Image Top Left Screen
 * N.A Fresh Fruits & Coconuts
 */
$pageTitle = 'N.A Fresh Fruits & Coconuts - Fresh Coconut Water';
$activePage = 'home';
require_once __DIR__ . '/layouts/header.php';
?>

<!-- Hero Section (Desktop & Mobile) -->
<section class="relative overflow-hidden pt-4 pb-12 sm:pt-8 sm:pb-16 lg:pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Hero Content -->
            <div class="lg:col-span-6 space-y-5 sm:space-y-6">
                
                <!-- Badge: 100% Natural -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs sm:text-sm font-semibold tracking-wide">
                    <svg class="w-4 h-4 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.16 4.16a7.25 7.25 0 0110.25 0c.2.2.38.42.54.64.44.62.75 1.33.91 2.08a8.3 8.3 0 01.14 1.62c0 2.21-.89 4.22-2.34 5.66A7.22 7.22 0 018 16.5a7.25 7.25 0 01-5.13-2.12 7.25 7.25 0 010-10.22zm1.41 1.41a5.25 5.25 0 000 7.43A5.25 5.25 0 0013.01 5.57 5.25 5.25 0 005.57 5.57z" clip-rule="evenodd"></path>
                    </svg>
                    <span>100% Natural</span>
                </div>

                <!-- Main Hero Heading -->
                <div class="space-y-2">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-950 leading-[1.1]">
                        Fresh<br>
                        Coconut Water
                    </h1>
                    <p class="text-xl sm:text-2xl font-bold text-brand-600">
                        Pure. Natural. Refreshing.
                    </p>
                </div>

                <!-- Description -->
                <p class="text-gray-600 text-base sm:text-lg max-w-lg leading-relaxed">
                    Direct from nature, our fresh coconut water keeps you hydrated, healthy and energized. No added sugar. No chemicals. Just pure goodness.
                </p>

                <!-- CTA Button -->
                <div class="pt-1 flex flex-wrap items-center gap-4">
                    <a href="/shop" class="inline-flex items-center gap-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-base px-7 py-3.5 rounded-full shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 active:translate-y-0">
                        <span>Order Now</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                    
                    <div class="text-sm font-semibold text-gray-700">
                        <span class="text-brand-600 text-lg font-bold">₹<?= number_format($product['price'] ?? 40) ?></span> / piece
                    </div>
                </div>

                <!-- 4 Hero Value Badges -->
                <div class="grid grid-cols-4 gap-2 sm:gap-4 pt-6 border-t border-gray-100/80">
                    
                    <!-- 1. 100% Natural -->
                    <div class="flex flex-col items-center text-center space-y-1.5">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-brand-100 flex items-center justify-center text-brand-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 0 0 9-9c0-4.97-4.03-9-9-9-4.97 0-9 4.03-9 9 0 4.97 4.03 9 9 9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9m0 0l4 4m-4-4l-4 4"></path>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-gray-700 leading-tight">100%<br>Natural</span>
                    </div>

                    <!-- 2. No Added Sugar -->
                    <div class="flex flex-col items-center text-center space-y-1.5">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-brand-100 flex items-center justify-center text-brand-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.93 4.93l14.14 14.14M9 9h6v6H9z"></path>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-gray-700 leading-tight">No Added<br>Sugar</span>
                    </div>

                    <!-- 3. Fresh & Hygienic -->
                    <div class="flex flex-col items-center text-center space-y-1.5">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-brand-100 flex items-center justify-center text-brand-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-gray-700 leading-tight">Fresh &<br>Hygienic</span>
                    </div>

                    <!-- 4. Home Delivery -->
                    <div class="flex flex-col items-center text-center space-y-1.5">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-brand-100 flex items-center justify-center text-brand-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8h4l3 4v4h-2"></path>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-gray-700 leading-tight">Home<br>Delivery</span>
                    </div>

                </div>

            </div>

            <!-- Right Hero Image: Coconut with water splash & flower on stone slab -->
            <div class="lg:col-span-6 relative flex justify-center">
                <div class="relative w-full max-w-md sm:max-w-lg rounded-3xl overflow-hidden shadow-2xl bg-emerald-50/50 p-2 sm:p-4">
                    <img 
                        src="/assets/images/hero-coconut.png" 
                        alt="Fresh Coconut Water with Straw and Splash" 
                        class="w-full h-auto object-cover rounded-2xl transform hover:scale-[1.02] transition duration-500"
                    >
                    <div class="absolute bottom-6 right-6 bg-white/90 backdrop-blur-md px-4 py-2.5 rounded-2xl shadow-lg border border-white/50 flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400">Fresh Stock</p>
                            <p class="text-xs font-bold text-gray-800"><?= (int)($product['stock_quantity'] ?? 100) ?> Tender Coconuts Available</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Section: Why Choose Us? (Matches reference image cards layout) -->
<section id="why-choose-us" class="py-12 sm:py-16 bg-white border-y border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-left mb-8 sm:mb-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Why Choose Us?
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Card 1: Fresh & Natural -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 0 0 9-9c0-4.97-4.03-9-9-9-4.97 0-9 4.03-9 9 0 4.97 4.03 9 9 9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9m0 0l4 4m-4-4l-4 4"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base mb-1.5">Fresh & Natural</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    100% natural coconut water straight from farm.
                </p>
            </div>

            <!-- Card 2: Hygienic Process -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base mb-1.5">Hygienic Process</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Cleaned, packed & delivered with care.
                </p>
            </div>

            <!-- Card 3: Fast Delivery -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8h4l3 4v4h-2"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base mb-1.5">Fast Delivery</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Get your order at your doorstep.
                </p>
            </div>

            <!-- Card 4: Healthy & Refreshing -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs hover:shadow-md transition group">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base mb-1.5">Healthy & Refreshing</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Stay hydrated, stay healthy naturally.
                </p>
            </div>

        </div>

    </div>
</section>

<!-- Direct Purchase Section on Homepage -->
<section class="py-12 bg-[#f8faf8]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col md:flex-row items-center gap-6 sm:gap-8">
            <div class="w-32 h-32 sm:w-44 sm:h-44 shrink-0 rounded-2xl overflow-hidden bg-emerald-50">
                <img src="<?= e($product['image_url'] ?? '/assets/images/product-main.png') ?>" alt="Fresh Tender Coconut" class="w-full h-full object-cover">
            </div>
            <div class="flex-grow space-y-2 text-center md:text-left">
                <span class="inline-block text-xs font-bold text-brand-600 uppercase tracking-wider">Direct Farm Sourced</span>
                <h3 class="text-2xl font-bold text-gray-900"><?= e($product['name'] ?? 'Fresh Coconut Water') ?></h3>
                <p class="text-sm text-gray-500 leading-relaxed"><?= e($product['description'] ?? 'Pure tender coconut water') ?></p>
                <div class="flex items-center justify-center md:justify-start gap-3 pt-1">
                    <span class="text-2xl font-black text-gray-900">₹<?= number_format($product['price'] ?? 40) ?></span>
                    <span class="text-sm font-medium text-gray-400">/ piece</span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full <?= ($product['stock_quantity'] > 0) ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' ?>">
                        <?= ($product['stock_quantity'] > 0) ? 'In Stock (' . (int)$product['stock_quantity'] . ')' : 'Sold Out' ?>
                    </span>
                </div>
            </div>
            <div class="shrink-0 w-full md:w-auto">
                <a href="/shop" class="w-full md:w-auto inline-flex justify-center items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm px-6 py-3 rounded-xl shadow-xs transition">
                    <span>View Product Details</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
