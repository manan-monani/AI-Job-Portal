<script setup>
import BlankLayout from '@/Layouts/BlankLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ArrowLeft, ShieldAlert } from 'lucide-vue-next';

defineProps({
    message: String,
    jobTitle: String,
});
</script>

<template>
    <Head title="Access Restricted - Assessment" />

    <BlankLayout>
        <div class="bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-900/5 sm:rounded-2xl sm:px-10">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                <ShieldAlert class="h-10 w-10 text-amber-600" aria-hidden="true" />
            </div>

            <h2 class="mb-2 text-2xl font-bold tracking-tight text-slate-900">Access Restricted</h2>

            <p class="mx-auto mb-8 max-w-md text-slate-600">
                {{ message || 'You are not eligible for this assessment or the link has expired.' }}
            </p>

            <div class="mb-8 inline-block rounded-lg bg-slate-50 p-4 text-sm text-slate-500">
                If you believe this is an error, please contact the hiring team for <strong>{{ jobTitle }}</strong> directly.
            </div>

            <div>
                <a href="/" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-500">
                    <ArrowLeft class="h-4 w-4" />
                    Back to Careers
                </a>
            </div>
        </div>
    </BlankLayout>
</template>
