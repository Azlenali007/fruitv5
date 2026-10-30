<?php
/**
 * WhatsApp Channel Popup Component
 * N.A Fresh Fruits & Coconuts
 * Non-intrusive, frequency-controlled storefront popup to invite visitors to the store's official WhatsApp channel.
 */
$waEnabled = Setting::get('whatsapp_popup_enabled', '1') === '1';
$waUrl = trim(Setting::get('whatsapp_channel_url', 'https://whatsapp.com/channel/0029VaNAFreshFruits'));
$waHeading = Setting::get('whatsapp_popup_heading', 'Join Our WhatsApp Channel');
$waMessage = Setting::get('whatsapp_popup_message', 'Get real-time fresh coconut harvest alerts, special seasonal discounts, and quick delivery updates straight on WhatsApp!');
$waBtnText = Setting::get('whatsapp_popup_button_text', 'Join WhatsApp Channel');
$waFrequency = Setting::get('whatsapp_popup_frequency', 'session');

if (!$waEnabled || empty($waUrl)) {
    return;
}
?>

<div x-data="{
    open: false,
    frequency: '<?= e($waFrequency) ?>',
    init() {
        const now = Date.now();
        let shouldShow = false;

        if (this.frequency === 'always') {
            shouldShow = true;
        } else if (this.frequency === 'session') {
            shouldShow = !sessionStorage.getItem('na_wa_channel_seen');
        } else if (this.frequency === 'daily') {
            const lastSeen = parseInt(localStorage.getItem('na_wa_channel_seen_daily') || '0', 10);
            const oneDay = 24 * 60 * 60 * 1000;
            shouldShow = (now - lastSeen) > oneDay;
        } else if (this.frequency === 'weekly') {
            const lastSeen = parseInt(localStorage.getItem('na_wa_channel_seen_weekly') || '0', 10);
            const sevenDays = 7 * 24 * 60 * 60 * 1000;
            shouldShow = (now - lastSeen) > sevenDays;
        }

        if (shouldShow) {
            // Friendly 2.2s delay so page finishes loading and user is not shocked
            setTimeout(() => {
                this.open = true;
            }, 2200);
        }
    },
    markSeen() {
        const now = Date.now();
        if (this.frequency === 'session') {
            sessionStorage.setItem('na_wa_channel_seen', 'true');
        } else if (this.frequency === 'daily') {
            localStorage.setItem('na_wa_channel_seen_daily', now.toString());
        } else if (this.frequency === 'weekly') {
            localStorage.setItem('na_wa_channel_seen_weekly', now.toString());
        }
    },
    closePopup() {
        this.open = false;
        this.markSeen();
    },
    onJoinClick() {
        this.markSeen();
        this.open = false;
    }
}" 
x-cloak
@keydown.escape.window="closePopup()">

    <!-- Backdrop -->
    <div 
        x-show="open" 
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 sm:p-6"
        @click="closePopup()"
    >
        <!-- Modal Card Container -->
        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-gray-100 overflow-hidden relative"
            @click.stop
        >
            <!-- Close Button -->
            <button 
                type="button" 
                @click="closePopup()" 
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 p-1.5 rounded-full transition z-10"
                aria-label="Close popup"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Card Header with WhatsApp Green Background Banner -->
            <div class="bg-gradient-to-br from-[#25D366] to-[#128C7E] px-6 pt-7 pb-6 text-white text-center relative overflow-hidden">
                <!-- Soft background pattern circles -->
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-sm"></div>
                <div class="absolute -bottom-8 -left-8 w-24 h-24 bg-white/10 rounded-full blur-sm"></div>

                <!-- WhatsApp Official Icon Badge -->
                <div class="w-16 h-16 mx-auto rounded-2xl bg-white text-[#25D366] flex items-center justify-center shadow-lg transform -rotate-3 hover:rotate-0 transition duration-300">
                    <svg class="w-10 h-10 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-10.416c-5.524 0-10 4.477-10 10 0 1.956.565 3.78 1.547 5.325l-1.642 5.999 6.179-1.62c1.493.864 3.228 1.365 5.078 1.365 5.523 0 10-4.477 10-10 0-5.522-4.477-10-10-10z"/>
                    </svg>
                </div>

                <!-- Verified Channel Pill -->
                <div class="mt-3 inline-flex items-center gap-1 bg-white/20 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide">
                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span>Official WhatsApp Channel</span>
                </div>

                <h3 class="text-lg font-black mt-2 tracking-tight">
                    <?= e($waHeading) ?>
                </h3>
            </div>

            <!-- Card Content Body -->
            <div class="p-6 text-center space-y-4">
                <p class="text-xs text-gray-600 leading-relaxed">
                    <?= e($waMessage) ?>
                </p>

                <!-- Value Highlights -->
                <div class="grid grid-cols-2 gap-2 text-left bg-emerald-50/60 p-3 rounded-2xl border border-emerald-100 text-[11px]">
                    <div class="flex items-center gap-1.5 font-semibold text-emerald-900">
                        <span class="text-emerald-600">✓</span> Fresh harvest alerts
                    </div>
                    <div class="flex items-center gap-1.5 font-semibold text-emerald-900">
                        <span class="text-emerald-600">✓</span> Exclusive coupons
                    </div>
                    <div class="flex items-center gap-1.5 font-semibold text-emerald-900">
                        <span class="text-emerald-600">✓</span> Zero spam
                    </div>
                    <div class="flex items-center gap-1.5 font-semibold text-emerald-900">
                        <span class="text-emerald-600">✓</span> 100% Free to join
                    </div>
                </div>

                <!-- Main Join Button -->
                <div class="space-y-2 pt-1">
                    <a 
                        href="<?= e($waUrl) ?>" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        @click="onJoinClick()"
                        class="w-full bg-[#25D366] hover:bg-[#1fb355] text-white font-extrabold text-sm py-3 px-6 rounded-2xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition transform active:scale-98"
                    >
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-10.416c-5.524 0-10 4.477-10 10 0 1.956.565 3.78 1.547 5.325l-1.642 5.999 6.179-1.62c1.493.864 3.228 1.365 5.078 1.365 5.523 0 10-4.477 10-10 0-5.522-4.477-10-10-10z"/></svg>
                        <span><?= e($waBtnText) ?></span>
                    </a>

                    <button 
                        type="button" 
                        @click="closePopup()" 
                        class="text-xs font-semibold text-gray-400 hover:text-gray-600 py-1 transition"
                    >
                        Maybe later
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>
