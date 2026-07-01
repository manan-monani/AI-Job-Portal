<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { DollarSign, ShoppingBag, Truck, Package } from 'lucide-vue-next';
import { computed, markRaw } from 'vue';

const page = usePage();

// Helper to access nested branding config safely (fallback to admin if customer specific not set)
const t = (section: string, key: string) => {
    return page.props.branding?.customer?.dashboard?.[section]?.[key] || page.props.branding?.admin?.dashboard?.[section]?.[key] || key;
}

const widgets = computed(() => [
    {
        key: 'total_orders',
        icon: markRaw(ShoppingBag),
        bgClass: 'bg-blue-600 shadow-blue-900/20',
        changeColor: 'emerald',
        value: '12',
        title: 'Total Orders'
    },
    {
        key: 'pending_orders',
        icon: markRaw(Package),
        bgClass: 'bg-amber-600 shadow-amber-900/20',
        changeColor: 'emerald',
        value: '2',
        title: 'Pending'
    },
    {
        key: 'delivered',
        icon: markRaw(Truck),
        bgClass: 'bg-emerald-600 shadow-emerald-900/20',
        changeColor: 'emerald',
        value: '10',
        title: 'Delivered'
    },
    {
        key: 'spent',
        icon: markRaw(DollarSign),
        bgClass: 'bg-indigo-600 shadow-indigo-900/20',
        changeColor: 'emerald',
        value: '$1,240',
        title: 'Total Spent'
    },
]);
</script>

<template>
    <CustomerLayout>
        
        <!-- DASHBOARD (CUSTOMER PANEL) -->
        <div class="space-y-6 animate-fade-in">
            
            <!-- Welcome Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-start">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __(t('overview', 'welcome_back')) }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __(t('overview', 'subtitle') || 'Here is what is happening with your account.') }}</p>
                </div>
                <button class="bg-[var(--brand-primary)] text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-lg hover:brightness-110 transition-all">
                    {{ __('New Order') }}
                </button>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div 
                    v-for="widget in widgets" 
                    :key="widget.key"
                    class="bg-admin-card dark:bg-admin-card-dark p-5 rounded-2xl border border-admin-card-border dark:border-admin-card-border-dark shadow-sm hover:shadow-md transition-all text-start"
                >
                    <div class="flex justify-between items-start mb-3">
                        <div :class="`p-2 rounded-xl shadow-lg ${widget.bgClass} text-white`">
                            <component :is="widget.icon" :size="20" />
                        </div>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">{{ __(widget.title) }}</h3>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ widget.value }}</p>
                </div>
            </div>

            <!-- Recent Orders Table (Placeholder) -->
            <div class="bg-admin-card dark:bg-admin-card-dark rounded-[2rem] border border-admin-card-border dark:border-admin-card-border-dark shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-50 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white text-start">{{ __('Recent Orders') }}</h3>
                    <button class="text-xs font-bold text-[var(--brand-primary)] hover:underline uppercase tracking-widest">{{ __('View All') }}</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-start">
                        <thead class="bg-slate-50/50 dark:bg-slate-800/30">
                            <tr>
                                <th class="py-3 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Order ID') }}</th>
                                <th class="py-3 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Status') }}</th>
                                <th class="py-3 px-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-3 px-6 text-sm font-bold text-slate-900 dark:text-white">
                                    #ORD-001
                                </td>
                                <td class="py-3 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400">{{ __('Completed') }}</span>
                                </td>
                                <td class="py-3 px-6 text-end font-bold text-slate-900 dark:text-white">$120.00</td>
                            </tr>
                             <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-3 px-6 text-sm font-bold text-slate-900 dark:text-white">
                                    #ORD-002
                                </td>
                                <td class="py-3 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400">{{ __('Processing') }}</span>
                                </td>
                                <td class="py-3 px-6 text-end font-bold text-slate-900 dark:text-white">$250.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </CustomerLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
