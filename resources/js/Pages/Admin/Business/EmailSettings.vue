<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { update } from '@/routes/admin/business/email';
import { useForm } from '@inertiajs/vue3';
import { AtSign, Hash, Lock, Mail, Send, Server, Settings, Shield, User } from 'lucide-vue-next';

const props = defineProps<{
    settings: {
        mail_enabled?: string | boolean;
        mail_host?: string;
        mail_port?: string;
        mail_username?: string;
        mail_password?: string;
        mail_encryption?: string;
        mail_from_address?: string;
        mail_from_name?: string;
    };
}>();

const form = useForm({
    mail_enabled: props.settings.mail_enabled === '1' || props.settings.mail_enabled === true || props.settings.mail_enabled === 'true',
    mail_host: props.settings.mail_host || '',
    mail_port: props.settings.mail_port || '',
    mail_username: props.settings.mail_username || '',
    mail_password: props.settings.mail_password || '',
    mail_encryption: props.settings.mail_encryption || 'tls',
    mail_from_address: props.settings.mail_from_address || '',
    mail_from_name: props.settings.mail_from_name || '',
});

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by flash message
        },
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-4xl">
            <!-- Header -->
            <div class="mb-6">
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-primary-50 p-2 dark:bg-primary-900/20">
                            <Mail class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ __('Email Setting') }}
                        </h1>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('Configure your SMTP credentials to enable application-wide email sending for candidate notifications and alerts.') }}
                </p>
            </div>

            <!-- Settings Form -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <form @submit.prevent="submit" class="space-y-8 p-6">
                    <!-- Master Toggle -->
                    <div
                        class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700/50"
                    >
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Enable Email Sending</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Turn this off to completely disable all outgoing emails from the system.
                            </p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" v-model="form.mail_enabled" class="peer sr-only" />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-primary-600 peer-focus:outline-none after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-gray-700"
                            ></div>
                        </label>
                    </div>

                    <div class="space-y-6" :class="{ 'pointer-events-none opacity-50': !form.mail_enabled }">
                        <!-- SMTP Host & Port -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="mail_host" class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <Server class="h-4 w-4" />
                                    {{ __('SMTP Host') }}
                                </label>
                                <input
                                    id="mail_host"
                                    v-model="form.mail_host"
                                    type="text"
                                    placeholder="e.g. smtp.mailgun.org"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="form.errors.mail_host" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.mail_host }}
                                </p>
                            </div>

                            <div>
                                <label for="mail_port" class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <Hash class="h-4 w-4" />
                                    {{ __('SMTP Port') }}
                                </label>
                                <input
                                    id="mail_port"
                                    v-model="form.mail_port"
                                    type="text"
                                    placeholder="e.g. 587 or 465"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="form.errors.mail_port" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.mail_port }}
                                </p>
                            </div>
                        </div>

                        <!-- Username & Password -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="mail_username" class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <User class="h-4 w-4" />
                                    {{ __('SMTP Username') }}
                                </label>
                                <input
                                    id="mail_username"
                                    v-model="form.mail_username"
                                    type="text"
                                    placeholder="e.g. user@example.com"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="form.errors.mail_username" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.mail_username }}
                                </p>
                            </div>

                            <div>
                                <label for="mail_password" class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <Lock class="h-4 w-4" />
                                    {{ __('SMTP Password') }}
                                </label>
                                <input
                                    id="mail_password"
                                    v-model="form.mail_password"
                                    type="password"
                                    placeholder="••••••••"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p class="mt-1 text-xs text-gray-500">Leave empty to keep current password.</p>
                                <p v-if="form.errors.mail_password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.mail_password }}
                                </p>
                            </div>
                        </div>

                        <!-- Encryption -->
                        <div>
                            <label for="mail_encryption" class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <Shield class="h-4 w-4" />
                                {{ __('Encryption Protocol') }}
                            </label>
                            <select
                                id="mail_encryption"
                                v-model="form.mail_encryption"
                                class="w-full appearance-none rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="tls">TLS (Recommended)</option>
                                <option value="ssl">SSL</option>
                            </select>
                            <p v-if="form.errors.mail_encryption" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.mail_encryption }}
                            </p>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-700" />

                        <!-- From Address & Name -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    for="mail_from_address"
                                    class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    <AtSign class="h-4 w-4" />
                                    {{ __('From Email Address') }}
                                </label>
                                <input
                                    id="mail_from_address"
                                    v-model="form.mail_from_address"
                                    type="email"
                                    placeholder="e.g. noreply@example.com"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="form.errors.mail_from_address" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.mail_from_address }}
                                </p>
                            </div>

                            <div>
                                <label for="mail_from_name" class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <Send class="h-4 w-4" />
                                    {{ __('From Name') }}
                                </label>
                                <input
                                    id="mail_from_name"
                                    v-model="form.mail_from_name"
                                    type="text"
                                    placeholder="e.g. HR Department"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="form.errors.mail_from_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.mail_from_name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex items-center gap-2 rounded-lg bg-primary-600 px-6 py-2.5 font-medium text-white shadow-lg shadow-primary-500/30 transition-colors hover:bg-primary-700 disabled:bg-gray-400 disabled:shadow-none"
                        >
                            <Settings class="h-4 w-4" :class="{ 'animate-spin': form.processing }" />
                            {{ form.processing ? __('Saving...') : __('Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
