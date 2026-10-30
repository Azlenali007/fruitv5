<?php
/**
 * Footer Layout Component with Fixed Mobile Bottom Navigation
 * N.A Fresh Fruits & Coconuts
 */
$cartData = CartService::getCart();
$cartCount = $cartData['count'];
$activePage = $activePage ?? 'home';
$storeName = Setting::get('store_name', 'N.A Fresh Fruits & Coconuts');
$storePhone = Setting::get('store_phone', '+91 98765 43210');
$storeEmail = Setting::get('store_email', 'contact@nafruitsandcoconuts.com');
$storeAddress = Setting::get('store_address', '123, Green Park, Near Main Road, Bhopal, Madhya Pradesh - 462016');
?>
</main>

<!-- Desktop Standard Footer -->
<footer class="bg-white border-t border-gray-100 mt-16 pb-20 md:pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand Column -->
            <div class="md:col-span-2 space-y-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 text-brand-600">
                        <svg viewBox="0 0 48 48" fill="none" class="w-full h-full" stroke="currentColor">
                            <path d="M24 44V26" stroke="#235d3a" stroke-width="3.5" stroke-linecap="round"/>
                            <path d="M24 26C16 26 8 20 8 10C16 10 22 17 24 26Z" fill="#235d3a" fill-opacity="0.2" stroke="#235d3a" stroke-width="2.5" stroke-linejoin="round"/>
                            <path d="M24 26C32 26 40 20 40 10C32 10 26 17 24 26Z" fill="#235d3a" fill-opacity="0.2" stroke="#235d3a" stroke-width="2.5" stroke-linejoin="round"/>
                            <circle cx="24" cy="24" r="2.5" fill="#235d3a"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl text-gray-900"><?= e($storeName) ?></span>
                </div>
                <p class="text-sm text-gray-500 max-w-md leading-relaxed">
                    Direct from nature, our fresh tender coconut water keeps you hydrated, healthy and energized. 100% natural, no added sugar, no chemicals. Just pure goodness.
                </p>
                <div class="flex items-center gap-4 text-xs text-gray-500 pt-2">
                    <span class="inline-flex items-center gap-1.5 text-brand-700 bg-brand-50 px-2.5 py-1 rounded-full font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Open Daily: <?= e(Setting::get('store_open_time', '07:00 AM')) ?> - <?= e(Setting::get('store_close_time', '09:00 PM')) ?>
                    </span>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Quick Links</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/" class="text-gray-600 hover:text-brand-600 transition">Home</a></li>
                    <li><a href="/shop" class="text-gray-600 hover:text-brand-600 transition">Shop Coconut Water</a></li>
                    <li><a href="/cart" class="text-gray-600 hover:text-brand-600 transition">Your Cart (<?= $cartCount ?>)</a></li>
                    <li><a href="/track" class="text-gray-600 hover:text-brand-600 transition">Track Your Order</a></li>
                </ul>
            </div>

            <!-- Contact & Delivery -->
            <div id="contact">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Visit / Contact Us</h4>
                <ul class="space-y-2.5 text-sm text-gray-600">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-brand-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span><?= e($storeAddress) ?></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <a href="tel:<?= e($storePhone) ?>" class="hover:text-brand-600"><?= e($storePhone) ?></a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span><?= e($storeEmail) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-100 mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-400 gap-2">
            <p>&copy; <?= date('Y') ?> <?= e($storeName) ?>. All rights reserved.</p>
            <p>100% Pure & Hygienic Tender Coconut Water</p>
        </div>
    </div>
</footer>

<!-- Spacer to prevent content from hiding behind fixed bottom nav on mobile screens -->
<div class="bottom-nav-spacer"></div>

<!-- SECTION 5: FIXED MOBILE BOTTOM NAVIGATION -->
<!-- Visible only on mobile/tablet viewports (< md), fixed to viewport bottom with safe area insets -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-gray-200/80 shadow-[0_-4px_12px_rgba(0,0,0,0.04)] pb-safe">
    <div class="grid grid-cols-4 h-16 max-w-md mx-auto items-center">
        
        <!-- 1. Home Tab -->
        <a href="/" class="flex flex-col items-center justify-center py-1 transition group <?= $activePage === 'home' ? 'text-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-800' ?>">
            <div class="relative">
                <svg class="w-5 h-5 <?= $activePage === 'home' ? 'text-brand-600 stroke-[2.2]' : 'text-gray-500 stroke-[1.8]' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </div>
            <span class="text-[11px] mt-1 <?= $activePage === 'home' ? 'text-brand-600 font-semibold' : 'text-gray-600 font-normal' ?>">Home</span>
        </a>

        <!-- 2. Shop Tab -->
        <a href="/shop" class="flex flex-col items-center justify-center py-1 transition group <?= $activePage === 'shop' ? 'text-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-800' ?>">
            <div class="relative">
                <svg class="w-5 h-5 <?= $activePage === 'shop' ? 'text-brand-600 stroke-[2.2]' : 'text-gray-500 stroke-[1.8]' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <span class="text-[11px] mt-1 <?= $activePage === 'shop' ? 'text-brand-600 font-semibold' : 'text-gray-600 font-normal' ?>">Shop</span>
        </a>

        <!-- 3. Cart Tab with Badge -->
        <a href="/cart" class="flex flex-col items-center justify-center py-1 transition group <?= $activePage === 'cart' ? 'text-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-800' ?>">
            <div class="relative">
                <svg class="w-5 h-5 <?= $activePage === 'cart' ? 'text-brand-600 stroke-[2.2]' : 'text-gray-500 stroke-[1.8]' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <?php if ($cartCount > 0): ?>
                <span class="absolute -top-1.5 -right-2 bg-brand-600 text-white text-[9px] font-bold rounded-full h-4 w-4 flex items-center justify-center shadow-xs">
                    <?= $cartCount ?>
                </span>
                <?php endif; ?>
            </div>
            <span class="text-[11px] mt-1 <?= $activePage === 'cart' ? 'text-brand-600 font-semibold' : 'text-gray-600 font-normal' ?>">Cart</span>
        </a>

        <!-- 4. Profile / My Account Tab -->
        <?php 
        $isCustLoggedIn = CustomerAuthService::check(); 
        $profileTarget = $isCustLoggedIn ? '/account' : '/login';
        $isProfileActive = in_array($activePage, ['profile', 'account']);
        ?>
        <a href="<?= $profileTarget ?>" class="flex flex-col items-center justify-center py-1 transition group <?= $isProfileActive ? 'text-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-800' ?>">
            <div class="relative">
                <svg class="w-5 h-5 <?= $isProfileActive ? 'text-brand-600 stroke-[2.2]' : 'text-gray-500 stroke-[1.8]' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <span class="text-[11px] mt-1 <?= $isProfileActive ? 'text-brand-600 font-semibold' : 'text-gray-600 font-normal' ?>">
                <?= $isCustLoggedIn ? 'Account' : 'Profile' ?>
            </span>
        </a>

    </div>
</nav>

<!-- WhatsApp Channel Modal Popup -->
<?php require_once __DIR__ . '/../components/whatsapp-popup.php'; ?>

</body>
</html>
