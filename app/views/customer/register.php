<?php
/**
 * Customer Registration View
 * N.A Fresh Fruits & Coconuts
 */
$pageTitle = 'Create Your Customer Account - N.A Fresh Fruits & Coconuts';
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
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-950 tracking-tight">Create Customer Account</h1>
        <p class="text-xs sm:text-sm text-gray-500 max-w-xs mx-auto">
            <?= $hasCartItems ? 'Register to complete your fresh coconut water order.' : 'Join for fast ordering, delivery tracking, and order history.' ?>
        </p>
    </div>

    <?php if ($hasCartItems): ?>
    <div class="mb-6 bg-brand-50/70 border border-brand-200/60 rounded-2xl p-3.5 flex items-center gap-3 text-xs text-brand-800">
        <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        <span><strong><?= $cart['count'] ?> item(s) in cart:</strong> Your coconut water will be saved and you'll go straight to checkout after registering!</span>
    </div>
    <?php endif; ?>

    <!-- Registration Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-6">
        <form action="/register" method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

            <!-- Full Name -->
            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[11px] mb-1.5">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        value="<?= e($_POST['name'] ?? '') ?>"
                        placeholder="e.g. Rahul Sharma"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                    >
                </div>
            </div>

            <!-- Mobile Number (10 digits) -->
            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[11px] mb-1.5">
                    Mobile Number <span class="text-red-500">*</span>
                </label>
                <div class="relative flex">
                    <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-600 font-bold text-sm">
                        +91
                    </span>
                    <input 
                        type="tel" 
                        name="phone" 
                        required 
                        maxlength="10"
                        value="<?= e($_POST['phone'] ?? '') ?>"
                        placeholder="98765 43210"
                        class="w-full bg-gray-50 border border-gray-200 rounded-r-xl px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                    >
                </div>
                <span class="text-[10px] text-gray-400 mt-1 block">Used for delivery updates & order tracking</span>
            </div>

            <!-- Email (Optional) -->
            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[11px] mb-1.5">
                    Email Address <span class="text-gray-400 font-normal lowercase">(optional)</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    value="<?= e($_POST['email'] ?? '') ?>"
                    placeholder="name@example.com"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                >
            </div>

            <!-- Delivery Address (Optional at registration, confirmed at checkout) -->
            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[11px] mb-1.5">
                    Delivery Address <span class="text-gray-400 font-normal lowercase">(optional)</span>
                </label>
                <textarea 
                    name="address" 
                    rows="2"
                    placeholder="Street, locality, house/flat number..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                ><?= e($_POST['address'] ?? '') ?></textarea>
            </div>

            <!-- Password -->
            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[11px] mb-1.5">
                    Create Password <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    minlength="6"
                    placeholder="At least 6 characters"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                >
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[11px] mb-1.5">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    name="confirm_password" 
                    required 
                    minlength="6"
                    placeholder="Repeat password"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                >
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm py-3.5 px-6 rounded-2xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                >
                    <span>Register Account</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>

        <div class="border-t border-gray-100 pt-4 text-center">
            <p class="text-xs text-gray-500">
                Already registered? 
                <a href="/login" class="text-brand-600 font-bold hover:underline">Log in to your account</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
