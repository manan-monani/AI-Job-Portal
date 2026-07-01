<script setup lang="ts">
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import { wTrans as __ } from '@/Core/i18n';
import { grade, reset } from '@/routes/admin/evaluations/submissions';
import { router, useForm } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, ExternalLink, FileText, Loader2, RefreshCcw, User, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    candidate: any;
    stage: any;
}>();

const emit = defineEmits(['close']);

const submission = computed(() => props.candidate?.stage_submission);
const isTaskOrExam = computed(() => ['assessment', 'interview'].includes(props.stage?.type));
const isQuiz = computed(() => props.stage?.subtype === 'online_quiz' || props.stage?.subtype === 'quiz');

const form = useForm({
    obtained_mark: '' as string | number,
});

const totalMark = computed(() => props.stage?.config?.total_marks ?? submission.value?.total_mark ?? 0);
const passingMark = computed(() => props.stage?.config?.passing_marks ?? submission.value?.passing_mark ?? 0);
const isOverScored = computed(() => Number(form.obtained_mark) > totalMark.value);

const showResetModal = ref(false);
const resetProcessing = ref(false);

watch(
    () => props.isOpen,
    (val) => {
        if (val && submission.value) {
            form.obtained_mark = submission.value.obtained_mark ?? '';
        }
    },
);

const submitGrade = () => {
    if (!submission.value) return;

    if (isOverScored.value) {
        alert(__('The assigned score exceeds the total marks (:total). Please correct it before submitting.', { total: String(totalMark.value) }));
        return;
    }

    form.post(grade.url(submission.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            close();
        },
    });
};

const resetSubmission = () => {
    if (!submission.value) return;
    resetProcessing.value = true;
    router.post(
        reset.url(submission.value.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showResetModal.value = false;
                close();
            },
            onFinish: () => {
                resetProcessing.value = false;
            },
        },
    );
};

const close = () => {
    emit('close');
};
</script>

<template>
    <div class="relative z-50">
        <transition
            enter-active-class="ease-in-out duration-500"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in-out duration-500"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="isOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="close"></div>
        </transition>

        <div class="pointer-events-none fixed inset-0 overflow-hidden" :class="{ 'z-60': isOpen }">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                    <transition
                        enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                        enter-from-class="translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                        leave-from-class="translate-x-0"
                        leave-to-class="translate-x-full"
                    >
                        <div v-show="isOpen" class="pointer-events-auto w-screen max-w-md">
                            <div class="flex h-full flex-col bg-white shadow-2xl dark:bg-slate-900">
                                <!-- Header -->
                                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400"
                                        >
                                            <User :size="24" />
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ candidate?.name }}</h3>
                                            <p class="text-xs font-medium text-slate-500">{{ stage?.title }} • {{ __('Submission Details') }}</p>
                                        </div>
                                    </div>
                                    <button
                                        @click="close"
                                        class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                                    >
                                        <X :size="20" />
                                    </button>
                                </div>

                                <!-- Body -->
                                <div class="flex-1 space-y-8 overflow-y-auto p-8">
                                    <!-- Submission Content -->
                                    <div v-if="submission" class="space-y-8">
                                        <!-- Status & Quick Info -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div
                                                class="rounded-3xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                                            >
                                                <p class="mb-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Status') }}</p>
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="h-2 w-2 rounded-full"
                                                        :class="submission.status === 'evaluated' ? 'bg-emerald-500' : 'bg-amber-500'"
                                                    ></div>
                                                    <span class="text-sm font-bold text-slate-700 capitalize dark:text-slate-300">{{
                                                        submission.status
                                                    }}</span>
                                                </div>
                                            </div>
                                            <div
                                                class="rounded-3xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                                            >
                                                <p class="mb-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                                    {{ __('Final Score') }}
                                                </p>
                                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                                    {{ submission.obtained_mark ?? '-' }}
                                                    <span class="text-xs font-medium text-slate-400">/ {{ totalMark }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div v-if="isTaskOrExam" class="space-y-6">
                                            <div v-if="submission.text_answer">
                                                <label class="block text-xs font-bold tracking-widest text-slate-400 uppercase">{{
                                                    __('Text Response')
                                                }}</label>
                                                <div
                                                    class="mt-3 rounded-3xl border border-slate-100 bg-white p-6 leading-relaxed text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                                                >
                                                    {{ submission.text_answer }}
                                                </div>
                                            </div>

                                            <div v-if="submission.file_url">
                                                <label class="block text-xs font-bold tracking-widest text-slate-400 uppercase">{{
                                                    __('Attachment')
                                                }}</label>
                                                <a
                                                    :href="submission.file_url"
                                                    target="_blank"
                                                    class="mt-3 flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-4 transition-all hover:border-brand-200 hover:bg-brand-50/30 dark:border-slate-800 dark:bg-slate-800/50"
                                                >
                                                    <div
                                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand-600 dark:bg-brand-900/40"
                                                    >
                                                        <FileText :size="24" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                                                            {{ __('View Submitted File') }}
                                                        </p>
                                                        <p class="text-xs text-slate-400">{{ __('Opens in a new tab') }}</p>
                                                    </div>
                                                    <ExternalLink :size="18" class="text-slate-300" />
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Quiz Results Polished -->
                                        <div v-if="isQuiz && submission.quiz_results" class="space-y-6">
                                            <div class="flex items-center justify-between">
                                                <label class="text-xs font-bold tracking-widest text-slate-400 uppercase">{{
                                                    __('Quiz Analysis')
                                                }}</label>
                                                <span
                                                    class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                                                >
                                                    {{ submission.quiz_results.length }} {{ __('Questions') }}
                                                </span>
                                            </div>
                                            <div class="space-y-4">
                                                <div
                                                    v-for="(res, i) in submission.quiz_results"
                                                    :key="i"
                                                    class="group rounded-3xl border border-slate-50 bg-white p-5 transition-all hover:border-slate-200 dark:border-slate-800 dark:bg-slate-800/30"
                                                >
                                                    <div class="mb-3 flex items-start gap-3">
                                                        <div
                                                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-black"
                                                            :class="
                                                                res.is_correct
                                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30'
                                                                    : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30'
                                                            "
                                                        >
                                                            {{ Number(i) + 1 }}
                                                        </div>
                                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ res.question_text }}</p>
                                                    </div>

                                                    <div class="ml-9 flex items-center gap-4">
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="flex h-5 w-5 items-center justify-center rounded-full"
                                                                :class="
                                                                    res.is_correct ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'
                                                                "
                                                            >
                                                                <component :is="res.is_correct ? CheckCircle2 : AlertCircle" :size="14" />
                                                            </div>
                                                            <span
                                                                class="text-xs font-medium"
                                                                :class="res.is_correct ? 'text-emerald-600' : 'text-rose-600'"
                                                            >
                                                                {{ res.is_correct ? __('Correct') : __('Incorrect') }}
                                                            </span>
                                                        </div>
                                                        <div class="h-1 w-1 rounded-full bg-slate-200"></div>
                                                        <p class="text-xs font-medium text-slate-400">
                                                            {{ __('Awarded') }}:
                                                            <span class="font-bold text-slate-600 dark:text-slate-300">{{ res.marks_awarded }}</span>
                                                            / {{ res.question_marks || 1 }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div
                                                    v-if="passingMark > 0"
                                                    class="mt-4 flex items-center justify-center gap-2 rounded-2xl bg-slate-50 p-3 text-xs font-bold text-slate-500 dark:bg-slate-800/50"
                                                >
                                                    <span>{{ __('Passing Mark') }}: {{ passingMark }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Grading Form -->
                                        <div
                                            class="rounded-[2.5rem] border border-slate-100 bg-slate-50/50 p-8 dark:border-slate-800 dark:bg-slate-800/30"
                                        >
                                            <h4 class="mb-6 text-sm font-bold text-slate-900 dark:text-white">{{ __('Evaluation & Grading') }}</h4>
                                            <form @submit.prevent="submitGrade" class="space-y-6">
                                                <div>
                                                    <label class="mb-2 block text-xs font-bold text-slate-500 uppercase">{{
                                                        __('Assign Marks')
                                                    }}</label>
                                                    <div class="relative">
                                                        <input
                                                            v-model="form.obtained_mark"
                                                            type="number"
                                                            step="0.01"
                                                            class="h-14 w-full rounded-2xl border-slate-200 bg-white px-6 text-lg font-bold transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                                            :class="{ 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10': isOverScored }"
                                                            :placeholder="__('Score (e.g. 8.5)')"
                                                        />
                                                        <div class="absolute top-1/2 right-6 -translate-y-1/2 font-bold text-slate-400">
                                                            / {{ totalMark }}
                                                        </div>
                                                    </div>
                                                    <p v-if="isOverScored" class="mt-1.5 animate-pulse px-2 text-[10px] font-bold text-rose-500">
                                                        ⚠️ {{ __('Warning: Score is higher than total marks') }}
                                                    </p>
                                                </div>

                                                <div class="flex flex-col gap-3">
                                                    <button
                                                        type="submit"
                                                        :disabled="form.processing"
                                                        class="flex h-14 w-full items-center justify-center gap-3 rounded-2xl bg-brand-600 text-sm font-bold text-white shadow-xl shadow-brand-600/20 transition-all hover:bg-brand-700 active:scale-[0.98] disabled:opacity-50"
                                                    >
                                                        <Loader2 v-if="form.processing" class="animate-spin" :size="20" />
                                                        {{ __('Submit Grade') }}
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="showResetModal = true"
                                                        class="flex h-14 w-full items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white text-sm font-bold text-slate-600 transition-all hover:border-rose-100 hover:bg-rose-50 hover:text-rose-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-rose-900/10"
                                                    >
                                                        <RefreshCcw :size="18" />
                                                        {{ __('Reset Assessment') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                                        <div
                                            class="mb-6 flex h-24 w-24 items-center justify-center rounded-4xl bg-slate-50 text-slate-300 dark:bg-slate-800/50"
                                        >
                                            <FileText :size="48" />
                                        </div>
                                        <h4 class="text-lg font-bold text-slate-400">{{ __('No submission found') }}</h4>
                                        <p class="mt-2 max-w-xs text-sm text-slate-400">
                                            {{ __("The candidate hasn't completed this assessment stage yet.") }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="showResetModal"
            type="danger"
            title="Reset Submission?"
            message="This will delete the current marks and results. The candidate will be able to re-participate in this assessment using their original link."
            confirm-text="Yes, Reset Assessment"
            :processing="resetProcessing"
            @confirm="resetSubmission"
            @close="showResetModal = false"
        />
    </div>
</template>
