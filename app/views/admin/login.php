<?php
/**
 * Admin Login View
 * N.A Fresh Fruits & Coconuts
 */
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - N.A Fresh Fruits & Coconuts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            600: '#235d3a',
                            700: '#1b4a2e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center p-4">

<div class="max-w-md w-full space-y-6">
    
    <div class="text-center space-y-2">
        <a href="/" class="inline-flex items-center gap-2 text-brand-600">
            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-black text-xl">NA</span>
        </a>
        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Admin Portal</h1>
        <p class="text-xs text-gray-500">N.A Fresh Fruits & Coconuts Management System</p>
    </div>

    <!-- Login Box -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5">
        
        <?php if (!empty($loginError)): ?>
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium">
            <?= e($loginError) ?>
        </div>
        <?php endif; ?>

        <form action="/admin/login" method="POST" class="space-y-4">
            <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    value="<?= e($_POST['username'] ?? 'admin') ?>" 
                    required 
                    autocomplete="username"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    value="admin12345"
                    required 
                    autocomplete="current-password"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm py-3 px-4 rounded-xl shadow-xs transition">
                    Sign In to Admin Panel
                </button>
            </div>
        </form>

        <div class="border-t border-gray-100 pt-3 text-center">
            <p class="text-[11px] text-gray-400">
                Default Credentials: Username <code class="font-mono text-gray-700 font-bold">admin</code> / Password <code class="font-mono text-gray-700 font-bold">admin12345</code>
            </p>
        </div>

    </div>

    <div class="text-center">
        <a href="/" class="text-xs font-semibold text-gray-500 hover:text-brand-600 transition">
            &larr; Return to Customer Website
        </a>
    </div>

</div>

</body>
</html>
