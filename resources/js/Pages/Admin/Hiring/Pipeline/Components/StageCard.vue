<script setup lang="ts">
import { ArrowDown, ArrowUp, FileSearch, Lock, Mail, MailX, Pencil, Trash2, Users } from 'lucide-vue-next';
import { computed } from 'vue';

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
    exam_mode?: string;
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
    interviewers?: { id: number; name: string; email: string }[];
    criteria: { label: string; weight: number }[];
    email_template?: { id: number; subject: string } | null;
    quiz_questions?: { question: string; options: { option_text: string; is_correct: boolean }[] }[];
}

const props = defineProps<{
    stage: Stage;
    index: number;
    total: number;
    interviewers: { id: number; name: string; email: string }[];
}>();

const emit = defineEmits(['edit', 'remove', 'toggle', 'move-up', 'move-down', 'email-preview']);

const typeConfig = computed(() => {
    const configs: Record<string, { label: string; color: string; bg: string }> = {
        system: { label: 'System', color: 'text-slate-600', bg: 'bg-slate-100 dark:bg-slate-700' },
        sorting: { label: 'Sorting', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-900/20' },
        assessment: { label: 'Assessment', color: 'text-blue-600', bg: 'bg-blue-50 dark:bg-blue-900/20' },
        interview: { label: 'Interview', color: 'text-violet-600', bg: 'bg-violet-50 dark:bg-violet-900/20' },
    };
    return configs[props.stage.type] || configs.system;
});

const subtypeLabel = computed(() => {
    const labels: Record<string, string> = {
        task: 'Task',
        exam: 'Exam',
        quiz: 'Quiz',
        onsite: 'Onsite',
        phone: 'Phone',
        online: 'Online',
    };
    return props.stage.subtype ? labels[props.stage.subtype] || props.stage.subtype : null;
});

const assignedInterviewers = computed(() => {
    if (props.stage.interviewers && props.stage.interviewers.length > 0) {
        return props.stage.interviewers;
    }
    return props.interviewers.filter((i) => props.stage.interviewer_ids?.includes(i.id));
});

const canMoveUp = computed(() => !props.stage.is_system && props.index > 0);
const canMoveDown = computed(() => !props.stage.is_system && props.index < props.total - 1);

const configSummary = computed(() => {
    const c = props.stage.config || {};
    const items: string[] = [];
    if (c.duration) items.push(`${c.duration} min`);
    if (c.total_marks) items.push(`${c.total_marks} marks`);
    if (c.due_date) items.push(`Due: ${c.due_date}`);
    if (c.scheduled_at) items.push(`At: ${c.scheduled_at}`);
    if (c.location) items.push(c.location);
    if (c.meeting_platform) items.push(c.meeting_platform);
    if (props.stage.subtype === 'exam' && c.exam_mode) items.push(c.exam_mode === 'online' ? '💻 Online' : '🏢 Onsite');
    if (props.stage.subtype === 'quiz' && props.stage.quiz_questions?.length) items.push(`${props.stage.quiz_questions.length} questions`);
    if (c.passing_marks) items.push(`Pass: ${c.passing_marks}`);
    return items;
});
</script>

<template>
    <div
        class="flex items-start gap-4 px-6 py-4 transition-all"
        :class="[stage.is_enabled ? '' : 'opacity-50', !stage.is_system ? 'hover:bg-slate-50/50 dark:hover:bg-slate-800/30' : '']"
    >
        <!-- Order indicator -->
        <div class="flex flex-col items-center gap-1 pt-1">
            <div class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold" :class="[typeConfig.bg, typeConfig.color]">
                {{ stage.sort_order }}
            </div>
            <div v-if="index < total - 1" class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>
        </div>

        <!-- Content -->
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <Lock v-if="stage.is_system" :size="12" class="shrink-0 text-slate-400" />
                <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ stage.title }}</h4>
                <span
                    class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold tracking-wider uppercase"
                    :class="[typeConfig.bg, typeConfig.color]"
                >
                    {{ typeConfig.label }}
                </span>
                <span
                    v-if="subtypeLabel"
                    class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-slate-700 dark:text-slate-400"
                >
                    {{ subtypeLabel }}
                </span>
                <span v-if="!stage.is_enabled" class="text-[10px] font-bold text-slate-400 uppercase">{{ __('Disabled') }}</span>
            </div>

            <p v-if="stage.instructions" class="mt-1 line-clamp-1 text-xs text-slate-500 dark:text-slate-400">{{ stage.instructions }}</p>

            <!-- Meta row -->
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <!-- Mail badge -->
                <button
                    v-if="stage.send_mail_on_trigger && stage.id"
                    @click="emit('email-preview')"
                    class="flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600 transition-all hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/30"
                >
                    <Mail :size="12" />
                    <span>{{ __('Email Template') }}</span>
                </button>
                <div v-else class="flex items-center gap-1 text-[10px] text-slate-400">
                    <MailX :size="12" />
                    <span>{{ __('Mail Off') }}</span>
                </div>

                <!-- Config summary chips -->
                <div
                    v-for="(item, i) in configSummary"
                    :key="i"
                    class="flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-slate-700 dark:text-slate-400"
                >
                    {{ item }}
                </div>

                <!-- Interviewers -->
                <div v-if="assignedInterviewers.length > 0" class="flex items-center gap-1 text-[10px] text-slate-400">
                    <Users :size="12" />
                    <span>{{ assignedInterviewers.map((i) => i.name).join(', ') }}</span>
                </div>

                <!-- Criteria -->
                <div v-if="stage.type === 'sorting' && stage.criteria?.length > 0" class="flex items-center gap-1 text-[10px] text-amber-500">
                    <FileSearch :size="12" />
                    <span>{{ stage.criteria.length }} {{ __('criteria') }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex shrink-0 items-center gap-1">
            <button
                v-if="canMoveUp"
                @click="emit('move-up')"
                class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700"
            >
                <ArrowUp :size="14" />
            </button>
            <button
                v-if="canMoveDown"
                @click="emit('move-down')"
                class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700"
            >
                <ArrowDown :size="14" />
            </button>
            <button
                v-if="stage.is_system"
                @click="emit('toggle')"
                class="flex h-7 items-center gap-1 rounded-lg px-2 text-[10px] font-bold transition-all"
                :class="
                    stage.is_enabled
                        ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/20'
                        : 'bg-slate-100 text-slate-400 hover:bg-slate-200 dark:bg-slate-700'
                "
            >
                {{ stage.is_enabled ? __('Enabled') : __('Disabled') }}
            </button>
            <button
                v-if="!stage.is_system"
                @click="emit('edit')"
                class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-all hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20"
            >
                <Pencil :size="14" />
            </button>
            <button
                v-if="!stage.is_system"
                @click="emit('remove')"
                class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-900/20"
            >
                <Trash2 :size="14" />
            </button>
        </div>
    </div>
</template>
