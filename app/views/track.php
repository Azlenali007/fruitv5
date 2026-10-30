<?php
/**
 * Track Order View
 * N.A Fresh Fruits & Coconuts
 */
$pageTitle = 'Track Order - N.A Fresh Fruits & Coconuts';
$activePage = 'track';
require_once __DIR__ . '/layouts/header.php';

$searched = !empty($_GET['num']) || !empty($_GET['phone']);
$order = $foundOrder ?? null;
?>

<div class="max-w-xl mx-auto px-4 py-6 sm:py-10">
    
    <div class="text-center mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
            Track Your Order
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-sm mx-auto">
            Check the live preparation and delivery status of your fresh tender coconut water.
        </p>
    </div>

    <!-- Search Form -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs mb-8" x-data="{ tab: 'number' }">
        <div class="flex border-b border-gray-100 pb-3 mb-4 gap-4 text-xs font-bold">
            <button type="button" @click="tab = 'number'" :class="tab === 'number' ? 'text-brand-600 border-b-2 border-brand-600 pb-1' : 'text-gray-400 hover:text-gray-600'">
                By Order Number
            </button>
            <button type="button" @click="tab = 'phone'" :class="tab === 'phone' ? 'text-brand-600 border-b-2 border-brand-600 pb-1' : 'text-gray-400 hover:text-gray-600'">
                By Phone Number
            </button>
        </div>

        <form action="/track" method="GET" class="space-y-4">
            <div x-show="tab === 'number'">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Order Number (e.g. NA1024)</label>
                <input 
                    type="text" 
                    name="num" 
                    value="<?= e($_GET['num'] ?? '') ?>" 
                    placeholder="Enter order number (e.g. NA1024)" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                >
            </div>

            <div x-show="tab === 'phone'" x-cloak>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Your Mobile Number</label>
                <input 
                    type="tel" 
                    name="phone" 
                    value="<?= e($_GET['phone'] ?? '') ?>" 
                    placeholder="Enter 10-digit mobile number" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                >
            </div>

            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm py-3 px-4 rounded-xl shadow-xs transition">
                Find Order
            </button>
        </form>
    </div>

    <!-- Order Result Display -->
    <?php if ($searched && $order): ?>
    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-6">
        
        <!-- Status Header -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-4 border-b border-gray-100 gap-2">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase">Order ID</span>
                <h2 class="text-xl font-extrabold text-gray-900">#<?= e($order['order_number']) ?></h2>
            </div>
            <div>
                <?php
                $statusColors = [
                    'pending' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
                    'confirmed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                    'delivered' => 'bg-blue-50 text-blue-800 border-blue-200',
                    'cancelled' => 'bg-red-50 text-red-800 border-red-200',
                ];
                $color = $statusColors[$order['order_status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase border <?= $color ?>">
                    <?= e(ucfirst($order['order_status'])) ?>
                </span>
            </div>
        </div>

        <!-- Visual Timeline / Progress Bar -->
        <?php if ($order['order_status'] !== 'cancelled'): ?>
        <?php
        $steps = ['pending', 'confirmed', 'delivered'];
        $currentIdx = array_search($order['order_status'], $steps);
        if ($currentIdx === false) $currentIdx = 1;
        ?>
        <div class="py-2">
            <div class="flex justify-between items-center relative">
                <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-1 bg-gray-200 -z-0">
                    <div class="h-full bg-brand-600 transition-all duration-500" style="width: <?= $currentIdx === 0 ? '0%' : ($currentIdx === 1 ? '50%' : '100%') ?>;"></div>
                </div>

                <!-- Step 1: Placed -->
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs <?= $currentIdx >= 0 ? 'bg-brand-600 text-white ring-4 ring-emerald-50' : 'bg-gray-200 text-gray-500' ?>">
                        ✓
                    </div>
                    <span class="text-[11px] font-semibold text-gray-700 mt-2">Placed</span>
                </div>

                <!-- Step 2: Confirmed / Preparing -->
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs <?= $currentIdx >= 1 ? 'bg-brand-600 text-white ring-4 ring-emerald-50' : 'bg-gray-200 text-gray-500' ?>">
                        <?= $currentIdx >= 1 ? '✓' : '2' ?>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-700 mt-2">Confirmed</span>
                </div>

                <!-- Step 3: Delivered -->
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs <?= $currentIdx >= 2 ? 'bg-brand-600 text-white ring-4 ring-emerald-50' : 'bg-gray-200 text-gray-500' ?>">
                        <?= $currentIdx >= 2 ? '✓' : '3' ?>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-700 mt-2">Delivered</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Order Information Details -->
        <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 p-4 rounded-2xl">
            <div>
                <span class="text-gray-400 font-semibold block">Estimated Fulfilment</span>
                <span class="font-bold text-gray-800"><?= e($order['estimated_delivery'] ?? 'Today') ?></span>
            </div>
            <div>
                <span class="text-gray-400 font-semibold block">Payment Method</span>
                <span class="font-bold text-gray-800 uppercase"><?= e($order['payment_method']) ?> (<?= e(ucfirst($order['payment_status'])) ?>)</span>
            </div>
            <div class="col-span-2">
                <span class="text-gray-400 font-semibold block">Delivery Address / Fulfilment</span>
                <span class="font-medium text-gray-700"><?= e($order['delivery_address'] ?: 'Shop Pickup at N.A Store') ?></span>
            </div>
        </div>

        <!-- Items Snapshot -->
        <div class="space-y-2 text-xs">
            <span class="font-bold text-gray-400 uppercase text-[11px]">Items Snapshot</span>
            <?php foreach ($order['items'] as $item): ?>
            <div class="flex justify-between items-center text-gray-700 border-b border-gray-50 pb-2">
                <span><?= (int)$item['quantity'] ?>x <?= e($item['product_name']) ?> (₹<?= number_format($item['unit_price']) ?>/pc)</span>
                <span class="font-bold text-gray-900">₹<?= number_format($item['subtotal']) ?></span>
            </div>
            <?php endforeach; ?>
            <div class="flex justify-between items-center text-sm font-extrabold text-gray-950 pt-2">
                <span>Total Amount</span>
                <span class="text-brand-700 text-base">₹<?= number_format($order['total_amount']) ?></span>
            </div>
        </div>

        <!-- Status Change History Logs -->
        <?php if (!empty($order['history'])): ?>
        <div class="border-t border-gray-100 pt-4 space-y-2 text-xs">
            <span class="font-bold text-gray-400 uppercase text-[11px]">Status Updates</span>
            <ul class="space-y-2 text-gray-600">
                <?php foreach ($order['history'] as $h): ?>
                <li class="flex items-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 mt-1.5 shrink-0"></span>
                    <div>
                        <span class="font-bold text-gray-800 uppercase text-[10px]"><?= e($h['new_status']) ?>:</span>
                        <span><?= e($h['comment']) ?></span>
                        <span class="text-gray-400 text-[10px] block"><?= e($h['created_at']) ?></span>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

    </div>
    <?php elseif ($searched): ?>
    <!-- Order Not Found -->
    <div class="bg-white rounded-2xl p-6 border border-red-100 text-center space-y-2">
        <p class="text-sm font-bold text-red-700">No matching order found</p>
        <p class="text-xs text-gray-500">Please double check your order number or phone number and try again.</p>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
