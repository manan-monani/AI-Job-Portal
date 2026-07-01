<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import { ChevronRight } from 'lucide-vue-next';
import admin from '@/routes/admin';

const props = defineProps({
    user: Object as PropType<any>,
    roles: Array as PropType<any[]>,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role_id: props.user.role_id,
    password: '',
    password_confirmation: '',
});

const showPasswordFields = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.put(admin.users.update.url(props.user.id), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
            showPasswordFields.value = false;
        },
    });
};
</script>

<template>
    <Head :title="__('Edit User Data')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-2xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                     <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <Link :href="admin.users.index.url()" class="hover:text-brand-500">{{ __('Member Directory') }}</Link>
                        <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                         <span class="text-brand-500">{{ __('Edit Member') }}</span>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Edit User Data') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Update member details and access role.') }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-700 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Full Name') }}</label>
                        <input v-model="form.name" type="text" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Email Address') }}</label>
                        <input v-model="form.email" type="email" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Access Role') }}</label>
                        <select v-model="form.role_id" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white">
                            <option value="" disabled>{{ __('Select Role') }}</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                        <p v-if="form.errors.role_id" class="text-red-500 text-xs mt-1">{{ form.errors.role_id }}</p>
                    </div>

                    <!-- Password Update Section -->
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                        <button 
                            type="button" 
                            @click="showPasswordFields = !showPasswordFields"
                            class="text-sm font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors flex items-center gap-2"
                        >
                            <span v-if="!showPasswordFields">{{ __('Change Password') }}</span>
                            <span v-else>{{ __('Cancel Password Change') }}</span>
                        </button>

                        <div v-if="showPasswordFields" class="mt-4 space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('New Password') }}</label>
                                <div class="relative">
                                    <input 
                                        v-model="form.password" 
                                        :type="showNewPassword ? 'text' : 'password'" 
                                        class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 pr-10"
                                        placeholder="Enter new password"
                                    >
                                    <button 
                                        type="button" 
                                        @click="showNewPassword = !showNewPassword"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                    >
                                        <svg v-if="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Confirm New Password') }}</label>
                                <div class="relative">
                                    <input 
                                        v-model="form.password_confirmation" 
                                        :type="showConfirmPassword ? 'text' : 'password'" 
                                        class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 pr-10"
                                        placeholder="Confirm new password"
                                    >
                                    <button 
                                        type="button" 
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                    >
                                        <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="admin.users.index.url()" class="px-6 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-center">
                            {{ __('Cancel') }}
                        </Link>
                        <button type="submit" :disabled="form.processing" class="bg-brand-600 text-white px-8 py-3 rounded-2xl font-bold text-sm shadow-xl shadow-brand-600/20 hover:bg-brand-700 transition-all disabled:opacity-50">
                            {{ form.processing ? __('Saving...') : __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
