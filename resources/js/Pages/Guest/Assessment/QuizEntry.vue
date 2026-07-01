<script setup>
import BlankLayout from '@/Layouts/BlankLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Clock, Lightbulb, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    application: Object,
    stage: Object,
    job: Object,
    quizConfig: Object,
    questionCount: Number,
    status: String,
    hasSubmitted: Boolean,
    startUrl: String,
});

const isProcessing = ref(false);

const form = useForm({
    email: '',
});

const startQuiz = () => {
    isProcessing.value = true;
    form.post(props.startUrl, {
        preserveScroll: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Quiz: ${stage.title} - ${job.title}`" />

    <BlankLayout>
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ stage.title }}</h1>
            <p class="mt-2 text-lg text-slate-600">
                Position: <span class="font-medium text-slate-900">{{ job.title }}</span>
            </p>
        </div>

        <div class="overflow-hidden bg-white shadow-sm ring-1 ring-slate-900/5 sm:rounded-2xl">
            <div class="border-b border-slate-100 bg-slate-50 p-6 sm:p-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="rounded-lg bg-blue-50 p-3"><Clock class="text-brand-primary h-6 w-6" /></div>
                        <div>
                            <div class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">Duration</div>
                            <div class="text-lg font-extrabold text-slate-900">{{ quizConfig?.duration || 30 }} Min</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="rounded-lg bg-amber-50 p-3"><Lightbulb class="h-6 w-6 text-amber-500" /></div>
                        <div>
                            <div class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">Questions</div>
                            <div class="text-lg font-extrabold text-slate-900">{{ questionCount || 0 }} Items</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="rounded-lg bg-purple-50 p-3"><ShieldCheck class="h-6 w-6 text-purple-600" /></div>
                        <div>
                            <div class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">Attempts</div>
                            <div class="text-lg font-extrabold text-slate-900">Once Only</div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 rounded-xl bg-blue-50/50 p-4 ring-1 ring-blue-100">
                    <h4 class="flex items-center gap-2 text-xs font-bold text-blue-900 uppercase">
                        <ShieldCheck class="h-4 w-4" />
                        Participation Rules
                    </h4>
                    <ul class="mt-3 space-y-2 text-sm text-blue-800">
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-blue-400"></span>
                            Each candidate can participate in this assessment only once.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-blue-400"></span>
                            The quiz will automatically end and submit when the time reaches zero.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-blue-400"></span>
                            Once started, do not refresh or close the browser window.
                        </li>
                    </ul>
                </div>

                <div
                    class="prose prose-sm mt-8 max-w-none whitespace-pre-line prose-slate"
                    v-html="stage.instructions || 'Please carefully read the questions. The timer will begin immediately once you start.'"
                ></div>
            </div>

            <div class="p-6 sm:p-8" v-if="!hasSubmitted">
                <form @submit.prevent="startQuiz" class="space-y-6">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-6">
                        <label for="email" class="block text-sm font-bold tracking-tight text-slate-900 uppercase">Verify Email Address</label>
                        <p class="mt-1 text-xs text-slate-500">
                            Please enter the email address used during your application for verification before starting.
                        </p>

                        <div class="relative mt-4">
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="block w-full rounded-xl border-slate-200 py-3 pr-10 pl-4 text-sm shadow-sm transition-all focus:border-brand-500 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="e.g. yourname@example.com"
                                required
                            />
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <ShieldCheck class="h-5 w-5 text-slate-400" />
                            </div>
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-xs font-medium text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-8 py-3 text-lg font-extrabold text-white shadow-lg shadow-brand-200 transition-all hover:bg-brand-500 hover:shadow-brand-300 disabled:opacity-50 sm:w-auto"
                            :disabled="form.processing || isProcessing"
                        >
                            Start Quiz Now
                        </button>
                    </div>
                </form>
            </div>

            <div v-else class="bg-slate-50 p-8 text-center">
                <h3 class="text-xl font-medium text-slate-900">Quiz Completed</h3>
                <p class="mt-1 text-sm text-slate-500">You have already completed this assessment.</p>
            </div>
        </div>
    </BlankLayout>
</template>
