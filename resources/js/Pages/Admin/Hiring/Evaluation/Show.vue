<script setup lang="ts">
import { wTrans as __ } from '@/Core/i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, Link, router, usePoll } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle,
    ChevronRight,
    ExternalLink,
    Eye,
    FileCheck,
    FileText,
    Link2,
    Loader2,
    Mail,
    Play,
    RotateCcw,
    Square,
    User,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import CandidateRejectionModal from './Components/CandidateRejectionModal.vue';
import StageSubmissionDrawer from './Components/StageSubmissionDrawer.vue';

interface Job {
    id: number;
    title: string;
    hiring_status: string;
    hiring_status_raw: string;
    department: { name: string } | null;
}

interface Stage {
    id: number;
    title: string;
    sort_order: number;
    type?: string;
    subtype?: string;
    is_system?: boolean;
    system_key?: string | null;
    send_mail_on_trigger?: boolean;
    has_email_template?: boolean;
    assessment_url?: string | null;
    config?: any;
}

interface Candidate {
    id: number;
    name: string;
    email: string;
    cover_letter: string | null;
    resume: { url: string; name: string } | null;
    current_stage_id: number;
    status: string;
    completed_stage_ids: number[];
    is_selected_by_criteria: boolean | null;
    ats_state?: string;
    ats_score?: number | null;
    ats_rank?: number | null;
    ats_breakdown?: any[];
    stage_submission?: any;
    assessment_url?: string | null;
}

const props = defineProps<{
    job: Job;
    stages: Stage[];
    candidates: Record<number, Candidate[]>;
    global_rejection_email?: {
        subject: string | null;
        body: string | null;
    };
}>();

const selectedCandidates = ref<number[]>([]);
const selectedStageId = ref<number | null>(null);
const sendMail = ref(false);
const isRunningAts = ref(false);
const localToast = ref<{ message: string; type: 'success' | 'error' } | null>(null);
const showStatusMenu = ref(false);

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    showLocalToast(__('Link copied to clipboard!'));
};

const isSubmissionDrawerOpen = ref(false);
const selectedCandidateForSubmission = ref<Candidate | null>(null);

const openSubmissionDrawer = (candidate: Candidate) => {
    selectedCandidateForSubmission.value = candidate;
    isSubmissionDrawerOpen.value = true;
};

const showLocalToast = (message: string, type: 'success' | 'error' = 'success') => {
    localToast.value = { message, type };
    setTimeout(() => {
        localToast.value = null;
    }, 5000);
};

const activeStage = computed(() => {
    return props.stages.find((s) => s.id === selectedStageId.value) || props.stages[0];
});

const candidatesInActiveStage = computed(() => {
    return props.candidates[activeStage.value?.id] || [];
});

const hasActiveJobs = computed(() => {
    return Object.values(props.candidates)
        .flat()
        .some((c) => c.ats_state === 'processing' || c.ats_state === 'pending');
});

const hasActiveJobsForActiveStage = computed(() => {
    return candidatesInActiveStage.value.some((c) => c.ats_state === 'processing' || c.ats_state === 'pending');
});

const { start, stop } = usePoll(
    3000,
    {
        only: ['candidates'],
    },
    {
        keepAlive: true,
        autoStart: hasActiveJobs.value,
    },
);

watch(hasActiveJobs, (active) => {
    if (active) {
        start();
    } else {
        stop();
    }
});

const isLastStage = computed(() => {
    if (!activeStage.value || props.stages.length === 0) return false;
    return activeStage.value.sort_order === props.stages[props.stages.length - 1].sort_order;
});

const nextStage = computed(() => {
    if (!activeStage.value || isLastStage.value) return null;
    return props.stages.find((s) => s.sort_order > activeStage.value.sort_order);
});

const isAssessmentStage = computed(() => {
    return activeStage.value?.type === 'assessment' || activeStage.value?.type === 'interview' || activeStage.value?.subtype === 'online_quiz';
});

const toggleSelection = (candidateId: number) => {
    const index = selectedCandidates.value.indexOf(candidateId);
    if (index === -1) {
        selectedCandidates.value.push(candidateId);
    } else {
        selectedCandidates.value.splice(index, 1);
    }
};

const selectAll = () => {
    const selectableCandidates = candidatesInActiveStage.value.filter((c) => c.status !== 'rejected');
    if (selectedCandidates.value.length === selectableCandidates.length && selectableCandidates.length > 0) {
        selectedCandidates.value = [];
    } else {
        selectedCandidates.value = selectableCandidates.map((c) => c.id);
    }
};

const moveCandidates = () => {
    if (selectedCandidates.value.length === 0 || !nextStage.value) return;

    if (isLastStage.value) {
        if (!confirm('This is the final stage. Moving candidates will mark them as hired. Proceed?')) {
            return;
        }
    }

    router.post(
        admin.evaluations.move.url(props.job.id),
        {
            candidate_ids: selectedCandidates.value,
            from_stage_id: activeStage.value.id,
            to_stage_id: nextStage.value.id,
            send_mail: sendMail.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedCandidates.value = [];
                sendMail.value = false;
            },
        },
    );
};

// Application details side panel
const activeCandidate = ref<Candidate | null>(null);
const openCandidateDetails = (candidate: Candidate) => {
    activeCandidate.value = candidate;
};
const closeCandidateDetails = () => {
    activeCandidate.value = null;
};

const getCandidatesCount = (stageId: number) => {
    return (props.candidates[stageId] || []).length;
};

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
};

// Manual Override Logic
const handleOverride = (candidateId: number, pass: boolean) => {
    const reason = window.prompt(__('Please provide a reason for the manual override:'));
    if (!reason) return;

    router.post(
        admin.evaluations.override.url(props.job.id),
        {
            candidate_id: candidateId,
            pipeline_stage_id: activeStage.value.id,
            overridden_passed: pass,
            reason: reason,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeCandidateDetails();
            },
        },
    );
};

const runAts = () => {
    if (!activeStage.value || activeStage.value.type !== 'sorting') return;

    isRunningAts.value = true;
    router.post(
        admin.evaluations.run_ats.url({ job: props.job.id, stage: activeStage.value.id }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showLocalToast(__('Scanning and scoring started for :count candidates.', { count: String(candidatesInActiveStage.value.length) }));
            },
            onFinish: () => {
                isRunningAts.value = false;
            },
        },
    );
};

const stopAts = () => {
    if (!activeStage.value || activeStage.value.type !== 'sorting') return;

    router.post(
        admin.evaluations.stop_ats.url({ job: props.job.id, stage: activeStage.value.id }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showLocalToast(__('ATS scanning stopped and reset.'));
            },
        },
    );
};

const updateHiringStatus = (status: string) => {
    showStatusMenu.value = false;
    router.post(
        admin.evaluations.hiring_status.url(props.job.id),
        { hiring_status: status },
        {
            preserveScroll: true,
            onSuccess: () => {
                showLocalToast(__('Job status updated successfully.'));
            },
        },
    );
};

const isRejectionModalOpen = ref(false);
const candidateForRejection = ref<Candidate | null>(null);

const openRejectionModal = (candidate: Candidate) => {
    candidateForRejection.value = candidate;
    isRejectionModalOpen.value = true;
};

const updateCandidateStatus = (candidate: Candidate, status: 'in_progress' | 'rejected') => {
    if (status === 'rejected') {
        openRejectionModal(candidate);
        return;
    }

    router.put(
        admin.evaluations.candidates.status.url(candidate.id),
        { status },
        {
            preserveScroll: true,
            onSuccess: () => {
                showLocalToast(__('Candidate status updated to Evaluating.'));
            },
        },
    );
};

const hiringStatusColor = computed(() => {
    switch (props.job.hiring_status_raw) {
        case 'hired_closed':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800';
        case 'not_hired_closed':
            return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800';
        case 'continue_hiring':
            return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800';
        default:
            return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800';
    }
});
</script>

<template>
    <Head :title="`Evaluate: ${job.title}`" />

    <AdminLayout>
        <!-- Local Toast -->
        <div v-if="localToast" class="animate-in fade-in slide-in-from-right-4 fixed top-24 right-6 z-60 duration-300">
            <div
                class="flex items-center gap-3 rounded-xl border-l-[6px] bg-white p-4 shadow-xl dark:bg-slate-900"
                :class="localToast.type === 'success' ? 'border-emerald-500' : 'border-rose-500'"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full"
                    :class="localToast.type === 'success' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'"
                >
                    <CheckCircle v-if="localToast.type === 'success'" :size="18" />
                    <X v-else :size="18" />
                </div>
                <div class="pr-8">
                    <p class="text-sm font-bold" :class="localToast.type === 'success' ? 'text-emerald-700' : 'text-rose-700'">
                        {{ localToast.type === 'success' ? __('Success') : __('Error') }}
                    </p>
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ localToast.message }}</p>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="border-b border-slate-200 bg-white px-4 py-4 md:px-8 dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto max-w-7xl">
                <Link
                    :href="admin.evaluations.index.url()"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                >
                    <ArrowLeft :size="16" />
                    {{ __('Back to Evaluations') }}
                </Link>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ job.title }}</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            <span v-if="job.department" class="font-medium text-slate-700 dark:text-slate-300">{{ job.department.name }}</span>
                            <span v-else class="text-slate-400 italic">{{ __('No Department') }}</span>
                            <span class="mx-2">•</span>
                            <span
                                class="font-bold"
                                :class="
                                    hiringStatusColor
                                        .split(' ')
                                        .filter((c) => c.startsWith('text-'))
                                        .join(' ')
                                "
                                >{{ job.hiring_status }}</span
                            >
                        </p>
                    </div>

                    <!-- Hiring Status Dropdown -->
                    <div class="relative">
                        <button
                            @click="showStatusMenu = !showStatusMenu"
                            class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition-all"
                            :class="hiringStatusColor"
                        >
                            {{ job.hiring_status }}
                            <ChevronRight :size="14" class="rotate-90 transition-transform" :class="{ '-rotate-90': showStatusMenu }" />
                        </button>

                        <div
                            v-if="showStatusMenu"
                            class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800"
                        >
                            <button
                                @click="updateHiringStatus('hired_closed')"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-medium transition-colors hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                            >
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-slate-700 dark:text-slate-200">{{ __('Hired & Closed') }}</span>
                            </button>
                            <button
                                @click="updateHiringStatus('continue_hiring')"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-medium transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                            >
                                <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                <span class="text-slate-700 dark:text-slate-200">{{ __('Continue Hiring') }}</span>
                            </button>
                            <button
                                @click="updateHiringStatus('not_hired_closed')"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-medium transition-colors hover:bg-rose-50 dark:hover:bg-rose-900/20"
                            >
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                <span class="text-slate-700 dark:text-slate-200">{{ __('Not Hired & Closed') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl p-4 md:p-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
                <!-- Stages Sidebar -->
                <div class="space-y-2 lg:col-span-1">
                    <h3 class="text-sm font-bold tracking-wider text-slate-500 uppercase">{{ __('Pipeline Stages') }}</h3>
                    <div class="flex flex-col gap-2">
                        <button
                            v-for="(stage, index) in stages"
                            :key="stage.id"
                            @click="
                                selectedStageId = stage.id;
                                selectedCandidates = [];
                                scrollToTop();
                            "
                            class="group relative flex min-h-18 items-center justify-between rounded-xl border px-5 py-4 transition-all"
                            :class="
                                selectedStageId === stage.id || (!selectedStageId && index === 0)
                                    ? 'border-brand-200 bg-brand-50/50 shadow-sm dark:border-brand-900/30 dark:bg-brand-900/10'
                                    : 'border-transparent hover:bg-slate-50 dark:hover:bg-slate-800/50'
                            "
                        >
                            <div class="flex items-start gap-4 overflow-hidden pt-0.5">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sm font-bold transition-all"
                                    :class="
                                        selectedStageId === stage.id || (!selectedStageId && index === 0)
                                            ? 'bg-brand-600 text-white shadow-md'
                                            : 'bg-slate-100 text-slate-500 group-hover:bg-brand-100 group-hover:text-brand-600 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                >
                                    {{ index + 1 }}
                                </div>
                                <div class="flex flex-col items-start overflow-hidden pt-1">
                                    <span
                                        class="truncate text-sm font-bold transition-colors"
                                        :class="
                                            selectedStageId === stage.id || (!selectedStageId && index === 0)
                                                ? 'text-brand-700 dark:text-brand-400'
                                                : 'text-slate-600 group-hover:text-slate-900 dark:text-slate-400 dark:group-hover:text-slate-200'
                                        "
                                        :title="stage.title"
                                    >
                                        {{ stage.title }}
                                    </span>
                                </div>
                            </div>

                            <span
                                class="flex h-6 min-w-6 items-center justify-center rounded-full px-1.5 text-[11px] font-extrabold transition-all"
                                :class="
                                    selectedStageId === stage.id || (!selectedStageId && index === 0)
                                        ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-400'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                "
                            >
                                {{ getCandidatesCount(stage.id) }}
                            </span>

                            <!-- Active Indicator -->
                            <div
                                v-if="selectedStageId === stage.id || (!selectedStageId && index === 0)"
                                class="absolute top-1/2 -left-1 h-8 w-1.5 -translate-y-1/2 rounded-full bg-brand-600 shadow-lg shadow-brand-600/50"
                            ></div>
                        </button>
                    </div>
                </div>

                <!-- Candidates List -->
                <div class="lg:col-span-3">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div
                            class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/50 px-6 py-4 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800 dark:bg-slate-800/20"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-lg font-extrabold shadow-sm dark:bg-slate-800 dark:text-white"
                                >
                                    {{ getCandidatesCount(activeStage?.id || 0) }}
                                </div>
                                <div class="min-w-0">
                                    <h2 class="truncate text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">
                                        {{ activeStage?.title }}
                                    </h2>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center gap-3">
                                <button
                                    v-if="activeStage?.type === 'sorting' && candidatesInActiveStage.length > 0"
                                    @click="runAts"
                                    :disabled="hasActiveJobsForActiveStage"
                                    class="flex items-center gap-2 rounded-xl bg-amber-100 px-4 py-2 text-sm font-bold text-amber-700 transition-all hover:bg-amber-200 disabled:opacity-50 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
                                    :title="__('Scan Resumes & Score Candidates')"
                                >
                                    <Loader2 v-if="hasActiveJobsForActiveStage" :size="16" class="animate-spin" />
                                    <Play v-else :size="16" />
                                    {{ hasActiveJobsForActiveStage ? __('Processing...') : __('Run ATS Sort') }}
                                </button>

                                <button
                                    v-if="activeStage?.type === 'sorting' && hasActiveJobsForActiveStage"
                                    @click="stopAts"
                                    class="flex items-center gap-2 rounded-xl bg-rose-100 px-4 py-2 text-sm font-bold text-rose-700 transition-all hover:bg-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-900/50"
                                    :title="__('Force Stop Scanning')"
                                >
                                    <Square :size="16" />
                                    {{ __('Stop') }}
                                </button>

                                <div
                                    class="hidden h-6 w-px bg-slate-200 lg:block dark:bg-slate-700"
                                    v-if="activeStage?.type === 'sorting' && !isLastStage && candidatesInActiveStage.length > 0"
                                ></div>

                                <template v-if="!isLastStage && candidatesInActiveStage.length > 0">
                                    <div class="flex items-center gap-2">
                                        <label
                                            v-if="(nextStage?.has_email_template || nextStage?.send_mail_on_trigger) && selectedCandidates.length > 0"
                                            class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                        >
                                            <input
                                                type="checkbox"
                                                v-model="sendMail"
                                                class="h-4 w-4 rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800"
                                            />
                                            {{ __('Send Email') }}
                                        </label>

                                        <button
                                            @click="moveCandidates"
                                            :disabled="selectedCandidates.length === 0"
                                            class="flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white transition-all hover:bg-brand-700 disabled:opacity-50 dark:bg-brand-500 dark:hover:bg-brand-600"
                                        >
                                            {{ __('Move Selected to Next') }}
                                            <ChevronRight :size="16" />
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div
                            v-if="candidatesInActiveStage.length === 0"
                            class="flex flex-col items-center justify-center p-12 text-center text-slate-500"
                        >
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 dark:bg-slate-800">
                                <User :size="32" class="text-slate-400" />
                            </div>
                            <p class="mt-4 text-base font-medium text-slate-900 dark:text-white">{{ __('No candidates in this stage') }}</p>
                            <p class="mt-1 text-sm">{{ __('Candidates will appear here once they are moved from the previous stage.') }}</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-800/50">
                                    <tr>
                                        <th
                                            scope="col"
                                            class="w-10 px-4 py-3 text-left text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                            v-if="!isLastStage"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    selectedCandidates.length ===
                                                        candidatesInActiveStage.filter((c) => c.status !== 'rejected').length &&
                                                    candidatesInActiveStage.filter((c) => c.status !== 'rejected').length > 0
                                                "
                                                @change="selectAll"
                                                class="rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800 dark:ring-offset-slate-900"
                                            />
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-4 py-3 text-left text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('Candidate') }}
                                        </th>
                                        <th
                                            scope="col"
                                            v-if="activeStage?.type === 'sorting'"
                                            class="px-4 py-3 text-left text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('ATS Score') }}
                                        </th>
                                        <th
                                            scope="col"
                                            v-if="isAssessmentStage"
                                            class="px-4 py-3 text-left text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('Assessment') }}
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-4 py-3 text-left text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('Status') }}
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-4 py-3 text-right text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                    <tr
                                        v-for="candidate in candidatesInActiveStage"
                                        :key="candidate.id"
                                        class="group border-b border-slate-50 transition-all last:border-0 hover:bg-slate-50/80 dark:border-slate-800/50 dark:hover:bg-slate-800/30"
                                        :class="{
                                            'bg-brand-50/30 dark:bg-brand-900/10': selectedCandidates.includes(candidate.id),
                                            'border-l-4 border-l-emerald-500 bg-emerald-50/20 dark:bg-emerald-900/5':
                                                !selectedCandidates.includes(candidate.id) &&
                                                candidate.is_selected_by_criteria === true &&
                                                candidate.status !== 'rejected',
                                            'opacity-60 grayscale-[0.5]': candidate.status === 'rejected',
                                        }"
                                    >
                                        <td class="px-4 py-4 whitespace-nowrap" v-if="!isLastStage">
                                            <input
                                                type="checkbox"
                                                :checked="selectedCandidates.includes(candidate.id)"
                                                :disabled="candidate.status === 'rejected'"
                                                @change="toggleSelection(candidate.id)"
                                                class="rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:ring-offset-slate-900"
                                            />
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ candidate.name }}</div>
                                                    <div class="flex items-center gap-2">
                                                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ candidate.email }}</div>
                                                        <template v-if="candidate.assessment_url">
                                                            <div class="h-1 w-1 rounded-full bg-slate-200"></div>
                                                            <div class="flex items-center gap-1.5">
                                                                <button
                                                                    @click="copyToClipboard(candidate.assessment_url)"
                                                                    class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-brand-600 dark:hover:bg-slate-800"
                                                                    title="Copy Assessment Link"
                                                                >
                                                                    <Link2 :size="12" />
                                                                </button>
                                                                <a
                                                                    :href="candidate.assessment_url"
                                                                    target="_blank"
                                                                    class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-brand-600 dark:hover:bg-slate-800"
                                                                    title="Open Assessment Link"
                                                                >
                                                                    <ExternalLink :size="12" />
                                                                </a>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap" v-if="activeStage?.type === 'sorting'">
                                            <div v-if="candidate.ats_score !== null" class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ candidate.ats_score }}</span>
                                                <span
                                                    v-if="candidate.ats_rank"
                                                    class="rounded bg-brand-100 px-1.5 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-400"
                                                    >#{{ candidate.ats_rank }}</span
                                                >
                                            </div>
                                            <div
                                                v-else-if="candidate.ats_state === 'processing' || candidate.ats_state === 'pending'"
                                                class="flex items-center gap-2 text-sm text-slate-500 italic dark:text-slate-400"
                                            >
                                                <Loader2 :size="14" class="animate-spin" />
                                                {{ __('Analyzing...') }}
                                            </div>
                                            <div v-else class="text-sm text-slate-500 italic dark:text-slate-400">
                                                {{ candidate.ats_state === 'not_started' ? __('Not Scanned') : __('N/A') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" v-if="isAssessmentStage">
                                            <div class="flex items-center justify-between gap-4">
                                                <div v-if="candidate.stage_submission">
                                                    <button
                                                        @click="openSubmissionDrawer(candidate)"
                                                        class="group flex items-center gap-2 transition-colors"
                                                    >
                                                        <div
                                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400"
                                                        >
                                                            <FileCheck :size="16" />
                                                        </div>
                                                        <div class="flex cursor-pointer flex-col items-start text-left">
                                                            <span
                                                                class="text-sm font-bold text-slate-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400"
                                                            >
                                                                {{
                                                                    candidate.stage_submission.status === 'evaluated'
                                                                        ? __('Evaluated')
                                                                        : __('Submitted')
                                                                }}
                                                            </span>
                                                            <span class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                                                <template
                                                                    v-if="candidate.stage_submission.total_mark || activeStage?.config?.total_marks"
                                                                >
                                                                    {{ candidate.stage_submission.obtained_mark ?? '?' }} /
                                                                    {{ activeStage?.config?.total_marks ?? candidate.stage_submission.total_mark }}
                                                                </template>
                                                                <template v-else-if="candidate.stage_submission.obtained_mark !== null">
                                                                    Score: {{ candidate.stage_submission.obtained_mark }}
                                                                </template>
                                                                <template v-else>
                                                                    {{ __('Needs Review') }}
                                                                </template>

                                                                <!-- Pass/Fail Badge -->
                                                                <template
                                                                    v-if="
                                                                        candidate.stage_submission.status === 'evaluated' &&
                                                                        (activeStage?.config?.passing_marks > 0 ||
                                                                            candidate.stage_submission.passing_mark > 0)
                                                                    "
                                                                >
                                                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                                                    <span
                                                                        class="flex items-center gap-1 font-bold"
                                                                        :class="
                                                                            candidate.stage_submission.obtained_mark >=
                                                                            (activeStage?.config?.passing_marks ??
                                                                                candidate.stage_submission.passing_mark)
                                                                                ? 'text-emerald-500'
                                                                                : 'text-rose-500'
                                                                        "
                                                                    >
                                                                        {{
                                                                            candidate.stage_submission.obtained_mark >=
                                                                            (activeStage?.config?.passing_marks ??
                                                                                candidate.stage_submission.passing_mark)
                                                                                ? __('Passed')
                                                                                : __('Failed')
                                                                        }}
                                                                    </span>
                                                                </template>
                                                            </span>
                                                        </div>
                                                    </button>
                                                </div>
                                                <div v-else class="flex items-center gap-2 text-sm text-slate-500 italic dark:text-slate-400">
                                                    <span class="flex h-2 w-2 rounded-full bg-slate-300"></span>
                                                    {{ __('Pending') }}
                                                </div>

                                                <div v-if="candidate.assessment_url">
                                                    <!-- Removed from row as per user request -->
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold"
                                                :class="{
                                                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400':
                                                        candidate.status === 'passed' || isLastStage,
                                                    'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400':
                                                        candidate.status === 'in_progress' && !isLastStage,
                                                    'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400':
                                                        candidate.status === 'failed' || candidate.status === 'rejected',
                                                }"
                                            >
                                                <CheckCircle v-if="candidate.status === 'passed' || isLastStage" :size="14" />
                                                <XCircle v-else-if="candidate.status === 'rejected'" :size="14" />
                                                {{
                                                    isLastStage
                                                        ? __('Hired')
                                                        : candidate.status === 'passed'
                                                          ? __('Completed')
                                                          : candidate.status === 'rejected'
                                                            ? __('Rejected')
                                                            : __('Evaluating')
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right text-sm font-medium whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Review Button -->
                                                <button
                                                    @click="openCandidateDetails(candidate)"
                                                    class="group flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-all hover:bg-brand-600 hover:text-white dark:bg-brand-900/20 dark:text-brand-400 dark:hover:bg-brand-500 dark:hover:text-white"
                                                    :title="isLastStage ? __('Application') : __('Review')"
                                                >
                                                    <Eye :size="18" />
                                                </button>

                                                <!-- Reject Button -->
                                                <button
                                                    v-if="
                                                        !isLastStage &&
                                                        candidate.status !== 'rejected' &&
                                                        (candidate.status === 'in_progress' ||
                                                            candidate.status === 'passed' ||
                                                            candidate.status === 'failed')
                                                    "
                                                    @click="openRejectionModal(candidate)"
                                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition-all hover:bg-rose-600 hover:text-white dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-500 dark:hover:text-white"
                                                    :title="__('Reject Candidate')"
                                                >
                                                    <XCircle :size="18" />
                                                </button>

                                                <!-- Re-activate Button (If rejected) -->
                                                <button
                                                    v-if="!isLastStage && candidate.status === 'rejected'"
                                                    @click="updateCandidateStatus(candidate, 'in_progress')"
                                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-all hover:bg-emerald-600 hover:text-white dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-white"
                                                    :title="__('Undo Rejection')"
                                                >
                                                    <RotateCcw :size="18" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidate Details Slide-Over -->
            <Teleport to="body">
                <!-- Backdrop -->
                <Transition
                    enter-active-class="ease-in-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in-out duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="activeCandidate"
                        @click="closeCandidateDetails"
                        class="fixed inset-0 z-55 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                    ></div>
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
                        v-if="activeCandidate"
                        class="fixed inset-y-0 right-0 z-60 flex w-full max-w-md flex-col bg-white shadow-2xl dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Application Details') }}</h2>
                            <button
                                @click="closeCandidateDetails"
                                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-500 dark:hover:bg-slate-800"
                            >
                                <X :size="20" />
                            </button>
                        </div>

                        <div class="flex-1 space-y-8 overflow-y-auto p-6">
                            <!-- Profile Header -->
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-2xl font-bold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400"
                                >
                                    {{ activeCandidate.name.charAt(0) }}
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ activeCandidate.name }}</h3>
                                    <div class="mt-1 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                                        <Mail :size="14" />
                                        <a :href="`mailto:${activeCandidate.email}`" class="hover:underline">{{ activeCandidate.email }}</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Cover Letter -->
                            <div v-if="activeCandidate.cover_letter">
                                <h4 class="mb-3 text-sm font-bold tracking-wider text-slate-500 uppercase">{{ __('Cover Letter / Message') }}</h4>
                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm whitespace-pre-wrap text-slate-700 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                                >
                                    {{ activeCandidate.cover_letter }}
                                </div>
                            </div>

                            <!-- CV/Resume -->
                            <div>
                                <h4 class="mb-3 text-sm font-bold tracking-wider text-slate-500 uppercase">{{ __('Resume / CV') }}</h4>
                                <div
                                    v-if="activeCandidate.resume"
                                    class="flex items-center justify-between rounded-xl border border-slate-200 p-4 dark:border-slate-800"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-600 dark:bg-red-900/20 dark:text-red-400"
                                        >
                                            <FileText :size="20" />
                                        </div>
                                        <span class="text-sm font-medium text-slate-900 dark:text-white">{{ activeCandidate.resume.name }}</span>
                                    </div>
                                    <a
                                        :href="activeCandidate.resume.url"
                                        target="_blank"
                                        class="rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-600 hover:bg-brand-100 dark:bg-brand-900/20 dark:text-brand-400 dark:hover:bg-brand-900/40"
                                    >
                                        {{ __('View') }}
                                    </a>
                                </div>
                                <div v-else class="rounded-xl border border-dashed border-slate-300 p-6 text-center dark:border-slate-700">
                                    <p class="text-sm text-slate-500">{{ __('No resume uploaded.') }}</p>
                                </div>
                            </div>

                            <!-- ATS Breakdown -->
                            <div v-if="activeCandidate.ats_breakdown?.length">
                                <div class="mb-3 flex items-center justify-between">
                                    <h4 class="text-sm font-bold tracking-wider text-slate-500 uppercase">{{ __('ATS Score Breakdown') }}</h4>
                                    <div class="flex gap-2">
                                        <button
                                            @click="handleOverride(activeCandidate.id, true)"
                                            class="rounded-lg bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-600 transition-colors hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400"
                                        >
                                            {{ __('Force Pass') }}
                                        </button>
                                        <button
                                            @click="handleOverride(activeCandidate.id, false)"
                                            class="rounded-lg bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-600 transition-colors hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400"
                                        >
                                            {{ __('Force Fail') }}
                                        </button>
                                    </div>
                                </div>
                                <div
                                    class="overflow-hidden rounded-xl border border-slate-200 bg-white text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900"
                                >
                                    <div
                                        class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/50"
                                    >
                                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ __('Total Score') }}</span>
                                        <span class="font-bold text-brand-600 dark:text-brand-400">{{ activeCandidate.ats_score }}</span>
                                    </div>
                                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <div v-for="(item, index) in activeCandidate.ats_breakdown" :key="index" class="px-4 py-3">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-2 w-2 rounded-full" :class="item.matched ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                                                    <span class="text-xs font-medium tracking-wider text-slate-900 uppercase dark:text-white">{{
                                                        item.type
                                                    }}</span>
                                                </div>
                                                <span class="font-bold text-slate-600 dark:text-slate-400"
                                                    >+{{ item.awarded_score }} / {{ item.weight }}</span
                                                >
                                            </div>
                                            <p
                                                class="mt-1 ml-4 border-l-2 pl-2 text-xs text-slate-500 dark:text-slate-400"
                                                :class="
                                                    item.matched
                                                        ? 'border-emerald-200 dark:border-emerald-900/50'
                                                        : 'border-rose-200 dark:border-rose-900/50'
                                                "
                                            >
                                                {{ item.explanation }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 p-6 dark:border-slate-800">
                            <button
                                @click="closeCandidateDetails"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                {{ __('Close Details') }}
                            </button>
                        </div>
                    </div>
                </Transition>

                <Transition
                    enter-active-class="transition-opacity ease-linear duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity ease-linear duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="activeCandidate" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm" @click="closeCandidateDetails"></div>
                </Transition>
            </Teleport>

            <!-- Stage Submission Grading Drawer -->
            <StageSubmissionDrawer
                :is-open="isSubmissionDrawerOpen"
                :candidate="selectedCandidateForSubmission"
                :stage="activeStage"
                @close="
                    isSubmissionDrawerOpen = false;
                    selectedCandidateForSubmission = null;
                "
            />

            <CandidateRejectionModal
                :show="isRejectionModalOpen"
                :candidate="candidateForRejection"
                :stage="activeStage"
                :job-title="job.title"
                :global-rejection-email="global_rejection_email"
                @close="isRejectionModalOpen = false"
            />
        </div>
    </AdminLayout>
</template>
```
