<?php
/**
 * Customer Login View
 * N.A Fresh Fruits & Coconuts
 */
$pageTitle = 'Customer Login - N.A Fresh Fruits & Coconuts';
$activePage = 'profile';
require_once __DIR__ . '/../layouts/header.php';

$cart = CartService::getCart();
$hasCartItems = !empty($cart['items']);
?>

<div class="max-w-md mx-auto px-4 py-8 sm:py-12">
    <!-- Brand Header -->
    <div class="text-center space-y-2 mb-8">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center font-black text-2xl border border-brand-100/80 shadow-xs">
            NA
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-950 tracking-tight">Customer Login</h1>
        <p class="text-xs sm:text-sm text-gray-500 max-w-xs mx-auto">
            <?= $hasCartItems ? 'Log in to place your fresh coconut water order.' : 'Access your order history, delivery addresses, and fast ordering.' ?>
        </p>
    </div>

    <?php if ($hasCartItems): ?>
    <div class="mb-6 bg-brand-50/70 border border-brand-200/60 rounded-2xl p-3.5 flex items-center gap-3 text-xs text-brand-800">
        <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        <span><strong><?= $cart['count'] ?> item(s) in cart:</strong> Your items are waiting! Log in to continue directly to checkout.</span>
    </div>
    <?php endif; ?>

    <!-- Login Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-6">
        <form action="/login" method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

            <!-- Mobile or Email Identifier -->
            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[11px] mb-1.5">
                    Mobile Number or Email
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="identifier" 
                        required 
                        value="<?= e($_POST['identifier'] ?? '') ?>"
                        placeholder="e.g. 9876543210 or your@email.com"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                    >
                </div>
            </div>

            <!-- Password -->
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="font-bold text-gray-700 uppercase tracking-wider text-[11px]">
                        Password
                    </label>
                </div>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    placeholder="Enter your password"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                >
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm py-3.5 px-6 rounded-2xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                >
                    <span>Log In</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>

        <div class="border-t border-gray-100 pt-4 text-center space-y-2">
            <p class="text-xs text-gray-500">
                New customer? 
                <a href="/register" class="text-brand-600 font-bold hover:underline">Create an account</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
