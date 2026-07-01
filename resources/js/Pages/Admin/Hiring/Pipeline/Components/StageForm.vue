<script setup lang="ts">
import { CirclePlus, Plus, Trash2, X } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

interface QuizOption {
    option_text: string;
    is_correct: boolean;
}

interface QuizQuestion {
    question: string;
    options: QuizOption[];
}

interface Criterion {
    label: string;
    weight: number;
}

interface StageConfig {
    duration?: number | null;
    total_marks?: number | null;
    passing_marks?: number | null;
    due_date?: string;
    requires_attachment?: boolean;
    scheduled_at?: string;
    location?: string;
    phone_details?: string;
    meeting_link?: string;
    meeting_platform?: string;
    exam_mode?: string; // 'online' | 'onsite'
    min_score_to_proceed?: number | null;
}

interface Stage {
    id?: number;
    type: string;
    subtype: string | null;
    title: string;
    instructions: string;
    config: StageConfig;
    sort_order: number;
    is_system: boolean;
    system_key: string | null;
    is_enabled: boolean;
    send_mail_on_trigger: boolean;
    interviewer_ids: number[];
    criteria: Criterion[];
    quiz_questions: QuizQuestion[];
}

interface Interviewer {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    show: boolean;
    stage: Stage | null;
    interviewers: Interviewer[];
}>();

const emit = defineEmits(['close', 'save']);

const defaultConfig = (): StageConfig => ({
    duration: null,
    total_marks: null,
    passing_marks: null,
    due_date: '',
    requires_attachment: false,
    scheduled_at: '',
    location: '',
    phone_details: '',
    meeting_link: '',
    meeting_platform: '',
    exam_mode: 'online',
    min_score_to_proceed: null,
});

const form = ref({
    type: 'interview' as string,
    subtype: null as string | null,
    title: '',
    instructions: '',
    config: defaultConfig(),
    is_enabled: true,
    send_mail_on_trigger: false,
    interviewer_ids: [] as number[],
    criteria: [] as Criterion[],
    quiz_questions: [] as QuizQuestion[],
});

const validationErrors = ref<string[]>([]);
const isInitializing = ref(false);

watch(
    () => props.show,
    async (isVisible) => {
        if (isVisible) {
            isInitializing.value = true;
            validationErrors.value = [];
            if (props.stage) {
                form.value = {
                    type: props.stage.type,
                    subtype: props.stage.subtype || null,
                    title: props.stage.title,
                    instructions: props.stage.instructions || '',
                    config: { ...defaultConfig(), ...(props.stage.config || {}) },
                    is_enabled: props.stage.is_enabled,
                    send_mail_on_trigger: props.stage.send_mail_on_trigger,
                    interviewer_ids: [...props.stage.interviewer_ids],
                    criteria: props.stage.criteria?.map((c) => ({ ...c })) || [],
                    quiz_questions:
                        props.stage.quiz_questions?.map((q) => ({
                            question: q.question,
                            options: q.options?.map((o) => ({ ...o })) || [],
                        })) || [],
                };
            } else {
                form.value = {
                    type: 'interview',
                    subtype: null,
                    title: '',
                    instructions: '',
                    config: defaultConfig(),
                    is_enabled: true,
                    send_mail_on_trigger: false,
                    interviewer_ids: [],
                    criteria: [],
                    quiz_questions: [],
                };
            }
            await nextTick();
            isInitializing.value = false;
        }
    },
);

watch(
    () => form.value.type,
    () => {
        if (isInitializing.value) return;
        form.value.subtype = null;
        form.value.config = defaultConfig();
        form.value.quiz_questions = [];
    },
);

const assessmentSubtypes = [
    { value: 'task', label: 'Task', icon: '📋' },
    { value: 'exam', label: 'Exam', icon: '📝' },
    { value: 'quiz', label: 'Quiz', icon: '❓' },
];

const interviewSubtypes = [
    { value: 'onsite', label: 'Onsite', icon: '🏢' },
    { value: 'phone', label: 'Phone', icon: '📞' },
    { value: 'online', label: 'Online Meeting', icon: '💻' },
];

const currentSubtypes = computed(() => {
    if (form.value.type === 'assessment') return assessmentSubtypes;
    if (form.value.type === 'interview') return interviewSubtypes;
    return [];
});

const needsSubtype = computed(() => form.value.type === 'assessment' || form.value.type === 'interview');

// Quiz helpers
const addQuestion = () => {
    form.value.quiz_questions.push({
        question: '',
        options: [
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false },
        ],
    });
};

const removeQuestion = (qi: number) => {
    form.value.quiz_questions.splice(qi, 1);
};

const addOption = (qi: number) => {
    form.value.quiz_questions[qi].options.push({ option_text: '', is_correct: false });
};

const removeOption = (qi: number, oi: number) => {
    form.value.quiz_questions[qi].options.splice(oi, 1);
};

const setCorrectAnswer = (qi: number, oi: number) => {
    form.value.quiz_questions[qi].options.forEach((o, idx) => {
        o.is_correct = idx === oi;
    });
};

// Sorting criteria helpers
const addCriterion = () => {
    form.value.criteria.push({ label: '', weight: 1 });
};
const removeCriterion = (index: number) => {
    form.value.criteria.splice(index, 1);
};

const toggleInterviewer = (id: number) => {
    const idx = form.value.interviewer_ids.indexOf(id);
    if (idx > -1) form.value.interviewer_ids.splice(idx, 1);
    else form.value.interviewer_ids.push(id);
};

const validate = (): boolean => {
    const errors: string[] = [];
    if (!form.value.title.trim()) errors.push('Title is required.');
    if (needsSubtype.value && !form.value.subtype) errors.push('Please select a subtype.');

    if (form.value.type === 'interview' && form.value.subtype) {
        if (form.value.subtype === 'onsite' && !form.value.config.location?.trim()) errors.push('Location is required for onsite interviews.');
        if (form.value.subtype === 'phone' && !form.value.config.phone_details?.trim())
            errors.push('Phone details are required for phone interviews.');
        if (form.value.subtype === 'online' && !form.value.config.meeting_link?.trim())
            errors.push('Meeting link is required for online interviews.');
    }

    if (
        form.value.type === 'assessment' &&
        form.value.subtype === 'exam' &&
        form.value.config.exam_mode === 'onsite' &&
        !form.value.config.location?.trim()
    ) {
        errors.push('Location is required for onsite exams.');
    }

    if (form.value.type === 'assessment' && form.value.subtype === 'quiz') {
        if (!form.value.config.duration) errors.push('Duration is required for quiz.');
        if (!form.value.config.passing_marks && form.value.config.passing_marks !== 0) errors.push('Passing marks is required for quiz.');
        if (form.value.quiz_questions.length === 0) errors.push('At least one question is required.');
        form.value.quiz_questions.forEach((q, i) => {
            if (!q.question.trim()) errors.push(`Question ${i + 1} text is required.`);
            if (q.options.length < 2) errors.push(`Question ${i + 1} needs at least 2 options.`);
            if (!q.options.some((o) => o.is_correct)) errors.push(`Question ${i + 1} must have a correct answer.`);
        });
    }

    validationErrors.value = errors;
    return errors.length === 0;
};

const handleSave = () => {
    if (!validate()) return;
    emit('save', {
        ...form.value,
        config: { ...form.value.config },
        quiz_questions: form.value.subtype === 'quiz' ? form.value.quiz_questions : [],
    });
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-60 flex items-start justify-end">
                <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" @click="emit('close')"></div>

                <div class="relative z-10 flex h-full w-full max-w-xl flex-col overflow-hidden bg-white shadow-2xl dark:bg-slate-900">
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ stage ? __('Edit Stage') : __('New Stage') }}</h3>
                        <button
                            @click="emit('close')"
                            class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                        >
                            <X :size="18" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 space-y-5 overflow-y-auto p-6">
                        <!-- Validation Errors -->
                        <div
                            v-if="validationErrors.length > 0"
                            class="rounded-xl border border-rose-200 bg-rose-50 p-3 dark:border-rose-800 dark:bg-rose-900/20"
                        >
                            <p v-for="(err, i) in validationErrors" :key="i" class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ err }}</p>
                        </div>

                        <!-- Stage Type -->
                        <div>
                            <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase">{{ __('Stage Type') }}</label>
                            <div class="mt-2 grid grid-cols-3 gap-2">
                                <button
                                    v-for="t in ['sorting', 'assessment', 'interview']"
                                    :key="t"
                                    @click="form.type = t"
                                    class="rounded-xl border px-3 py-2 text-xs font-bold capitalize transition-all"
                                    :class="
                                        form.type === t
                                            ? 'border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-700 dark:bg-brand-900/20 dark:text-brand-400'
                                            : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-slate-700 dark:text-slate-400'
                                    "
                                >
                                    {{ t }}
                                </button>
                            </div>
                        </div>

                        <!-- Subtype Selector -->
                        <div v-if="needsSubtype">
                            <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase">{{
                                form.type === 'assessment' ? __('Assessment Type') : __('Interview Type')
                            }}</label>
                            <div class="mt-2 grid grid-cols-3 gap-2">
                                <button
                                    v-for="st in currentSubtypes"
                                    :key="st.value"
                                    @click="form.subtype = st.value"
                                    class="flex flex-col items-center gap-1 rounded-xl border p-3 text-xs font-bold transition-all"
                                    :class="
                                        form.subtype === st.value
                                            ? 'border-violet-300 bg-violet-50 text-violet-700 dark:border-violet-700 dark:bg-violet-900/20 dark:text-violet-400'
                                            : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-slate-700 dark:text-slate-400'
                                    "
                                >
                                    <span class="text-lg">{{ st.icon }}</span>
                                    <span>{{ st.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase">{{ __('Title') }}</label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                :placeholder="__('e.g. Technical Interview')"
                            />
                        </div>

                        <!-- Instructions -->
                        <div>
                            <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase">{{ __('Instructions') }}</label>
                            <textarea
                                v-model="form.instructions"
                                rows="3"
                                class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 p-4 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                :placeholder="__('Details or notes for this stage...')"
                            ></textarea>
                        </div>

                        <!-- =============================== -->
                        <!-- ASSESSMENT: TASK CONFIG -->
                        <!-- =============================== -->
                        <div
                            v-if="form.type === 'assessment' && form.subtype === 'task'"
                            class="space-y-4 rounded-xl border border-amber-200/60 bg-amber-50/30 p-4 dark:border-amber-800/40 dark:bg-amber-900/10"
                        >
                            <h4 class="text-xs font-bold tracking-widest text-amber-600 uppercase">{{ __('Task Configuration (Online)') }}</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Date & Time') }}</label>
                                    <input
                                        v-model="form.config.scheduled_at"
                                        type="datetime-local"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Duration (min)') }}</label>
                                    <input
                                        v-model.number="form.config.duration"
                                        type="number"
                                        min="1"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Due Date') }}</label>
                                    <input
                                        v-model="form.config.due_date"
                                        type="date"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Total Marks') }}</label>
                                    <input
                                        v-model.number="form.config.total_marks"
                                        type="number"
                                        min="1"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Passing Marks') }}</label>
                                    <input
                                        v-model.number="form.config.passing_marks"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                                <div class="flex items-end">
                                    <label
                                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 dark:border-slate-700"
                                    >
                                        <input
                                            v-model="form.config.requires_attachment"
                                            type="checkbox"
                                            class="rounded border-slate-300 text-amber-600"
                                        />
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ __('Requires File Upload') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- =============================== -->
                        <!-- ASSESSMENT: EXAM CONFIG -->
                        <!-- =============================== -->
                        <div
                            v-if="form.type === 'assessment' && form.subtype === 'exam'"
                            class="space-y-4 rounded-xl border border-amber-200/60 bg-amber-50/30 p-4 dark:border-amber-800/40 dark:bg-amber-900/10"
                        >
                            <h4 class="text-xs font-bold tracking-widest text-amber-600 uppercase">{{ __('Exam Configuration') }}</h4>

                            <!-- Exam Mode -->
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Exam Mode') }}</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        @click="form.config.exam_mode = 'online'"
                                        class="rounded-lg border px-3 py-2 text-xs font-bold transition-all"
                                        :class="
                                            form.config.exam_mode === 'online'
                                                ? 'border-emerald-300 bg-emerald-50 text-emerald-700'
                                                : 'border-slate-200 text-slate-500'
                                        "
                                    >
                                        💻 {{ __('Online') }}
                                    </button>
                                    <button
                                        @click="form.config.exam_mode = 'onsite'"
                                        class="rounded-lg border px-3 py-2 text-xs font-bold transition-all"
                                        :class="
                                            form.config.exam_mode === 'onsite'
                                                ? 'border-blue-300 bg-blue-50 text-blue-700'
                                                : 'border-slate-200 text-slate-500'
                                        "
                                    >
                                        🏢 {{ __('Onsite') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Shared fields (both online and onsite) -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Date & Time') }}</label>
                                    <input
                                        v-model="form.config.scheduled_at"
                                        type="datetime-local"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Duration (min)') }}</label>
                                    <input
                                        v-model.number="form.config.duration"
                                        type="number"
                                        min="1"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- Location (onsite only) -->
                            <div v-if="form.config.exam_mode === 'onsite'">
                                <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase"
                                    >{{ __('Location') }} <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.config.location"
                                    type="text"
                                    class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    :placeholder="__('e.g. Room 301, Floor 3')"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Total Marks') }}</label>
                                    <input
                                        v-model.number="form.config.total_marks"
                                        type="number"
                                        min="1"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Passing Marks') }}</label>
                                    <input
                                        v-model.number="form.config.passing_marks"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                            </div>
                            <label
                                v-if="form.config.exam_mode === 'online'"
                                class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 dark:border-slate-700"
                            >
                                <input v-model="form.config.requires_attachment" type="checkbox" class="rounded border-slate-300 text-amber-600" />
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ __('Requires File Upload') }}</span>
                            </label>
                        </div>

                        <!-- =============================== -->
                        <!-- ASSESSMENT: QUIZ BUILDER -->
                        <!-- =============================== -->
                        <div
                            v-if="form.type === 'assessment' && form.subtype === 'quiz'"
                            class="space-y-4 rounded-xl border border-purple-200/60 bg-purple-50/30 p-4 dark:border-purple-800/40 dark:bg-purple-900/10"
                        >
                            <h4 class="text-xs font-bold tracking-widest text-purple-600 uppercase">{{ __('Quiz Configuration') }}</h4>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase"
                                        >{{ __('Duration (min)') }} <span class="text-rose-500">*</span></label
                                    >
                                    <input
                                        v-model.number="form.config.duration"
                                        type="number"
                                        min="1"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase"
                                        >{{ __('Passing Marks') }} <span class="text-rose-500">*</span></label
                                    >
                                    <input
                                        v-model.number="form.config.passing_marks"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Min Score to Proceed') }}</label>
                                    <input
                                        v-model.number="form.config.min_score_to_proceed"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        :placeholder="__('Same as passing')"
                                    />
                                </div>
                            </div>

                            <!-- Questions -->
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold tracking-widest text-purple-600 uppercase"
                                    >{{ __('Questions') }} ({{ form.quiz_questions.length }})</label
                                >
                                <button
                                    @click="addQuestion"
                                    class="flex items-center gap-1 rounded-lg bg-purple-100 px-2 py-1 text-[10px] font-bold text-purple-600 transition-all hover:bg-purple-200 dark:bg-purple-900/30"
                                >
                                    <Plus :size="12" />
                                    {{ __('Add Question') }}
                                </button>
                            </div>

                            <div v-if="form.quiz_questions.length === 0" class="py-4 text-center">
                                <p class="text-xs text-slate-400">{{ __('No questions yet. Click "Add Question" to start building your quiz.') }}</p>
                            </div>

                            <div
                                v-for="(question, qi) in form.quiz_questions"
                                :key="qi"
                                class="rounded-lg border border-purple-200/40 bg-white p-4 dark:border-purple-800/30 dark:bg-slate-800"
                            >
                                <div class="mb-3 flex items-start gap-2">
                                    <span
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400"
                                        >{{ qi + 1 }}</span
                                    >
                                    <textarea
                                        v-model="question.question"
                                        rows="2"
                                        class="flex-1 rounded-lg border-slate-200 bg-slate-50 px-3 py-1.5 text-xs focus:border-purple-500 focus:ring-1 focus:ring-purple-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        :placeholder="__('Enter your question...')"
                                    ></textarea>
                                    <button
                                        @click="removeQuestion(qi)"
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-slate-400 hover:bg-rose-50 hover:text-rose-500"
                                    >
                                        <Trash2 :size="12" />
                                    </button>
                                </div>

                                <!-- Options -->
                                <div class="ml-8 space-y-1.5">
                                    <div v-for="(option, oi) in question.options" :key="oi" class="flex items-center gap-2">
                                        <button
                                            @click="setCorrectAnswer(qi, oi)"
                                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-all"
                                            :class="
                                                option.is_correct
                                                    ? 'border-emerald-500 bg-emerald-500 text-white'
                                                    : 'border-slate-300 hover:border-emerald-400'
                                            "
                                            :title="__('Mark as correct')"
                                        >
                                            <svg v-if="option.is_correct" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <input
                                            v-model="option.option_text"
                                            type="text"
                                            class="flex-1 rounded-lg border-slate-200 bg-slate-50 px-3 py-1 text-xs focus:border-purple-500 focus:ring-1 focus:ring-purple-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                            :placeholder="__('Option') + ' ' + (oi + 1)"
                                        />
                                        <button
                                            v-if="question.options.length > 2"
                                            @click="removeOption(qi, oi)"
                                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-slate-300 hover:text-rose-500"
                                        >
                                            <X :size="10" />
                                        </button>
                                    </div>
                                    <button
                                        @click="addOption(qi)"
                                        class="flex items-center gap-1 text-[10px] font-bold text-purple-500 transition-all hover:text-purple-700"
                                    >
                                        <CirclePlus :size="12" />
                                        {{ __('Add Option') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- =============================== -->
                        <!-- INTERVIEW CONFIG FIELDS -->
                        <!-- =============================== -->
                        <div
                            v-if="form.type === 'interview' && form.subtype"
                            class="space-y-4 rounded-xl border border-violet-200/60 bg-violet-50/30 p-4 dark:border-violet-800/40 dark:bg-violet-900/10"
                        >
                            <h4 class="text-xs font-bold tracking-widest text-violet-600 uppercase">{{ __('Interview Configuration') }}</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Date & Time') }}</label>
                                    <input
                                        v-model="form.config.scheduled_at"
                                        type="datetime-local"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Duration (min)') }}</label>
                                    <input
                                        v-model.number="form.config.duration"
                                        type="number"
                                        min="1"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div v-if="form.subtype === 'onsite'">
                                <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase"
                                    >{{ __('Location') }} <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.config.location"
                                    type="text"
                                    class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    :placeholder="__('e.g. 123 Main St, Floor 5')"
                                />
                            </div>
                            <div v-if="form.subtype === 'phone'">
                                <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase"
                                    >{{ __('Phone Details') }} <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.config.phone_details"
                                    type="text"
                                    class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    :placeholder="__('e.g. +1 (555) 123-4567')"
                                />
                            </div>
                            <div v-if="form.subtype === 'online'" class="space-y-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase">{{ __('Meeting Platform') }}</label>
                                    <select
                                        v-model="form.config.meeting_platform"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    >
                                        <option value="">{{ __('Select Platform') }}</option>
                                        <option value="Zoom">Zoom</option>
                                        <option value="Google Meet">Google Meet</option>
                                        <option value="Microsoft Teams">Microsoft Teams</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 uppercase"
                                        >{{ __('Meeting Link') }} <span class="text-rose-500">*</span></label
                                    >
                                    <input
                                        v-model="form.config.meeting_link"
                                        type="url"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        :placeholder="__('https://zoom.us/j/...')"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Toggles -->
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                                <input v-model="form.is_enabled" type="checkbox" class="rounded border-slate-300 text-brand-600" />
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Enabled') }}</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                                <input v-model="form.send_mail_on_trigger" type="checkbox" class="rounded border-slate-300 text-brand-600" />
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Send Mail') }}</span>
                            </label>
                        </div>

                        <!-- Interviewers (for interview type) -->
                        <div>
                            <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase">{{ __('Assign Person(s)') }}</label>
                            <div class="mt-2 max-h-40 space-y-1 overflow-y-auto rounded-xl border border-slate-200 p-2 dark:border-slate-700">
                                <button
                                    v-for="interviewer in interviewers"
                                    :key="interviewer.id"
                                    @click="toggleInterviewer(interviewer.id)"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-start text-xs transition-all"
                                    :class="
                                        form.interviewer_ids.includes(interviewer.id)
                                            ? 'bg-brand-50 font-bold text-brand-700 dark:bg-brand-900/20 dark:text-brand-400'
                                            : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800'
                                    "
                                >
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-bold"
                                        :class="
                                            form.interviewer_ids.includes(interviewer.id)
                                                ? 'bg-brand-100 text-brand-700 dark:bg-brand-800 dark:text-brand-300'
                                                : 'bg-slate-100 text-slate-500 dark:bg-slate-700'
                                        "
                                    >
                                        {{ interviewer.name.charAt(0) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold">{{ interviewer.name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ interviewer.email }}</p>
                                    </div>
                                    <svg
                                        v-if="form.interviewer_ids.includes(interviewer.id)"
                                        class="h-4 w-4 shrink-0 text-emerald-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <p v-if="interviewers.length === 0" class="py-3 text-center text-xs text-slate-400">
                                    {{ __('No persons available.') }}
                                </p>
                            </div>
                        </div>

                        <!-- Sorting Criteria -->
                        <div v-if="form.type === 'sorting'">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase">{{ __('Sorting Criteria') }}</label>
                                <button
                                    @click="addCriterion"
                                    class="flex items-center gap-1 rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-600 transition-all hover:bg-amber-100 dark:bg-amber-900/20"
                                >
                                    <Plus :size="12" />{{ __('Add') }}
                                </button>
                            </div>
                            <div class="mt-2 space-y-2">
                                <div v-for="(criterion, cIndex) in form.criteria" :key="cIndex" class="flex items-center gap-2">
                                    <input
                                        v-model="criterion.label"
                                        type="text"
                                        class="flex-1 rounded-lg border-slate-200 bg-slate-50 px-3 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        :placeholder="__('Criterion label')"
                                    />
                                    <input
                                        v-model.number="criterion.weight"
                                        type="number"
                                        min="1"
                                        class="w-16 rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-center text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                    <button
                                        @click="removeCriterion(cIndex)"
                                        class="flex h-6 w-6 items-center justify-center rounded-md text-slate-400 hover:bg-rose-50 hover:text-rose-500"
                                    >
                                        <Trash2 :size="12" />
                                    </button>
                                </div>
                                <p v-if="form.criteria.length === 0" class="py-2 text-center text-[10px] text-slate-400">
                                    {{ __('No criteria defined.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                        <button
                            @click="emit('close')"
                            class="rounded-xl px-4 py-2 text-sm font-bold text-slate-500 transition-all hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            @click="handleSave"
                            class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white transition-all hover:bg-brand-700 disabled:opacity-50"
                        >
                            {{ stage ? __('Update Stage') : __('Add Stage') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-from > div:last-child,
.modal-leave-to > div:last-child {
    transform: translateX(100%);
}
</style>
