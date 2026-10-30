<?php
$currentStep = 6;
$installerTitle = $allPassed ? 'Installation Completed - N.A Installer' : 'Installation Verification - N.A Installer';
ob_start();
?>

<div class="space-y-6 text-center">
    
    <?php if ($allPassed): ?>
    <!-- Big Checkmark -->
    <div class="w-20 h-20 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>

    <div class="space-y-1">
        <h2 class="text-2xl font-black text-gray-950 tracking-tight">Installation Completed Successfully!</h2>
        <p class="text-xs text-gray-500 max-w-md mx-auto leading-relaxed">
            N.A Fresh Fruits &amp; Coconuts has been installed, initialized with MySQL, and locked for security.
        </p>
    </div>
    <?php else: ?>
    <!-- Warning Icon -->
    <div class="w-20 h-20 mx-auto rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
    </div>

    <div class="space-y-1">
        <h2 class="text-2xl font-black text-gray-950 tracking-tight">Installation Incomplete</h2>
        <p class="text-xs text-gray-500 max-w-md mx-auto leading-relaxed">
            One or more verification steps could not be confirmed. For security, the installation lock was not created.
        </p>
    </div>
    <?php endif; ?>

    <!-- Verification Checklist -->
    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 text-left text-xs space-y-2.5">
        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">System Verification Status</span>
        <?php foreach ($validation as $v): ?>
        <div class="p-2 rounded-xl flex items-center justify-between <?= $v['pass'] ? 'bg-white' : 'bg-red-50 border border-red-200' ?>">
            <div>
                <span class="font-bold text-gray-800 block"><?= e($v['name']) ?></span>
                <?php if (!empty($v['error'])): ?>
                <span class="text-[11px] text-red-600 font-medium block"><?= e($v['error']) ?></span>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($v['pass']): ?>
                <span class="text-emerald-700 font-bold flex items-center gap-1 text-[11px]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    Verified
                </span>
                <?php else: ?>
                <span class="text-red-700 font-bold flex items-center gap-1 text-[11px]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Action Needed
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($allPassed): ?>
    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 text-left">
        <span class="font-bold block">Security Lock Created</span>
        <span class="text-[11px] text-emerald-700">The installer lock file <code class="font-mono font-bold">storage/installed.lock</code> has been created. The store is live and all installer routes are now permanently locked.</span>
    </div>

    <!-- Launch Buttons -->
    <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
        <a href="/" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm py-3 px-6 rounded-xl shadow-xs transition flex items-center justify-center gap-2">
            <span>Visit Live Storefront</span>
            &rarr;
        </a>

        <a href="/admin" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-sm py-3 px-6 rounded-xl transition flex items-center justify-center gap-2">
            <span>Log In to Store Admin</span>
        </a>
    </div>
    <?php else: ?>
    <div class="pt-2 flex justify-center gap-3">
        <a href="/install?step=2" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl transition">
            &larr; Return to Step 2 &amp; Resolve Issues
        </a>
        <a href="/install?step=6" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs py-3 px-6 rounded-xl transition">
            Re-run Verification
        </a>
    </div>
    <?php endif; ?>

</div>

<?php
$stepContent = ob_get_clean();
require __DIR__ . '/layout.php';
