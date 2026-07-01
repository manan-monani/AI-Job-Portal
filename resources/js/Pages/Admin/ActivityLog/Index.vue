<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { 
    Gauge, 
    Search, 
    ChevronRight, 
    ChevronLeft,
    Bolt,
    Bot,
    User as UserIcon,
    Info,
    Download
} from 'lucide-vue-next';
import { debounce } from 'lodash';
import admin from '@/routes/admin';

const props = defineProps<{
    logs: {
        data: any[];
        links: any[];
        total: number;
        from: number;
        to: number;
    };
    stats: {
        total_events: number;
        today_events: number;
        security_events: number;
        critical_alerts: number;
    };
    filters: {
        search?: string;
        event?: string;
        date_range?: string;
    };
}>();

const search = ref(props.filters.search || '');
const event = ref(props.filters.event || '');
const date_range = ref(props.filters.date_range || '');

const debouncedSearch = debounce(() => {
    router.get(admin.activity_logs.index.url(), {
        search: search.value,
        event: event.value,
        date_range: date_range.value
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

watch(search, debouncedSearch);

const applyFilters = () => {
    router.get(admin.activity_logs.index.url(), {
        search: search.value,
        event: event.value,
        date_range: date_range.value
    }, {
        preserveState: true,
        replace: true
    });
};

const getEventBadgeClass = (type: string) => {
    switch (type) {
        case 'created':
            return 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600';
        case 'updated':
            return 'bg-sky-100 dark:bg-sky-900/30 text-sky-600';
        case 'deleted':
            return 'bg-rose-100 dark:bg-rose-900/30 text-rose-600';
        case 'failed_login':
            return 'bg-amber-100 dark:bg-amber-900/30 text-amber-600';
        default:
            return 'bg-slate-100 dark:bg-slate-800 text-slate-600';
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const getRelativeTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + ' mins ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + ' hours ago';
    return date.toLocaleDateString();
};

const exportCsv = () => {
    window.location.href = admin.activity_logs.index.url({ query: { ...props.filters, export: 'csv' } });
};
</script>

<template>
    <Head :title="__('Activity Log')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-7xl mx-auto w-full p-4 lg:p-0">
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Activity Log') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Real-time infrastructure audit trail') }}</p>
                </div>
                <button 
                    @click="exportCsv"
                    class="bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-4 py-2 rounded-2xl font-bold text-sm shadow-xl hover:opacity-90 transition-all flex items-center gap-2"
                >
                    <Download :size="16" />
                    {{ __('Export CSV') }}
                </button>
            </div>

            <!-- Stats section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 p-5 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 rounded-2xl bg-sky-100 dark:bg-sky-900/30 text-sky-600 flex items-center justify-center">
                        <Bolt :size="24" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Events') }}</p>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white">{{ stats.total_events }}</h4>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-5 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center">
                        <Gauge :size="24" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Today Events') }}</p>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white">{{ stats.today_events }}</h4>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-white dark:bg-slate-900 p-5 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1 relative">
                        <Search :size="18" class="absolute start-5 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input 
                            v-model="search"
                            type="text" 
                            :placeholder="__('Search activity, users or specific IP addresses...')" 
                            class="w-full px-4 py-2 ps-12 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500/20 transition-all font-medium"
                        >
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <select 
                            v-model="event" 
                            @change="applyFilters"
                            class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500/20 transition-all text-sm"
                        >
                            <option value="">{{ __('All Types') }}</option>
                            <option value="created">Created</option>
                            <option value="updated">Updated</option>
                            <option value="deleted">Deleted</option>
                            <option value="failed_login">Security</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table section -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                                <th class="px-6 py-3 text-start text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('User / Subject') }}</th>
                                <th class="px-6 py-3 text-start text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Activity Description') }}</th>
                                <th class="px-6 py-3 text-start text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-start text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Time') }}</th>
                                <th class="px-6 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Details') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                            <component :is="log.user ? UserIcon : Bot" :size="20" />
                                        </div>
                                        <div class="text-start">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                                                {{ log.user ? log.user.name : 'System / Bot' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 font-medium tracking-tight">
                                                {{ log.ip_address }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-sm text-slate-600 dark:text-slate-300 font-medium max-w-md">
                                        {{ log.description }}
                                    </p>
                                </td>
                                <td class="px-6 py-3">
                                    <span 
                                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                        :class="getEventBadgeClass(log.event)"
                                    >
                                        {{ log.event.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ getRelativeTime(log.created_at) }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium">{{ formatDate(log.created_at) }}</p>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <Link 
                                        :href="admin.activity_logs.show.url(log.id)"
                                        class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-sky-500 transition-all flex items-center justify-center mx-auto"
                                    >
                                        <ChevronRight :size="14" class="rtl:rotate-180" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Info :size="40" class="text-slate-200 dark:text-slate-700" />
                                        <p class="text-slate-500 font-medium">{{ __('No activity logs found.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                <div class="p-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest uppercase">
                        {{ __('Showing') }} {{ logs.from || 0 }} {{ __('of') }} {{ logs.total }} {{ __('events') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <template v-for="(link, index) in logs.links" :key="index">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all shadow-sm"
                                :class="link.active 
                                    ? 'bg-sky-600 text-white shadow-sky-600/20' 
                                    : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'"
                            >
                                <ChevronLeft v-if="link.label.includes('Previous')" :size="16" />
                                <ChevronRight v-if="link.label.includes('Next')" :size="16" />
                                <span v-if="!link.label.includes('Previous') && !link.label.includes('Next')" v-html="link.label"></span>
                            </Link>
                            <div 
                                v-else-if="link.label === '...'"
                                class="w-10 h-10 flex items-center justify-center text-slate-400"
                            >
                                ...
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
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
