<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import { PlusCircle, Crown, Shield, ShieldCheck, MoreVertical, Layers, Users, Trash2, ArrowRight, Plus } from 'lucide-vue-next';
import admin from '@/routes/admin';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';

defineProps({
    roles: Array as PropType<any[]>,
});

const getRoleIcon = (roleName: string) => {
    if (roleName.toLowerCase().includes('admin')) return Crown;
    if (roleName.toLowerCase().includes('moderator')) return Shield;
    return ShieldCheck;
};

const getRoleColor = (roleName: string) => {
    if (roleName.toLowerCase().includes('admin')) return 'text-brand-600 bg-brand-50 dark:bg-brand-500/10';
    if (roleName.toLowerCase().includes('moderator')) return 'text-slate-500 bg-slate-50 dark:bg-slate-800';
    return 'text-blue-500 bg-blue-50 dark:bg-blue-900/20';
};

// Deletion Logic
const showDeleteModal = ref(false);
const roleToDelete = ref<any>(null);
const processingDelete = ref(false);

const confirmDelete = (role: any) => {
    roleToDelete.value = role;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!roleToDelete.value) return;
    
    processingDelete.value = true;
    router.delete(admin.roles.destroy.url(roleToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            roleToDelete.value = null;
        },
        onFinish: () => {
            processingDelete.value = false;
        }
    });
};
</script>

<template>
    <Head :title="__('Access Roles')" />

    <AdminLayout>
        <div class="space-y-8 animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Access Roles') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Manage organizational security boundaries and permissions.') }}</p>
                </div>
                <Link :href="admin.roles.create ? admin.roles.create.url() : '#'" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-2xl font-bold text-sm shadow-xl shadow-brand-600/20 flex items-center transition-all active:scale-95">
                    <PlusCircle :size="18" class="me-2" /> {{ __('Create New Role') }}
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Role Card -->
                <div v-for="role in roles" :key="role.id" class="group bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200/60 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-inner transition-transform group-hover:scale-110" :class="getRoleColor(role.name)">
                                <component :is="getRoleIcon(role.name)" :size="20" />
                            </div>
                            <div class="flex flex-col items-end relative">
                                <span class="mt-2 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[9px] font-bold uppercase tracking-widest border border-emerald-100 dark:border-emerald-500/20">{{ __('Active') }}</span>
                            </div>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">{{ role.name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-6 h-8">{{ role.description || __('No description provided.') }}</p>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="p-2 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-1"><Layers :size="10" /> {{ __('Permissions') }}</p>
                                <p class="text-sm font-black text-brand-600">{{ role.permissions_count || 0 }} {{ __('Active') }}</p>
                            </div>
                            <div class="p-2 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-1"><Users :size="10" /> {{ __('Users') }}</p>
                                <p class="text-sm font-black text-blue-500">{{ role.users_count || 0 }} {{ __('Members') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between">
                         <div class="flex items-center gap-2">
                             <button @click="confirmDelete(role)" class="text-xs font-bold text-slate-400 hover:text-red-500 transition-colors flex items-center group/delete">
                                <Trash2 :size="12" class="me-1 group-hover/delete:scale-110 transition-transform" />
                                {{ __('Delete') }}
                             </button>
                        </div>
                        <Link :href="admin.roles.edit ? admin.roles.edit.url(role.id) : '#'" class="text-xs font-bold text-brand-600 hover:underline flex items-center group-hover:translate-x-1 transition-transform rtl:group-hover:-translate-x-1">
                            {{ __('Edit') }} <ArrowRight :size="10" class="ms-2 rtl:rotate-180" />
                        </Link>
                    </div>
                </div>

                <!-- Create New Role Card -->
                <Link :href="admin.roles.create ? admin.roles.create.url() : '#'" class="rounded-[2rem] border-2 border-dashed border-slate-200 dark:border-slate-700 p-5 flex flex-col items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-all group min-h-[300px]">
                    <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <Plus :size="20" />
                    </div>
                    <p class="mt-4 text-sm font-bold text-slate-400 group-hover:text-brand-500 transition-colors">{{ __('Define Custom Role') }}</p>
                    <p class="text-[10px] text-slate-300 mt-2 uppercase tracking-widest">{{ __('New Security Boundary') }}</p>
                </Link>
            </div>
        </div>

        <ConfirmationModal 
            :show="showDeleteModal" 
            :title="__('Delete Access Role?')"
            :message="__('Are you sure you want to delete this role? This action cannot be undone and may affect users assigned to this role.')"
            :confirmText="__('Yes, Delete Role')"
            :cancelText="__('No, Cancel')"
            type="danger"
            :processing="processingDelete"
            @close="showDeleteModal = false"
            @confirm="handleDelete"
        />
    </AdminLayout>
</template>
