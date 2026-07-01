<script setup lang="ts">
import { switchMethod } from '@/routes/locale';
import { usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const currentLocale = computed(() => page.props.locale as string);
const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const locales: Record<string, { name: string; flag: string }> = {
    en: { name: 'English', flag: '🇺🇸' },
    ar: { name: 'العربية', flag: '🇸🇦' },
};

const switchLocale = (locale: string) => {
    isOpen.value = false;
    // @ts-ignore
    window.location.href = switchMethod({ locale }).url;
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <button 
            @click.stop="toggleDropdown"
            class="flex items-center space-x-2 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-colors px-3 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            <span class="text-base">{{ locales[currentLocale]?.flag }}</span>
            <span class="hidden md:inline text-xs">{{ locales[currentLocale]?.name }}</span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div 
            v-show="isOpen"
            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-50 transform origin-top-right transition-all duration-200"
            :class="isOpen ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
        >
            <button
                v-for="(data, locale) in locales"
                :key="locale"
                @click="switchLocale(locale as string)"
                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between group transition-colors first:rounded-t-xl last:rounded-b-xl"
            >
                <div class="flex items-center space-x-3">
                    <span class="text-lg">{{ data.flag }}</span>
                    <span :class="{ 'font-bold text-primary-600 dark:text-primary-400': currentLocale === locale }">
                        {{ data.name }}
                    </span>
                </div>
                <svg v-if="currentLocale === locale" class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </div>
    </div>
</template>
