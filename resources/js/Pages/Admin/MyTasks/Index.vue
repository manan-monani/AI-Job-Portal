<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, Link, router } from '@inertiajs/vue3';
import { Activity, Briefcase, Building2, CheckSquare, ChevronRight, Hash, Info } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    jobs: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const activePopover = ref<number | null>(null);

const togglePopover = (id: number, e: Event) => {
    e.preventDefault();
    e.stopPropagation();
    if (activePopover.value === id) {
        activePopover.value = null;
    } else {
        activePopover.value = id;
    }
};

const closePopover = () => {
    activePopover.value = null;
};
</script>

<template>
    <Head :title="__('My Tasks')" />

    <AdminLayout>
        <div class="animate-fade-in space-y-6" @click="closePopover">
            <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('My Tasks') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Manage and review candidates in the pipeline phases assigned to you.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Summary Cards -->
                <div
                    class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="absolute top-0 right-0 p-4 opacity-5 transition-opacity group-hover:opacity-10">
                        <Briefcase :size="64" />
                    </div>
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400"
                    >
                        <Briefcase :size="24" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ summary.total_jobs }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Jobs Assigned') }}</p>
                </div>

                <div
                    class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="absolute top-0 right-0 p-4 opacity-5 transition-opacity group-hover:opacity-10">
                        <CheckSquare :size="64" />
                    </div>
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400"
                    >
                        <CheckSquare :size="24" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ summary.total_assigned_phases }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Total Phases Assigned to Me') }}</p>
                </div>

                <div
                    class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="absolute top-0 right-0 p-4 opacity-5 transition-opacity group-hover:opacity-10">
                        <Activity :size="64" />
                    </div>
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400"
                    >
                        <Activity :size="24" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ summary.active_tasks }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Phases Currently Running') }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-visible rounded-[2rem] border border-slate-200/60 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="min-h-75 overflow-visible">
                    <table class="w-full min-w-max border-collapse text-start">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-700/50 dark:bg-slate-800/20">
                                <th
                                    class="w-[30%] px-6 py-4 text-start text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-400 uppercase"
                                >
                                    {{ __('Job Info') }}
                                </th>
                                <th
                                    class="w-[10%] px-6 py-4 text-start text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-400 uppercase"
                                >
                                    {{ __('Total Phases') }}
                                </th>
                                <th
                                    class="w-[20%] px-6 py-4 text-start text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-400 uppercase"
                                >
                                    {{ __('My Assigned Phases') }}
                                </th>
                                <th
                                    class="w-[15%] px-6 py-4 text-start text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-400 uppercase"
                                >
                                    {{ __('Last Opened Phase') }}
                                </th>
                                <th
                                    class="w-[15%] px-6 py-4 text-start text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-400 uppercase"
                                >
                                    {{ __('My Phase Running') }}
                                </th>
                                <th
                                    class="w-[10%] px-6 py-4 text-start text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-400 uppercase"
                                >
                                    {{ __('Job Status') }}
                                </th>
                                <th
                                    class="w-[5%] px-6 py-4 text-end text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-400 uppercase"
                                >
                                    {{ __('Action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-start dark:divide-slate-800/50">
                            <tr
                                v-for="job in jobs.data"
                                :key="job.id"
                                @click="router.visit(admin.evaluations.show.url(job.id))"
                                :class="[
                                    'group cursor-pointer transition-all duration-200 hover:bg-slate-50/80 dark:hover:bg-slate-800/40',
                                    activePopover === job.id ? 'relative z-50' : 'relative z-0',
                                ]"
                            >
                                <td class="relative z-0 px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ job.title }}</span>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                            <Building2 :size="12" />
                                            <span>{{ job.department || __('No Department') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="relative z-0 px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Hash :size="14" class="text-slate-400" />
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ job.total_phases }}</span>
                                    </div>
                                </td>
                                <td :class="['px-6 py-4', activePopover === job.id ? 'relative z-50' : 'relative z-10']">
                                    <div class="relative inline-block">
                                        <button
                                            @click.stop.prevent="(e) => togglePopover(job.id, e)"
                                            class="flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700"
                                        >
                                            <span class="text-sm font-bold text-brand-600 dark:text-brand-400">{{ job.assigned_phase_count }}</span>
                                            <Info :size="14" class="text-slate-400" />
                                        </button>

                                        <!-- Popover -->
                                        <div
                                            v-if="activePopover === job.id"
                                            class="absolute top-full left-0 z-50 mt-2 w-72 rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800"
                                            @click.stop
                                        >
                                            <div class="border-b border-slate-100 bg-slate-50 px-4 py-2 dark:border-slate-700 dark:bg-slate-900/50">
                                                <h4 class="text-xs font-bold tracking-wider text-slate-500 uppercase">
                                                    {{ __('Your Assigned Phases') }}
                                                </h4>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto p-2">
                                                <ul class="space-y-1">
                                                    <li
                                                        v-for="phase in job.assigned_phases"
                                                        :key="phase.id"
                                                        class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:bg-slate-900/20 dark:text-slate-300"
                                                    >
                                                        <div class="font-medium">{{ phase.title }}</div>
                                                        <div class="mt-0.5 text-[10px] text-slate-400 uppercase">
                                                            {{ phase.type.replace('_', ' ') }}
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="relative z-0 px-6 py-4">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ job.last_opened_phase }}</span>
                                </td>
                                <td class="relative z-0 px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            job.is_running
                                                ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                        "
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="job.is_running ? 'animate-pulse bg-emerald-500' : 'bg-slate-400'"
                                        ></span>
                                        {{ job.is_running ? __('Yes, Running') : __('No') }}
                                    </span>
                                </td>
                                <td class="relative z-0 px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="w-max rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                            >{{ job.job_status.toUpperCase() }}</span
                                        >
                                        <span class="text-[10px] tracking-widest text-slate-500 uppercase">{{ job.hiring_status }}</span>
                                    </div>
                                </td>
                                <td class="relative z-0 px-6 py-4 text-end">
                                    <div class="flex items-center justify-end">
                                        <!-- Real link for explicit button (overlay handles the row click) -->
                                        <div
                                            class="relative z-20 flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-slate-800 dark:group-hover:bg-brand-900/20 dark:group-hover:text-brand-400"
                                        >
                                            <ChevronRight :size="16" />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="jobs.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <CheckSquare :size="48" class="mb-4 text-slate-300 dark:text-slate-600" />
                                        <p class="text-base font-medium text-slate-900 dark:text-white">{{ __('No tasks assigned') }}</p>
                                        <p class="mt-1 text-sm">{{ __('You currently have no pipeline phases assigned to you.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="jobs.links && jobs.links.length > 3" class="flex justify-end border-t border-slate-100 p-6 dark:border-slate-700">
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, key) in jobs.links"
                            :key="key"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="rounded-lg border border-transparent px-3 py-1 text-xs transition-colors"
                            :class="
                                link.active
                                    ? 'bg-brand-600 text-white'
                                    : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'
                            "
                            :preserve-state="true"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
