<script setup>
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Clock, TriangleAlert } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    application: Object,
    stage: Object,
    questions: Array,
    timeRemainingMs: Number,
    submitUrl: String,
});

// Brand details
const appName = computed(() => usePage().props.branding?.business_settings?.app_name || 'Job Portal');
const appLogo = computed(() => usePage().props.branding?.business_settings?.logo_url || '/logo.png');

const isProcessing = ref(false);
const timeLeft = ref(Math.floor(props.timeRemainingMs / 1000)); // in seconds
let timerInterval = null;

const showConfirmModal = ref(false);
const showTimeUpModal = ref(false);

// Track answers: { question_id: [option_id, option_id] }
const answers = ref({});

// Initialize answers structure
props.questions.forEach((q) => {
    answers.value[q.id] = [];
});

const formattedTime = computed(() => {
    if (timeLeft.value <= 0) return '00:00';
    const mins = Math.floor(timeLeft.value / 60);
    const secs = timeLeft.value % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
});

const isTimeLow = computed(() => timeLeft.value <= 60); // red color under 1 min

const startTimer = () => {
    timerInterval = setInterval(() => {
        timeLeft.value--;

        if (timeLeft.value <= 0) {
            clearInterval(timerInterval);
            autoSubmit();
        }
    }, 1000);
};

onMounted(() => {
    if (timeLeft.value > 0) {
        startTimer();
    } else {
        autoSubmit();
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const toggleAnswer = (questionId, optionId) => {
    answers.value[questionId] = [optionId];
};

const isOptionSelected = (questionId, optionId) => {
    return answers.value[questionId].includes(optionId);
};

const submitQuiz = (isAuto = false) => {
    if (isProcessing.value) return;

    if (!isAuto && !showConfirmModal.value) {
        showConfirmModal.value = true;
        return;
    }

    isProcessing.value = true;
    showConfirmModal.value = false;
    showTimeUpModal.value = false;

    if (timerInterval) clearInterval(timerInterval);

    // Transform ref to payload
    const payload = {
        answers: answers.value,
    };

    router.post(props.submitUrl, payload, {
        preserveScroll: true,
        replace: true, // prevent back button weirdness
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const autoSubmit = () => {
    showTimeUpModal.value = true;
};
</script>

<template>
    <Head :title="`Active Quiz: ${stage.title}`" />

    <div class="min-h-screen bg-slate-50 pb-24">
        <!-- Sticky Header (Timer & Progress) -->
        <div class="sticky top-0 z-50 border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto flex h-16 max-w-4xl items-center justify-between px-4 sm:px-6">
                <div class="flex items-center gap-4 truncate font-bold text-slate-800">
                    <img v-if="appLogo" :src="appLogo" :alt="appName" class="hidden h-8 w-auto rounded-lg sm:block" />
                    <span class="hidden truncate sm:block">{{ stage.title }}</span>
                </div>

                <div
                    class="flex items-center gap-2 rounded-full px-4 py-1.5"
                    :class="[isTimeLow ? 'animate-pulse bg-red-50 text-red-700' : 'bg-slate-100 text-slate-700']"
                >
                    <Clock class="h-5 w-5 shrink-0" />
                    <span class="font-mono text-xl font-bold tracking-wider">{{ formattedTime }}</span>
                </div>

                <div>
                    <button
                        @click="submitQuiz(false)"
                        type="button"
                        :disabled="isProcessing"
                        class="inline-flex items-center justify-center rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ isProcessing ? 'Submitting...' : 'Finish & Submit' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Quiz Content -->
        <div class="mx-auto max-w-3xl space-y-8 px-4 pt-8 sm:px-6">
            <div v-if="isTimeLow" class="mb-6 flex items-start gap-4 rounded-r-md border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
                <TriangleAlert class="mt-0.5 h-6 w-6 shrink-0 text-red-500" />
                <div class="text-sm font-medium text-red-700">
                    Hurry up! Less than a minute remaining. The quiz will submit automatically when time ends.
                </div>
            </div>

            <!-- Questions -->
            <div
                v-for="(question, index) in questions"
                :key="question.id"
                class="overflow-hidden bg-white shadow-sm ring-1 ring-slate-900/5 sm:rounded-xl"
            >
                <div class="flex items-start gap-4 border-b border-slate-50 bg-slate-50/50 p-6 sm:p-8">
                    <div class="bg-brand-primary/10 text-brand-primary flex h-8 w-8 shrink-0 items-center justify-center rounded-full font-bold">
                        {{ index + 1 }}
                    </div>
                    <div class="pt-1">
                        <h3 class="text-lg leading-snug font-medium text-slate-900">{{ question.question }}</h3>
                        <p class="mt-2 text-xs font-medium tracking-wide text-slate-500 uppercase">
                            {{ __('Pick one answer') }}
                        </p>
                    </div>
                </div>

                <div class="space-y-3 bg-white p-4 sm:p-6">
                    <label
                        v-for="option in question.options"
                        :key="option.id"
                        class="relative flex cursor-pointer items-center rounded-xl border p-4 transition-all duration-200"
                        :class="[
                            isOptionSelected(question.id, option.id)
                                ? 'border-brand-primary bg-brand-primary/5 ring-brand-primary shadow-sm ring-1'
                                : 'hover:border-brand-primary/50 border-slate-200 hover:bg-slate-50',
                        ]"
                    >
                        <div class="mr-4 flex h-6 shrink-0 items-center">
                            <!-- Always Radio Visuals -->
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full border-2 transition-all duration-300"
                                :class="[
                                    isOptionSelected(question.id, option.id)
                                        ? 'border-brand-600 bg-brand-600 shadow-md shadow-brand-600/20'
                                        : 'border-slate-200 bg-white group-hover:border-slate-300',
                                ]"
                            >
                                <div
                                    class="h-2.5 w-2.5 rounded-full bg-white transition-all duration-300"
                                    :class="[isOptionSelected(question.id, option.id) ? 'scale-100 opacity-100' : 'scale-0 opacity-0']"
                                ></div>
                            </div>

                            <!-- Hidden inputs so it still functions if needed normally -->
                            <input
                                :type="question.type === 'single' ? 'radio' : 'checkbox'"
                                :name="`q_${question.id}`"
                                class="sr-only"
                                :checked="isOptionSelected(question.id, option.id)"
                                @change="toggleAnswer(question.id, option.id)"
                            />
                        </div>
                        <div
                            class="w-full text-base font-semibold transition-colors"
                            :class="[
                                isOptionSelected(question.id, option.id)
                                    ? 'text-brand-700 dark:text-brand-400'
                                    : 'text-slate-600 dark:text-slate-300',
                            ]"
                        >
                            {{ option.option_text }}
                        </div>

                        <!-- Selected Badge -->
                        <div v-if="isOptionSelected(question.id, option.id)" class="animate-in fade-in zoom-in ml-2 duration-300">
                            <span class="inline-flex items-center rounded-full bg-brand-600/10 px-2 py-0.5 text-[10px] font-bold text-brand-600">
                                {{ __('Selected') }}
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="showConfirmModal"
            type="info"
            title="Finish Assessment?"
            message="Are you sure you want to finalize and submit your answers? You won't be able to change them afterwards."
            confirm-text="Yes, Submit Quiz"
            cancel-text="Wait, let me review"
            :processing="isProcessing"
            @confirm="submitQuiz(false)"
            @close="showConfirmModal = false"
        />

        <ConfirmationModal
            :show="showTimeUpModal"
            type="warning"
            title="Time's Up!"
            message="The allocated time for this quiz has ended. Your current progress will be submitted automatically."
            confirm-text="Submit Now"
            :show-cancel="false"
            :processing="isProcessing"
            @confirm="submitQuiz(true)"
            @close="submitQuiz(true)"
        />
    </div>
</template>
