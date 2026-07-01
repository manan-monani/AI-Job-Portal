<script setup lang="ts">
import SearchableSelect from '@/Components/Common/SearchableSelect.vue';
import { wTrans as __ } from '@/Core/i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, router } from '@inertiajs/vue3';
import { FileText, Mail, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Candidate {
    id: number;
    name: string;
    email: string;
    phone: string;
    message: string | null;
    status: string;
    evaluation_status: string;
    ats_score_cached: number | null;
    job_post: {
        id: number;
        title: string;
        department: { name: string } | null;
    };
    stage_statuses: any[];
}

const props = defineProps<{
    candidates: {
        data: Candidate[];
        links: any[];
        meta: any;
    };
    filters: {
        job_post_id?: string;
        department_id?: string;
    };
    jobs: { id: number; title: string }[];
    departments: { id: number; name: string }[];
}>();

const searchTerm = ref('');
const selectedJob = ref(props.filters.job_post_id || '');
const selectedDepartment = ref(props.filters.department_id || '');

const openDrawer = ref(false);
const activeApplication = ref<any>(null);
const isLoadingDetails = ref(false);

const handleSearch = () => {
    router.get(
        admin.candidates.index.url(),
        {
            job_post_id: selectedJob.value,
            department_id: selectedDepartment.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

watch([selectedJob, selectedDepartment], () => {
    handleSearch();
});

const showApplicationDetails = async (applicationId: number) => {
    isLoadingDetails.value = true;
    openDrawer.value = true;

    try {
        const response = await fetch(admin.candidates.show.url(applicationId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });
        const data = await response.json();
        activeApplication.value = data.application;
    } catch (error) {
        console.error('Failed to load application details', error);
    } finally {
        isLoadingDetails.value = false;
    }
};

const closeDrawer = () => {
    openDrawer.value = false;
    activeApplication.value = null;
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'hired':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400';
        case 'rejected':
            return 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400';
        case 'evaluating':
            return 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400';
        case 'pending':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400';
        default:
            return 'bg-slate-50 text-slate-700 dark:bg-slate-900/20 dark:text-slate-400';
    }
};
</script>

<template>
    <Head :title="__('Candidates')" />

    <AdminLayout>
        <div class="animate-fade-in space-y-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-6 text-start md:flex-row md:items-center">
                <div class="text-start">
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Candidate Management') }}</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('View and manage all job applications in one place.') }}
                    </p>
                </div>
            </div>

            <!-- Filters & Table Card -->
            <div class="overflow-hidden rounded-4xl border border-slate-200/60 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <!-- Filters -->
                <div class="flex flex-wrap gap-4 border-b border-slate-100 p-4 dark:border-slate-700">
                    <div class="min-w-40">
                        <SearchableSelect
                            v-model="selectedJob"
                            :options="jobs.map((j) => ({ id: j.id, name: j.title }))"
                            :placeholder="__('All Jobs')"
                            :search-placeholder="__('Search jobs...')"
                            clearable
                        />
                    </div>

                    <div class="min-w-40">
                        <SearchableSelect
                            v-model="selectedDepartment"
                            :options="departments.map((d) => ({ id: d.id, name: d.name }))"
                            :placeholder="__('All Departments')"
                            :search-placeholder="__('Search department...')"
                            clearable
                        />
                    </div>

                    <button
                        v-if="selectedJob || selectedDepartment"
                        @click="
                            selectedJob = '';
                            selectedDepartment = '';
                        "
                        class="h-10 px-2 text-sm font-bold text-rose-600 hover:text-rose-700"
                    >
                        {{ __('Clear Filters') }}
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left dark:divide-slate-800">
                        <thead class="bg-slate-50 text-start dark:bg-slate-800/50">
                            <tr class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-700/50 dark:bg-slate-800/20">
                                <th class="px-6 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('SL') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Candidate Info') }}
                                </th>
                                <th class="px-6 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Applied Job Info') }}
                                </th>
                                <th class="px-6 py-4 text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Evaluation Status') }}
                                </th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Application') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-start dark:divide-slate-800/50">
                            <tr
                                v-for="(candidate, index) in candidates.data"
                                :key="candidate.id"
                                class="group transition-all duration-200 hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-6 py-4 text-xs font-bold text-slate-400">#{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 font-bold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400"
                                        >
                                            {{ candidate.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ candidate.name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ candidate.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="group/candidate relative max-w-50">
                                        <div class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                            {{ candidate.job_post.title }}
                                        </div>
                                        <div class="flex items-center gap-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                            <Building2 :size="12" />
                                            {{ candidate.job_post.department?.name || __('No Department') }}
                                        </div>

                                        <!-- Hover Info -->
                                        <div
                                            class="absolute bottom-full z-10 mb-2 hidden w-max max-w-xs rounded bg-slate-900 p-2 text-xs text-white shadow-lg transition-opacity group-hover/candidate:block"
                                        >
                                            <p class="font-bold">{{ candidate.job_post.title }}</p>
                                            <p>{{ candidate.job_post.department?.name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-start">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase"
                                        :class="getStatusClass(candidate.evaluation_status)"
                                    >
                                        {{ __(candidate.evaluation_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        @click="showApplicationDetails(candidate.id)"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-xl bg-slate-50 px-3 text-sm font-bold text-brand-600 transition-all hover:bg-brand-50 dark:bg-slate-800 dark:hover:bg-brand-900/20"
                                    >
                                        {{ __('Review') }}
                                        <ChevronRight :size="16" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="candidates.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <User :size="48" class="text-slate-200" />
                                        <p>{{ __('No candidates found.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Application Details Slide-Over (Shared Logic) -->
        <Teleport to="body">
            <Transition
                enter-active-class="ease-in-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in-out duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="openDrawer" @click="closeDrawer" class="fixed inset-0 z-55 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            </Transition>

            <Transition
                enter-active-class="transform transition ease-in-out duration-300"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-300"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <div
                    v-if="openDrawer"
                    class="fixed inset-y-0 right-0 z-60 flex w-full max-w-md flex-col border-l bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Application Details') }}</h2>
                        <button @click="closeDrawer" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100">
                            <X :size="20" />
                        </button>
                    </div>

                    <div v-if="isLoadingDetails" class="flex flex-1 items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="h-8 w-8 animate-spin rounded-full border-2 border-brand-600 border-t-transparent"></div>
                            <p class="text-sm text-slate-500">{{ __('Loading data...') }}</p>
                        </div>
                    </div>

                    <div v-else-if="activeApplication" class="flex-1 space-y-8 overflow-y-auto p-6">
                        <!-- Profile -->
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-2xl font-bold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400"
                            >
                                {{ activeApplication.name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ activeApplication.name }}</h3>
                                <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                                    <Mail :size="14" />
                                    {{ activeApplication.email }}
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                <p class="mb-1 text-[10px] font-extrabold text-slate-500 uppercase">{{ __('Phone') }}</p>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ activeApplication.phone }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                <p class="mb-1 text-[10px] font-extrabold text-slate-500 uppercase">{{ __('Status') }}</p>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase"
                                    :class="getStatusClass(activeApplication.status)"
                                >
                                    {{ activeApplication.status }}
                                </span>
                            </div>
                        </div>

                        <div v-if="activeApplication.message">
                            <h4 class="mb-3 text-xs font-extrabold tracking-widest text-slate-500 uppercase">{{ __('Message') }}</h4>
                            <div
                                class="rounded-xl border border-slate-100 bg-white p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ activeApplication.message }}
                            </div>
                        </div>

                        <!-- Resume -->
                        <div>
                            <h4 class="mb-3 text-xs font-extrabold tracking-widest text-slate-500 uppercase">{{ __('Resume / CV') }}</h4>
                            <div
                                v-if="activeApplication.resume"
                                class="flex items-center justify-between rounded-xl border p-4 dark:border-slate-800"
                            >
                                <div class="flex items-center gap-3">
                                    <FileText class="text-rose-500" />
                                    <span class="text-sm font-medium">{{ activeApplication.resume.cv_title }}</span>
                                </div>
                                <a
                                    :href="`/storage/${activeApplication.resume.file_path}`"
                                    target="_blank"
                                    class="rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-600 hover:bg-brand-100"
                                    >{{ __('View File') }}</a
                                >
                            </div>
                            <div v-else class="rounded-xl border-2 border-dashed border-slate-200 p-8 text-center text-xs text-slate-400">
                                {{ __('No CV uploaded') }}
                            </div>
                        </div>

                        <!-- Timeline (Stage History) -->
                        <div v-if="activeApplication.stage_statuses?.length">
                            <h4 class="mb-3 text-xs font-extrabold tracking-widest text-slate-500 uppercase">{{ __('Pipeline History') }}</h4>
                            <div class="space-y-4">
                                <div v-for="status in activeApplication.stage_statuses" :key="status.id" class="flex gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="mt-1.5 h-2 w-2 rounded-full bg-brand-500 ring-4 ring-brand-50 dark:ring-brand-900/20"></div>
                                        <div class="mt-1 w-0.5 flex-1 bg-slate-100 dark:bg-slate-800"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ status.stage?.title }}</p>
                                        <p class="text-[10px] font-extrabold text-slate-500 uppercase">
                                            {{ status.status }} • {{ new Date(status.created_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t p-6 dark:border-slate-800">
                        <button
                            @click="closeDrawer"
                            class="w-full rounded-xl border py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
