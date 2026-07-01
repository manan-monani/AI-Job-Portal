<script setup>
import BlankLayout from '@/Layouts/BlankLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';

defineProps({
    stage: Object,
    jobTitle: String,
});
</script>

<template>
    <Head :title="`${stage.title} - Already Participated`" />

    <BlankLayout>
        <div class="bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-900/5 sm:rounded-2xl sm:px-10">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                <CheckCircle2 class="h-10 w-10 text-green-600" aria-hidden="true" />
            </div>

            <h2 class="mb-2 text-2xl font-bold tracking-tight text-slate-900">Assessment Completed</h2>

            <p class="mx-auto mb-8 max-w-md text-slate-600">
                You have already submitted your {{ stage.title.toLowerCase() }} for the <strong>{{ jobTitle }}</strong> position. Duplicate
                submissions are not allowed.
            </p>

            <div class="inline-block rounded-lg bg-slate-50 p-4 text-sm text-slate-500">
                Our hiring team will review your submission and notify you of the next steps.
            </div>
        </div>
    </BlankLayout>
</template>
