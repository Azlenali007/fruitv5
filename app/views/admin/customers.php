<?php
/**
 * Admin Customer Management View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Customer Management - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'customers';
require_once __DIR__ . '/layout-header.php';

$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$totalCustomersCount = Customer::countAll($filters);
$customers = Customer::getAll($filters, $limit, $offset);
$totalPages = ceil($totalCustomersCount / $limit);
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Customer Management</h1>
            <p class="text-xs text-gray-500">View registered customers, account details, and purchasing history</p>
        </div>
        <div class="text-xs text-gray-500 bg-white px-3.5 py-1.5 rounded-xl border border-gray-100 shadow-xs">
            Total Customers: <span class="font-bold text-gray-900"><?= $totalCustomersCount ?></span>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs">
        <form action="/admin/customers" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end text-xs">
            <!-- Search bar -->
            <div class="sm:col-span-8">
                <label class="block font-bold text-gray-600 uppercase tracking-wider text-[10px] mb-1">Search Customer</label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="<?= e($filters['search']) ?>"
                        placeholder="Search by name, mobile number, or email..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"></path>
                    </svg>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <label class="block font-bold text-gray-600 uppercase tracking-wider text-[10px] mb-1">Status</label>
                <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-3 rounded-xl transition text-xs shadow-xs">
                    Filter
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
                <a href="/admin/customers" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold py-2.5 px-3 rounded-xl transition text-xs">
                    Reset
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Customer</th>
                        <th class="py-3.5 px-4">Phone Number</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4 text-center">Orders</th>
                        <th class="py-3.5 px-4 text-right">Total Spent</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Registered</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-400">
                            No registered customers match your search criteria.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr class="hover:bg-gray-50/50 transition">
                        <!-- Customer Name -->
                        <td class="py-3.5 px-4 sm:px-6">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0">
                                    <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900 block"><?= e($c['name']) ?></span>
                                    <span class="text-[10px] text-gray-400">ID: #<?= (int)$c['id'] ?></span>
                                </div>
                            </div>
                        </td>

                        <!-- Phone -->
                        <td class="py-3.5 px-4 text-gray-800 font-semibold font-mono">
                            <?= e($c['phone']) ?>
                        </td>

                        <!-- Email -->
                        <td class="py-3.5 px-4 text-gray-600">
                            <?= !empty($c['email']) ? e($c['email']) : '<span class="text-gray-300 italic">None</span>' ?>
                        </td>

                        <!-- Orders Count -->
                        <td class="py-3.5 px-4 text-center font-bold text-gray-900">
                            <span class="inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-[11px]">
                                <?= (int)$c['total_orders'] ?>
                            </span>
                        </td>

                        <!-- Total Spent -->
                        <td class="py-3.5 px-4 text-right font-black text-gray-950">
                            ₹<?= number_format((float)$c['total_spent'], 2) ?>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3.5 px-4">
                            <?php if ($c['status'] === 'active'): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                    Active
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 uppercase">
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Registered Date -->
                        <td class="py-3.5 px-4 text-gray-500 text-[11px]">
                            <?= date('d M Y', strtotime($c['created_at'])) ?>
                        </td>

                        <!-- Action -->
                        <td class="py-3.5 px-4 text-right">
                            <a href="/admin/customers/view?id=<?= $c['id'] ?>" class="text-brand-600 hover:text-brand-800 font-bold hover:underline inline-flex items-center gap-1">
                                <span>Details</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                            </a>
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
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <div class="flex gap-1.5">
                <?php if ($page > 1): ?>
                <a href="/admin/customers?page=<?= $page - 1 ?>&search=<?= urlencode($filters['search']) ?>&status=<?= urlencode($filters['status']) ?>" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold">
                    &larr; Prev
                </a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                <a href="/admin/customers?page=<?= $page + 1 ?>&search=<?= urlencode($filters['search']) ?>&status=<?= urlencode($filters['status']) ?>" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold">
                    Next &rarr;
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
