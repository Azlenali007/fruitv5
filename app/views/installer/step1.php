<?php
$currentStep = 1;
$installerTitle = 'Step 1: System Requirements - N.A Installer';
ob_start();
?>

<div class="space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold text-brand-600 uppercase tracking-wider">Step 1 of 6</span>
        <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight mt-0.5">Welcome & System Checks</h2>
        <p class="text-xs text-gray-500 mt-1">
            Verifying that your PHP environment and storage permissions meet all requirements for running N.A Fresh Fruits & Coconuts.
        </p>
    </div>

    <!-- Requirements Table -->
    <div class="divide-y divide-gray-100 border border-gray-100 rounded-2xl overflow-hidden text-xs">
        <?php foreach ($checks as $key => $chk): ?>
        <div class="p-3.5 flex items-center justify-between hover:bg-gray-50/50">
            <div>
                <span class="font-bold text-gray-800 block"><?= e($chk['name']) ?></span>
                <span class="text-[11px] text-gray-400">Current: <?= e($chk['current']) ?></span>
            </div>
            <div>
                <?php if ($chk['pass']): ?>
                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-[11px] font-bold border border-emerald-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    Passed
                </span>
                <?php else: ?>
                <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 px-2.5 py-1 rounded-full text-[11px] font-bold border border-red-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Required
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (!$allPassed): ?>
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 space-y-1">
        <p class="font-bold">One or more requirements failed.</p>
        <p class="text-[11px] leading-relaxed">
            Please resolve the items marked "Required" above, or grant write permissions (`chmod 775 storage app/config public/assets/uploads`), then refresh this page to proceed.
        </p>
    </div>
    <?php endif; ?>

    <div class="pt-2 flex justify-between items-center">
        <a href="/install?step=1" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
            Re-check System
        </a>

        <?php if ($allPassed): ?>
        <a href="/install?step=2" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-xs transition flex items-center gap-2">
            <span>Continue to Database Setup</span>
            &rarr;
        </a>
        <?php else: ?>
        <button disabled class="bg-gray-200 text-gray-400 font-bold text-xs py-3 px-6 rounded-xl cursor-not-allowed">
            Resolve Issues to Proceed
        </button>
        <?php endif; ?>
    </div>
</div>

<?php
$stepContent = ob_get_clean();
require __DIR__ . '/layout.php';
