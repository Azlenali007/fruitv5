<?php
$currentStep = 4;
$installerTitle = 'Step 4: Administrator Account - N.A Installer';
ob_start();
?>

<div class="space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold text-brand-600 uppercase tracking-wider">Step 4 of 6</span>
        <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight mt-0.5">Create First Administrator</h2>
        <p class="text-xs text-gray-500 mt-1">
            Configure the primary store administrator account for managing inventory, order dispatch, and delivery settings.
        </p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="p-3.5 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium">
        <?= e($error) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($adminCount) && $adminCount > 0): ?>
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 space-y-2">
        <div class="flex items-center gap-2 font-bold text-sm">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
            Existing Administrator Account Detected
        </div>
        <p class="text-[11px] leading-relaxed">
            The database already has a configured administrator account. You can retain your existing login without entering passwords, or update it using the form below.
        </p>
    </div>
    <?php endif; ?>

    <form action="/install?step=4" method="POST" class="space-y-4 text-xs">
        <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

        <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Full Name</label>
            <input 
                type="text" 
                name="admin_name" 
                value="<?= e($_POST['admin_name'] ?? 'Store Manager') ?>" 
                required 
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
            >
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Username</label>
                <input 
                    type="text" 
                    name="admin_username" 
                    value="<?= e($_POST['admin_username'] ?? 'admin') ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Email Address</label>
                <input 
                    type="email" 
                    name="admin_email" 
                    value="<?= e($_POST['admin_email'] ?? 'admin@nafruitsandcoconuts.com') ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Password (min 8 characters)</label>
                <input 
                    type="password" 
                    name="admin_password" 
                    value="<?= e($_POST['admin_password'] ?? 'admin12345') ?>" 
                    required 
                    minlength="8"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Confirm Password</label>
                <input 
                    type="password" 
                    name="admin_password_confirm" 
                    value="<?= e($_POST['admin_password_confirm'] ?? 'admin12345') ?>" 
                    required 
                    minlength="8"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>
        </div>

        <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-[11px] text-gray-500">
            Passwords are encrypted using PHP <code class="font-mono font-bold text-gray-700">password_hash()</code> with bcrypt and stored safely in MySQL.
        </div>

        <div class="pt-4 border-t border-gray-100 flex flex-wrap justify-between items-center gap-3">
            <a href="/install?step=3" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                &larr; Back
            </a>

            <div class="flex items-center gap-2">
                <?php if (!empty($adminCount) && $adminCount > 0): ?>
                <button type="submit" name="keep_existing_admin" value="1" formnovalidate class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs py-3 px-5 rounded-xl transition">
                    Keep Existing Account &rarr;
                </button>
                <?php endif; ?>

                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-xs transition flex items-center gap-2">
                    <span>Save Admin & Continue</span>
                    &rarr;
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$stepContent = ob_get_clean();
require __DIR__ . '/layout.php';
