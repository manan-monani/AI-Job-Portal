<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { Link, usePage } from '@inertiajs/vue3';
import { Briefcase, CheckCircle, ChevronRight, Clock, LayoutDashboard, XCircle } from 'lucide-vue-next';
import { computed, markRaw } from 'vue';

const props = defineProps<{
    applications: {
        data: Array<{
            id: number;
            job_title: string;
            job_slug: string;
            applied_at: string;
            status: string;
            email: string;
            department: string;
        }>;
        links: any;
    };
    filters: {
        status?: string;
    };
}>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user as any);

const stats = computed(() => {
    const apps = props.applications.data;
    return [
        {
            label: 'Total Applications',
            value: props.applications.data.length, // This is just for the current page in a real app we'd pass total
            icon: markRaw(Briefcase),
            color: 'text-blue-600',
            bg: 'bg-blue-100 dark:bg-blue-900/30',
        },
        {
            label: 'Pending',
            value: apps.filter((a) => a.status === 'pending').length,
            icon: markRaw(Clock),
            color: 'text-amber-600',
            bg: 'bg-amber-100 dark:bg-amber-900/30',
        },
        {
            label: 'Accepted',
            value: apps.filter((a) => a.status === 'accepted').length,
            icon: markRaw(CheckCircle),
            color: 'text-emerald-600',
            bg: 'bg-emerald-100 dark:bg-emerald-900/30',
        },
        {
            label: 'Rejected',
            value: apps.filter((a) => a.status === 'rejected').length,
            icon: markRaw(XCircle),
            color: 'text-rose-600',
            bg: 'bg-rose-100 dark:bg-rose-900/30',
        },
    ];
});

const getStatusClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400';
        case 'accepted':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400';
        case 'rejected':
            return 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400';
        default:
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400';
    }
};
</script>

<template>
    <CustomerLayout>
        <div class="animate-fade-in space-y-8 p-2 sm:p-0">
            <!-- Header -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="flex items-center gap-3 text-2xl font-black text-slate-900 dark:text-white">
                        <LayoutDashboard class="text-primary-600" :size="28" />
                        {{ __('Candidate Dashboard') }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Welcome back,') }} <span class="font-bold text-slate-700 dark:text-slate-200">{{ authUser.name }}</span
                        >. {{ __('Track your applications and career progress.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        href="/"
                        class="rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-500/25 transition-all hover:scale-[1.02] hover:bg-primary-700 active:scale-[0.98]"
                    >
                        {{ __('Browse Jobs') }}
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <div :class="[stat.bg, stat.color]" class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl">
                        <component :is="stat.icon" :size="20" />
                    </div>
                    <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">{{ __(stat.label) }}</p>
                    <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ stat.value }}</p>
                </div>
            </div>

            <!-- Applications List -->
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-50 p-6 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('My Applications') }}</h3>
                    <div class="flex items-center gap-2">
                        <!-- Simple Filter Buttons -->
                        <Link
                            :href="customer.dashboard.url({ query: { status: 'pending' } })"
                            :class="
                                filters.status === 'pending'
                                    ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400'
                                    : 'text-slate-500 hover:text-slate-700'
                            "
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
                        >
                            {{ __('Pending') }}
                        </Link>
                        <Link
                            :href="customer.dashboard.url({ query: { status: 'rejected' } })"
                            :class="
                                filters.status === 'rejected'
                                    ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400'
                                    : 'text-slate-500 hover:text-slate-700'
                            "
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
                        >
                            {{ __('Rejected') }}
                        </Link>
                        <Link
                            :href="customer.dashboard.url()"
                            v-if="filters.status"
                            class="rounded-lg px-3 py-1.5 text-xs font-bold text-rose-500 transition-all hover:bg-rose-50"
                        >
                            {{ __('Clear') }}
                        </Link>
                    </div>
                </div>

                <div v-if="applications.data.length > 0" class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                                <th class="px-6 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Position') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Applied On') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Applied Email') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            <tr
                                v-for="app in applications.data"
                                :key="app.id"
                                class="group transition-all hover:bg-slate-50/50 dark:hover:bg-slate-800/30"
                            >
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition-colors group-hover:bg-white group-hover:text-primary-600 dark:bg-slate-800 dark:group-hover:bg-slate-700"
                                        >
                                            <Briefcase :size="18" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ app.job_title }}</p>
                                            <p class="text-[10px] font-bold tracking-tight text-slate-400 uppercase">{{ app.department }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ app.applied_at }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ app.email }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span
                                        :class="getStatusClass(app.status)"
                                        class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-bold tracking-wide uppercase"
                                    >
                                        {{ __(app.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <Link
                                        :href="`/jobs/${app.job_slug}`"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-primary-600 hover:underline dark:text-primary-400"
                                    >
                                        {{ __('View Job') }}
                                        <ChevronRight :size="14" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center px-6 py-20 text-center">
                    <div
                        class="mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600"
                    >
                        <Briefcase :size="40" />
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('No applications found') }}</h4>
                    <p class="mt-2 max-w-xs text-sm text-slate-500 dark:text-slate-400">
                        {{ __("You haven't applied for any positions matching your search criteria yet.") }}
                    </p>
                    <Link
                        href="/"
                        class="mt-8 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-900 shadow-sm transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-white dark:hover:bg-slate-800"
                    >
                        {{ __('Start Applying') }}
                    </Link>
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
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
