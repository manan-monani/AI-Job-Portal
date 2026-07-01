<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowUpRight,
    Briefcase,
    Calendar,
    CheckCircle2,
    Clock,
    ExternalLink,
    Filter,
    Search,
    User,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        jobs: {
            total_active: number;
            published: number;
            draft: number;
            hidden: number;
            trending: {
                this_week: number;
                last_week: number;
            };
        };
        applications: Record<string, number>;
        stalled_candidates_count: number;
        pending_reviews_count: number;
    };
    top_jobs: any[];
    upcoming_interviews: any[];
    jobs_nearing_deadline: any[];
    recently_hired: any[];
}>();

// Application pipeline visualization sequence
const pipelineStages = [
    { key: 'pending', label: 'Pending', icon: Clock, color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-900/20' },
    { key: 'applied', label: 'Applied', icon: UserPlus, color: 'text-blue-600', bg: 'bg-blue-50 dark:bg-blue-900/20' },
    { key: 'screening', label: 'Screening', icon: Search, color: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-900/20' },
    { key: 'shortlisted', label: 'Shortlisted', icon: Filter, color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-900/20' },
    { key: 'interview', label: 'Interview', icon: Calendar, color: 'text-pink-600', bg: 'bg-pink-50 dark:bg-pink-900/20' },
    { key: 'hired', label: 'Hired', icon: CheckCircle2, color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-900/20' },
];

const jobTrend = computed(() => {
    const { this_week, last_week } = props.stats.jobs.trending;
    if (last_week === 0) return this_week > 0 ? '+100%' : '0%';
    const diff = ((this_week - last_week) / last_week) * 100;
    return `${diff > 0 ? '+' : ''}${diff.toFixed(0)}%`;
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="animate-fade-in space-y-6 pb-12">
            <!-- Page Header -->
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div class="text-start">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Recruitment Hub</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Monitor your hiring pipeline and job health in real-time.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/jobs/create"
                        class="flex items-center gap-2 rounded-xl bg-(--brand-primary) px-4 py-2 text-sm font-bold text-white shadow-lg transition-all hover:brightness-110"
                    >
                        <Briefcase class="h-4 w-4" />
                        Post New Job
                    </Link>
                </div>
            </div>

            <!-- Key Metrics Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Active Jobs -->
                <div
                    class="rounded-2xl border border-admin-card-border bg-admin-card p-5 shadow-sm transition-all hover:shadow-md dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div class="rounded-xl bg-blue-600 p-2 text-white shadow-lg shadow-blue-900/20">
                            <Briefcase :size="20" />
                        </div>
                        <span
                            :class="[
                                'rounded-full px-2 py-1 text-[10px] font-bold',
                                stats.jobs.trending.this_week >= stats.jobs.trending.last_week
                                    ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40'
                                    : 'bg-rose-100 text-rose-600 dark:bg-rose-900/40',
                            ]"
                        >
                            {{ jobTrend }} vs last week
                        </span>
                    </div>
                    <h3 class="text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">Active Jobs</h3>
                    <p class="mt-1 text-start text-2xl font-black text-slate-900 dark:text-white">{{ stats.jobs.total_active }}</p>
                </div>

                <!-- Pending Reviews -->
                <div
                    class="rounded-2xl border border-admin-card-border bg-admin-card p-5 shadow-sm transition-all hover:shadow-md dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div class="rounded-xl bg-indigo-600 p-2 text-white shadow-lg shadow-indigo-900/20">
                            <Clock :size="20" />
                        </div>
                        <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-600 dark:bg-amber-900/40"
                            >Action Required</span
                        >
                    </div>
                    <h3 class="text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">Awaiting Review</h3>
                    <p class="mt-1 text-start text-2xl font-black text-slate-900 dark:text-white">{{ stats.pending_reviews_count }}</p>
                </div>

                <!-- Stalled Candidates -->
                <div
                    class="rounded-2xl border border-admin-card-border bg-admin-card p-5 shadow-sm transition-all hover:shadow-md dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div class="rounded-xl bg-rose-600 p-2 text-white shadow-lg shadow-rose-900/20">
                            <AlertCircle :size="20" />
                        </div>
                        <span
                            v-if="stats.stalled_candidates_count > 0"
                            class="rounded-full bg-rose-100 px-2 py-1 text-[10px] font-bold text-rose-600 dark:bg-rose-900/40"
                            >Nudge Needed</span
                        >
                        <span v-else class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-600 dark:bg-emerald-900/40"
                            >Healthy</span
                        >
                    </div>
                    <h3 class="text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">Stalled Candidates</h3>
                    <p class="mt-1 text-start text-2xl font-black text-slate-900 dark:text-white">{{ stats.stalled_candidates_count }}</p>
                </div>

                <!-- Total Applications (This Week) -->
                <div
                    class="rounded-2xl border border-admin-card-border bg-admin-card p-5 shadow-sm transition-all hover:shadow-md dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div class="rounded-xl bg-emerald-600 p-2 text-white shadow-lg shadow-emerald-900/20">
                            <Users :size="20" />
                        </div>
                        <div class="flex -space-x-2">
                            <div
                                v-for="i in 3"
                                :key="i"
                                class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-slate-200 text-[8px] font-bold text-slate-500 dark:border-slate-800 dark:bg-slate-700"
                            >
                                <Users :size="10" />
                            </div>
                        </div>
                    </div>
                    <h3 class="text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">Hired (This Quarter)</h3>
                    <p class="mt-1 text-start text-2xl font-black text-slate-900 dark:text-white">{{ stats.applications.hired || 0 }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Application Pipeline (Center Column - 2/3) -->
                <div class="space-y-6 lg:col-span-2">
                    <div
                        class="overflow-hidden rounded-4xl border border-admin-card-border bg-admin-card shadow-sm dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                    >
                        <div class="flex items-center justify-between border-b border-slate-50 p-5 dark:border-slate-800">
                            <h3 class="text-start text-base font-extrabold text-slate-900 dark:text-white">Application Pipeline</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-6">
                                <div
                                    v-for="stage in pipelineStages"
                                    :key="stage.key"
                                    class="flex flex-col items-center rounded-2xl border border-slate-50 p-4 transition-all hover:shadow-sm dark:border-slate-800"
                                    :class="stage.bg"
                                >
                                    <div :class="['mb-3 rounded-xl p-2', stage.color]">
                                        <component :is="stage.icon" :size="24" />
                                    </div>
                                    <p class="mb-1 text-[10px] font-bold tracking-widest text-slate-500 uppercase">{{ stage.label }}</p>
                                    <p class="text-xl font-black text-slate-900 dark:text-white">{{ stats.applications[stage.key] || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Jobs Table -->
                    <div
                        class="overflow-hidden rounded-4xl border border-admin-card-border bg-admin-card shadow-sm dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                    >
                        <div class="flex items-center justify-between border-b border-slate-50 p-5 dark:border-slate-800">
                            <h3 class="text-start text-base font-extrabold text-slate-900 dark:text-white">Top Jobs by Volume</h3>
                            <Link href="/admin/jobs" class="text-xs font-bold tracking-widest text-(--brand-primary) uppercase hover:underline"
                                >View All Jobs</Link
                            >
                        </div>
                        <div class="max-h-96 overflow-x-auto overflow-y-auto">
                            <table class="w-full text-start">
                                <thead class="bg-slate-50/50 dark:bg-slate-800/30">
                                    <tr>
                                        <th class="px-6 py-3 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">Job Title</th>
                                        <th class="px-6 py-3 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                            Applicants
                                        </th>
                                        <th class="px-6 py-3 text-end text-[10px] font-bold tracking-widest text-slate-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                    <tr
                                        v-for="job in top_jobs"
                                        :key="job.id"
                                        class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
                                    >
                                        <td class="px-6 py-3">
                                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ job.title }}</div>
                                            <div class="text-[10px] tracking-wider text-slate-500 uppercase">{{ job.slug }}</div>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span
                                                class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-400"
                                            >
                                                {{ job.applications_count }} Candidates
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-end">
                                            <Link
                                                :href="`/admin/jobs/${job.id}`"
                                                class="inline-block rounded-lg border border-slate-100 p-1.5 transition-all hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                            >
                                                <ArrowUpRight :size="16" class="text-slate-400" />
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="top_jobs.length === 0">
                                        <td colspan="3" class="py-12 text-center text-sm text-slate-400 italic">No data available</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Column (1/3) -->
                <div class="space-y-6">
                    <!-- Upcoming Interviews -->
                    <div
                        class="overflow-hidden rounded-4xl border border-admin-card-border bg-admin-card shadow-sm dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                    >
                        <div class="border-b border-slate-50 p-5 dark:border-slate-800">
                            <h3 class="text-start text-base font-extrabold text-slate-900 dark:text-white">Upcoming Interviews</h3>
                        </div>
                        <div class="max-h-96 space-y-4 overflow-y-auto p-5">
                            <div
                                v-for="interview in upcoming_interviews"
                                :key="interview.id"
                                class="flex items-start gap-3 rounded-2xl border border-slate-50 bg-slate-50/30 p-3 dark:border-slate-800 dark:bg-slate-800/20"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400"
                                >
                                    <Calendar :size="20" />
                                </div>
                                <div class="min-w-0 flex-1 text-start">
                                    <p class="truncate text-xs font-bold text-slate-900 dark:text-white">
                                        {{ interview.application?.name || 'Unknown Candidate' }}
                                    </p>
                                    <p class="truncate text-[10px] text-slate-500">{{ interview.application?.job_post?.title || 'Unknown Job' }}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span
                                            class="rounded bg-indigo-50 px-1.5 py-0.5 text-[9px] font-bold text-indigo-600 uppercase dark:bg-indigo-900/40 dark:text-indigo-400"
                                            >{{ formatDateTime(interview.scheduled_at) }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="upcoming_interviews.length === 0"
                                class="rounded-2xl border-2 border-dashed border-slate-100 py-8 text-center text-xs text-slate-400 italic dark:border-slate-800"
                            >
                                No interviews scheduled for the next 7 days.
                            </div>
                        </div>
                    </div>

                    <!-- Deadlines -->
                    <div
                        class="overflow-hidden rounded-4xl border border-admin-card-border bg-admin-card shadow-sm dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                    >
                        <div class="border-b border-slate-50 p-5 dark:border-slate-800">
                            <h3 class="text-start text-base font-extrabold text-slate-900 dark:text-white">Nearing Deadlines</h3>
                        </div>
                        <div class="max-h-96 space-y-3 overflow-y-auto p-5">
                            <div
                                v-for="job in jobs_nearing_deadline"
                                :key="job.id"
                                class="flex items-center justify-between rounded-2xl border border-slate-50 p-3 dark:border-slate-800"
                            >
                                <div class="min-w-0 text-start">
                                    <p class="truncate text-xs font-bold text-slate-900 dark:text-white">{{ job.title }}</p>
                                    <p class="text-[10px] font-bold tracking-wider text-rose-500 uppercase">Due {{ formatDate(job.deadline) }}</p>
                                </div>
                                <Link :href="`/admin/jobs/${job.id}`" class="text-slate-300 transition-all hover:text-(--brand-primary)">
                                    <ExternalLink :size="16" />
                                </Link>
                            </div>
                            <div v-if="jobs_nearing_deadline.length === 0" class="py-4 text-center text-xs text-slate-400 italic">
                                No urgent deadlines.
                            </div>
                        </div>
                    </div>

                    <!-- Recently Hired -->
                    <div
                        class="overflow-hidden rounded-4xl border border-admin-card-border bg-admin-card shadow-sm dark:border-admin-card-border-dark dark:bg-admin-card-dark"
                    >
                        <div class="border-b border-slate-50 p-5 dark:border-slate-800">
                            <h3 class="text-start text-base font-extrabold text-slate-900 dark:text-white">Recently Hired</h3>
                        </div>
                        <div class="max-h-96 space-y-3 overflow-y-auto p-5">
                            <div
                                v-for="candidate in recently_hired"
                                :key="candidate.id"
                                class="flex items-start gap-3 rounded-2xl border border-slate-50 bg-slate-50/30 p-3 dark:border-slate-800 dark:bg-slate-800/20"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400"
                                >
                                    <User :size="20" />
                                </div>
                                <div class="min-w-0 flex-1 text-start">
                                    <p class="truncate text-xs font-bold text-slate-900 dark:text-white">
                                        {{ candidate.name }}
                                    </p>
                                    <p class="truncate text-[10px] text-slate-500">{{ candidate.job_post?.title || 'Unknown Role' }}</p>
                                    <p class="mt-1 text-[9px] font-medium text-emerald-600 dark:text-emerald-400">
                                        Hired {{ formatDate(candidate.updated_at) }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="recently_hired.length === 0" class="py-4 text-center text-xs text-slate-400 italic">No recent hires.</div>
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
