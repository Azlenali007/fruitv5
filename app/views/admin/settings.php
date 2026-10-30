<?php
/**
 * Admin Delivery & Business Settings View
 * N.A Fresh Fruits & Coconuts
 */
$adminTitle = 'Delivery Settings - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'settings';
require_once __DIR__ . '/layout-header.php';

$settings = Setting::getAll();
?>

<div class="max-w-3xl mx-auto space-y-6">
    
    <div>
        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Delivery & Store Settings</h1>
        <p class="text-xs text-gray-500">Configure local delivery parameters, pickup options, delivery fees, and service areas</p>
    </div>

    <form action="/admin/settings/update" method="POST" class="space-y-6">
        <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">

        <!-- Fulfilment Methods Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Fulfilment Method</h2>
            
            <div class="grid grid-cols-1 gap-4">
                <label class="flex items-start gap-3 p-4 bg-gray-50 border border-gray-200 rounded-2xl cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="local_delivery_enabled" 
                        value="1" 
                        <?= ($settings['local_delivery_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                        class="w-4 h-4 text-brand-600 rounded-sm focus:ring-brand-500 mt-0.5"
                    >
                    <div>
                        <span class="font-bold text-xs text-gray-900 block">Enable Home Doorstep Delivery</span>
                        <span class="text-[11px] text-gray-500">Allow customers to order for fresh tender coconut doorstep delivery.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Charges & Minimum Order Thresholds -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Delivery Charges & Minimum Order</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Standard Delivery Fee (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 font-bold">₹</span>
                        <input 
                            type="number" 
                            step="1" 
                            name="delivery_charge" 
                            value="<?= (int)($settings['delivery_charge'] ?? 40) ?>" 
                            required 
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-7 pr-3 py-2.5 font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Default charge applied at checkout</span>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Minimum Order Amount (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 font-bold">₹</span>
                        <input 
                            type="number" 
                            step="1" 
                            name="min_order_amount" 
                            value="<?= (int)($settings['min_order_amount'] ?? 40) ?>" 
                            required 
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-7 pr-3 py-2.5 font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Minimum subtotal required to place delivery</span>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Free Delivery Above (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 font-bold">₹</span>
                        <input 
                            type="number" 
                            step="1" 
                            name="free_delivery_above" 
                            value="<?= (int)($settings['free_delivery_above'] ?? 500) ?>" 
                            required 
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-7 pr-3 py-2.5 font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Zero delivery fee when subtotal reaches this</span>
                </div>

            </div>

            <!-- Service Areas -->
            <div class="text-xs">
                <label class="block font-bold text-gray-700 uppercase mb-1">Supported Service Areas</label>
                <input 
                    type="text" 
                    name="service_areas" 
                    value="<?= e($settings['service_areas'] ?? 'Bhopal, MP Nagar, Arera Colony, Kolar Road, Green Park') ?>" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-brand-600 focus:outline-none"
                >
                <span class="text-[10px] text-gray-400 mt-1 block">Comma-separated list of localities served</span>
            </div>

        </div>

        <!-- Store Information -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Business & Contact Info</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Store Name</label>
                    <input 
                        type="text" 
                        name="store_name" 
                        value="<?= e($settings['store_name'] ?? 'N.A Fresh Fruits & Coconuts') ?>" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Contact Phone</label>
                    <input 
                        type="text" 
                        name="store_phone" 
                        value="<?= e($settings['store_phone'] ?? '+91 98765 43210') ?>" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Store Address</label>
                    <input 
                        type="text" 
                        name="store_address" 
                        value="<?= e($settings['store_address'] ?? '123, Green Park, Near Main Road, Bhopal, Madhya Pradesh - 462016') ?>" 
                        required 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Daily Opening Time</label>
                    <input 
                        type="text" 
                        name="store_open_time" 
                        value="<?= e($settings['store_open_time'] ?? '07:00 AM') ?>" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Daily Closing Time</label>
                    <input 
                        type="text" 
                        name="store_close_time" 
                        value="<?= e($settings['store_close_time'] ?? '09:00 PM') ?>" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"
                    >
                </div>
            </div>
        </div>

        <!-- WhatsApp Channel Popup Settings Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5" x-data="{
            previewOpen: false,
            heading: '<?= e($settings['whatsapp_popup_heading'] ?? 'Join Our WhatsApp Channel') ?>',
            message: '<?= e($settings['whatsapp_popup_message'] ?? 'Get real-time fresh coconut harvest alerts, special seasonal discounts, and quick delivery updates straight on WhatsApp!') ?>',
            btnText: '<?= e($settings['whatsapp_popup_button_text'] ?? 'Join WhatsApp Channel') ?>'
        }">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#25D366]"></span>
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">WhatsApp Channel Popup Settings</h2>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-0.5">Control the promotional modal inviting storefront visitors to your official WhatsApp channel</p>
                </div>
                <button 
                    type="button" 
                    @click="previewOpen = true" 
                    class="inline-flex items-center gap-1.5 bg-[#25D366]/10 text-[#128C7E] hover:bg-[#25D366]/20 font-bold text-xs py-2 px-3.5 rounded-xl transition"
                >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-10.416c-5.524 0-10 4.477-10 10 0 1.956.565 3.78 1.547 5.325l-1.642 5.999 6.179-1.62c1.493.864 3.228 1.365 5.078 1.365 5.523 0 10-4.477 10-10 0-5.522-4.477-10-10-10z"/></svg>
                    <span>Test / Preview Popup</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                
                <!-- Toggle -->
                <div class="sm:col-span-2">
                    <label class="flex items-start gap-3 p-4 bg-emerald-50/50 border border-emerald-200/60 rounded-2xl cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="whatsapp_popup_enabled" 
                            value="1" 
                            <?= ($settings['whatsapp_popup_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                            class="w-4 h-4 text-[#25D366] rounded-sm focus:ring-[#25D366] mt-0.5"
                        >
                        <div>
                            <span class="font-bold text-xs text-gray-900 block">Enable WhatsApp Channel Popup</span>
                            <span class="text-[11px] text-gray-600">Displays a floating invitation modal for visitors to join your official WhatsApp channel.</span>
                        </div>
                    </label>
                </div>

                <!-- Channel URL -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">
                        Official WhatsApp Channel Link <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 font-mono text-[11px]">🔗</span>
                        <input 
                            type="url" 
                            name="whatsapp_channel_url" 
                            value="<?= e($settings['whatsapp_channel_url'] ?? 'https://whatsapp.com/channel/0029VaNAFreshFruits') ?>" 
                            placeholder="https://whatsapp.com/channel/..." 
                            required 
                            pattern="https://.*"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-2.5 text-xs font-mono font-medium focus:ring-2 focus:ring-[#25D366] focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Must be a secure HTTPS link provided by WhatsApp (e.g. https://whatsapp.com/channel/...)</span>
                </div>

                <!-- Popup Heading -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Popup Heading</label>
                    <input 
                        type="text" 
                        name="whatsapp_popup_heading" 
                        x-model="heading"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold focus:ring-2 focus:ring-[#25D366] focus:outline-none"
                    >
                </div>

                <!-- Button Text -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Join Button Label</label>
                    <input 
                        type="text" 
                        name="whatsapp_popup_button_text" 
                        x-model="btnText"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold focus:ring-2 focus:ring-[#25D366] focus:outline-none"
                    >
                </div>

                <!-- Message Body -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Invitation Message</label>
                    <textarea 
                        name="whatsapp_popup_message" 
                        x-model="message"
                        rows="2" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs leading-relaxed focus:ring-2 focus:ring-[#25D366] focus:outline-none"
                    ></textarea>
                </div>

                <!-- Frequency Dropdown -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Display Frequency / Persistence</label>
                    <select 
                        name="whatsapp_popup_frequency" 
                        class="w-full sm:w-72 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold focus:ring-2 focus:ring-[#25D366] focus:outline-none"
                    >
                        <?php $currFreq = $settings['whatsapp_popup_frequency'] ?? 'session'; ?>
                        <option value="session" <?= $currFreq === 'session' ? 'selected' : '' ?>>Once per browser session (Recommended)</option>
                        <option value="daily" <?= $currFreq === 'daily' ? 'selected' : '' ?>>Once per day (24-hour cooldown)</option>
                        <option value="weekly" <?= $currFreq === 'weekly' ? 'selected' : '' ?>>Once per week (7-day cooldown)</option>
                        <option value="always" <?= $currFreq === 'always' ? 'selected' : '' ?>>Always on every visit</option>
                    </select>
                    <span class="text-[10px] text-gray-400 mt-1 block">Uses browser storage to prevent annoying returning customers on every page navigation</span>
                </div>

            </div>

            <!-- Interactive Live Modal Preview for Admin -->
            <div 
                x-show="previewOpen" 
                x-cloak 
                class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4"
                @click="previewOpen = false"
                @keydown.escape.window="previewOpen = false"
            >
                <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-gray-100 overflow-hidden relative" @click.stop>
                    <button type="button" @click="previewOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 p-1.5 rounded-full transition z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="bg-gradient-to-br from-[#25D366] to-[#128C7E] px-6 pt-7 pb-6 text-white text-center relative overflow-hidden">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-white text-[#25D366] flex items-center justify-center shadow-lg transform -rotate-3">
                            <svg class="w-10 h-10 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-10.416c-5.524 0-10 4.477-10 10 0 1.956.565 3.78 1.547 5.325l-1.642 5.999 6.179-1.62c1.493.864 3.228 1.365 5.078 1.365 5.523 0 10-4.477 10-10 0-5.522-4.477-10-10-10z"/></svg>
                        </div>
                        <div class="mt-3 inline-flex items-center gap-1 bg-white/20 px-2.5 py-0.5 rounded-full text-[11px] font-bold">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span>Official WhatsApp Channel</span>
                        </div>
                        <h3 class="text-lg font-black mt-2 tracking-tight" x-text="heading"></h3>
                    </div>
                    <div class="p-6 text-center space-y-4">
                        <p class="text-xs text-gray-600 leading-relaxed" x-text="message"></p>
                        <div class="grid grid-cols-2 gap-2 text-left bg-emerald-50/60 p-3 rounded-2xl border border-emerald-100 text-[11px]">
                            <div class="font-semibold text-emerald-900"><span class="text-emerald-600">✓</span> Fresh harvest alerts</div>
                            <div class="font-semibold text-emerald-900"><span class="text-emerald-600">✓</span> Exclusive coupons</div>
                            <div class="font-semibold text-emerald-900"><span class="text-emerald-600">✓</span> Zero spam</div>
                            <div class="font-semibold text-emerald-900"><span class="text-emerald-600">✓</span> 100% Free to join</div>
                        </div>
                        <button type="button" @click="previewOpen = false" class="w-full bg-[#25D366] text-white font-extrabold text-sm py-3 px-6 rounded-2xl flex items-center justify-center gap-2 shadow-md">
                            <span x-text="btnText"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-xs transition">
                Save All Settings
            </button>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
