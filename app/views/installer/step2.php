<?php
$currentStep = 2;
$installerTitle = 'Step 2: Database Configuration - N.A Installer';
ob_start();
?>

<div class="space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold text-brand-600 uppercase tracking-wider">Step 2 of 6</span>
        <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight mt-0.5">MySQL Database Setup</h2>
        <p class="text-xs text-gray-500 mt-1">
            Enter your MySQL database connection credentials. We will test the PDO connection before continuing.
        </p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="p-3.5 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium flex items-start gap-2">
        <svg class="w-4 h-4 shrink-0 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?= e($error) ?></span>
    </div>
    <?php endif; ?>

    <form action="/install?step=2" method="POST" class="space-y-4 text-xs">
        <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block font-bold text-gray-700 uppercase mb-1">Database Host</label>
                <input 
                    type="text" 
                    name="host" 
                    value="<?= e($host) ?>" 
                    required 
                    placeholder="127.0.0.1 or localhost"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Port</label>
                <input 
                    type="number" 
                    name="port" 
                    value="<?= e($port) ?>" 
                    required 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>
        </div>

        <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Database Name</label>
            <input 
                type="text" 
                name="database" 
                value="<?= e($database) ?>" 
                required 
                placeholder="e.g. your_cpanel_dbname"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
            >
            <p class="text-[10px] text-gray-400 mt-1">
                On cPanel/shared hosting, enter the full database name created in your control panel (e.g., <code class="font-mono">user_dbname</code>).
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">MySQL Username</label>
                <input 
                    type="text" 
                    name="username" 
                    value="<?= e($username) ?>" 
                    required 
                    placeholder="e.g. your_cpanel_dbuser"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">MySQL Password</label>
                <input 
                    type="password" 
                    name="password" 
                    value="<?= e($password) ?>" 
                    placeholder="Password (leave blank if none)"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>
        </div>

        <!-- Optional Create Database Checkbox -->
        <div class="pt-1">
            <label class="flex items-center gap-2.5 p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
                <input 
                    type="checkbox" 
                    name="create_db" 
                    value="1" 
                    class="w-4 h-4 text-brand-600 rounded-sm focus:ring-brand-500"
                >
                <div>
                    <span class="text-gray-700 font-medium block">Attempt to create database automatically if it does not exist</span>
                    <span class="text-[10px] text-gray-400 block">Leave unchecked if your database was already created in cPanel / hosting control panel.</span>
                </div>
            </label>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
            <a href="/install?step=1" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                &larr; Back to Requirements
            </a>

            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-xs transition flex items-center gap-2">
                <span>Test & Save Database</span>
                &rarr;
            </button>
        </div>
    </form>
</div>

<?php
$stepContent = ob_get_clean();
require __DIR__ . '/layout.php';
