<script setup lang="ts">
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import Modal from '@/Components/Common/Modal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import admin from '@/routes/admin';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Pencil, PlusCircle, Search, Tag, Trash2 } from 'lucide-vue-next';
import { PropType, ref, watch } from 'vue';

const props = defineProps({
    tags: Object as PropType<any>,
});

const search = ref('');

watch(
    search,
    debounce((value) => {
        router.get(admin.tags.index.url(), { search: value }, { preserveState: true, replace: true });
    }, 300),
);

const showModal = ref(false);
const editingTag = ref<any>(null);

const form = useForm({
    name: '',
    description: '',
    status: true,
});

const openCreateModal = () => {
    editingTag.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (tag: any) => {
    editingTag.value = tag;
    form.name = tag.name;
    form.description = tag.description;
    form.status = !!tag.status;
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    if (editingTag.value) {
        form.put(admin.tags.update.url(editingTag.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(admin.tags.store.url(), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const toggleStatus = (tag: any) => {
    router.patch(
        admin.tags.status.url(tag.id),
        {
            status: !tag.status,
        },
        { preserveScroll: true },
    );
};

const showDeleteModal = ref(false);
const tagToDelete = ref<any>(null);
const processingDelete = ref(false);

const confirmDelete = (tag: any) => {
    tagToDelete.value = tag;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!tagToDelete.value) return;
    processingDelete.value = true;
    router.delete(admin.tags.destroy.url(tagToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            tagToDelete.value = null;
        },
        onFinish: () => {
            processingDelete.value = false;
        },
    });
};
</script>

<template>
    <Head :title="__('Tags')" />

    <AdminLayout>
        <div class="animate-fade-in space-y-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-6 text-start md:flex-row md:items-center">
                <div class="text-start">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Tags & Skills') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Manage skills and tags for job postings.') }}</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="flex items-center rounded-2xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-brand-600/20 transition-all hover:bg-brand-700 active:scale-95"
                >
                    <PlusCircle :size="18" class="me-2" /> {{ __('Add New Tag') }}
                </button>
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
                            :placeholder="__('Search tags...')"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-4 pl-10 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:outline-none dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-200 dark:placeholder:text-slate-500"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-start">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-700/50 dark:bg-slate-800/20">
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Tag Name') }}
                                </th>
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Description') }}
                                </th>
                                <th class="px-6 py-4 text-start text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                    {{ __('Status') }}
                                </th>
                                <th class="px-6 py-4 text-end text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-start dark:divide-slate-800/50">
                            <tr
                                v-for="tag in tags.data"
                                :key="tag.id"
                                class="group transition-all duration-200 hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/20"
                                        >
                                            <Tag :size="18" />
                                        </div>
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ tag.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="line-clamp-1 max-w-xs text-xs text-slate-500">{{ tag.description || '---' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input type="checkbox" :checked="!!tag.status" class="peer sr-only" @change="toggleStatus(tag)" />
                                        <div
                                            class="peer h-5 w-9 rounded-full bg-slate-200 peer-checked:bg-brand-600 peer-focus:ring-2 peer-focus:ring-brand-300 peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-slate-700 dark:peer-focus:ring-brand-800"
                                        ></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEditModal(tag)"
                                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition-all hover:bg-brand-50 hover:text-brand-600 dark:bg-slate-800 dark:hover:bg-brand-900/20"
                                        >
                                            <Pencil :size="14" />
                                        </button>
                                        <button
                                            @click="confirmDelete(tag)"
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
                <div v-if="tags.links.length > 3" class="flex justify-end border-t border-slate-100 p-6 dark:border-slate-700">
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, key) in tags.links"
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

            <!-- Create/Edit Modal -->
            <Modal :show="showModal" :title="editingTag ? __('Edit Tag') : __('Add New Tag')" @close="showModal = false">
                <form id="tag-form" @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Tag Name') }}</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800"
                            :class="{ 'border-rose-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs font-medium text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Description') }}</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm transition-all focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800"
                            :class="{ 'border-rose-500': form.errors.description }"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs font-medium text-rose-500">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" v-model="form.status" class="peer sr-only" />
                            <div
                                class="peer h-5 w-9 rounded-full bg-slate-200 peer-checked:bg-brand-600 peer-focus:ring-2 peer-focus:ring-brand-300 peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-slate-700 dark:peer-focus:ring-brand-800"
                            ></div>
                        </label>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Active Status') }}</span>
                    </div>
                </form>

                <template #footer>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-4 py-2 text-sm font-bold text-slate-500 transition-colors hover:text-slate-700 dark:hover:text-slate-300"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            form="tag-form"
                            :disabled="form.processing"
                            class="flex items-center rounded-xl bg-brand-600 px-6 py-2 text-sm font-bold text-white shadow-lg shadow-brand-600/20 transition-all hover:bg-brand-700 active:scale-95 disabled:opacity-50"
                        >
                            <span
                                v-if="form.processing"
                                class="me-2 h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            ></span>
                            {{ editingTag ? __('Update') : __('Create') }}
                        </button>
                    </div>
                </template>
            </Modal>

            <ConfirmationModal
                :show="showDeleteModal"
                :title="__('Delete Tag?')"
                :message="__('Are you sure you want to delete this tag? This action cannot be undone.')"
                :confirmText="__('Yes, Delete')"
                :cancelText="__('No, Cancel')"
                type="danger"
                :processing="processingDelete"
                @close="showDeleteModal = false"
                @confirm="handleDelete"
            />
        </div>
    </AdminLayout>
</template>
