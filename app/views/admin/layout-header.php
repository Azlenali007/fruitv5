<?php
/**
 * Modern Responsive Admin Layout Header with Collapsible Desktop Sidebar & Off-Canvas Mobile Drawer
 * N.A Fresh Fruits & Coconuts
 */
$adminUser = AuthService::user();
$activeAdminTab = $activeAdminTab ?? 'dashboard';

$navItems = [
    [
        'id' => 'dashboard',
        'label' => 'Dashboard',
        'url' => '/admin',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>'
    ],
    [
        'id' => 'orders',
        'label' => 'Orders',
        'url' => '/admin/orders',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>'
    ],
    [
        'id' => 'payments',
        'label' => 'Payments',
        'url' => '/admin/payments',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>'
    ],
    [
        'id' => 'products',
        'label' => 'Products & Stock',
        'url' => '/admin/products',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>'
    ],
    [
        'id' => 'coupons',
        'label' => 'Coupons & Offers',
        'url' => '/admin/coupons',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>'
    ],
    [
        'id' => 'customers',
        'label' => 'Customers',
        'url' => '/admin/customers',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>'
    ],
    [
        'id' => 'reports',
        'label' => 'Sales Reports',
        'url' => '/admin/reports',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>'
    ],
    [
        'id' => 'payment_settings',
        'label' => 'Payment Gateway',
        'url' => '/admin/payment-settings',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>'
    ],
    [
        'id' => 'areas',
        'label' => 'Delivery Areas',
        'url' => '/admin/areas',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'
    ],
    [
        'id' => 'settings',
        'label' => 'Store & Delivery',
        'url' => '/admin/settings',
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
    ]
];
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle ?? 'Admin Portal - N.A Fresh Fruits & Coconuts') ?></title>
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
                            500: '#2e7d32',
                            600: '#235d3a',
                            700: '#1b4a2e',
                            800: '#143823',
                            900: '#0c2617',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Chart.js for Reports -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-gray-50 text-gray-800 antialiased overflow-x-hidden"
      x-data="{ 
          sidebarExpanded: localStorage.getItem('na_admin_sidebar_expanded') !== 'false',
          mobileSidebarOpen: false,
          toggleSidebar() {
              this.sidebarExpanded = !this.sidebarExpanded;
              localStorage.setItem('na_admin_sidebar_expanded', this.sidebarExpanded);
          }
      }"
      @keydown.escape.window="mobileSidebarOpen = false">

    <!-- ============================================================== -->
    <!-- 1. MOBILE OFF-CANVAS SIDEBAR DRAWER WITH DARK BACKDROP        -->
    <!-- ============================================================== -->
    <div x-show="mobileSidebarOpen" 
         x-cloak 
         class="fixed inset-0 z-50 lg:hidden flex" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Dark Translucent Backdrop -->
        <div x-show="mobileSidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileSidebarOpen = false" 
             class="fixed inset-0 bg-black/60 backdrop-blur-xs"></div>

        <!-- Slide-out Drawer Panel -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative max-w-xs w-full bg-brand-800 text-white flex flex-col justify-between shadow-2xl z-10">
            
            <div class="p-5 flex-1 overflow-y-auto">
                <!-- Mobile Drawer Header -->
                <div class="flex items-center justify-between pb-4 border-b border-brand-700/60 mb-6">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center font-black text-white text-base">
                            NA
                        </div>
                        <div>
                            <span class="font-extrabold text-white text-base block tracking-tight">N.A Admin</span>
                            <span class="text-[10px] text-emerald-300">Fresh Fruits & Coconuts</span>
                        </div>
                    </div>
                    <button type="button" @click="mobileSidebarOpen = false" class="text-white/70 hover:text-white p-1" aria-label="Close menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="space-y-1.5 text-xs font-semibold">
                    <?php foreach ($navItems as $item): ?>
                    <a href="<?= $item['url'] ?>" 
                       @click="mobileSidebarOpen = false"
                       class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition <?= $activeAdminTab === $item['id'] ? 'bg-brand-600 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-brand-700/60 hover:text-white' ?>">
                        <?= $item['icon'] ?>
                        <span class="text-sm"><?= e($item['label']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Mobile Drawer Bottom Actions -->
            <div class="p-5 border-t border-brand-700/60 bg-brand-900/40 space-y-3">
                <a href="/" target="_blank" class="flex items-center gap-2 text-xs text-emerald-300 hover:text-white font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>View Customer Storefront &rarr;</span>
                </a>
                <form action="/admin/logout" method="POST" class="pt-1">
                    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                    <button type="submit" class="w-full bg-red-950/60 hover:bg-red-900 text-red-200 hover:text-white text-xs font-bold py-2.5 px-4 rounded-xl transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Logout of Admin</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. PERMANENT DESKTOP SIDEBAR (EXPANDED & COMPACT MODES)        -->
    <!-- ============================================================== -->
    <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:flex lg:flex-col bg-brand-800 text-white transition-all duration-300 ease-in-out border-r border-brand-700/60 shadow-xl"
           :class="sidebarExpanded ? 'w-64' : 'w-20'">
        
        <!-- Sidebar Brand Header -->
        <div class="h-16 flex items-center px-4 border-b border-brand-700/60 transition-all duration-300"
             :class="sidebarExpanded ? 'justify-between' : 'justify-center'">
            
            <a href="/admin" class="flex items-center gap-2.5 overflow-hidden group">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center font-black text-white text-base shrink-0 group-hover:scale-105 transition-transform">
                    NA
                </div>
                <div x-show="sidebarExpanded" x-cloak class="transition-opacity duration-200">
                    <span class="font-black text-white text-sm tracking-tight block leading-tight">N.A Admin</span>
                    <span class="text-[10px] text-emerald-300 block">Fresh Fruits & Coconuts</span>
                </div>
            </a>

            <!-- Collapse Toggle Button (Inside header when expanded) -->
            <button type="button" 
                    x-show="sidebarExpanded" 
                    @click="toggleSidebar()" 
                    title="Collapse sidebar to icon-only mode"
                    class="p-1.5 rounded-lg text-emerald-300 hover:text-white hover:bg-white/10 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation List -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5 text-xs font-semibold">
            <?php foreach ($navItems as $item): ?>
            <a href="<?= $item['url'] ?>" 
               title="<?= e($item['label']) ?>"
               class="flex items-center rounded-xl transition group <?= $activeAdminTab === $item['id'] ? 'bg-brand-600 text-white font-bold shadow-xs' : 'text-white/80 hover:bg-brand-700/60 hover:text-white' ?>"
               :class="sidebarExpanded ? 'px-3.5 py-3 gap-3 justify-start' : 'px-0 py-3 justify-center'">
                <?= $item['icon'] ?>
                <span x-show="sidebarExpanded" 
                      x-cloak 
                      class="text-xs transition-opacity duration-200 whitespace-nowrap">
                    <?= e($item['label']) ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar Bottom Footer / Expand Toggle -->
        <div class="p-3 border-t border-brand-700/60 bg-brand-900/30">
            <!-- Expand Button when collapsed -->
            <button type="button" 
                    x-show="!sidebarExpanded" 
                    @click="toggleSidebar()" 
                    title="Expand sidebar"
                    class="w-full flex items-center justify-center p-2 rounded-xl text-emerald-300 hover:text-white hover:bg-white/10 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Store & Logout Links when expanded -->
            <div x-show="sidebarExpanded" x-cloak class="space-y-2">
                <a href="/" target="_blank" class="flex items-center gap-2 text-xs text-emerald-300 hover:text-white font-semibold px-2 py-1 transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>View Customer Storefront</span>
                </a>
                <form action="/admin/logout" method="POST">
                    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                    <button type="submit" class="w-full bg-red-950/60 hover:bg-red-900 text-red-200 hover:text-white text-xs font-bold py-2 px-3 rounded-xl transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ============================================================== -->
    <!-- 3. TOP ADMIN BAR & MAIN CONTENT WRAPPER                        -->
    <!-- ============================================================== -->
    <div class="flex flex-col flex-1 min-h-screen transition-all duration-300 ease-in-out"
         :class="sidebarExpanded ? 'lg:pl-64' : 'lg:pl-20'">

        <!-- Sticky Top Bar -->
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-xs h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <!-- Mobile Menu Button (off-canvas toggle) -->
                <button type="button" 
                        @click="mobileSidebarOpen = true" 
                        class="lg:hidden p-2 rounded-xl text-gray-600 hover:text-brand-600 hover:bg-gray-100 transition"
                        aria-label="Open sidebar menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <!-- Desktop Sidebar Toggle Button -->
                <button type="button" 
                        @click="toggleSidebar()" 
                        class="hidden lg:inline-flex p-2 rounded-xl text-gray-500 hover:text-brand-600 hover:bg-gray-100 transition"
                        title="Toggle Sidebar Expand/Collapse">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                </button>

                <!-- Breadcrumb Section Indicator -->
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500">
                    <span class="text-gray-400">Admin</span>
                    <span>/</span>
                    <span class="text-brand-700 font-bold uppercase tracking-wider text-[11px]"><?= e($activeAdminTab) ?></span>
                </div>
            </div>

            <!-- Top Right Profile & Quick Actions -->
            <div class="flex items-center space-x-3">
                <a href="/" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-brand-700 font-semibold bg-gray-100 hover:bg-brand-50 px-3 py-1.5 rounded-full border border-gray-200 transition">
                    <span>Storefront</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>

                <!-- User Badge -->
                <div class="flex items-center gap-2 pl-2">
                    <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                        <?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <span class="hidden md:inline-block text-xs font-bold text-gray-800"><?= e($adminUser['name'] ?? 'Store Manager') ?></span>
                </div>

                <form action="/admin/logout" method="POST" class="inline">
                    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                    <button type="submit" class="text-xs text-gray-500 hover:text-red-600 p-2 rounded-lg hover:bg-gray-100 transition" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Alert Message -->
        <?php $flash = get_flash(); if ($flash): ?>
        <div class="px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-3.5 rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-2.5 <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' ?>">
                <?php if ($flash['type'] === 'error'): ?>
                <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php else: ?>
                <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?php endif; ?>
                <span><?= e($flash['message']) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Main Content View Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">
