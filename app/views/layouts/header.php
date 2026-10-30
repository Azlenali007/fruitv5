<?php
/**
 * Header Layout Component
 * N.A Fresh Fruits & Coconuts
 */
$cartData = CartService::getCart();
$cartCount = $cartData['count'];
$activePage = $activePage ?? 'home';
$storeName = Setting::get('store_name', 'N.A Fresh Fruits & Coconuts');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#f8faf8]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($pageTitle ?? 'Fresh Coconut Water - N.A Fresh Fruits & Coconuts') ?></title>
    <meta name="description" content="Pure, natural and refreshing fresh tender coconut water delivered straight from farm. No chemicals, no added sugar.">
    <meta property="og:title" content="<?= e($pageTitle ?? 'N.A Fresh Fruits & Coconuts') ?>">
    <meta property="og:description" content="Pure, natural and refreshing fresh tender coconut water.">
    <link rel="icon" href="/assets/images/product-main.png" type="image/png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#ebf5ed',
                            200: '#d1ead6',
                            300: '#a3d5ad',
                            400: '#529762',
                            500: '#2e7d32',
                            600: '#235d3a', // Primary reference green
                            700: '#1b4a2e',
                            800: '#173c26',
                            900: '#143220',
                        },
                        bgsoft: '#f8faf8',
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        /* Safe area support for mobile devices */
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 1rem);
        }
        .bottom-nav-spacer {
            height: 4.5rem;
        }
        @media (min-width: 768px) {
            .bottom-nav-spacer {
                display: none;
            }
        }
        /* Smooth Page Transition Overlay */
        #page-loader {
            transition: opacity 0.25s ease-in-out, visibility 0.25s ease-in-out;
        }
        @media (prefers-reduced-motion: reduce) {
            #page-loader, .page-loader-spinner {
                animation: none !important;
                transition: none !important;
                display: none !important;
            }
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-gray-800 antialiased bg-[#f8faf8] selection:bg-brand-100 selection:text-brand-700" x-data="{ mobileMenuOpen: false, searchOpen: false }">

<!-- Page Transition Loading Animation Overlay -->
<div id="page-loader" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-xs opacity-0 invisible pointer-events-none" aria-hidden="true">
    <div class="flex flex-col items-center p-6 rounded-3xl bg-white/95 shadow-xl border border-gray-100 max-w-xs text-center transform scale-95 transition-transform duration-200">
        <!-- Modern animated spinner -->
        <div class="relative w-12 h-12 flex items-center justify-center">
            <div class="w-12 h-12 rounded-full border-3 border-emerald-100 border-t-[#235d3a] animate-spin"></div>
            <div class="absolute w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center animate-pulse">
                <span class="w-2 h-2 rounded-full bg-[#235d3a]"></span>
            </div>
        </div>
        <p class="text-xs font-bold text-gray-800 mt-3.5 tracking-wide">N.A Fresh Fruits</p>
        <p class="text-[10px] text-gray-400 mt-0.5">Harvesting freshness...</p>
    </div>
</div>

<script>
(function() {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    var loader = document.getElementById('page-loader');
    if (!loader) return;

    var hideTimer = null;

    function showLoader() {
        if (!loader) return;
        clearTimeout(hideTimer);
        loader.classList.remove('opacity-0', 'invisible', 'pointer-events-none');
        loader.classList.add('opacity-100', 'visible');
        // Safety timeout: auto-hide after 6 seconds to prevent stuck state
        hideTimer = setTimeout(hideLoader, 6000);
    }

    function hideLoader() {
        if (!loader) return;
        clearTimeout(hideTimer);
        loader.classList.remove('opacity-100', 'visible');
        loader.classList.add('opacity-0', 'invisible', 'pointer-events-none');
        // Reset navigation lock on all links
        var navigatingLinks = document.querySelectorAll('a[data-navigating="true"]');
        for (var i = 0; i < navigatingLinks.length; i++) {
            delete navigatingLinks[i].dataset.navigating;
        }
    }

    // Hide loader immediately on DOM load and when restored from BFCache (Back/Forward navigation)
    window.addEventListener('pageshow', function(e) {
        hideLoader();
    });
    window.addEventListener('load', hideLoader);
    document.addEventListener('DOMContentLoaded', hideLoader);

    // Click handler for smooth transitions
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a');
        if (!link) return;

        var href = link.getAttribute('href');
        if (!href) return;

        // Skip anchors, javascript, empty
        if (href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href === '') return;

        // Skip new tab / blank
        if (link.target === '_blank') return;

        // Skip downloads
        if (link.hasAttribute('download')) return;

        // Skip special protocols
        if (/^(mailto|tel|sms|whatsapp|blob):/i.test(href)) return;

        // Check if internal domain
        try {
            var url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) return;
            // Skip hash navigation on same page
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
        } catch (_) {
            return;
        }

        // Prevent double click navigation
        if (link.dataset.navigating === 'true') {
            e.preventDefault();
            return;
        }
        link.dataset.navigating = 'true';

        // Trigger smooth transition
        showLoader();
    }, true);
})();
</script>


<!-- Top Navigation Bar -->
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            
            <!-- Logo Section -->
            <a href="/" class="flex items-center gap-2.5 group">
                <!-- Palm Tree SVG Icon -->
                <div class="w-9 h-9 sm:w-10 sm:h-10 text-brand-600 flex items-center justify-center transition-transform group-hover:scale-105">
                    <svg viewBox="0 0 48 48" fill="none" class="w-full h-full" stroke="currentColor">
                        <path d="M24 44V26" stroke="#235d3a" stroke-width="3.5" stroke-linecap="round"/>
                        <path d="M24 26C16 26 8 20 8 10C16 10 22 17 24 26Z" fill="#235d3a" fill-opacity="0.2" stroke="#235d3a" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M24 26C32 26 40 20 40 10C32 10 26 17 24 26Z" fill="#235d3a" fill-opacity="0.2" stroke="#235d3a" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M24 26C24 16 19 8 10 6C15 15 20 22 24 26Z" stroke="#235d3a" stroke-width="2.5" stroke-linecap="round"/>
                        <path d="M24 26C24 16 29 8 38 6C33 15 28 22 24 26Z" stroke="#235d3a" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="24" cy="24" r="2.5" fill="#235d3a"/>
                        <circle cx="21" cy="27" r="2.5" fill="#235d3a"/>
                        <circle cx="27" cy="27" r="2.5" fill="#235d3a"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-xl sm:text-2xl tracking-tight text-gray-900 leading-none">N.A</span>
                    <span class="text-[10px] sm:text-xs font-medium text-gray-500 tracking-wide">Fresh Fruits & Coconuts</span>
                </div>
            </a>

            <?php 
            $isCustomerLoggedIn = CustomerAuthService::check(); 
            $currentCustomer = $isCustomerLoggedIn ? CustomerAuthService::user() : null;
            ?>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center space-x-7 text-sm font-medium">
                <a href="/" class="<?= $activePage === 'home' ? 'text-brand-600 font-semibold border-b-2 border-brand-600 pb-1' : 'text-gray-600 hover:text-brand-600 transition' ?>">
                    Home
                </a>
                <a href="/shop" class="<?= $activePage === 'shop' ? 'text-brand-600 font-semibold border-b-2 border-brand-600 pb-1' : 'text-gray-600 hover:text-brand-600 transition' ?>">
                    Shop
                </a>
                <?php if ($isCustomerLoggedIn): ?>
                <a href="/my-orders" class="<?= $activePage === 'track' ? 'text-brand-600 font-semibold border-b-2 border-brand-600 pb-1' : 'text-gray-600 hover:text-brand-600 transition' ?>">
                    My Orders
                </a>
                <?php else: ?>
                <a href="/track" class="<?= $activePage === 'track' ? 'text-brand-600 font-semibold border-b-2 border-brand-600 pb-1' : 'text-gray-600 hover:text-brand-600 transition' ?>">
                    Track Order
                </a>
                <?php endif; ?>
                <a href="/#why-choose-us" class="text-gray-600 hover:text-brand-600 transition">
                    About
                </a>
                <a href="/#contact" class="text-gray-600 hover:text-brand-600 transition">
                    Contact
                </a>
            </nav>

            <!-- Right Icons (Search, Cart, Profile, Admin) -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <!-- Search Button -->
                <button type="button" @click="searchOpen = !searchOpen" class="text-gray-600 hover:text-brand-600 p-1.5 transition" aria-label="Search">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"></path>
                    </svg>
                </button>

                <!-- Cart Button with Counter Badge -->
                <a href="/cart" class="relative text-gray-600 hover:text-brand-600 p-1.5 transition" aria-label="Shopping Cart">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-brand-600 text-white text-[10px] sm:text-xs font-bold rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center shadow-xs">
                        <?= $cartCount ?>
                    </span>
                    <?php endif; ?>
                </a>

                <!-- Customer Profile / Login Button -->
                <?php if ($isCustomerLoggedIn): ?>
                <a href="/account" class="hidden sm:inline-flex items-center gap-2 bg-emerald-50 hover:bg-emerald-100/80 text-brand-700 px-3 py-1.5 rounded-full border border-emerald-200/60 transition text-xs font-bold" title="My Account">
                    <span class="w-5 h-5 rounded-full bg-brand-600 text-white flex items-center justify-center text-[10px]">
                        <?= strtoupper(substr($currentCustomer['name'] ?? 'U', 0, 1)) ?>
                    </span>
                    <span class="truncate max-w-[100px]"><?= e(explode(' ', $currentCustomer['name'] ?? 'Account')[0]) ?></span>
                </a>
                <?php else: ?>
                <a href="/login" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100/70 px-3 py-1.5 rounded-full border border-brand-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Log In</span>
                </a>
                <?php endif; ?>

                <!-- Mobile Hamburger Menu Button -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 hover:text-brand-600 p-1.5" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Inline Search Drawer -->
        <div x-show="searchOpen" x-cloak class="py-3 border-t border-gray-100">
            <form action="/shop" method="GET" class="flex gap-2">
                <input type="text" name="q" placeholder="Search fresh tender coconut water..." class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-700">Search</button>
            </form>
        </div>
    </div>

    <!-- Mobile Slideover Drawer -->
    <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-gray-100 bg-white px-4 pt-3 pb-6 space-y-3 shadow-lg">
        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
            <span class="text-xs uppercase font-semibold text-gray-400">Navigation</span>
            <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Customer Status in Mobile Drawer -->
        <?php if ($isCustomerLoggedIn): ?>
        <div class="bg-emerald-50 rounded-2xl p-3 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs">
                    <?= strtoupper(substr($currentCustomer['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <span class="font-bold text-gray-900 block"><?= e($currentCustomer['name']) ?></span>
                    <span class="text-[10px] text-gray-500 font-mono"><?= e($currentCustomer['phone']) ?></span>
                </div>
            </div>
            <a href="/account" class="text-brand-700 font-bold text-xs underline">Manage</a>
        </div>
        <?php else: ?>
        <div class="bg-gray-50 rounded-2xl p-3 flex items-center justify-between gap-2">
            <div class="text-xs text-gray-600">
                <span class="font-bold text-gray-900 block">Welcome, Guest!</span>
                <span class="text-[10px] text-gray-500">Log in for fast checkout & tracking</span>
            </div>
            <div class="flex gap-1.5">
                <a href="/login" class="bg-brand-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-xs">Log In</a>
                <a href="/register" class="bg-white border border-gray-200 text-gray-700 text-xs font-semibold px-2.5 py-1.5 rounded-lg">Sign Up</a>
            </div>
        </div>
        <?php endif; ?>

        <a href="/" class="block text-base font-medium <?= $activePage === 'home' ? 'text-brand-600' : 'text-gray-700' ?>">Home</a>
        <a href="/shop" class="block text-base font-medium <?= $activePage === 'shop' ? 'text-brand-600' : 'text-gray-700' ?>">Shop Tender Coconut</a>
        <a href="/cart" class="block text-base font-medium <?= $activePage === 'cart' ? 'text-brand-600' : 'text-gray-700' ?>">Cart (<?= $cartCount ?>)</a>

        <?php if ($isCustomerLoggedIn): ?>
        <a href="/account" class="block text-base font-medium <?= $activePage === 'profile' ? 'text-brand-600' : 'text-gray-700' ?>">My Account</a>
        <a href="/my-orders" class="block text-base font-medium <?= $activePage === 'track' ? 'text-brand-600' : 'text-gray-700' ?>">My Order History</a>
        <?php else: ?>
        <a href="/track" class="block text-base font-medium <?= $activePage === 'track' ? 'text-brand-600' : 'text-gray-700' ?>">Track Any Order</a>
        <?php endif; ?>

        <a href="/#why-choose-us" @click="mobileMenuOpen = false" class="block text-base font-medium text-gray-700">Why Choose Us</a>
        <a href="/#contact" @click="mobileMenuOpen = false" class="block text-base font-medium text-gray-700">Store Hours & Contact</a>

        <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
            <?php if ($isCustomerLoggedIn): ?>
            <form action="/logout" method="POST">
                <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                <button type="submit" class="w-full text-left text-xs font-semibold text-red-600 py-1.5 hover:underline">
                    &larr; Log out of customer account
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Flash notification banner -->
<?php $flash = get_flash(); if ($flash): ?>
<div class="max-w-4xl mx-auto px-4 mt-3">
    <div class="p-3.5 rounded-xl text-sm font-medium flex items-center gap-2.5 <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' ?>">
        <?php if ($flash['type'] === 'error'): ?>
            <svg class="w-5 h-5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <?php else: ?>
            <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <?php endif; ?>
        <span><?= e($flash['message']) ?></span>
    </div>
</div>
<?php endif; ?>

<!-- Main Content Area -->
<main class="flex-grow">
