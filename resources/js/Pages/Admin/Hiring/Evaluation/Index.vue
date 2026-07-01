<script setup lang="ts">
import SearchableSelect from '@/Components/Common/SearchableSelect.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronRight, Search, Stethoscope } from 'lucide-vue-next';
import { watch } from 'vue';

interface JobPost {
    id: number;
    title: string;
    department: {
        id: number;
        name: string;
    } | null;
    deadline: string | null;
    hiring_status: string;
    applications_count: number;
    stage_stats: {
        id: number;
        title: string;
        count: number;
    }[];
}

const props = defineProps<{
    jobs: {
        data: JobPost[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    departments: { id: number; name: string }[];
    filters: {
        search?: string;
        department_id?: string;
    };
}>();

const evaluateJob = (jobId: number) => {
    router.visit(admin.evaluations.show.url(jobId));
};

const filters = useForm({
    search: props.filters.search || '',
    department_id: props.filters.department_id || '',
});

watch(
    () => [filters.search, filters.department_id],
    () => {
        filters.get(admin.evaluations.index.url(), {
            preserveState: true,
            replace: true,
        });
    },
);
</script>

<template>
    <Head :title="__('Evaluation')" />

    <AdminLayout>
        <div class="animate-fade-in space-y-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-6 text-start md:flex-row md:items-center">
                <div class="text-start">
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Pipeline Evaluation') }}</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Evaluate candidates and transition them through hiring stages across eligible jobs.') }}
                    </p>
                </div>
            </div>

            <!-- Filters & Table Card -->
            <div class="overflow-hidden rounded-4xl border border-slate-200/60 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <!-- Filters -->
                <div class="flex flex-wrap gap-4 border-b border-slate-100 p-4 dark:border-slate-700">
                    <div class="relative w-full sm:w-64">
                        <Search :size="16" class="absolute top-1/2 left-3 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="filters.search"
                            type="text"
                            :placeholder="__('Search jobs...')"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 pr-4 pl-10 text-sm focus:border-brand-500 focus:ring-brand-500/20 focus:outline-none dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-200"
                        />
                    </div>

                    <div class="min-w-40">
                        <SearchableSelect
                            v-model="filters.department_id"
                            :options="departments.map((d) => ({ id: d.id, name: d.name }))"
                            :placeholder="__('All Departments')"
                            :search-placeholder="__('Search department...')"
                            clearable
                        />
                    </div>

                    <button
                        v-if="filters.search || filters.department_id"
                        @click="
                            filters.search = '';
                            filters.department_id = '';
                        "
                        class="h-10 px-2 text-sm font-bold text-rose-600 hover:text-rose-700"
                    >
                        {{ __('Clear Filters') }}
                    </button>
                </div>

                <div class="custom-scrollbar overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left dark:divide-slate-800">
                        <thead class="bg-slate-50 text-start dark:bg-slate-800/50">
                            <tr class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-700/50 dark:bg-slate-800/20">
                                <th class="px-4 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('SL') }}</th>
                                <th class="px-4 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Job Info') }}</th>
                                <th class="px-4 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Department') }}</th>
                                <th class="px-4 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Applications') }}</th>
                                <th class="px-4 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Active Stages') }}</th>
                                <th class="px-4 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Status') }}</th>
                                <th class="px-4 py-4 text-right text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-start dark:divide-slate-800/50">
                            <tr
                                v-for="(job, index) in jobs.data"
                                :key="job.id"
                                @click="evaluateJob(job.id)"
                                class="group cursor-pointer transition-all duration-200 hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-4 py-4 text-xs font-bold text-slate-400">#{{ index + 1 }}</td>
                                <td class="px-4 py-4">
                                    <div>
                                        <div
                                            class="text-sm font-bold text-slate-900 transition-colors group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400"
                                        >
                                            {{ job.title }}
                                        </div>
                                        <div v-if="job.deadline" class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ __('Deadline:') }} {{ new Date(job.deadline).toLocaleDateString() }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ job.department?.name || __('No Department') }}
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400"
                                    >
                                        {{ job.applications_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div
                                        class="inline-flex items-center gap-0.5 rounded-lg border border-slate-100 bg-slate-50/40 p-1 dark:border-slate-700/50 dark:bg-slate-900/30"
                                    >
                                        <template v-for="(stat, idx) in job.stage_stats" :key="stat.id">
                                            <div class="group/dot relative flex items-center">
                                                <div
                                                    class="relative flex h-4.5 w-4.5 items-center justify-center rounded-full text-[8px] font-black transition-all duration-300"
                                                    :class="
                                                        stat.count > 0
                                                            ? 'bg-brand-600 text-white shadow-[0_0_6px_rgba(var(--brand-primary),0.3)] ring-2 ring-brand-500/15'
                                                            : 'bg-white text-slate-400 dark:bg-slate-800 dark:text-slate-600'
                                                    "
                                                >
                                                    {{ idx + 1 }}

                                                    <!-- Pulse effect for active stages -->
                                                    <div
                                                        v-if="stat.count > 0"
                                                        class="absolute inset-0 animate-pulse rounded-full bg-brand-500/10"
                                                    ></div>
                                                </div>

                                                <!-- Tooltip -->
                                                <div
                                                    class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-3 -translate-x-1/2 opacity-0 transition-all duration-200 group-hover/dot:translate-y-0 group-hover/dot:opacity-100"
                                                >
                                                    <div
                                                        class="flex flex-col gap-1 rounded-xl bg-slate-900 p-2.5 text-[10px] font-bold whitespace-nowrap text-white shadow-2xl ring-1 ring-white/10 backdrop-blur-md dark:bg-white dark:text-slate-900"
                                                    >
                                                        <div class="flex items-center justify-between gap-3">
                                                            <span class="tracking-wide uppercase opacity-70">{{ stat.title }}</span>
                                                            <span
                                                                class="flex h-5 min-w-5 items-center justify-center rounded-lg bg-white/20 px-1.5 text-[11px] tabular-nums dark:bg-slate-900/10"
                                                            >
                                                                {{ stat.count }}
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="absolute top-full left-1/2 -mt-1 -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-white"
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Connector -->
                                            <div
                                                v-if="idx < job.stage_stats.length - 1"
                                                class="h-px w-1 rounded-full"
                                                :class="stat.count > 0 ? 'bg-brand-200 dark:bg-brand-900/40' : 'bg-slate-200 dark:bg-slate-800'"
                                            ></div>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-bold tracking-wide uppercase"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400':
                                                job.hiring_status === 'Hiring In Progress',
                                            'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400': job.hiring_status === 'Start Hiring',
                                            'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400': job.hiring_status === 'Closed',
                                            'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': ![
                                                'Start Hiring',
                                                'Hiring In Progress',
                                                'Closed',
                                            ].includes(job.hiring_status),
                                        }"
                                    >
                                        {{ __(job.hiring_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div
                                        class="inline-flex h-8 items-center gap-1.5 rounded-xl bg-slate-50 px-3 text-sm font-bold text-brand-600 transition-all group-hover:bg-brand-50 dark:bg-slate-800 dark:group-hover:bg-brand-900/20"
                                    >
                                        {{ __('Evaluate') }}
                                        <ChevronRight :size="16" />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="jobs.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <Stethoscope :size="48" class="text-slate-200" />
                                        <p>{{ __('No eligible jobs found.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (Placeholder if needed) -->
                <div v-if="jobs.last_page > 1" class="border-t border-slate-100 p-4 dark:border-slate-800">
                    <div class="flex items-center justify-between text-sm text-slate-500">
                        <span>{{ __('Showing page :page of :total', { page: String(jobs.current_page), total: String(jobs.last_page) }) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
