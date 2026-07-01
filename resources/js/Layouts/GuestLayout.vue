<script setup lang="ts">
import Toast from '@/Components/Common/Toast.vue';
import DirectionToggle from '@/Components/DirectionToggle.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

onMounted(() => {
    const direction = localStorage.getItem('direction') || 'ltr';
    document.documentElement.setAttribute('dir', direction);
});
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 transition-colors duration-300 sm:justify-center sm:pt-0 dark:bg-gray-900">
        <Toast />
        <div class="absolute top-4 right-4 flex items-center space-x-2">
            <DirectionToggle :show-label="false" />
            <LanguageSwitcher />
            <ThemeToggle />
        </div>
        <div>
            <Link href="/" class="mb-6 flex justify-center">
                <img
                    v-if="$page.props.branding.business_settings?.logo_url"
                    :src="$page.props.branding.business_settings.logo_url"
                    class="h-10 w-auto"
                />
                <span v-else class="text-3xl font-bold text-gray-800 dark:text-white">{{
                    $page.props.branding?.business_settings?.business_name || 'Laravel Factory'
                }}</span>
            </Link>
        </div>

        <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800">
            <slot />
        </div>
    </div>
</template>
