<script setup>
import BlankLayout from '@/Layouts/BlankLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle, FileText, Paperclip } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    application: Object,
    stage: Object,
    job: Object,
    hasSubmitted: Boolean,
});

const isProcessing = ref(false);
const showSuccess = computed(() => usePage().props.flash?.success);

// We use Inertia form for tracking file payload
const form = useForm({
    email: '',
    description: '',
    file: null,
});

// Since the page requires a signed URL, we can't use standard named route functions without passing the signature query parameters.
// Our current request URL is already the target for the POST containing all the right signatures.
const submitAssessment = () => {
    isProcessing.value = true;

    // Send standard inertia POST to current URL
    form.post(window.location.pathname + window.location.search, {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
        onSuccess: () => {
            isProcessing.value = false;
        },
        onError: () => {
            isProcessing.value = false;
        },
    });
};

const handleFileDrop = (event) => {
    const file = event.dataTransfer?.files[0] || event.target?.files[0];
    if (file && file.size <= 5 * 1024 * 1024) {
        // 5MB check
        form.file = file;
    } else if (file) {
        alert('File must be smaller than 5MB');
        event.target.value = '';
    }
};
</script>

<template>
    <Head :title="`${stage.title} - ${job.company_name}`" />

    <BlankLayout>
        <!-- Header Section -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ stage.title }}</h1>
            <p class="mt-2 text-lg text-slate-600">
                Position: <span class="font-medium text-slate-900">{{ job.title }}</span>
            </p>
        </div>

        <div class="overflow-hidden bg-white shadow-sm ring-1 ring-slate-900/5 sm:rounded-2xl">
            <!-- Instructions Panel -->
            <div class="bg-slate-50/50 p-6 sm:p-8">
                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="rounded-lg bg-blue-50 p-3"><FileText class="text-brand-primary h-6 w-6" /></div>
                        <div>
                            <div class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">Assessment Type</div>
                            <div class="text-lg font-extrabold text-slate-900">
                                {{ ['exam', 'online'].includes(stage.subtype) ? 'Online Exam' : 'Task Submission' }}
                            </div>
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

                <div class="mb-8 rounded-xl bg-amber-50/50 p-4 ring-1 ring-amber-100">
                    <h4 class="flex items-center gap-2 text-xs font-bold text-amber-900 uppercase">
                        <ShieldCheck class="h-4 w-4" />
                        Submission Rules
                    </h4>
                    <ul class="mt-3 space-y-2 text-sm text-amber-800">
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-amber-400"></span>
                            You can submit this assessment only once. Please ensure all details are correct.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-amber-400"></span>
                            Uploads are restricted to PDF, DOC, or ZIP formats (Max 5MB).
                        </li>
                    </ul>
                </div>

                <h3 class="mb-4 flex items-center gap-2 text-sm leading-6 font-semibold text-slate-900">
                    <FileText class="text-brand-primary h-5 w-5" />
                    Instructions
                </h3>
                <div
                    class="prose prose-sm max-w-none whitespace-pre-line prose-slate"
                    v-html="stage.instructions || 'Please complete the assigned task and submit your files and description below.'"
                ></div>
            </div>

            <!-- Submission Form -->
            <div class="p-6 sm:p-8" v-if="!showSuccess && !hasSubmitted">
                <form @submit.prevent="submitAssessment" class="space-y-6">
                    <!-- Candidate Email Verification -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-6">
                        <label for="email" class="block text-sm font-bold tracking-tight text-slate-900 uppercase">Verify Email Address</label>
                        <p class="mt-1 text-xs text-slate-500">Please enter the email address used during your application for verification.</p>

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
                                <CheckCircle class="h-5 w-5 text-slate-400" />
                            </div>
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-xs font-medium text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label for="file_upload" class="block text-sm leading-6 font-medium text-slate-900">Upload Submission (Optional)</label>
                        <div
                            class="mt-2 flex justify-center rounded-lg border border-dashed border-slate-900/25 px-6 py-8"
                            @dragover.prevent
                            @drop.prevent="handleFileDrop"
                        >
                            <div class="text-center">
                                <Paperclip v-if="!form.file" class="mx-auto h-12 w-12 text-slate-300" aria-hidden="true" />
                                <FileText v-else class="text-brand-primary mx-auto h-12 w-12" aria-hidden="true" />

                                <div class="mt-4 flex justify-center text-sm leading-6 text-slate-600">
                                    <label
                                        for="file_upload"
                                        class="text-brand-primary focus-within:ring-brand-primary hover:text-brand-secondary relative cursor-pointer rounded-md bg-white font-semibold focus-within:ring-2 focus-within:outline-none"
                                    >
                                        <span>{{ form.file ? 'Change file' : 'Upload a file' }}</span>
                                        <input id="file_upload" name="file_upload" type="file" class="sr-only" @change="handleFileDrop" />
                                    </label>
                                    <p class="pl-1" v-if="!form.file">or drag and drop</p>
                                </div>
                                <p class="mt-1 text-xs leading-5 text-slate-600" v-if="!form.file">PDF, DOC, ZIP up to 5MB</p>
                                <p class="mt-2 text-sm font-medium text-slate-900" v-else>{{ form.file.name }}</p>
                            </div>
                        </div>
                        <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm leading-6 font-medium text-slate-900">Description / Comments</label>
                        <div class="mt-1 mb-2 text-xs text-slate-500">Provide your written answers, links, or notes regarding your submission.</div>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="5"
                            class="focus:ring-brand-primary block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-slate-300 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6"
                            placeholder="Write your submission details here..."
                            required
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end border-t border-slate-100 pt-6">
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-8 py-3 text-lg font-extrabold text-white shadow-lg shadow-brand-200 transition-all hover:bg-brand-500 hover:shadow-brand-300 disabled:opacity-50 sm:w-auto"
                            :disabled="form.processing || isProcessing"
                        >
                            Submit Assessment
                        </button>
                    </div>
                </form>
            </div>

            <!-- Success State -->
            <div v-else-if="showSuccess || hasSubmitted" class="p-12 text-center text-green-600">
                <CheckCircle class="mx-auto mb-4 h-16 w-16 text-green-500" />
                <h3 class="text-2xl font-bold text-slate-900">Successfully Submitted</h3>
                <p class="mt-2 text-slate-600">Your assessment has been recorded. Our team will review it and get back to you soon.</p>
            </div>
        </div>
    </BlankLayout>
</template>
