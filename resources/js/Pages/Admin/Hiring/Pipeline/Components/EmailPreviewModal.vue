<script setup lang="ts">
import admin from '@/routes/admin';
import axios from 'axios';
import { Eye, Save, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Token {
    token: string;
    label: string;
}

const props = defineProps<{
    show: boolean;
    stageId: number | null;
}>();

const emit = defineEmits(['close']);

const loading = ref(false);
const saving = ref(false);
const saveSuccess = ref(false);
const saveError = ref('');
const activeTab = ref<'edit' | 'preview'>('edit');

const subject = ref('');
const body = ref('');
const renderedSubject = ref('');
const renderedBody = ref('');
const tokens = ref<Token[]>([]);
const sampleData = ref<Record<string, string>>({});

watch(
    () => props.show,
    async (isVisible) => {
        if (isVisible && props.stageId) {
            await fetchPreview();
        }
    },
);

const fetchPreview = async () => {
    if (!props.stageId) return;
    loading.value = true;
    try {
        const { data } = await axios.get(admin.stages.email_preview.url(props.stageId));
        subject.value = data.subject;
        body.value = data.body;
        renderedSubject.value = data.rendered_subject;
        renderedBody.value = data.rendered_body;
        tokens.value = data.tokens;
        sampleData.value = data.sample_data;
    } catch (e) {
        console.error('Failed to load email preview:', e);
    } finally {
        loading.value = false;
    }
};

const refreshPreview = () => {
    let s = subject.value;
    let b = body.value;
    for (const [key, val] of Object.entries(sampleData.value)) {
        const placeholder = `{{${key}}}`;
        s = s.replaceAll(placeholder, val);
        b = b.replaceAll(placeholder, val);
    }
    renderedSubject.value = s;
    renderedBody.value = b;
    activeTab.value = 'preview';
};

const insertToken = (token: string) => {
    body.value += token;
};

const saveTemplate = async () => {
    if (!props.stageId) return;
    saving.value = true;
    saveSuccess.value = false;
    saveError.value = '';
    try {
        await axios.put(admin.stages.email_template.update.url(props.stageId), {
            subject: subject.value,
            body: body.value,
        });
        saveSuccess.value = true;
        refreshPreview();
        setTimeout(() => (saveSuccess.value = false), 3000);
    } catch (e: any) {
        saveError.value = e.response?.data?.message || 'Failed to save template.';
        setTimeout(() => (saveError.value = ''), 5000);
    } finally {
        saving.value = false;
    }
};

const bodyAsHtml = computed(() => {
    return renderedBody.value.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
});
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-60 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="emit('close')"></div>

                <div class="relative z-10 flex h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Email Template Editor') }}</h3>
                        <button
                            @click="emit('close')"
                            class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            <X :size="18" />
                        </button>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="flex flex-1 items-center justify-center">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-brand-200 border-t-brand-600"></div>
                    </div>

                    <!-- Content -->
                    <div v-else class="flex flex-1 overflow-hidden">
                        <!-- Token Panel (Left) -->
                        <div
                            class="w-56 shrink-0 overflow-y-auto border-r border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <h4 class="mb-3 text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Available Tokens') }}</h4>
                            <div class="space-y-1">
                                <button
                                    v-for="t in tokens"
                                    :key="t.token"
                                    @click="insertToken(t.token)"
                                    class="block w-full rounded-lg px-2 py-1.5 text-start text-[10px] transition-all hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20"
                                >
                                    <code class="block font-bold text-brand-600">{{ t.token }}</code>
                                    <span class="text-slate-400">{{ t.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Editor/Preview (Right) -->
                        <div class="flex flex-1 flex-col overflow-hidden">
                            <!-- Tabs -->
                            <div class="flex items-center gap-1 border-b border-slate-100 px-6 py-2 dark:border-slate-800">
                                <button
                                    @click="activeTab = 'edit'"
                                    class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
                                    :class="
                                        activeTab === 'edit'
                                            ? 'bg-brand-50 text-brand-700 dark:bg-brand-900/20 dark:text-brand-400'
                                            : 'text-slate-400 hover:text-slate-600'
                                    "
                                >
                                    {{ __('Edit') }}
                                </button>
                                <button
                                    @click="refreshPreview()"
                                    class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
                                    :class="
                                        activeTab === 'preview'
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400'
                                            : 'text-slate-400 hover:text-slate-600'
                                    "
                                >
                                    <Eye :size="12" />
                                    {{ __('Preview') }}
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto p-6">
                                <!-- Edit Tab -->
                                <div v-if="activeTab === 'edit'" class="space-y-4">
                                    <div>
                                        <label class="mb-1 block text-[10px] font-bold tracking-widest text-slate-500 uppercase">{{
                                            __('Subject')
                                        }}</label>
                                        <input
                                            v-model="subject"
                                            type="text"
                                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[10px] font-bold tracking-widest text-slate-500 uppercase">{{
                                            __('Body')
                                        }}</label>
                                        <textarea
                                            v-model="body"
                                            rows="18"
                                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-4 font-mono text-sm leading-relaxed focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        ></textarea>
                                        <p class="mt-1 text-[10px] text-slate-400">
                                            {{ __('Use tokens from the left panel. Supports **bold** with double asterisks.') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Preview Tab -->
                                <div v-if="activeTab === 'preview'" class="space-y-4">
                                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                                        <div class="mb-4 border-b border-slate-100 pb-3 dark:border-slate-700">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase">{{ __('Subject') }}</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ renderedSubject }}</p>
                                        </div>
                                        <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300" v-html="bodyAsHtml"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                        <div class="flex-1">
                            <p v-if="saveSuccess" class="text-xs font-bold text-emerald-600">✓ {{ __('Template saved successfully!') }}</p>
                            <p v-if="saveError" class="text-xs font-bold text-rose-600">{{ saveError }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                @click="emit('close')"
                                class="rounded-xl px-4 py-2 text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                            >
                                {{ __('Close') }}
                            </button>
                            <button
                                @click="saveTemplate"
                                :disabled="saving"
                                class="flex items-center gap-2 rounded-xl bg-slate-700 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 disabled:opacity-50"
                            >
                                <Save :size="14" />
                                {{ saving ? __('Saving...') : __('Save Template') }}
                            </button>
                        </div>
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
.modal-enter-from > div:nth-child(2),
.modal-leave-to > div:nth-child(2) {
    transform: scale(0.95);
}
</style>
