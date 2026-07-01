<script setup lang="ts">
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Crown, Pencil, PlusCircle, Search, Shield, Trash2 } from 'lucide-vue-next';
import { PropType, ref, watch } from 'vue';

const props = defineProps({
    users: Object as PropType<any>,
});

const search = ref('');

watch(
    search,
    debounce((value) => {
        router.get(admin.users.index.url(), { search: value }, { preserveState: true, replace: true });
    }, 300),
);

const toggleStatus = (user: any) => {
    const newStatus = user.status === 'active' ? 'inactive' : 'active';
    router.patch(
        admin.users.status.url(user.id),
        {
            status: newStatus,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Optimistic update or handled by Inertia reload
            },
        },
    );
};

// Delete Logic
const showDeleteModal = ref(false);
const userToDelete = ref<any>(null);
const processingDelete = ref(false);

const confirmDelete = (user: any) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!userToDelete.value) return;

    processingDelete.value = true;
    router.delete(admin.users.destroy.url(userToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        },
        onFinish: () => {
            processingDelete.value = false;
        },
    });
};
</script>

<template>
    <Head :title="__('Member Directory')" />

    <AdminLayout>
        <div class="animate-fade-in space-y-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-6 text-start md:flex-row md:items-center">
                <div class="text-start">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Organization Members') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Manage member identities and access levels.') }}</p>
                </div>
                <Link
                    :href="admin.users.create ? admin.users.create.url() : '#'"
                    class="flex items-center rounded-2xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-brand-600/20 transition-all hover:bg-brand-700 active:scale-95"
                >
                    <PlusCircle :size="18" class="me-2" /> {{ __('Add New Member') }}
                </Link>
            </div>

            <!-- Table Card -->
            <div class="overflow-hidden rounded-[2rem] border border-slate-200/60 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <!-- Search -->
                <div class="border-b border-slate-100 p-4 dark:border-slate-700">
                    <div class="relative w-full sm:w-64">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <Search :size="16" class="text-slate-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="__('Search members...')"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-4 pl-10 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:outline-none dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-200 dark:placeholder:text-slate-500"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-start">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-700/50 dark:bg-slate-800/20">
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Identity') }}
                                </th>
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Access Role') }}
                                </th>
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Account Status') }}
                                </th>
                                <th class="px-6 py-4 text-end text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-start dark:divide-slate-800/50">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="group transition-all duration-200 hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img
                                            :src="
                                                user.profile_image
                                                    ? '/storage/' + user.profile_image
                                                    : `https://ui-avatars.com/api/?name=${user.name}&background=random`
                                            "
                                            class="h-11 w-11 rounded-2xl bg-slate-100 object-cover dark:bg-slate-800"
                                        />
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ user.name }}</p>
                                            <p class="text-[11px] text-slate-500">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/20"
                                        >
                                            <Crown
                                                v-if="user.type === 'super-admin' || user.roles?.[0]?.name.toLowerCase().includes('admin')"
                                                :size="14"
                                            />
                                            <Shield v-else :size="14" />
                                        </span>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{
                                            user.type === 'super-admin' ? 'Super Admin' : user.roles?.[0]?.name || user.type
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <label
                                        class="relative inline-flex items-center"
                                        :class="user.type === 'super-admin' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="user.status === 'active'"
                                            :disabled="user.type === 'super-admin'"
                                            class="peer sr-only"
                                            @change="toggleStatus(user)"
                                        />
                                        <div
                                            class="peer h-5 w-9 rounded-full bg-slate-200 peer-checked:bg-brand-600 peer-focus:ring-2 peer-focus:ring-brand-300 peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-slate-700 dark:peer-focus:ring-brand-800"
                                        ></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            v-if="user.type !== 'super-admin' || $page.props.auth.user.type === 'super-admin'"
                                            :href="admin.users.edit ? admin.users.edit.url(user.id) : '#'"
                                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition-all hover:bg-brand-50 hover:text-brand-600 dark:bg-slate-800 dark:hover:bg-brand-900/20"
                                        >
                                            <Pencil :size="14" />
                                        </Link>
                                        <button
                                            v-if="user.type !== 'super-admin'"
                                            @click="confirmDelete(user)"
                                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-500 dark:bg-slate-800 dark:hover:bg-rose-900/20"
                                        >
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links.length > 3" class="flex justify-end border-t border-slate-100 p-6 dark:border-slate-700">
                    <!-- Simple pagination implementation -->
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, key) in users.links"
                            :key="key"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="rounded-lg border border-transparent px-3 py-1 text-xs transition-colors"
                            :class="
                                link.active
                                    ? 'bg-brand-600 text-white'
                                    : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'
                            "
                            :preserve-state="true"
                        />
                    </div>
                </div>
            </div>

            <ConfirmationModal
                :show="showDeleteModal"
                :title="__('Delete Member?')"
                :message="__('Are you sure you want to delete this member? This action cannot be undone.')"
                :confirmText="__('Yes, Delete Member')"
                :cancelText="__('No, Cancel')"
                type="danger"
                :processing="processingDelete"
                @close="showDeleteModal = false"
                @confirm="handleDelete"
            />
        </div>
    </AdminLayout>
</template>
