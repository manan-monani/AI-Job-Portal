<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, GitBranch, Plus, Power, Save } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import EmailPreviewModal from './Components/EmailPreviewModal.vue';
import StageCard from './Components/StageCard.vue';
import StageForm from './Components/StageForm.vue';

interface Criterion {
    id?: number;
    label: string;
    weight: number;
}

interface Interviewer {
    id: number;
    name: string;
    email: string;
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
    interviewers?: Interviewer[];
    criteria: Criterion[];
    email_template?: { id: number; subject: string } | null;
    quiz_questions: { question: string; options: { option_text: string; is_correct: boolean }[] }[];
}

const props = defineProps<{
    job: { id: number; title: string; slug: string; status: string; pipeline_enabled: boolean };
    stages: Stage[];
    interviewers: Interviewer[];
}>();

const mapStages = (stages: any[]): Stage[] =>
    stages.map((s: any) => ({
        ...s,
        subtype: s.subtype || null,
        config: s.config || {},
        interviewer_ids: s.interviewers?.map((i: Interviewer) => i.id) || [],
        criteria: s.criteria || [],
        quiz_questions:
            s.quiz_questions?.map((q: any) => ({
                question: q.question,
                options: q.options?.map((o: any) => ({ option_text: o.option_text, is_correct: o.is_correct })) || [],
            })) || [],
    }));

const localStages = ref<Stage[]>(mapStages(props.stages));

watch(
    () => props.stages,
    (newStages) => {
        localStages.value = mapStages(newStages);
    },
);

const showStageForm = ref(false);
const editingStageIndex = ref<number | null>(null);
const savingPipeline = ref(false);

const togglePipeline = () => {
    router.patch(admin.jobs.pipeline.toggle.url(props.job.id), {}, { preserveScroll: true });
};

// Email preview state
const showEmailPreview = ref(false);
const emailPreviewStageId = ref<number | null>(null);

const orderedStages = computed(() => [...localStages.value].sort((a, b) => a.sort_order - b.sort_order));

const openNewStageForm = () => {
    editingStageIndex.value = null;
    showStageForm.value = true;
};

const openEditStageForm = (index: number) => {
    const stage = orderedStages.value[index];
    const actualIndex = localStages.value.findIndex((s) => s === stage);
    editingStageIndex.value = actualIndex;
    showStageForm.value = true;
};

const addStage = (stageData: Omit<Stage, 'sort_order' | 'is_system' | 'system_key'>) => {
    const onboardStage = localStages.value.find((s) => s.system_key === 'onboard_mail');
    const newSortOrder = onboardStage ? onboardStage.sort_order - 1 : localStages.value.length + 1;
    if (onboardStage && newSortOrder >= onboardStage.sort_order) {
        onboardStage.sort_order = newSortOrder + 1;
    }
    localStages.value.push({ ...stageData, sort_order: newSortOrder, is_system: false, system_key: null });
    reindex();
    showStageForm.value = false;
};

const updateStage = (index: number, stageData: Partial<Stage>) => {
    const stage = localStages.value[index];
    if (stage.is_system) {
        stage.is_enabled = stageData.is_enabled ?? stage.is_enabled;
    } else {
        Object.assign(stage, stageData);
    }
    showStageForm.value = false;
};

const removeStage = (index: number) => {
    const stage = orderedStages.value[index];
    if (stage.is_system) return;
    const actualIndex = localStages.value.findIndex((s) => s === stage);
    localStages.value.splice(actualIndex, 1);
    reindex();
};

const toggleSystemStage = (index: number) => {
    const stage = orderedStages.value[index];
    if (!stage.is_system) return;
    stage.is_enabled = !stage.is_enabled;
};

const reindex = () => {
    localStages.value
        .sort((a, b) => a.sort_order - b.sort_order)
        .forEach((s, i) => {
            s.sort_order = i + 1;
        });
};

const moveStage = (fromIndex: number, direction: 'up' | 'down') => {
    const stages = orderedStages.value;
    const toIndex = direction === 'up' ? fromIndex - 1 : fromIndex + 1;
    if (toIndex < 0 || toIndex >= stages.length) return;
    const fromStage = stages[fromIndex];
    const toStage = stages[toIndex];
    if (fromStage.is_system || toStage.is_system) return;
    const tempOrder = fromStage.sort_order;
    fromStage.sort_order = toStage.sort_order;
    toStage.sort_order = tempOrder;
};

const openEmailPreview = (index: number) => {
    const stage = orderedStages.value[index];
    if (stage.id) {
        emailPreviewStageId.value = stage.id;
        showEmailPreview.value = true;
    }
};

const savePipeline = () => {
    savingPipeline.value = true;
    reindex();

    const stagesPayload = localStages.value.map((s) => ({
        id: s.id || undefined,
        type: s.type,
        subtype: s.subtype || null,
        title: s.title,
        instructions: s.instructions || '',
        config: s.type === 'assessment' || s.type === 'interview' ? s.config : null,
        sort_order: s.sort_order,
        is_enabled: s.is_enabled,
        send_mail_on_trigger: s.send_mail_on_trigger,
        interviewer_ids: s.interviewer_ids,
        criteria: s.type === 'sorting' ? s.criteria : [],
        quiz_questions: s.type === 'assessment' && s.subtype === 'quiz' ? s.quiz_questions : [],
    }));

    router.put(
        admin.jobs.pipeline.update.url(props.job.id),
        { stages: stagesPayload },
        {
            preserveScroll: true,
            onSuccess: () => {
                savingPipeline.value = false;
            },
            onError: () => {
                savingPipeline.value = false;
            },
        },
    );
};

const getEditingStage = computed(() => {
    if (editingStageIndex.value === null) return null;
    return localStages.value[editingStageIndex.value];
});

const isDirty = computed(() => {
    return JSON.stringify(localStages.value) !== JSON.stringify(mapStages(props.stages));
});

const handleBeforeUnload = (e: BeforeUnloadEvent) => {
    if (isDirty.value) {
        e.preventDefault();
        e.returnValue = '';
    }
};

onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

router.on('before', (event) => {
    if (isDirty.value && !savingPipeline.value && event.detail.visit.method === 'get') {
        if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
            event.preventDefault();
        }
    }
});
</script>

<template>
    <Head :title="__('Pipeline Builder') + ' — ' + job.title" />

    <AdminLayout>
        <div class="animate-fade-in space-y-6">
            <!-- Sticky Header -->
            <div
                class="sticky top-16 z-20 -mx-4 -mt-6 mb-6 border-b border-slate-100 bg-slate-50/80 p-4 backdrop-blur-md lg:-mx-6 lg:px-6 dark:border-slate-700/50 dark:bg-slate-900/80"
            >
                <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="admin.jobs.index.url()"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition-all hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700"
                        >
                            <ArrowLeft :size="16" />
                        </Link>
                        <div>
                            <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                                <GitBranch :size="20" class="me-2 inline text-violet-500" />
                                {{ __('Pipeline Builder') }}
                            </h2>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ job.title }}</p>
                        </div>
                        <!-- Pipeline Toggle -->
                        <button
                            @click="togglePipeline"
                            class="flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-bold transition-all"
                            :class="
                                job.pipeline_enabled
                                    ? 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400'
                                    : 'border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800'
                            "
                        >
                            <Power :size="14" />
                            {{ job.pipeline_enabled ? __('Enabled') : __('Disabled') }}
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <span v-if="isDirty" class="flex animate-pulse items-center gap-1.5 text-xs font-bold text-amber-600 dark:text-amber-400">
                            <AlertTriangle :size="14" />
                            {{ __('Unsaved Changes') }}
                        </span>
                        <button
                            @click="openNewStageForm"
                            class="flex items-center gap-2 rounded-xl bg-slate-200/50 px-4 py-2 text-sm font-bold text-slate-700 transition-all hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        >
                            <Plus :size="16" />
                            {{ __('Add Stage') }}
                        </button>
                        <button
                            @click="savePipeline"
                            :disabled="savingPipeline"
                            class="flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-brand-600/20 transition-all hover:bg-brand-700 active:scale-95 disabled:opacity-50"
                        >
                            <Save :size="16" />
                            {{ savingPipeline ? __('Saving...') : __('Save Pipeline') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pipeline Timeline -->
            <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-100 bg-slate-50/50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Evaluation Stages') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Configure the evaluation flow for candidates applying to this position.') }}</p>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    <div v-if="orderedStages.length === 0" class="p-12 text-center">
                        <GitBranch :size="48" class="mx-auto text-slate-300" />
                        <p class="mt-4 text-sm text-slate-500">{{ __('No stages configured yet.') }}</p>
                    </div>

                    <div v-for="(stage, index) in orderedStages" :key="stage.id || index" class="group">
                        <StageCard
                            :stage="stage"
                            :index="index"
                            :total="orderedStages.length"
                            :interviewers="interviewers"
                            @edit="openEditStageForm(index)"
                            @remove="removeStage(index)"
                            @toggle="toggleSystemStage(index)"
                            @move-up="moveStage(index, 'up')"
                            @move-down="moveStage(index, 'down')"
                            @email-preview="openEmailPreview(index)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Stage Form -->
        <StageForm
            :show="showStageForm"
            :stage="getEditingStage"
            :interviewers="interviewers"
            @close="showStageForm = false"
            @save="editingStageIndex !== null ? updateStage(editingStageIndex, $event) : addStage($event)"
        />

        <!-- Email Preview Modal -->
        <EmailPreviewModal :show="showEmailPreview" :stage-id="emailPreviewStageId" @close="showEmailPreview = false" />
    </AdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
