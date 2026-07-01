<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

interface Toast {
    id: string;
    message: string;
    type: 'success' | 'error' | 'warning';
}

const page = usePage();
const toasts = ref<Toast[]>([]);

const addToast = (message: string, type: 'success' | 'error' | 'warning') => {
    const id = Date.now().toString() + Math.random();
    toasts.value.push({ id, message, type });

    setTimeout(() => removeToast(id), 5000);
};

const removeToast = (id: string) => {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

watch(() => page.props.flash, (flash: any) => {
    if (flash?.success) {
        addToast(flash.success, 'success');
    }
    if (flash?.error) {
        addToast(flash.error, 'error');
    }
    if (flash?.warning) {
        addToast(flash.warning, 'warning');
    }
    // Handle error bags if needed, but usually those are inline. 
    // Sometimes 'message' is used for generic success/error but let's stick to these 3.
}, { deep: true });

onMounted(() => {
    // Check if there are initial flash messages (e.g. from redirect)
    const flash = page.props.flash as any;
    if (flash?.success) addToast(flash.success, 'success');
    if (flash?.error) addToast(flash.error, 'error');
    if (flash?.warning) addToast(flash.warning, 'warning');
});
</script>

<template>
    <div class="fixed top-24 right-6 z-50 flex flex-col space-y-4 pointer-events-none">
        <TransitionGroup name="toast">
            <div 
                v-for="toast in toasts" 
                :key="toast.id"
                class="pointer-events-auto min-w-[300px] max-w-md bg-white dark:bg-slate-900 shadow-xl rounded-xl border-l-[6px] overflow-hidden transform transition-all duration-300 relative group"
                :class="{
                    'border-[var(--toast-success)]': toast.type === 'success',
                    'border-[var(--toast-error)]': toast.type === 'error',
                    'border-[var(--toast-warning)]': toast.type === 'warning',
                }"
            >
                <div class="p-4 flex items-start space-x-3">
                    <!-- Icons -->
                    <div class="flex-shrink-0">
                        <svg v-if="toast.type === 'success'" class="w-6 h-6 text-[var(--toast-success)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-if="toast.type === 'error'" class="w-6 h-6 text-[var(--toast-error)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-if="toast.type === 'warning'" class="w-6 h-6 text-[var(--toast-warning)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 pt-0.5">
                        <h3 
                            class="text-sm font-bold capitalize mb-1"
                            :class="{
                                'text-[var(--toast-success)]': toast.type === 'success',
                                'text-[var(--toast-error)]': toast.type === 'error',
                                'text-[var(--toast-warning)]': toast.type === 'warning',
                            }"
                        >
                            {{ toast.type }}
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                            {{ toast.message }}
                        </p>
                    </div>

                    <!-- Close Button -->
                    <button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Progress Bar -->
                <div class="absolute bottom-0 left-0 h-1 bg-current opacity-20 animate-wiggle w-full" 
                    :class="{
                        'text-[var(--toast-success)]': toast.type === 'success',
                        'text-[var(--toast-error)]': toast.type === 'error',
                        'text-[var(--toast-warning)]': toast.type === 'warning',
                    }"
                    style="animation: progress 5s linear forwards;"
                ></div>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.4s cubic-bezier(0.5, 0, 0, 1.25);
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100px) scale(0.9);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100px) scale(0.9);
}

@keyframes progress {
    from { width: 100%; }
    to { width: 0%; }
}
</style>
