<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { ArrowLeftRight } from 'lucide-vue-next';

interface Props {
    showLabel?: boolean;
    className?: string;
}

const props = withDefaults(defineProps<Props>(), {
    showLabel: true,
    className: ''
});

const direction = ref(localStorage.getItem('direction') || 'ltr');

const toggleDirection = () => {
    direction.value = direction.value === 'ltr' ? 'rtl' : 'ltr';
    localStorage.setItem('direction', direction.value);
    document.documentElement.setAttribute('dir', direction.value);
};

onMounted(() => {
    document.documentElement.setAttribute('dir', direction.value);
});
</script>

<template>
    <button 
        @click="toggleDirection" 
        :class="[
            'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center shadow-sm whitespace-nowrap',
            showLabel ? 'p-1.5 sm:px-3 sm:py-1.5' : 'p-1.5',
            className
        ]"
        :title="__('Toggle Direction')"
    >
        <ArrowLeftRight :size="16" :class="{ 'sm:me-2': showLabel }" />
        <span v-if="showLabel" class="hidden sm:inline">{{ direction.toUpperCase() }}</span>
    </button>
</template>
