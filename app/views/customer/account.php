<?php
/**
 * Customer Account & Profile Dashboard
 * N.A Fresh Fruits & Coconuts
 */
$pageTitle = 'My Account - N.A Fresh Fruits & Coconuts';
$activePage = 'profile';
require_once __DIR__ . '/../layouts/header.php';

$customer = CustomerAuthService::user();
$customerId = (int)$customer['id'];
$orders = Order::getByCustomerId($customerId);
$totalOrders = count($orders);
$totalSpent = 0;
foreach ($orders as $o) {
    if ($o['order_status'] !== 'cancelled') {
        $totalSpent += (float)$o['total_amount'];
    }
}
?>

<div class="max-w-4xl mx-auto px-4 py-8 sm:py-10 space-y-8">
    <!-- Top Welcome Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col sm:flex-row justify-between sm:items-center gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-2xl border border-brand-100/80 shrink-0">
                <?= strtoupper(substr($customer['name'], 0, 1)) ?>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900"><?= e($customer['name']) ?></h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Active Account
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                    <?= e($customer['phone']) ?> <?= !empty($customer['email']) ? '• ' . e($customer['email']) : '' ?>
                </p>
                <span class="text-[11px] text-gray-400 block mt-1">Customer since <?= date('M Y', strtotime($customer['created_at'])) ?></span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="/shop" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl transition inline-flex items-center gap-1.5 shadow-xs">
                <span>Order Coconut Water</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
            <form action="/logout" method="POST" class="inline">
                <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs py-2.5 px-4 rounded-xl transition">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total Orders</span>
            <p class="text-2xl sm:text-3xl font-black text-gray-950"><?= $totalOrders ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total Spent</span>
            <p class="text-2xl sm:text-3xl font-black text-brand-600">₹<?= number_format($totalSpent, 2) ?></p>
        </div>
        <div class="col-span-2 sm:col-span-1 bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Default Locality</span>
            <p class="text-sm font-bold text-gray-800 truncate"><?= e($customer['city'] ?? 'Bhopal') ?>, <?= e($customer['state'] ?? 'MP') ?></p>
        </div>
    </div>

    <!-- Grid: Profile Settings & Recent Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Editable Profile Info -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-7 border border-gray-100 shadow-sm space-y-5">
            <div class="border-b border-gray-100 pb-3 flex justify-between items-center">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Account Details</h2>
                <span class="text-[10px] text-gray-400">Update anytime</span>
            </div>

            <form action="/account/profile" method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Full Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        value="<?= e($customer['name']) ?>"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs text-gray-900 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Mobile Number (Login ID)</label>
                    <input 
                        type="text" 
                        disabled 
                        value="<?= e($customer['phone']) ?>"
                        class="w-full bg-gray-100 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs text-gray-500 cursor-not-allowed"
                    >
                    <span class="text-[10px] text-gray-400 mt-0.5 block">Mobile number is your primary account identifier</span>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="<?= e($customer['email'] ?? '') ?>"
                        placeholder="your@email.com"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs text-gray-900 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Default Delivery Address</label>
                    <textarea 
                        name="address" 
                        rows="2"
                        placeholder="House/flat, building, street, locality..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs text-gray-900 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none"
                    ><?= e($customer['address'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">City</label>
                        <input 
                            type="text" 
                            name="city" 
                            value="<?= e($customer['city'] ?? 'Bhopal') ?>"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 focus:ring-2 focus:ring-brand-500 focus:outline-none"
                        >
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Pincode</label>
                        <input 
                            type="text" 
                            name="pincode" 
                            value="<?= e($customer['pincode'] ?? '462016') ?>"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 focus:ring-2 focus:ring-brand-500 focus:outline-none"
                        >
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-4 rounded-xl transition text-xs shadow-xs">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Recent Orders List -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-7 border border-gray-100 shadow-sm space-y-5">
            <div class="border-b border-gray-100 pb-3 flex justify-between items-center">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">My Orders (<?= $totalOrders ?>)</h2>
                <a href="/my-orders" class="text-xs font-bold text-brand-600 hover:underline">View All Orders &rarr;</a>
            </div>

            <?php if (empty($orders)): ?>
            <div class="py-12 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 text-brand-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <p class="text-sm font-bold text-gray-800">No Orders Placed Yet</p>
                <p class="text-xs text-gray-400 max-w-xs mx-auto">Get pure tender coconut water delivered to your doorstep today!</p>
                <div class="pt-2">
                    <a href="/shop" class="bg-brand-600 text-white text-xs font-bold py-2 px-5 rounded-xl inline-block hover:bg-brand-700 transition">
                        Shop Now
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                <div class="py-4 space-y-2.5">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-gray-900 text-sm">Order #<?= e($order['order_number']) ?></span>
                            <span class="text-[11px] text-gray-400 block"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-gray-950 text-sm">₹<?= number_format($order['total_amount'], 2) ?></span>
                            <div>
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'confirmed' => 'bg-blue-50 text-blue-800 border-blue-200',
                                    'delivered' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'cancelled' => 'bg-red-50 text-red-800 border-red-200'
                                ];
                                $badgeClass = $statusColors[$order['order_status']] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                ?>
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border <?= $badgeClass ?>">
                                    <?= ucfirst($order['order_status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Item summary -->
                    <div class="bg-gray-50 rounded-xl p-2.5 text-xs text-gray-600 flex justify-between items-center">
                        <span>
                            <?php foreach ($order['items'] as $it): ?>
                                <?= e($it['product_name']) ?> &times; <?= (int)$it['quantity'] ?>
                            <?php endforeach; ?>
                        </span>
                        <a href="/track?h=<?= urlencode($order['tracking_hash']) ?>" class="text-brand-600 hover:text-brand-700 font-bold text-[11px] inline-flex items-center gap-1">
                            <span>Track</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
