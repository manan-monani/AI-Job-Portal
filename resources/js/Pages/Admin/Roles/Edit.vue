<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PropType } from 'vue';
import { ChevronRight, Fingerprint } from 'lucide-vue-next';
import admin from '@/routes/admin';

const props = defineProps({
    role: {
        type: Object as PropType<any>,
        required: true,
    },
    permissions: Object as PropType<Record<string, any>>,
});

const form = useForm({
    name: props.role.name,
    description: props.role.description || '',
    // Add logic to populate permissions from role if your backend sends them,
    // otherwise assume role.permissions is an array of strings like in create
    permissions: props.role.permissions || [] as string[],
});

const togglePermission = (key: string) => {
    if (form.permissions.includes(key)) {
        form.permissions = form.permissions.filter((p: string) => p !== key);
    } else {
        form.permissions.push(key);
    }
};

const toggleModule = (moduleKey: string, subModules: any) => {
    const keys = Object.keys(subModules);
    const allSelected = keys.every(k => form.permissions.includes(k));
    
    if (allSelected) {
        form.permissions = form.permissions.filter((p: string) => !keys.includes(p));
    } else {
        keys.forEach(k => {
            if (!form.permissions.includes(k)) form.permissions.push(k);
        });
    }
};

const submit = () => {
    form.put(admin.roles.update.url(props.role.id), {
        onSuccess: () => {
            // Optional: redirect logic handled by controller
        },
    });
};
</script>

<template>
    <Head :title="__('Edit Role')" />

    <AdminLayout>
        <div class="space-y-8 animate-fade-in max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <Link :href="admin.roles.index.url()" class="hover:text-brand-500">{{ __('Access Roles') }}</Link>
                        <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                        <span class="text-brand-500">{{ __('Edit Role') }}</span>
                    </div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Edit Access Role') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Update permissions and role details.') }}</p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <Link :href="admin.roles.index.url()" class="flex-1 sm:flex-none px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-center">
                        {{ __('Cancel') }}
                    </Link>
                    <button @click="submit" :disabled="form.processing" class="flex-1 sm:flex-none bg-brand-600 text-white px-4 py-2 rounded-2xl font-bold text-sm shadow-xl shadow-brand-600/20 hover:bg-brand-700 transition-all disabled:opacity-50">
                        {{ form.processing ? __('Saving...') : __('Save Changes') }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Role Details -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-[2rem] border border-slate-200/60 dark:border-slate-700 shadow-sm">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-8 flex items-center">
                            <Fingerprint :size="16" class="me-3 text-brand-500" /> {{ __('Role Identity') }}
                        </h3>
                        <div class="space-y-6">
                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Role Name') }}</label>
                                <input v-model="form.name" type="text" :placeholder="__('e.g. Access Controller')" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Short Description') }}</label>
                                <textarea v-model="form.description" rows="3" :placeholder="__('e.g. Can manage all system settings')" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 resize-none text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500"></textarea>
                                <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permission Matrix -->
                <div class="lg:col-span-8 space-y-8">
                    <div v-for="(module, moduleKey) in permissions" :key="moduleKey" class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-700 dark:text-slate-300">{{ __(module.label) }}</h3>
                            </div>
                            <button @click="toggleModule(moduleKey as string, module.sub_modules)" class="text-[10px] font-bold text-brand-600 uppercase tracking-widest hover:underline">
                                {{ __('Select Section All') }}
                            </button>
                        </div>
                        
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label v-for="(sub, subKey) in module.sub_modules" :key="subKey" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all cursor-pointer">
                                <div>
                                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ __(sub.label) }}</p>
                                    <p class="text-[9px] text-slate-400 mt-0.5">{{ __(sub.description) }}</p>
                                </div>
                                <div class="relative inline-block w-9 h-5">
                                    <input 
                                        type="checkbox" 
                                        :checked="form.permissions.includes(String(subKey))" 
                                        @change="togglePermission(String(subKey))" 
                                        class="peer sr-only"
                                    >
                                    <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
