<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ChevronRight, 
    ArrowLeft,
    Clock,
    Monitor,
    Globe,
    User as UserIcon,
    FileText,
    History
} from 'lucide-vue-next';
import admin from '@/routes/admin';

const props = defineProps<{
    log: any;
}>();

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
    return new Date(dateString).toLocaleString();
};

const formatJson = (json: any) => {
    return JSON.stringify(json, null, 2);
};
</script>

<template>
    <Head :title="__('Activity Details')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-4xl mx-auto w-full p-4 lg:p-0">
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <Link :href="admin.activity_logs.index.url()" class="hover:text-brand-500">{{ __('Activity Log') }}</Link>
                        <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                        <span class="text-brand-500">{{ __('Details') }}</span>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Log Details') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Deep audit trail analysis for event') }} #{{ log.id }}</p>
                </div>
                <Link 
                    :href="admin.activity_logs.index.url()"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 px-6 py-3 rounded-2xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2"
                >
                    <ArrowLeft :size="16" class="rtl:rotate-180" />
                    {{ __('Back to List') }}
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Sidebar: Metadata -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                <UserIcon :size="24" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Subject') }}</p>
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ log.user ? log.user.name : 'System' }}</h4>
                                <p class="text-xs text-slate-400">{{ log.user ? log.user.email : 'Automated Task' }}</p>
                            </div>
                        </div>

                        <div class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <Clock :size="16" class="text-slate-400" />
                                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    <span class="block text-slate-900 dark:text-white font-bold">{{ formatDate(log.created_at) }}</span>
                                    {{ __('Timestamp') }}
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <Globe :size="16" class="text-slate-400" />
                                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    <span class="block text-slate-900 dark:text-white font-bold">{{ log.ip_address }}</span>
                                    IP Address
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <Monitor :size="16" class="text-slate-400" />
                                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    <span class="block text-slate-900 dark:text-white font-bold truncate max-w-[180px]" :title="log.user_agent">{{ log.user_agent }}</span>
                                    User Agent
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                            <span 
                                class="inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider w-full text-center"
                                :class="getEventBadgeClass(log.event)"
                            >
                                {{ log.event.replace('_', ' ') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Content: Description & Properties -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm space-y-8">
                        <div>
                            <div class="flex items-center gap-2 mb-4">
                                <FileText :size="18" class="text-brand-500" />
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Activity Summary') }}</h3>
                            </div>
                            <p class="text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 font-medium">
                                {{ log.description }}
                            </p>
                        </div>

                        <div v-if="log.properties">
                            <div class="flex items-center gap-2 mb-4">
                                <History :size="18" class="text-brand-500" />
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Data Changes') }}</h3>
                            </div>
                            
                            <div class="rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden bg-slate-900 text-slate-300 p-6 font-mono text-xs overflow-x-auto shadow-inner">
                                <pre>{{ formatJson(log.properties) }}</pre>
                            </div>
                        </div>

                        <div v-if="log.subject_type">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Subject Information') }}</p>
                            <div class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-400">
                                {{ __('Type') }}: <span class="text-slate-900 dark:text-white">{{ log.subject_type }}</span><br>
                                {{ __('ID') }}: <span class="text-slate-900 dark:text-white">{{ log.subject_id }}</span>
                            </div>
                        </div>
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
