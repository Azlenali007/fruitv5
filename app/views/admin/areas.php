<?php
/**
 * Admin Delivery Areas Management View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Delivery Areas - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'areas';
require_once __DIR__ . '/layout-header.php';

$filterStatus = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');

$areas = DeliveryArea::getAll($filterStatus === 'all' ? null : $filterStatus, $searchQuery);
$totalCount = count($areas);
$activeCount = count(array_filter($areas, fn($a) => $a['status'] === 'active'));
$withBoundaryCount = count(array_filter($areas, fn($a) => !empty($a['boundary_geojson'])));
?>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Delivery Area Coverage & Boundaries</h1>
            <p class="text-xs text-gray-500">Configure delivery localities, per-area charges, and interactive polygon boundaries for Jaora (MP)</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="/admin/areas/create" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Delivery Area</span>
            </a>
        </div>
    </div>

    <!-- Diagnostic Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                📍
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase text-gray-400 tracking-wider">Total Areas</p>
                <p class="text-lg font-black text-gray-900"><?= $totalCount ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl <?= $activeCount > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' ?> flex items-center justify-center font-bold text-lg">
                ✅
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase text-gray-400 tracking-wider">Active For Delivery</p>
                <p class="text-lg font-black <?= $activeCount > 0 ? 'text-emerald-700' : 'text-gray-500' ?>"><?= $activeCount ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl <?= $withBoundaryCount > 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600' ?> flex items-center justify-center font-bold text-lg">
                🗺️
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase text-gray-400 tracking-wider">Map Boundaries Set</p>
                <p class="text-lg font-black <?= $withBoundaryCount > 0 ? 'text-indigo-700' : 'text-amber-700' ?>">
                    <?= $withBoundaryCount ?> / <?= $totalCount ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form action="/admin/areas" method="GET" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <input 
                type="text" 
                name="search" 
                value="<?= e($searchQuery) ?>" 
                placeholder="Search area by name..." 
                class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none w-full sm:w-64"
            >

            <select name="status" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active Only</option>
                <option value="inactive" <?= $filterStatus === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
            </select>

            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold py-2 px-3.5 rounded-xl transition">
                Filter
            </button>
            <?php if (!empty($searchQuery) || $filterStatus !== 'all'): ?>
            <a href="/admin/areas" class="text-xs text-gray-500 hover:text-gray-800 underline">Reset</a>
            <?php endif; ?>
        </form>

        <span class="text-xs font-semibold text-gray-400 self-end sm:self-auto">
            Showing <?= count($areas) ?> area(s)
        </span>
    </div>

    <!-- Table of Areas -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6">Locality / Area Name</th>
                        <th class="py-3.5 px-4">Delivery Fee</th>
                        <th class="py-3.5 px-4">Map Boundary</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Last Updated</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    <?php if (empty($areas)): ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-400">
                            <p class="font-bold text-sm text-gray-600">No delivery areas found</p>
                            <p class="text-xs mt-1">Try resetting your filter or add a new delivery locality.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($areas as $area): ?>
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="py-3.5 px-4 sm:px-6">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 text-sm"><?= e($area['name']) ?></span>
                                <span class="text-[10px] text-gray-400 font-mono">/<?= e($area['slug']) ?></span>
                            </div>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="font-bold text-gray-900">₹<?= number_format((float)$area['delivery_charge'], 2) ?></span>
                        </td>

                        <td class="py-3.5 px-4">
                            <?php if (!empty($area['boundary_geojson'])): ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200/60 px-2 py-0.5 rounded-full">
                                    <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                    Polygon Drawn
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                    No Boundary
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="py-3.5 px-4">
                            <form action="/admin/areas/toggle-status" method="POST" class="inline">
                                <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                                <input type="hidden" name="area_id" value="<?= $area['id'] ?>">
                                <button type="submit" class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full transition <?= $area['status'] === 'active' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>" title="Click to toggle active status">
                                    <span class="w-2 h-2 rounded-full <?= $area['status'] === 'active' ? 'bg-emerald-500' : 'bg-gray-400' ?>"></span>
                                    <span><?= strtoupper($area['status']) ?></span>
                                </button>
                            </form>
                        </td>

                        <td class="py-3.5 px-4 text-gray-400 text-[11px]">
                            <?= date('d M Y, h:i A', strtotime($area['updated_at'] ?? $area['created_at'])) ?>
                        </td>

                        <td class="py-3.5 px-4 sm:px-6 text-right space-x-1.5">
                            <a href="/admin/areas/edit?id=<?= $area['id'] ?>" class="inline-flex items-center gap-1 bg-gray-100 hover:bg-brand-50 hover:text-brand-700 text-gray-700 font-bold px-2.5 py-1.5 rounded-lg transition" title="Edit area & map boundary">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                <span>Edit Map</span>
                            </a>

                            <form action="/admin/areas/delete" method="POST" class="inline" onsubmit="return confirm('Delete this area? If orders exist, it will be safely deactivated.');">
                                <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                                <input type="hidden" name="area_id" value="<?= $area['id'] ?>">
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Delete or deactivate area">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
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
