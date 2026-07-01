<script setup lang="ts">
import { ChevronDown, Search, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface Option {
    id: number | string;
    name: string;
}

const props = defineProps<{
    modelValue: number | string | null | undefined;
    options: Option[];
    placeholder?: string;
    searchPlaceholder?: string;
    disabled?: boolean;
    clearable?: boolean;
}>();

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const selectRef = ref<HTMLElement | null>(null);
const triggerRef = ref<HTMLElement | null>(null);
const dropdownStyle = ref({
    top: '0px',
    left: '0px',
    width: '0px',
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const query = searchQuery.value.toLowerCase();
    return props.options.filter((option) => option.name.toLowerCase().includes(query));
});

const selectedOption = computed(() => {
    return props.options.find((opt) => opt.id == props.modelValue);
});

const updateDropdownPosition = () => {
    if (!triggerRef.value) return;
    const rect = triggerRef.value.getBoundingClientRect();
    const scrollY = window.scrollY || window.pageYOffset;
    const scrollX = window.scrollX || window.pageXOffset;

    dropdownStyle.value = {
        top: `${rect.bottom + scrollY + 4}px`, // 4px gap
        left: `${rect.left + scrollX}px`,
        width: `${rect.width}px`,
    };
};

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        updateDropdownPosition();
        // Add listeners for scroll and resize to keep position matched
        window.addEventListener('scroll', updateDropdownPosition, true);
        window.addEventListener('resize', updateDropdownPosition);
    } else {
        window.removeEventListener('scroll', updateDropdownPosition, true);
        window.removeEventListener('resize', updateDropdownPosition);
    }
};

const selectOption = (option: Option) => {
    emit('update:modelValue', option.id);
    emit('change', option.id);
    isOpen.value = false;
    window.removeEventListener('scroll', updateDropdownPosition, true);
    window.removeEventListener('resize', updateDropdownPosition);
};

const clearSelection = (e: Event) => {
    e.stopPropagation();
    emit('update:modelValue', '');
    emit('change', '');
};

const handleClickOutside = (event: MouseEvent) => {
    if (selectRef.value && !selectRef.value.contains(event.target as Node)) {
        // Also check if the click was inside the teleported dropdown
        const dropdown = document.querySelector('.searchable-select-dropdown');
        if (dropdown && dropdown.contains(event.target as Node)) return;

        isOpen.value = false;
        window.removeEventListener('scroll', updateDropdownPosition, true);
        window.removeEventListener('resize', updateDropdownPosition);
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('scroll', updateDropdownPosition, true);
    window.removeEventListener('resize', updateDropdownPosition);
});

// Reset search when closing
watch(isOpen, (val) => {
    if (!val) {
        searchQuery.value = '';
    }
});
</script>

<template>
    <div ref="selectRef" class="relative w-full">
        <div
            ref="triggerRef"
            @click="toggleDropdown"
            class="flex h-10 w-full cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm transition-all focus-within:ring-2 focus-within:ring-brand-500/20 dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-200"
            :class="{ 'cursor-not-allowed opacity-50': disabled, 'border-brand-500/50 ring-2 ring-brand-500/20': isOpen }"
        >
            <div class="flex-1 truncate">
                <span v-if="selectedOption" class="font-medium text-slate-900 dark:text-white">{{ selectedOption.name }}</span>
                <span v-else class="text-slate-400">{{ placeholder || __('Select option') }}</span>
            </div>

            <div class="ml-2 flex items-center gap-2">
                <button
                    v-if="clearable && modelValue"
                    @click="clearSelection"
                    class="rounded-full p-0.5 text-slate-400 transition-colors hover:bg-slate-200 dark:hover:bg-slate-700"
                >
                    <X :size="14" />
                </button>
                <ChevronDown :size="16" class="text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" />
            </div>
        </div>

        <!-- Dropdown Menu -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div
                    v-if="isOpen"
                    class="searchable-select-dropdown fixed z-[9999] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                    :style="dropdownStyle"
                >
                    <!-- Search Input -->
                    <div class="sticky top-0 border-b border-slate-100 bg-slate-50/50 p-3 dark:border-slate-800 dark:bg-slate-900/50">
                        <div class="relative">
                            <Search :size="14" class="absolute top-1/2 left-3 -translate-y-1/2 text-slate-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                ref="searchInput"
                                :placeholder="searchPlaceholder || __('Search...')"
                                class="h-9 w-full rounded-lg border border-slate-200 bg-white pr-4 pl-9 text-xs focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                @click.stop
                            />
                        </div>
                    </div>

                    <!-- Options List -->
                    <div class="custom-scrollbar max-h-60 overflow-y-auto py-2">
                        <div
                            v-for="option in filteredOptions"
                            :key="option.id"
                            @click="selectOption(option)"
                            class="cursor-pointer px-4 py-2.5 text-sm transition-colors"
                            :class="
                                modelValue == option.id
                                    ? 'bg-brand-50 font-bold text-brand-700 dark:bg-brand-900/20 dark:text-brand-400'
                                    : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800'
                            "
                        >
                            {{ option.name }}
                        </div>

                        <div v-if="filteredOptions.length === 0" class="px-4 py-8 text-center text-xs text-slate-400">
                            {{ __('No options found') }}
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
