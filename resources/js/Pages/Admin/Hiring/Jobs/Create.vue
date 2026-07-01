<script setup lang="ts">
import RichTextEditor from '@/Components/Common/RichTextEditor.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Briefcase, Info, MapPin, Save, Tag } from 'lucide-vue-next';
import { PropType } from 'vue';

const props = defineProps({
    departments: Array as PropType<any[]>,
    tags: Array as PropType<any[]>,
});

const form = useForm({
    department_id: '',
    title: '',
    description: '',
    salary_type: 'negotiable',
    salary_period: 'monthly',
    min_salary: null as number | null,
    max_salary: null as number | null,
    min_experience: 0,
    max_experience: 1,
    job_type: 'onsite',
    location: '',
    deadline: '',
    employment_type: 'full-time',
    internship_duration: '',
    contract_duration: '',
    working_hours: '',
    status: 'draft',
    tags: [] as number[],
});

const submit = () => {
    form.post(admin.jobs.store.url());
};

const toggleTag = (tagId: number) => {
    const index = form.tags.indexOf(tagId);
    if (index > -1) {
        form.tags.splice(index, 1);
    } else {
        form.tags.push(tagId);
    }
};
</script>

<template>
    <Head :title="__('Create Job Opening')" />

    <AdminLayout>
        <div class="animate-fade-in mx-auto max-w-5xl space-y-6 text-start">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-6 text-start md:flex-row md:items-center">
                <div>
                    <Link
                        :href="admin.jobs.index.url()"
                        class="mb-2 flex items-center text-sm font-bold text-slate-500 transition-colors hover:text-brand-600"
                    >
                        <ArrowLeft :size="16" class="me-1" /> {{ __('Back to Jobs') }}
                    </Link>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Create Job Opening') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Fill in the details to post a new recruitment opportunity.') }}
                    </p>
                </div>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="flex items-center rounded-2xl bg-brand-600 px-8 py-3 text-sm font-bold text-white shadow-xl shadow-brand-600/20 transition-all hover:bg-brand-700 active:scale-95 disabled:opacity-50"
                >
                    <Save :size="18" class="me-2" /> {{ __('Save & Publish') }}
                </button>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Form Column -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- General Information -->
                    <div class="space-y-5 rounded-[2rem] border border-slate-200/60 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div class="mb-4 flex items-center gap-2 border-b border-slate-50 pb-2 dark:border-slate-700/50">
                            <Info :size="18" class="text-brand-600" />
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Job Basics') }}</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                    >{{ __('Job Title') }} <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.title"
                                    type="text"
                                    :placeholder="__('e.g. Senior Frontend Developer')"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                    :class="{ 'border-rose-500': form.errors.title }"
                                />
                                <p v-if="form.errors.title" class="mt-1 text-xs font-medium text-rose-500">{{ form.errors.title }}</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                    >{{ __('Department') }} <span class="text-rose-500">*</span></label
                                >
                                <select
                                    v-model="form.department_id"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                    :class="{ 'border-rose-500': form.errors.department_id }"
                                >
                                    <option value="">{{ __('Select Department') }}</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <p v-if="form.errors.department_id" class="mt-1 text-xs font-medium text-rose-500">{{ form.errors.department_id }}</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                    >{{ __('Employment Type') }} <span class="text-rose-500">*</span></label
                                >
                                <select
                                    v-model="form.employment_type"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                >
                                    <option value="full-time">{{ __('Full-time') }}</option>
                                    <option value="part-time">{{ __('Part-time') }}</option>
                                    <option value="contract">{{ __('Contract') }}</option>
                                    <option value="internship">{{ __('Internship') }}</option>
                                </select>
                                <p v-if="form.errors.employment_type" class="mt-1 text-xs font-medium text-rose-500">
                                    {{ form.errors.employment_type }}
                                </p>
                            </div>

                            <!-- Conditional Fields for Employment Type -->
                            <div v-if="form.employment_type === 'internship'" class="animate-slide-down md:col-span-2">
                                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                    >{{ __('Internship Duration') }} <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.internship_duration"
                                    type="text"
                                    :placeholder="__('e.g. 3 Months, 6 Months')"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                    :class="{ 'border-rose-500': form.errors.internship_duration }"
                                />
                                <p v-if="form.errors.internship_duration" class="mt-1 text-xs font-medium text-rose-500">
                                    {{ form.errors.internship_duration }}
                                </p>
                            </div>

                            <div v-if="form.employment_type === 'contract'" class="animate-slide-down md:col-span-2">
                                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                    >{{ __('Contract Duration') }} <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.contract_duration"
                                    type="text"
                                    :placeholder="__('e.g. 1 Year, 6 Months Project Based')"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                    :class="{ 'border-rose-500': form.errors.contract_duration }"
                                />
                                <p v-if="form.errors.contract_duration" class="mt-1 text-xs font-medium text-rose-500">
                                    {{ form.errors.contract_duration }}
                                </p>
                            </div>

                            <div v-if="form.employment_type === 'part-time'" class="animate-slide-down md:col-span-2">
                                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                    >{{ __('Working Hours') }} <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.working_hours"
                                    type="text"
                                    :placeholder="__('e.g. 20 hours/week, Mon-Fri 4pm-8pm')"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                    :class="{ 'border-rose-500': form.errors.working_hours }"
                                />
                                <p v-if="form.errors.working_hours" class="mt-1 text-xs font-medium text-rose-500">{{ form.errors.working_hours }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                >{{ __('Job Description') }} <span class="text-rose-500">*</span></label
                            >
                            <RichTextEditor v-model="form.description" />
                            <p v-if="form.errors.description" class="mt-1 text-xs font-medium text-rose-500">{{ form.errors.description }}</p>
                        </div>
                    </div>

                    <!-- Compensation & Experience -->
                    <div class="space-y-5 rounded-[2rem] border border-slate-200/60 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div class="mb-4 flex items-center gap-2 border-b border-slate-50 pb-2 dark:border-slate-700/50">
                            <Briefcase :size="18" class="text-brand-600" />
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Compensation & Experience') }}</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Salary Type') }}</label>
                                    <div class="flex gap-4">
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/50 dark:hover:bg-slate-800"
                                        >
                                            <input
                                                type="radio"
                                                v-model="form.salary_type"
                                                value="negotiable"
                                                class="h-4 w-4 border-slate-300 bg-white text-brand-600 focus:ring-brand-500"
                                            />
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Negotiable') }}</span>
                                        </label>
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/50 dark:hover:bg-slate-800"
                                        >
                                            <input
                                                type="radio"
                                                v-model="form.salary_type"
                                                value="non-negotiable"
                                                class="h-4 w-4 border-slate-300 bg-white text-brand-600 focus:ring-brand-500"
                                            />
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Fixed Range') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Salary Period') }}</label>
                                    <div class="flex gap-4">
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/50 dark:hover:bg-slate-800"
                                        >
                                            <input
                                                type="radio"
                                                v-model="form.salary_period"
                                                value="monthly"
                                                class="h-4 w-4 border-slate-300 bg-white text-brand-600 focus:ring-brand-500"
                                            />
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Monthly') }}</span>
                                        </label>
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/50 dark:hover:bg-slate-800"
                                        >
                                            <input
                                                type="radio"
                                                v-model="form.salary_period"
                                                value="yearly"
                                                class="h-4 w-4 border-slate-300 bg-white text-brand-600 focus:ring-brand-500"
                                            />
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Yearly') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="form.salary_type === 'non-negotiable'" class="animate-slide-down grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="mb-1 block text-[11px] font-bold text-slate-500 uppercase">{{ __('Min Salary') }}</label>
                                        <input
                                            v-model="form.min_salary"
                                            type="number"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-bold text-slate-500 uppercase">{{ __('Max Salary') }}</label>
                                        <input
                                            v-model="form.max_salary"
                                            type="number"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                        />
                                    </div>
                                    <p v-if="form.errors.min_salary || form.errors.max_salary" class="col-span-2 text-xs text-rose-500">
                                        {{ form.errors.min_salary || form.errors.max_salary }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300">{{
                                        __('Experience (Years)')
                                    }}</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="mb-1 block text-[11px] font-bold text-slate-500 uppercase">{{ __('Min') }}</label>
                                            <input
                                                v-model="form.min_experience"
                                                type="number"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                            />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[11px] font-bold text-slate-500 uppercase">{{ __('Max') }}</label>
                                            <input
                                                v-model="form.max_experience"
                                                type="number"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <p v-if="form.errors.min_experience || form.errors.max_experience" class="text-xs font-medium text-rose-500">
                                    {{ form.errors.min_experience || form.errors.max_experience }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side Panels Column -->
                <div class="space-y-6">
                    <!-- Publishing Status -->
                    <div class="space-y-4 rounded-[2rem] border border-slate-200/60 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <h3 class="border-b border-slate-50 pb-2 font-bold text-slate-900 dark:border-slate-700/50 dark:text-white">
                            {{ __('Publication') }}
                        </h3>

                        <div>
                            <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Post Status') }}</label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                            >
                                <option value="draft">{{ __('Draft') }}</option>
                                <option value="published">{{ __('Published') }}</option>
                                <option value="hidden">{{ __('Hidden / Closed') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                >{{ __('Application Deadline') }} <span class="text-rose-500">*</span></label
                            >
                            <input
                                v-model="form.deadline"
                                type="date"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                :class="{ 'border-rose-500': form.errors.deadline }"
                            />
                            <p v-if="form.errors.deadline" class="mt-1 text-xs font-medium text-rose-500">{{ form.errors.deadline }}</p>
                        </div>
                    </div>

                    <!-- Work Location -->
                    <div class="space-y-4 rounded-[2rem] border border-slate-200/60 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <h3 class="border-b border-slate-50 pb-2 font-bold text-slate-900 dark:border-slate-700/50 dark:text-white">
                            {{ __('Work Arrangement') }}
                        </h3>

                        <div class="space-y-2">
                            <div
                                v-for="type in ['onsite', 'remote', 'hybrid']"
                                :key="type"
                                @click="form.job_type = type"
                                class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-100 p-3 transition-all hover:bg-slate-50 dark:border-slate-700/50 dark:hover:bg-slate-700/30"
                                :class="{ 'border-brand-500 bg-brand-50/30 dark:bg-brand-900/10': form.job_type === type }"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-2 rounded-full" :class="form.job_type === type ? 'bg-brand-500' : 'bg-slate-300'"></div>
                                    <span
                                        class="text-sm font-bold capitalize"
                                        :class="form.job_type === type ? 'text-brand-700 dark:text-brand-400' : 'text-slate-600 dark:text-slate-400'"
                                        >{{ type }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div v-if="form.job_type !== 'remote' && form.job_type" class="animate-slide-down">
                            <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-300"
                                >{{ __('Location') }} <span class="text-rose-500">*</span></label
                            >
                            <div class="relative">
                                <MapPin :size="16" class="absolute top-3.5 left-4 text-slate-400" />
                                <input
                                    v-model="form.location"
                                    type="text"
                                    :placeholder="__('City, Country')"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pr-4 pl-11 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-900/50"
                                    :class="{ 'border-rose-500': form.errors.location }"
                                />
                            </div>
                            <p v-if="form.errors.location" class="mt-1 text-xs font-medium text-rose-500">{{ form.errors.location }}</p>
                        </div>
                    </div>

                    <!-- Skills & Tags -->
                    <div class="space-y-4 rounded-[2rem] border border-slate-200/60 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div class="flex items-center justify-between border-b border-slate-50 pb-2 dark:border-slate-700/50">
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Skills & Tags') }}</h3>
                            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-600 dark:bg-brand-900/30">{{
                                form.tags.length
                            }}</span>
                        </div>

                        <div class="custom-scrollbar flex max-h-50 flex-wrap gap-2 overflow-y-auto pr-2">
                            <button
                                v-for="tag in tags"
                                :key="tag.id"
                                type="button"
                                @click="toggleTag(tag.id)"
                                class="flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-bold transition-all"
                                :class="
                                    form.tags.includes(tag.id)
                                        ? 'border-brand-600 bg-brand-600 text-white shadow-lg shadow-brand-600/20'
                                        : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-brand-300 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400'
                                "
                            >
                                <Tag :size="12" />
                                {{ tag.name }}
                            </button>
                        </div>
                        <p v-if="tags && tags.length === 0" class="text-xs text-slate-400 italic">
                            {{ __('No tags available. create tags first.') }}
                        </p>
                    </div>
                </div>
            </form>

            <!-- Floating Error Notification -->
            <div
                v-if="Object.keys(form.errors).length > 0"
                class="animate-slide-up fixed right-6 bottom-6 z-50 flex items-center gap-3 rounded-2xl bg-rose-600 px-6 py-4 text-white shadow-2xl shadow-rose-600/30"
            >
                <AlertCircle :size="20" />
                <div>
                    <p class="text-sm font-bold">{{ __('Please fix the errors') }}</p>
                    <p class="text-[11px] opacity-90">{{ __('There are some issues with your job posting details.') }}</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(var(--brand-primary-rgb), 0.2);
    border-radius: 10px;
}
@keyframes slide-down {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-slide-down {
    animation: slide-down 0.3s ease-out;
}
@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>
