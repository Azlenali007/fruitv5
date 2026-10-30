<?php
/**
 * Admin Razorpay & Payment Gateway Settings View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Payment Gateway Settings - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'payment_settings';
require_once __DIR__ . '/layout-header.php';

$pSettings = RazorpayService::getSettings();
$currentDomain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$webhookEndpointUrl = $currentDomain . '/webhooks/razorpay';
?>

<div class="max-w-4xl mx-auto space-y-6" x-data="{
    testing: false,
    testResult: null,
    testStatus: null,
    testRazorpay() {
        this.testing = true;
        this.testResult = null;
        this.testStatus = null;
        fetch('/admin/payment-settings/test-connection', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '<?= CSRF_TOKEN_KEY ?>=<?= csrf_token() ?>'
        })
        .then(res => res.json())
        .then(data => {
            this.testing = false;
            if (data.success) {
                this.testStatus = 'success';
                this.testResult = data.message;
            } else {
                this.testStatus = 'error';
                this.testResult = data.error;
            }
        })
        .catch(err => {
            this.testing = false;
            this.testStatus = 'error';
            this.testResult = 'Network or server error while testing connection.';
        });
    },
    copyWebhook() {
        const el = document.getElementById('webhookUrlInput');
        el.select();
        document.execCommand('copy');
        alert('Webhook URL copied to clipboard: ' + el.value);
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Payment Gateway & COD Settings</h1>
            <p class="text-xs text-gray-500">Configure native Razorpay credentials, Webhook secret, and Cash on Delivery</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/admin/payments" class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold py-2.5 px-4 rounded-xl transition flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>View Payment Transactions</span>
            </a>
        </div>
    </div>

    <!-- Active Mode & Diagnostics Banner -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl <?= $pSettings['razorpay_enabled'] === '1' ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' ?> flex items-center justify-center font-bold text-lg">
                💳
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase text-gray-400 tracking-wider">Razorpay Gateway</p>
                <p class="text-xs font-black <?= !empty($pSettings['razorpay_enabled']) ? 'text-emerald-700' : 'text-gray-500' ?>">
                    <?= !empty($pSettings['razorpay_enabled']) ? 'ENABLED (' . strtoupper($pSettings['razorpay_mode']) . ' MODE)' : 'DISABLED' ?>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl <?= !empty($pSettings['cod_enabled']) ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' ?> flex items-center justify-center font-bold text-lg">
                💵
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase text-gray-400 tracking-wider">Cash on Delivery</p>
                <p class="text-xs font-black <?= !empty($pSettings['cod_enabled']) ? 'text-emerald-700' : 'text-gray-500' ?>">
                    <?= !empty($pSettings['cod_enabled']) ? 'ENABLED' : 'DISABLED' ?>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl <?= !empty($pSettings['razorpay_key_secret']) ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' ?> flex items-center justify-center font-bold text-lg">
                🔒
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase text-gray-400 tracking-wider">Credentials Security</p>
                <p class="text-xs font-black <?= !empty($pSettings['razorpay_key_secret']) ? 'text-blue-700' : 'text-amber-700' ?>">
                    <?= !empty($pSettings['razorpay_key_secret']) ? 'Configured & Encrypted' : 'Key Secret Required' ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="/admin/payment-settings/update" method="POST" class="space-y-6">
        <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

        <input type="hidden" name="is_form_submission" value="1">

        <!-- Section 1: Payment Methods Enable / Disable -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Payment Methods</h2>
                    <p class="text-xs text-gray-500">Toggle customer checkout payment options</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- COD Toggle -->
                <label class="flex items-start gap-3.5 p-4 bg-gray-50 border border-gray-200 rounded-2xl cursor-pointer hover:border-gray-300 transition">
                    <input type="hidden" name="cod_enabled" value="0">
                    <input 
                        type="checkbox" 
                        name="cod_enabled" 
                        value="1" 
                        <?= $pSettings['cod_enabled'] ? 'checked' : '' ?>
                        class="w-4 h-4 text-brand-600 rounded-sm focus:ring-brand-500 mt-1"
                    >
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs text-gray-900">Enable Cash on Delivery (COD)</span>
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">Recommended</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                            Customers pay in cash directly when the delivery agent arrives with fresh tender coconuts at their doorstep.
                        </p>
                    </div>
                </label>

                <!-- Razorpay Toggle -->
                <label class="flex items-start gap-3.5 p-4 bg-gray-50 border border-gray-200 rounded-2xl cursor-pointer hover:border-gray-300 transition">
                    <input type="hidden" name="razorpay_enabled" value="0">
                    <input 
                        type="checkbox" 
                        name="razorpay_enabled" 
                        value="1" 
                        <?= $pSettings['razorpay_enabled'] ? 'checked' : '' ?>
                        class="w-4 h-4 text-brand-600 rounded-sm focus:ring-brand-500 mt-1"
                    >
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs text-gray-900">Enable Razorpay Payment Gateway</span>
                            <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded-full">UPI / Cards / NetBanking</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                            Allows customers to pay instantly using PhonePe, Google Pay, Paytm, credit/debit cards, and UPI.
                        </p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Section 2: Razorpay API Credentials -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Razorpay API Credentials</h2>
                    <p class="text-xs text-gray-500">Acquire these from your official Razorpay Dashboard &rarr; Settings &rarr; API Keys</p>
                </div>
            </div>

            <!-- Mode Selector -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Gateway Environment Mode</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-md">
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer <?= $pSettings['razorpay_mode'] === 'test' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold ring-1 ring-brand-600' : 'border-gray-200 bg-gray-50 hover:bg-white text-gray-700' ?>">
                        <input type="radio" name="razorpay_mode" value="test" <?= $pSettings['razorpay_mode'] === 'test' ? 'checked' : '' ?> class="text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="text-xs block font-bold">Test / Sandbox Mode</span>
                            <span class="text-[10px] text-gray-500 block">Uses test keys (rzp_test_...) for dry-run orders</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer <?= $pSettings['razorpay_mode'] === 'live' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-900 font-bold ring-1 ring-emerald-600' : 'border-gray-200 bg-gray-50 hover:bg-white text-gray-700' ?>">
                        <input type="radio" name="razorpay_mode" value="live" <?= $pSettings['razorpay_mode'] === 'live' ? 'checked' : '' ?> class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs block font-bold">Live Production Mode</span>
                            <span class="text-[10px] text-gray-500 block">Uses real keys (rzp_live_...) with real INR settlements</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Key ID and Secret Inputs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Razorpay Key ID</label>
                    <input 
                        type="text" 
                        name="razorpay_key_id" 
                        value="<?= e($pSettings['razorpay_key_id']) ?>" 
                        placeholder="rzp_test_... or rzp_live_..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 font-mono text-xs text-gray-900 focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                    <p class="text-[10px] text-gray-400 mt-1">Publicly used in the checkout popup to initialize payments.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Razorpay Key Secret</label>
                    <input 
                        type="password" 
                        name="razorpay_key_secret" 
                        placeholder="<?= !empty($pSettings['razorpay_key_secret']) ? '•••••••••••••••••••• (Leave blank to keep)' : 'Enter Razorpay Key Secret' ?>"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 font-mono text-xs text-gray-900 focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                    <p class="text-[10px] text-gray-400 mt-1">
                        Current: <span class="font-mono font-bold text-gray-600"><?= e(RazorpayService::maskSecret($pSettings['razorpay_key_secret'])) ?></span>. Secret is kept strictly server-side.
                    </p>
                </div>
            </div>

            <!-- Webhook Secret Input -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Razorpay Webhook Secret</label>
                <div class="max-w-md">
                    <input 
                        type="password" 
                        name="razorpay_webhook_secret" 
                        placeholder="<?= !empty($pSettings['razorpay_webhook_secret']) ? '•••••••••••••••••••• (Leave blank to keep)' : 'Enter Razorpay Webhook Secret' ?>"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 font-mono text-xs text-gray-900 focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>
                <p class="text-[10px] text-gray-400 mt-1">
                    Current: <span class="font-mono font-bold text-gray-600"><?= e(RazorpayService::maskSecret($pSettings['razorpay_webhook_secret'])) ?></span>. Shared secret used to verify webhook signatures (HMAC SHA256).
                </p>
            </div>
        </div>

        <!-- Section 3: Webhook Endpoint & Setup Guide -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Razorpay Webhook Configuration</h2>
            <p class="text-xs text-gray-500">
                To guarantee 100% order capture even if a customer closes their mobile browser before the redirect, configure this Webhook in your Razorpay Dashboard:
            </p>

            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200 space-y-3">
                <label class="block text-[11px] font-bold text-gray-600 uppercase">Your Store Webhook URL</label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="webhookUrlInput"
                        readonly 
                        value="<?= e($webhookEndpointUrl) ?>" 
                        class="flex-1 bg-white border border-gray-300 rounded-xl px-3.5 py-2 font-mono text-xs text-gray-800"
                    >
                    <button 
                        type="button" 
                        @click="copyWebhook()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold px-4 py-2 rounded-xl transition shrink-0"
                    >
                        Copy URL
                    </button>
                </div>

                <div class="text-[11px] text-gray-600 space-y-1 pt-1">
                    <p class="font-bold text-gray-800">Required Webhook Events to Subscribe to in Razorpay Dashboard:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-gray-500 font-mono text-[10px]">
                        <li><span class="text-brand-700 font-bold">payment.captured</span> (Marks payment as captured and confirms order)</li>
                        <li><span class="text-brand-700 font-bold">order.paid</span> (Confirms full order payment settlement)</li>
                        <li><span class="text-red-700 font-bold">payment.failed</span> (Records customer card/UPI failure reason)</li>
                        <li><span class="text-blue-700 font-bold">refund.processed</span> (Records admin refund settlements)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section 4: Live Diagnostics & Action Buttons -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Gateway Connection Test</h2>
                    <p class="text-xs text-gray-500">Verify credentials against the official Razorpay API before enabling for customers</p>
                </div>
                <button 
                    type="button" 
                    @click="testRazorpay()" 
                    :disabled="testing"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold py-2.5 px-4 rounded-xl transition flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <svg x-show="testing" class="animate-spin w-4 h-4 text-gray-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="testing ? 'Testing Connection...' : 'Test Razorpay Connection'"></span>
                </button>
            </div>

            <!-- Diagnostics Alert Box -->
            <div x-show="testResult" x-cloak class="p-3.5 rounded-2xl text-xs font-semibold flex items-start gap-2.5"
                 :class="testStatus === 'success' ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-red-50 text-red-900 border border-red-200'">
                <template x-if="testStatus === 'success'">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </template>
                <template x-if="testStatus === 'error'">
                    <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </template>
                <span class="leading-relaxed" x-text="testResult"></span>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <button 
                    type="submit" 
                    class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold py-3 px-6 rounded-xl transition shadow-xs flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save Payment Gateway Settings</span>
                </button>
            </div>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
