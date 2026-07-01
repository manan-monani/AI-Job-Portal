<script setup lang="ts">
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import SearchableSelect from '@/Components/Common/SearchableSelect.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Briefcase, Building2, Calendar, Copy, Eye, EyeOff, FileText, GitBranch, MapPin, Pencil, PlusCircle, Search, Trash2 } from 'lucide-vue-next';
import { PropType, ref, watch } from 'vue';

const props = defineProps({
    jobs: Object as PropType<any>,
    departments: Array as PropType<any[]>,
});

const filters = ref({
    search: '',
    department_id: '',
    status: '',
});

watch(
    filters,
    debounce((value) => {
        router.get(admin.jobs.index.url(), value, { preserveState: true, replace: true });
    }, 300),
    { deep: true },
);

const toggleStatus = (job: any, newStatus: string) => {
    router.patch(
        admin.jobs.status.url(job.id),
        {
            status: newStatus,
        },
        { preserveScroll: true },
    );
};

const showDeleteModal = ref(false);
const jobToDelete = ref<any>(null);
const processingDelete = ref(false);

const confirmDelete = (job: any) => {
    jobToDelete.value = job;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!jobToDelete.value) return;
    processingDelete.value = true;
    router.delete(admin.jobs.destroy.url(jobToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            jobToDelete.value = null;
        },
        onFinish: () => {
            processingDelete.value = false;
        },
    });
};

const duplicatingJobId = ref<number | null>(null);

const handleDuplicate = (job: any) => {
    duplicatingJobId.value = job.id;
    router.post(
        admin.jobs.duplicate.url(job.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                duplicatingJobId.value = null;
            },
        },
    );
};

const getStatusClass = (status: string) => {
    return (
        {
            published: 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600',
            draft: 'bg-slate-50 dark:bg-slate-800 text-slate-500',
            hidden: 'bg-rose-50 dark:bg-rose-900/20 text-rose-600',
        }[status] || 'bg-slate-50 text-slate-500'
    );
};
</script>

<template>
    <Head :title="__('Job Management')" />

    <AdminLayout>
        <div class="animate-fade-in space-y-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-6 text-start md:flex-row md:items-center">
                <div class="text-start">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Job Openings') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Post and manage recruitment opportunities.') }}</p>
                </div>
                <Link
                    :href="admin.jobs.create.url()"
                    class="flex items-center rounded-2xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-brand-600/20 transition-all hover:bg-brand-700 active:scale-95"
                >
                    <PlusCircle :size="18" class="me-2" /> {{ __('Create New Job') }}
                </Link>
            </div>

            <!-- Filters & Table Card -->
            <div class="overflow-hidden rounded-[2rem] border border-slate-200/60 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <!-- Filters -->
                <div class="flex flex-wrap gap-4 border-b border-slate-100 p-4 dark:border-slate-700">
                    <div class="relative w-full sm:w-64">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <Search :size="16" class="text-slate-400" />
                        </div>
                        <input
                            v-model="filters.search"
                            type="text"
                            :placeholder="__('Search jobs...')"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-4 pl-10 text-sm text-slate-700 focus:ring-2 focus:ring-brand-500/20 focus:outline-none dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-200"
                        />
                    </div>

                    <div class="min-w-[160px]">
                        <SearchableSelect
                            v-model="filters.department_id"
                            :options="departments.map((d) => ({ id: d.id, name: d.name }))"
                            :placeholder="__('All Departments')"
                            :search-placeholder="__('Search department...')"
                            clearable
                        />
                    </div>

                    <select
                        v-model="filters.status"
                        class="min-w-[140px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-brand-500/20 focus:outline-none dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-200"
                    >
                        <option value="">{{ __('All Status') }}</option>
                        <option value="published">{{ __('Published') }}</option>
                        <option value="draft">{{ __('Draft') }}</option>
                        <option value="hidden">{{ __('Hidden') }}</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-start">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-700/50 dark:bg-slate-800/20">
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Job Details') }}
                                </th>
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Department') }}
                                </th>
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Schedule & Type') }}
                                </th>
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Status') }}
                                </th>
                                <th class="px-6 py-4 text-end text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-start dark:divide-slate-800/50">
                            <tr
                                v-for="job in jobs.data"
                                :key="job.id"
                                class="group transition-all duration-200 hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/20"
                                        >
                                            <Briefcase :size="18" />
                                        </div>
                                        <div class="flex flex-col items-start text-start">
                                            <p class="text-sm leading-tight font-bold text-slate-900 dark:text-white">{{ job.title }}</p>
                                            <div class="mt-1.5 flex flex-col items-start gap-1.5">
                                                <span
                                                    class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 uppercase dark:bg-slate-700/50"
                                                    >{{ job.job_type }}</span
                                                >
                                                <div class="flex items-start gap-1 text-[10px] text-slate-400">
                                                    <MapPin :size="12" class="mt-0.5 shrink-0" />
                                                    <span class="text-start leading-tight">{{ job.location || 'Remote' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Building2 :size="14" class="text-slate-400" />
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{
                                            job.department?.name || 'Unassigned'
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <p class="text-xs font-medium text-slate-700 capitalize dark:text-slate-300">
                                            {{ job.employment_type.replace('-', ' ') }}
                                        </p>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                            <Calendar :size="12" />
                                            <span>Deadline: {{ new Date(job.deadline).toLocaleDateString() }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="rounded-lg px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase"
                                            :class="getStatusClass(job.status)"
                                        >
                                            {{ job.status }}
                                        </span>

                                        <!-- Quick Status Toggle -->
                                        <div class="flex items-center rounded-lg bg-slate-100 p-0.5 dark:bg-slate-700">
                                            <button
                                                @click="toggleStatus(job, 'published')"
                                                class="rounded-md p-1 transition-all"
                                                :class="
                                                    job.status === 'published'
                                                        ? 'bg-white text-emerald-600 shadow-sm dark:bg-slate-600'
                                                        : 'text-slate-400 hover:text-slate-600'
                                                "
                                                title="Publish"
                                            >
                                                <Eye :size="12" />
                                            </button>
                                            <button
                                                @click="toggleStatus(job, 'hidden')"
                                                class="rounded-md p-1 transition-all"
                                                :class="
                                                    job.status === 'hidden'
                                                        ? 'bg-white text-rose-600 shadow-sm dark:bg-slate-600'
                                                        : 'text-slate-400 hover:text-slate-600'
                                                "
                                                title="Hide"
                                            >
                                                <EyeOff :size="12" />
                                            </button>
                                            <button
                                                @click="toggleStatus(job, 'draft')"
                                                class="rounded-md p-1 transition-all"
                                                :class="
                                                    job.status === 'draft'
                                                        ? 'bg-white text-slate-600 shadow-sm dark:bg-slate-600'
                                                        : 'text-slate-400 hover:text-slate-600'
                                                "
                                                title="Draft"
                                            >
                                                <FileText :size="12" />
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="admin.jobs.pipeline.url(job.id)"
                                            class="relative flex h-8 w-8 items-center justify-center rounded-xl transition-all"
                                            :class="
                                                job.pipeline_enabled
                                                    ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/30'
                                                    : 'bg-slate-50 text-slate-400 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700'
                                            "
                                            :title="
                                                job.pipeline_enabled
                                                    ? __('Pipeline Active') + ' (' + job.pipeline_stages_count + ' stages)'
                                                    : __('Pipeline Disabled')
                                            "
                                        >
                                            <GitBranch :size="14" />
                                            <span
                                                v-if="job.pipeline_enabled && job.pipeline_stages_count > 0"
                                                class="absolute -top-1 -right-1 flex h-3.5 min-w-3.5 items-center justify-center rounded-full bg-emerald-500 px-1 text-[8px] font-bold text-white"
                                            >
                                                {{ job.pipeline_stages_count }}
                                            </span>
                                        </Link>
                                        <button
                                            @click="handleDuplicate(job)"
                                            :disabled="duplicatingJobId === job.id"
                                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition-all hover:bg-indigo-50 hover:text-indigo-600 disabled:opacity-50 dark:bg-slate-800 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-400"
                                            title="Duplicate Job"
                                        >
                                            <Copy :size="14" :class="{ 'animate-pulse': duplicatingJobId === job.id }" />
                                        </button>
                                        <Link
                                            :href="admin.jobs.edit.url(job.id)"
                                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition-all hover:bg-brand-50 hover:text-brand-600 dark:bg-slate-800 dark:hover:bg-brand-900/20"
                                        >
                                            <Pencil :size="14" />
                                        </Link>
                                        <button
                                            @click="confirmDelete(job)"
                                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-500 dark:bg-slate-800 dark:hover:bg-rose-900/20"
                                        >
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="jobs.links.length > 3" class="flex justify-end border-t border-slate-100 p-6 dark:border-slate-700">
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

            <ConfirmationModal
                :show="showDeleteModal"
                :title="__('Delete Job Posting?')"
                :message="__('Are you sure you want to delete this job posting? Historical data may be lost.')"
                :confirmText="__('Yes, Delete')"
                :cancelText="__('No, Cancel')"
                type="danger"
                :processing="processingDelete"
                @close="showDeleteModal = false"
                @confirm="handleDelete"
            />
        </div>
    </AdminLayout>
</template>
