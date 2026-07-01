<script setup lang="ts">
import JobApplyPanel from '@/Components/Guest/JobApplyPanel.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Briefcase, Calendar, ChevronRight, Gauge, Home, MapPin } from 'lucide-vue-next';

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    job: {
        id: number;
        slug: string;
        title: string;
        description: string;
        salary_type: string;
        salary_period: string;
        min_salary: string;
        max_salary: string;
        min_experience: string;
        max_experience: string;
        job_type: string;
        location: string;
        deadline: string;
        employment_type: string;
        apply_instructions?: string;
        is_cv_required?: boolean;
        department?: { name: string };
        tags?: Array<{ id: number; name: string }>;
    };
    has_applied?: boolean;
}>();

const page = usePage();
const businessSettings = computed(() => (page.props.branding as any).business_settings || {});
const currencySymbol = computed(() => businessSettings.value.currency_symbol || '$');
const timezone = computed(() => businessSettings.value.timezone || 'UTC');

const formatNumber = (value: string | number) => {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    if (isNaN(num)) {
        return value;
    }
    // Maximum 2 decimal places, remove trailing zeros
    return parseFloat(num.toFixed(2)).toString();
};

const formatDate = (dateString: string) => {
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            timeZone: timezone.value,
        }).format(date);
    } catch (e) {
        return new Date(dateString).toLocaleDateString();
    }
};
</script>

<template>
    <Head :title="__(job.title)" />
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="animate-fade-in mb-6 flex items-center space-x-2 text-sm">
                <Link
                    href="/"
                    class="flex items-center text-gray-500 transition-colors hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                >
                    <Home class="mr-1 h-4 w-4" />
                    {{ __('Home') }}
                </Link>
                <ChevronRight class="h-4 w-4 text-gray-400" />
                <span class="font-medium text-gray-900 dark:text-white">{{ __(job.title) }}</span>
            </nav>

            <div class="grid grid-cols-1 gap-12 py-6 lg:grid-cols-3">
                <!-- Left Column: Job Details -->
                <div class="space-y-8 lg:col-span-2">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl dark:text-white">
                            {{ job.title }}
                        </h1>
                        <div class="mt-4 flex flex-wrap gap-4 text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <Briefcase class="h-4 w-4" />
                                {{ job.department?.name || 'General' }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <MapPin class="h-4 w-4" />
                                {{ job.location || 'Remote' }}
                            </div>
                            <div class="flex items-center gap-1.5 font-medium text-primary-600 capitalize dark:text-primary-400">
                                {{ job.employment_type }}
                            </div>
                        </div>
                    </div>

                    <div class="prose max-w-none prose-blue dark:prose-invert">
                        <h3 class="text-xl font-bold">About the Role</h3>
                        <div v-html="job.description" class="text-gray-600 dark:text-gray-400"></div>
                    </div>

                    <!-- Skills & Tags -->
                    <div v-if="job.tags && job.tags.length > 0" class="space-y-4 border-t border-gray-200 pt-8 dark:border-gray-800">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Required Skills & Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in job.tags"
                                :key="tag.id"
                                class="inline-flex items-center rounded-full bg-primary-50 px-3 py-1 text-sm font-medium text-primary-700 ring-1 ring-primary-700/10 ring-inset dark:bg-primary-900/20 dark:text-primary-400 dark:ring-primary-400/20"
                            >
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 border-t border-gray-200 pt-8 sm:grid-cols-2 dark:border-gray-800">
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 dark:text-white">Experience</h4>
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <Gauge class="h-5 w-5" />
                                {{ formatNumber(job.min_experience) }} - {{ formatNumber(job.max_experience) }} years
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 dark:text-white">Salary</h4>
                            <div class="text-gray-600 dark:text-gray-400">
                                <template v-if="job.salary_type === 'negotiable'"> Negotiable </template>
                                <template v-else>
                                    {{ currencySymbol }}{{ formatNumber(job.min_salary) }} - {{ currencySymbol }}{{ formatNumber(job.max_salary) }}
                                    <span class="capitalize">/ {{ job.salary_period || 'month' }}</span>
                                </template>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 dark:text-white">Job Type</h4>
                            <div class="text-gray-600 capitalize dark:text-gray-400">
                                {{ job.job_type }}
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 dark:text-white">Deadline</h4>
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <Calendar class="h-5 w-5" />
                                {{ formatDate(job.deadline) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Apply Panel -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <JobApplyPanel :job="job" :has-applied="has_applied" />
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
