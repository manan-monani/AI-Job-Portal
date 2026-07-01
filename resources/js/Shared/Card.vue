<script setup lang="ts">
defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div v-if="title" class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ title }}</h3>
        </div>
        <div class="p-6 bg-white border-b border-gray-200">
            <slot />
        </div>
    </div>
</template>
