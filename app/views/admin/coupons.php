<?php
/**
 * Admin Coupons Management View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Coupons & Discount Offers - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'coupons';
require_once __DIR__ . '/layout-header.php';

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');
$typeFilter = trim($_GET['type'] ?? '');

$filters = [
    'search' => $search,
    'status' => $statusFilter,
    'type' => $typeFilter
];

$coupons = Coupon::getAll($filters);
$totalCouponsCount = Coupon::countAll();
$activeCouponsCount = Coupon::countAll(['status' => 'active']);
$expiredCouponsCount = Coupon::countAll(['status' => 'expired']);
$disabledCouponsCount = Coupon::countAll(['status' => 'disabled']);

// Summary totals
$totalRedemptions = 0;
$totalDiscountGiven = 0.0;
foreach ($coupons as $c) {
    $totalRedemptions += (int)($c['total_redemptions'] ?? 0);
    $totalDiscountGiven += (float)($c['total_discount_given'] ?? 0);
}
?>

<div class="space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Coupons & Discounts</h1>
            <p class="text-xs text-gray-500">Manage promotional discount codes, free delivery offers, and customer limits</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/admin/coupons/create" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                <span>Create New Coupon</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase block">Active Coupons</span>
            <span class="text-2xl font-black text-brand-700 mt-1 block"><?= $activeCouponsCount ?></span>
            <span class="text-[10px] text-gray-500">Live in storefront</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase block">Total Coupons</span>
            <span class="text-2xl font-black text-gray-900 mt-1 block"><?= $totalCouponsCount ?></span>
            <span class="text-[10px] text-gray-500">All configurations</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase block">Total Redemptions</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block"><?= $totalRedemptions ?></span>
            <span class="text-[10px] text-gray-500">Orders with coupon</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase block">Total Savings Given</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block">₹<?= number_format($totalDiscountGiven, 0) ?></span>
            <span class="text-[10px] text-gray-500">Discount & Free Delivery</span>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            
            <!-- Status Tabs -->
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 text-xs font-bold text-gray-600">
                <a href="/admin/coupons?status=all<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                   class="px-3 py-1.5 rounded-lg transition <?= $statusFilter === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'hover:bg-gray-100' ?>">
                    All (<?= $totalCouponsCount ?>)
                </a>
                <a href="/admin/coupons?status=active<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                   class="px-3 py-1.5 rounded-lg transition <?= $statusFilter === 'active' ? 'bg-brand-600 text-white shadow-xs' : 'hover:bg-gray-100' ?>">
                    Active (<?= $activeCouponsCount ?>)
                </a>
                <a href="/admin/coupons?status=expired<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                   class="px-3 py-1.5 rounded-lg transition <?= $statusFilter === 'expired' ? 'bg-brand-600 text-white shadow-xs' : 'hover:bg-gray-100' ?>">
                    Expired (<?= $expiredCouponsCount ?>)
                </a>
                <a href="/admin/coupons?status=disabled<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                   class="px-3 py-1.5 rounded-lg transition <?= $statusFilter === 'disabled' ? 'bg-brand-600 text-white shadow-xs' : 'hover:bg-gray-100' ?>">
                    Disabled (<?= $disabledCouponsCount ?>)
                </a>
            </div>

            <!-- Search Box -->
            <form action="/admin/coupons" method="GET" class="relative max-w-xs w-full">
                <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
                <input 
                    type="text" 
                    name="search" 
                    value="<?= e($search) ?>" 
                    placeholder="Search code or title..." 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </form>
        </div>
    </div>

    <!-- Coupons Table Card -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Coupon Code & Title</th>
                        <th class="py-3 px-4">Discount Type</th>
                        <th class="py-3 px-4">Conditions</th>
                        <th class="py-3 px-4">Validity</th>
                        <th class="py-3 px-4">Redemptions</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($coupons)): ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            </div>
                            <p class="font-bold text-gray-600">No coupons found</p>
                            <p class="text-[11px] mt-0.5">Try clearing filters or create your first promotional coupon.</p>
                            <a href="/admin/coupons/create" class="inline-block mt-3 bg-brand-600 text-white font-bold px-3.5 py-1.5 rounded-lg hover:bg-brand-700">
                                Create Coupon
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($coupons as $cpn): 
                        $isExpired = !empty($cpn['expiry_date']) && strtotime($cpn['expiry_date']) < time();
                        $isNotStarted = !empty($cpn['start_date']) && strtotime($cpn['start_date']) > time();
                    ?>
                    <tr class="hover:bg-gray-50/70 transition">
                        
                        <!-- Code & Title -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-brand-800 bg-brand-50 border border-brand-200 px-2.5 py-0.5 rounded-md text-xs tracking-wider">
                                    <?= e($cpn['code']) ?>
                                </span>
                            </div>
                            <span class="font-bold text-gray-900 block mt-1"><?= e($cpn['name']) ?></span>
                            <?php if (!empty($cpn['description'])): ?>
                            <span class="text-[11px] text-gray-500 line-clamp-1 mt-0.5"><?= e($cpn['description']) ?></span>
                            <?php endif; ?>
                        </td>

                        <!-- Discount Type -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <?php if ($cpn['type'] === 'free_delivery'): ?>
                                <span class="inline-flex items-center gap-1 text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full font-bold text-[11px]">
                                    🚚 Free Delivery
                                </span>
                            <?php elseif ($cpn['type'] === 'percentage'): ?>
                                <div class="space-y-0.5">
                                    <span class="inline-flex items-center gap-1 text-blue-800 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full font-bold text-[11px]">
                                        % <?= (float)$cpn['discount_value'] ?>% Off
                                    </span>
                                    <?php if (!empty($cpn['max_discount_amount'])): ?>
                                    <span class="block text-[10px] text-gray-400">Max ₹<?= number_format($cpn['max_discount_amount']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-purple-800 bg-purple-50 border border-purple-200 px-2.5 py-1 rounded-full font-bold text-[11px]">
                                    ₹ Flat ₹<?= number_format($cpn['discount_value']) ?> Off
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Conditions -->
                        <td class="py-3.5 px-4 text-[11px] text-gray-600">
                            <div>
                                <span class="text-gray-400">Min Order:</span> 
                                <strong class="text-gray-900">₹<?= number_format($cpn['min_order_amount']) ?></strong>
                            </div>
                            <div>
                                <span class="text-gray-400">Per Customer:</span> 
                                <strong class="text-gray-900"><?= $cpn['max_uses_per_customer'] ? $cpn['max_uses_per_customer'] . 'x' : 'Unlimited' ?></strong>
                            </div>
                        </td>

                        <!-- Validity -->
                        <td class="py-3.5 px-4 text-[11px] text-gray-500 whitespace-nowrap">
                            <?php if ($isExpired): ?>
                                <span class="text-red-600 font-bold block">Expired</span>
                                <span class="text-[10px]"><?= date('d M Y', strtotime($cpn['expiry_date'])) ?></span>
                            <?php elseif ($isNotStarted): ?>
                                <span class="text-amber-600 font-bold block">Upcoming</span>
                                <span class="text-[10px]">Starts <?= date('d M Y', strtotime($cpn['start_date'])) ?></span>
                            <?php elseif (!empty($cpn['expiry_date'])): ?>
                                <span class="text-emerald-700 font-semibold block">Valid until</span>
                                <span class="text-[10px] text-gray-600"><?= date('d M Y', strtotime($cpn['expiry_date'])) ?></span>
                            <?php else: ?>
                                <span class="text-gray-600 font-medium">No Expiry</span>
                            <?php endif; ?>
                        </td>

                        <!-- Redemptions / Uses -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <strong class="text-gray-900"><?= (int)$cpn['current_uses'] ?></strong>
                                    <span class="text-gray-400">/ <?= $cpn['max_uses'] ? (int)$cpn['max_uses'] : '∞' ?></span>
                                </div>
                                <?php if (!empty($cpn['max_uses']) && (int)$cpn['max_uses'] > 0): 
                                    $pct = min(100, round(((int)$cpn['current_uses'] / (int)$cpn['max_uses']) * 100));
                                ?>
                                <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-brand-600 h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                                </div>
                                <?php endif; ?>
                                <span class="text-[10px] text-gray-400 block">Saved: ₹<?= number_format($cpn['total_discount_given'] ?? 0) ?></span>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <form action="/admin/coupons/toggle-status" method="POST" class="inline">
                                <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                                <input type="hidden" name="coupon_id" value="<?= $cpn['id'] ?>">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold transition <?= $cpn['is_active'] ? 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>" title="Click to toggle status">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $cpn['is_active'] ? 'bg-emerald-500' : 'bg-gray-400' ?>"></span>
                                    <span><?= $cpn['is_active'] ? 'Active' : 'Disabled' ?></span>
                                </button>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <a href="/admin/coupons/view?id=<?= $cpn['id'] ?>" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-gray-100 rounded-lg transition" title="View Usage & Orders">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="/admin/coupons/edit?id=<?= $cpn['id'] ?>" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-gray-100 rounded-lg transition" title="Edit Coupon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="/admin/coupons/delete" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete or archive this coupon?');">
                                    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="coupon_id" value="<?= $cpn['id'] ?>">
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Coupon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
