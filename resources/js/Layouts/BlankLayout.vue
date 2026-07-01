<script setup>
import Toast from '@/Components/Common/Toast.vue';
import { usePage } from '@inertiajs/vue3';

// Brand details for the header
const appName = usePage().props.branding?.business_settings?.app_name || 'Job Portal';
const appLogo = usePage().props.branding?.business_settings?.logo_url || '/logo.png';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-slate-50 px-4 pt-12 pb-16 sm:px-6 lg:px-8">
        <Toast />
        <!-- Logo Header -->
        <div class="pointer-events-none mb-8 select-none">
            <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-10 w-auto" />
            <span v-else class="text-2xl font-bold text-slate-900">{{ appName }}</span>
        </div>

        <!-- Main Content Slot -->
        <main class="w-full max-w-2xl">
            <slot />
        </main>
    </div>
</template>
