<?php
$currentStep = 3;
$installerTitle = 'Step 3: Database Initialization - N.A Installer';
ob_start();
?>

<div class="space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold text-brand-600 uppercase tracking-wider">Step 3 of 6</span>
        <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight mt-0.5">Initialize Database Tables</h2>
        <p class="text-xs text-gray-500 mt-1">
            Connected to database <span class="font-bold text-gray-800 font-mono"><?= e(DB_NAME) ?></span> on <span class="font-bold text-gray-800 font-mono"><?= e(DB_HOST) ?></span>.
        </p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-2xl font-medium space-y-1.5 leading-relaxed">
        <div class="flex items-center gap-2 font-bold text-red-700">
            <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Database Notice
        </div>
        <div><?= nl2br(e($error)) ?></div>
    </div>
    <?php endif; ?>

    <?php if (!empty($connectionError)): ?>
    <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-2xl font-medium space-y-2">
        <div class="flex items-center gap-2 font-bold text-amber-700">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Database Connection Warning
        </div>
        <p class="text-[11px] leading-relaxed">
            Could not connect with saved settings (<span class="font-mono"><?= e(DB_USER) ?>@<?= e(DB_HOST) ?>/<?= e(DB_NAME) ?></span>): <?= e($connectionError) ?>
        </p>
        <div>
            <a href="/install?step=2" class="inline-flex items-center gap-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-[11px] px-3.5 py-2 rounded-xl transition">
                &larr; Re-configure Credentials in Step 2
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($isInitialized): ?>
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 space-y-2">
        <div class="flex items-center gap-2 font-bold text-sm">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
            Existing Tables Detected
        </div>
        <p class="text-[11px] leading-relaxed">
            The database already contains core tables (<code class="font-mono">products</code>, <code class="font-mono">orders</code>, <code class="font-mono">admins</code>, <code class="font-mono">settings</code>). You can verify/update the schema safely without destroying customer data.
        </p>
    </div>
    <?php else: ?>
    <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl text-xs space-y-2">
        <p class="font-bold text-gray-800">Ready to create database schema:</p>
        <ul class="grid grid-cols-2 gap-1.5 text-[11px] text-gray-500 font-medium list-disc list-inside">
            <li>Admin Accounts (<code class="font-mono">admins</code>)</li>
            <li>Security Throttling (<code class="font-mono">login_attempts</code>)</li>
            <li>Store Products (<code class="font-mono">products</code>)</li>
            <li>Customer Orders (<code class="font-mono">orders</code>)</li>
            <li>Order Snapshots (<code class="font-mono">order_items</code>)</li>
            <li>Status Audit Logs (<code class="font-mono">order_status_history</code>)</li>
            <li>Store Settings (<code class="font-mono">settings</code>)</li>
            <li>Customer Records (<code class="font-mono">customers</code>)</li>
        </ul>
    </div>
    <?php endif; ?>

    <form action="/install?step=3" method="POST" class="pt-2">
        <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

        <div class="flex justify-between items-center">
            <a href="/install?step=2" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                &larr; Back to Database Config
            </a>

            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-xs transition flex items-center gap-2">
                <span><?= $isInitialized ? 'Verify & Continue' : 'Create Tables & Continue' ?></span>
                &rarr;
            </button>
        </div>
    </form>
</div>

<?php
$stepContent = ob_get_clean();
require __DIR__ . '/layout.php';
