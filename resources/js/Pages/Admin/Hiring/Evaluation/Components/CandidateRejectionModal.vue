<script setup lang="ts">
import Modal from '@/Components/Common/Modal.vue';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';
import { router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, Eye, Loader2, Mail, XCircle } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    candidate: any;
    stage: any;
    jobTitle: string;
    globalRejectionEmail?: {
        subject: string | null;
        body: string | null;
    };
}>();

const emit = defineEmits(['close']);

const DEFAULT_SUBJECT = 'Update on Your Application for {{job_title}} at {{company_name}}';
const DEFAULT_BODY = `Hi {{candidate_name}},

Thank you for taking the time to apply for the **{{job_title}}** role at **{{company_name}}** and for your interest in joining our team.

After careful review, we have decided to move forward with other candidates whose experience more closely matches the current requirements of this position.

We truly appreciate your time and effort throughout the process. We encourage you to apply again for future openings that match your profile.

Wishing you all the best in your job search and future career.

Best regards,
6am Career Hiring Team
6am Career`;

const mode = ref<'edit' | 'preview'>('edit');

const form = useForm({
    status: 'rejected',
    send_email: true,
    email_subject: '',
    email_body: '',
});

const renderedSubject = computed(() => {
    return replaceTokens(form.email_subject);
});

const renderedBody = computed(() => {
    // Escape HTML to prevent XSS in preview, then convert specific patterns
    const escaped = replaceTokens(form.email_body)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    return escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
});

const replaceTokens = (text: string) => {
    return text
        .replace(/{{candidate_name}}/g, props.candidate?.name || '')
        .replace(/{{job_title}}/g, props.jobTitle || '')
        .replace(/{{company_name}}/g, '6am Career');
};

watch(
    () => props.show,
    (isVisible) => {
        if (isVisible) {
            mode.value = 'edit';

            // Prioritize Global -> Default
            let subject = props.globalRejectionEmail?.subject || DEFAULT_SUBJECT;
            let body = props.globalRejectionEmail?.body || DEFAULT_BODY;

            // Normalize tokens to {{token}} format safely by stripping extra braces before re-wrapping
            subject = subject
                .replace(/{{/g, '{')
                .replace(/}}/g, '}')
                .replace(/{([^}]+)}/g, '{{$1}}');
            body = body
                .replace(/{{/g, '{')
                .replace(/}}/g, '}')
                .replace(/{([^}]+)}/g, '{{$1}}');

            form.email_subject = subject;
            form.email_body = body;
            form.send_email = true;
        }
    },
);

const submit = () => {
    const data = form.send_email
        ? {
              ...form.data(),
              email_subject: replaceTokens(form.email_subject),
              email_body: replaceTokens(form.email_body),
          }
        : { status: 'rejected', send_email: false };

    router.put(admin.evaluations.candidates.status.url(props.candidate.id), data, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            form.reset();
        },
    });
};
</script>

<template>
    <Modal :show="show" @close="emit('close')" :max-width="form.send_email ? '6xl' : '2xl'">
        <div class="flex flex-col overflow-hidden bg-white dark:bg-slate-900" :class="form.send_email ? 'h-[85vh]' : 'h-auto'">
            <!-- Header -->
            <div
                class="flex items-center justify-between border-b border-slate-100 bg-rose-50/30 px-6 py-3 dark:border-slate-800 dark:bg-rose-900/10"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400"
                    >
                        <XCircle :size="20" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Reject Candidate') }}</h3>
                        <p class="text-[10px] font-medium text-slate-500">
                            {{ __('You are about to reject :name from the pipeline.', { name: candidate?.name }) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Interaction Section (Top Buttons & Checkbox) -->
            <div class="shrink-0 border-b border-slate-100 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col items-center justify-center gap-4">
                    <!-- Buttons -->
                    <div class="flex items-center gap-4">
                        <button
                            @click="emit('close')"
                            class="min-w-32 rounded-xl border border-slate-200 bg-white px-8 py-3 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            @click="submit"
                            :disabled="form.processing || (form.send_email && (!form.email_subject || !form.email_body))"
                            class="flex min-w-40 items-center justify-center gap-2 rounded-xl bg-rose-600 px-8 py-3 text-sm font-extrabold text-white shadow-lg shadow-rose-200 transition-all hover:bg-rose-700 active:scale-95 disabled:opacity-50 disabled:active:scale-100 dark:shadow-none"
                        >
                            <Loader2 v-if="form.processing" :size="16" class="animate-spin" />
                            {{ form.send_email ? __('Reject with mail') : __('Reject') }}
                        </button>
                    </div>

                    <label class="group flex cursor-pointer items-center gap-2.5 transition-all active:scale-[0.98]">
                        <input
                            type="checkbox"
                            v-model="form.send_email"
                            class="h-4 w-4 rounded border-slate-300 text-rose-600 transition-all focus:ring-rose-500/20 dark:border-slate-700 dark:bg-slate-900"
                        />
                        <span class="text-xs font-bold text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-slate-200">
                            {{ __('Send Rejection email checkbox') }}
                        </span>
                    </label>
                </div>
            </div>

            <!-- Content Area (Email Editor) -->
            <div v-if="form.send_email" class="animate-in fade-in slide-in-from-top-4 flex flex-1 flex-col overflow-hidden duration-500">
                <!-- Editor Panel (Full Width) -->
                <div class="flex flex-1 flex-col overflow-hidden bg-white dark:bg-slate-900">
                    <!-- Tabs -->
                    <div
                        class="flex items-center gap-1 border-b border-slate-100 bg-slate-50/20 px-6 py-1.5 dark:border-slate-800 dark:bg-slate-800/20"
                    >
                        <button
                            @click="mode = 'edit'"
                            class="rounded-lg px-5 py-1 text-[10px] font-bold tracking-tight transition-all"
                            :class="
                                mode === 'edit'
                                    ? 'bg-slate-900 text-white shadow-md dark:bg-slate-700'
                                    : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300'
                            "
                        >
                            {{ __('Edit') }}
                        </button>
                        <button
                            @click="mode = 'preview'"
                            class="flex items-center gap-2 rounded-lg px-5 py-1 text-[10px] font-bold tracking-tight transition-all"
                            :class="
                                mode === 'preview'
                                    ? 'bg-emerald-600 text-white shadow-md dark:bg-emerald-500'
                                    : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300'
                            "
                        >
                            <Eye :size="10" />
                            {{ __('Preview') }}
                        </button>
                    </div>

                    <!-- Workspace (Padding Reduced) -->
                    <div class="flex-1 overflow-y-auto px-6 py-5 lg:px-10">
                        <!-- Edit View -->
                        <div v-if="mode === 'edit'" class="animate-in fade-in mx-auto max-w-5xl space-y-6 duration-300">
                            <div class="space-y-2">
                                <label class="text-[9px] leading-none font-bold tracking-widest text-slate-400 uppercase">{{ __('Subject') }}</label>
                                <input
                                    v-model="form.email_subject"
                                    type="text"
                                    class="w-full rounded-2xl border-transparent bg-slate-50 px-6 py-4 text-sm font-semibold shadow-inner transition-all placeholder:text-slate-300 focus:border-rose-500/20 focus:bg-white focus:ring-4 focus:ring-rose-500/5 dark:bg-slate-800/50 dark:text-white dark:focus:bg-slate-800"
                                    :placeholder="__('Enter subject...')"
                                />
                                <p v-if="form.errors.email_subject" class="ml-1 text-[10px] font-bold text-rose-500">
                                    {{ form.errors.email_subject }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[9px] leading-none font-bold tracking-widest text-slate-400 uppercase">{{ __('Body') }}</label>
                                <textarea
                                    v-model="form.email_body"
                                    rows="12"
                                    class="w-full resize-none rounded-2xl border-transparent bg-slate-50 p-6 font-mono text-sm leading-relaxed shadow-inner transition-all placeholder:text-slate-300 focus:border-rose-500/20 focus:bg-white focus:ring-4 focus:ring-rose-500/5 dark:bg-slate-800/50 dark:text-white dark:focus:bg-slate-800"
                                    :placeholder="__('Write your message here...')"
                                ></textarea>
                                <p v-if="form.errors.email_body" class="ml-1 text-[10px] font-bold text-rose-500">{{ form.errors.email_body }}</p>
                            </div>
                        </div>

                        <!-- Preview View -->
                        <div v-else class="animate-in zoom-in-95 mx-auto max-w-5xl duration-300">
                            <div
                                class="rounded-3xl border border-slate-100 bg-white p-6 shadow-2xl shadow-slate-200/50 lg:p-10 dark:border-slate-800 dark:bg-slate-800/80 dark:shadow-none"
                            >
                                <div class="mb-8 border-b border-slate-100 pb-6 dark:border-slate-800">
                                    <span class="text-[9px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Final Email Subject') }}</span>
                                    <h4 class="mt-2 text-lg leading-tight font-bold text-slate-900 lg:text-xl dark:text-white">
                                        {{ renderedSubject || __('(No Subject)') }}
                                    </h4>
                                </div>
                                <div class="prose prose-sm max-w-none text-slate-600 dark:text-slate-300 dark:prose-invert">
                                    <div class="text-sm leading-[1.8] lg:text-base" v-html="renderedBody || __('(No Message)')"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State (When Email Toggle is False) -->
            <div v-else class="flex flex-1 flex-col items-center justify-center p-20 dark:bg-slate-900">
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-100/50 text-slate-300 dark:bg-slate-800/50 dark:text-slate-700"
                >
                    <Mail :size="40" stroke-width="1.5" />
                </div>
                <h4 class="mt-6 text-sm font-bold text-slate-900 dark:text-white">{{ __('Email notification is disabled') }}</h4>
            </div>

            <!-- Bottom Warning Banner (Full Width) -->
            <div
                v-if="form.send_email"
                class="flex items-center justify-center gap-3 border-t border-amber-100 bg-amber-50 px-8 py-3 dark:border-amber-900/20 dark:bg-amber-900/10"
            >
                <AlertTriangle class="h-4 w-4 text-amber-500" />
                <p class="text-[11px] font-bold text-amber-900 dark:text-amber-200">
                    {{ __('Saving this email will update the global rejection template.') }}
                </p>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Custom typography for the editor */
textarea {
    scrollbar-width: thin;
    scrollbar-color: #f1f5f9 transparent;
}

textarea::-webkit-scrollbar {
    width: 6px;
}

textarea::-webkit-scrollbar-thumb {
    background-color: #f1f5f9;
    border-radius: 10px;
}

.dark textarea::-webkit-scrollbar-thumb {
    background-color: #1e293b;
}
</style>
```
