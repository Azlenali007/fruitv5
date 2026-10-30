<?php
/**
 * Admin Payments Management View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Payment Transactions - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'payments';
require_once __DIR__ . '/layout-header.php';

$filterStatus = trim($_GET['status'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;

// Fetch filtered transactions
$transactionsData = PaymentTransaction::getFiltered([
    'status' => $filterStatus,
    'search' => $searchQuery,
    'page' => $page,
    'per_page' => $perPage
]);

$transactions = $transactionsData['items'] ?? [];
$totalTransactions = $transactionsData['total'] ?? 0;
$totalPages = ceil($totalTransactions / $perPage);

// Overall stats
$stats = PaymentTransaction::getSummaryStats();
?>

<div class="space-y-6" x-data="{
    detailModalOpen: false,
    selectedTx: null,
    viewTx(tx) {
        this.selectedTx = tx;
        this.detailModalOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Payment Transactions</h1>
            <p class="text-xs text-gray-500">Audit trail of all online Razorpay transactions and Cash on Delivery orders</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/admin/payment-settings" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <span>Gateway Settings</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs space-y-1">
            <span class="text-[10px] uppercase font-bold text-gray-400">Total Online Collected</span>
            <p class="text-xl font-black text-emerald-700">₹<?= number_format($stats['total_captured_amount'] ?? 0, 2) ?></p>
            <p class="text-[10px] text-gray-500 font-semibold"><?= (int)($stats['captured_count'] ?? 0) ?> successful payments</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs space-y-1">
            <span class="text-[10px] uppercase font-bold text-gray-400">Total Attempts</span>
            <p class="text-xl font-black text-gray-900"><?= (int)($stats['total_attempts'] ?? 0) ?></p>
            <p class="text-[10px] text-gray-500 font-semibold"><?= (int)($stats['initiated_count'] ?? 0) ?> initiated / pending</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs space-y-1">
            <span class="text-[10px] uppercase font-bold text-gray-400">Failed Attempts</span>
            <p class="text-xl font-black text-red-600"><?= (int)($stats['failed_count'] ?? 0) ?></p>
            <p class="text-[10px] text-gray-500 font-semibold">User dropped or bank declined</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs space-y-1">
            <span class="text-[10px] uppercase font-bold text-gray-400">Success Rate</span>
            <?php 
                $attempts = (int)($stats['total_attempts'] ?? 0);
                $captured = (int)($stats['captured_count'] ?? 0);
                $rate = $attempts > 0 ? round(($captured / $attempts) * 100, 1) : 100;
            ?>
            <p class="text-xl font-black text-brand-700"><?= $rate ?>%</p>
            <p class="text-[10px] text-gray-500 font-semibold">Razorpay conversion rate</p>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs">
        <form action="/admin/payments" method="GET" class="flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="flex-1 w-full md:w-auto relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input 
                    type="text" 
                    name="q" 
                    value="<?= e($searchQuery) ?>" 
                    placeholder="Search by order #, payment ID (pay_...), attempt ref..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-3 py-2 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none"
                >
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="status" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">All Payment Statuses</option>
                    <option value="captured" <?= $filterStatus === 'captured' ? 'selected' : '' ?>>Captured (Paid)</option>
                    <option value="authorized" <?= $filterStatus === 'authorized' ? 'selected' : '' ?>>Authorized</option>
                    <option value="initiated" <?= $filterStatus === 'initiated' ? 'selected' : '' ?>>Initiated</option>
                    <option value="failed" <?= $filterStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="refunded" <?= $filterStatus === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>

                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition shadow-xs">
                    Filter
                </button>
                <?php if (!empty($filterStatus) || !empty($searchQuery)): ?>
                <a href="/admin/payments" class="text-xs text-gray-500 hover:text-gray-800 underline px-2">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-bold">
                        <th class="py-3 px-4">Date / Time</th>
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Attempt Ref</th>
                        <th class="py-3 px-4">Gateway Payment ID</th>
                        <th class="py-3 px-4">Gateway Order ID</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <p class="font-bold text-gray-600">No payment transactions recorded yet</p>
                            <p class="text-xs text-gray-400 mt-0.5">Online Razorpay transactions will appear here as soon as checkout attempts begin.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap text-gray-500 font-mono text-[11px]">
                                <?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="/admin/orders/view?id=<?= (int)$tx['order_id'] ?>" class="font-bold text-brand-700 hover:text-brand-800 hover:underline">
                                    #<?= e($tx['order_number'] ?? ('ID:' . $tx['order_id'])) ?>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500">
                                <?= e($tx['attempt_reference'] ?? '—') ?>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                <?php if (!empty($tx['gateway_payment_id'])): ?>
                                    <span class="text-emerald-700 font-bold"><?= e($tx['gateway_payment_id']) ?></span>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Not generated yet</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500">
                                <?= e($tx['gateway_order_id'] ?? '—') ?>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap font-black text-gray-900">
                                ₹<?= number_format((float)$tx['amount'], 2) ?>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <?php
                                $status = strtolower($tx['status']);
                                $badgeClass = match($status) {
                                    'captured', 'paid' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'authorized' => 'bg-blue-50 text-blue-800 border-blue-200',
                                    'initiated', 'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'failed' => 'bg-red-50 text-red-800 border-red-200',
                                    'refunded' => 'bg-purple-50 text-purple-800 border-purple-200',
                                    default => 'bg-gray-50 text-gray-800 border-gray-200'
                                };
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeClass ?>">
                                    <?= strtoupper($status) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <button 
                                    type="button" 
                                    @click="viewTx(<?= htmlspecialchars(json_encode($tx), ENT_QUOTES, 'UTF-8') ?>)"
                                    class="text-brand-600 hover:text-brand-800 font-bold hover:underline"
                                >
                                    Inspect
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="p-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Showing <?= count($transactions) ?> of <?= $totalTransactions ?> records</span>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/admin/payments?page=<?= $i ?>&status=<?= urlencode($filterStatus) ?>&q=<?= urlencode($searchQuery) ?>" 
                   class="px-3 py-1.5 rounded-lg border <?= $i === $page ? 'bg-brand-600 text-white border-brand-600 font-bold' : 'bg-white border-gray-200 hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Transaction Detail Modal -->
    <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="detailModalOpen = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="font-bold text-base text-gray-900">Transaction Details</h3>
                    <p class="text-xs text-gray-400 font-mono" x-text="selectedTx?.attempt_reference"></p>
                </div>
                <button type="button" @click="detailModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <template x-if="selectedTx">
                <div class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded-xl">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Amount</span>
                            <span class="font-black text-gray-900 text-sm" x-text="'₹' + parseFloat(selectedTx.amount).toFixed(2)"></span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Status</span>
                            <span class="font-bold uppercase text-xs" x-text="selectedTx.status"></span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Gateway Payment ID</span>
                        <p class="font-mono bg-gray-100 p-2 rounded-lg text-gray-800 select-all" x-text="selectedTx.gateway_payment_id || 'Not generated'"></p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Gateway Order ID</span>
                        <p class="font-mono bg-gray-100 p-2 rounded-lg text-gray-800 select-all" x-text="selectedTx.gateway_order_id || 'Not generated'"></p>
                    </div>

                    <div class="space-y-1" x-show="selectedTx.error_message">
                        <span class="text-[10px] uppercase font-bold text-red-500 block">Gateway Error Message</span>
                        <p class="bg-red-50 text-red-700 p-2 rounded-lg border border-red-200" x-text="selectedTx.error_message"></p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Payment Method / Gateway</span>
                        <p class="font-semibold text-gray-700" x-text="(selectedTx.payment_method || 'razorpay') + ' (' + (selectedTx.gateway_name || 'razorpay') + ')'"></p>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                        <button type="button" @click="detailModalOpen = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-4 py-2 rounded-xl text-xs">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
