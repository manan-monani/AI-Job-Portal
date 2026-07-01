<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileText, Save, Scale } from 'lucide-vue-next';
import Toast from '@/Components/Common/Toast.vue';
import RichTextEditor from '@/Components/Common/RichTextEditor.vue';

import { update } from '@/routes/admin/legal';

const props = defineProps<{
    settings: Record<string, string>;
}>();

const tabs = [
    { id: 'privacy_policy', label: 'Privacy Policy' },
    { id: 'terms_and_conditions', label: 'Terms & Conditions' },
    { id: 'about_us', label: 'About Us' },
];

const activeTab = ref(tabs[0].id);

const form = useForm({
    privacy_policy: props.settings.privacy_policy || '',
    terms_and_conditions: props.settings.terms_and_conditions || '',
    about_us: props.settings.about_us || '',
});

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="__('Legal Center')" />
    <AdminLayout>
        <Toast />
        <div class="space-y-6 animate-fade-in max-w-5xl mx-auto w-full p-4 lg:p-0">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Legal Center') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Manage legal documents and policies.') }}</p>
                </div>
            </div>

            <!-- Content -->
            <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex flex-col lg:flex-row min-h-[600px]">
                    <!-- Sidebar Tabs -->
                    <div class="w-full lg:w-64 bg-slate-50/50 dark:bg-slate-800/30 border-e border-slate-200 dark:border-slate-800 p-4 space-y-2">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="w-full text-start px-4 py-2 rounded-xl font-bold text-sm transition-all flex items-center gap-3"
                            :class="activeTab === tab.id 
                                ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700' 
                                : 'text-slate-500 hover:bg-white/50 dark:hover:bg-slate-800/50'"
                        >
                            <Scale :size="16" v-if="tab.id.includes('policy')" />
                            <FileText :size="16" v-else />
                            {{ __(tab.label) }}
                        </button>
                    </div>

                    <!-- Editor Area -->
                    <div class="flex-1 p-4 lg:p-5">
                        <div v-show="activeTab === 'privacy_policy'" class="h-full flex flex-col">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">{{ __('Privacy Policy') }}</h3>
                            <div class="flex-1">
                                <RichTextEditor v-model="form.privacy_policy" />
                            </div>
                            <div class="sticky bottom-0 z-10 flex justify-end mt-4 p-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm border-t border-slate-100 dark:border-slate-800 rounded-b-2xl -mx-6 -mb-6">
                                <button 
                                    @click="submit"
                                    :disabled="form.processing"
                                    class="bg-brand-600 dark:bg-brand-500 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg hover:opacity-90 transition-all flex items-center gap-2"
                                >
                                    <Save :size="18" />
                                    {{ form.processing ? __('Saving...') : __('Save Changes') }}
                                </button>
                            </div>
                        </div>

                        <div v-show="activeTab === 'terms_and_conditions'" class="h-full flex flex-col">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">{{ __('Terms & Conditions') }}</h3>
                            <div class="flex-1">
                                <RichTextEditor v-model="form.terms_and_conditions" />
                            </div>
                            <div class="sticky bottom-0 z-10 flex justify-end mt-4 p-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm border-t border-slate-100 dark:border-slate-800 rounded-b-2xl -mx-6 -mb-6">
                                <button 
                                    @click="submit"
                                    :disabled="form.processing"
                                    class="bg-brand-600 dark:bg-brand-500 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg hover:opacity-90 transition-all flex items-center gap-2"
                                >
                                    <Save :size="18" />
                                    {{ form.processing ? __('Saving...') : __('Save Changes') }}
                                </button>
                            </div>
                        </div>

                        <div v-show="activeTab === 'about_us'" class="h-full flex flex-col">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">{{ __('About Us') }}</h3>
                            <div class="flex-1">
                                <RichTextEditor v-model="form.about_us" />
                            </div>
                            <div class="sticky bottom-0 z-10 flex justify-end mt-4 p-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm border-t border-slate-100 dark:border-slate-800 rounded-b-2xl -mx-6 -mb-6">
                                <button 
                                    @click="submit"
                                    :disabled="form.processing"
                                    class="bg-brand-600 dark:bg-brand-500 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg hover:opacity-90 transition-all flex items-center gap-2"
                                >
                                    <Save :size="18" />
                                    {{ form.processing ? __('Saving...') : __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
