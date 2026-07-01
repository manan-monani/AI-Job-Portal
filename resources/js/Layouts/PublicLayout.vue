<script setup lang="ts">
import Toast from '@/Components/Common/Toast.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { contact, login, register } from '@/routes';
import { dashboard as adminDashboard, login as adminLogin } from '@/routes/admin';
import { index as careersIndex } from '@/routes/careers';
import { dashboard as customerDashboard } from '@/routes/customer';
import { about, privacy, terms } from '@/routes/legal';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, ShieldCheck, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// ... (previous imports)

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const appMode = computed(() => (page.props as any).app_mode);
const businessSettings = computed(() => (page.props.branding as any)?.business_settings || {});

const logoUrl = computed(() => businessSettings.value.logo_url || businessSettings.value.business_logo);

const dashboardUrl = computed(() => {
    if (!user.value) return '#';
    return user.value.type === 'admin' || user.value.type === 'super-admin' ? adminDashboard.url() : customerDashboard.url();
});

const isMobileMenuOpen = ref(false);

onMounted(() => {
    const direction = localStorage.getItem('direction') || 'ltr';
    document.documentElement.setAttribute('dir', direction);
});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50 transition-colors duration-300 dark:bg-gray-900">
        <Toast />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo/Brand & Legal Links -->
                    <div class="flex items-center gap-8">
                        <Link href="/" class="flex items-center gap-2 transition-opacity hover:opacity-80">
                            <img
                                v-if="logoUrl"
                                :src="logoUrl"
                                class="h-10 w-auto rounded-lg object-contain"
                                :alt="businessSettings.business_name || 'Logo'"
                            />
                            <span class="max-w-37.5 truncate text-xl font-bold text-gray-800 md:max-w-none dark:text-white">
                                {{ businessSettings.business_name || 'Laravel Factory' }}
                            </span>
                        </Link>

                        <!-- Desktop Navigation Links -->
                        <div class="hidden items-center gap-8 lg:flex">
                            <Link
                                :href="careersIndex.url()"
                                :class="[
                                    'text-sm font-bold transition-colors',
                                    $page.url === '/' || $page.url.startsWith('/jobs') ? 'text-primary-600' : 'text-gray-500 hover:text-primary-600',
                                ]"
                            >
                                {{ __('Jobs') }}
                            </Link>
                            <Link
                                :href="about.url()"
                                class="text-sm font-bold text-gray-500 transition-colors hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                            >
                                {{ __('About Company') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Desktop Auth Actions -->
                    <div class="hidden items-center gap-4 lg:flex">
                        <ThemeToggle />
                        <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>

                        <template v-if="user">
                            <Link
                                :href="dashboardUrl"
                                class="text-sm font-medium text-gray-700 transition-colors hover:text-primary-600 dark:text-gray-200 dark:hover:text-primary-400"
                            >
                                {{ __('Dashboard') }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                v-if="appMode === 'demo'"
                                :href="adminLogin.url()"
                                class="flex items-center gap-2 rounded-full border border-primary-600 bg-white px-5 py-2.5 font-bold text-primary-600 shadow-sm transition-all hover:bg-primary-50 dark:border-primary-400 dark:bg-transparent dark:text-primary-400 dark:hover:bg-primary-400/10"
                            >
                                <ShieldCheck class="h-4 w-4" />
                                <span>{{ __('Login As Admin') }}</span>
                            </Link>

                            <Link
                                :href="login.url()"
                                class="text-sm font-medium text-gray-700 transition-colors hover:text-primary-600 dark:text-gray-200 dark:hover:text-primary-400"
                            >
                                {{ __('Log in') }}
                            </Link>
                            <Link
                                :href="register.url()"
                                class="rounded-full bg-gray-900 px-5 py-2.5 font-bold text-white shadow-lg transition-all hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                            >
                                {{ __('Sign up') }}
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center gap-3 lg:hidden">
                        <ThemeToggle />
                        <button
                            @click="isMobileMenuOpen = !isMobileMenuOpen"
                            class="text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
                        >
                            <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" />
                            <X v-else class="h-6 w-6" />
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div v-show="isMobileMenuOpen" class="border-t border-gray-200 py-4 lg:hidden dark:border-gray-700">
                    <div class="flex flex-col gap-3">
                        <!-- Mobile Legal Links -->
                        <div class="mb-2 space-y-2">
                            <Link
                                :href="privacy.url()"
                                class="block py-2 text-base font-bold text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
                            >
                                {{ __('Privacy Policy') }}
                            </Link>
                            <Link
                                :href="terms.url()"
                                class="block py-2 text-base font-bold text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
                            >
                                {{ __('Terms') }}
                            </Link>
                            <Link
                                :href="about.url()"
                                class="block py-2 text-base font-bold text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
                            >
                                {{ __('About Us') }}
                            </Link>
                            <Link
                                :href="contact.url()"
                                class="block py-2 text-base font-bold text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
                            >
                                {{ __('Contact Us') }}
                            </Link>
                        </div>

                        <div class="flex flex-col gap-3 border-t border-gray-100 pt-2 dark:border-gray-800">
                            <template v-if="user">
                                <Link
                                    :href="dashboardUrl"
                                    class="rounded-lg bg-primary-50 px-4 py-2 text-center font-medium text-primary-600 dark:bg-primary-900/10 dark:text-primary-400"
                                >
                                    {{ __('Dashboard') }}
                                </Link>
                            </template>
                            <template v-else>
                                <Link
                                    v-if="appMode === 'demo'"
                                    :href="adminLogin.url()"
                                    class="flex items-center justify-center gap-2 border-b border-gray-100 py-3 text-base font-bold text-primary-600 dark:border-gray-800 dark:text-primary-400"
                                >
                                    <ShieldCheck class="h-4 w-4" />
                                    <span>{{ __('Login As Admin') }}</span>
                                </Link>

                                <Link
                                    :href="login.url()"
                                    class="py-2 text-center font-medium text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
                                >
                                    {{ __('Log in') }}
                                </Link>
                                <Link
                                    :href="register.url()"
                                    class="rounded-lg bg-primary-600 px-5 py-3 text-center font-bold text-white shadow-lg shadow-primary-500/30 transition-all hover:bg-primary-700"
                                >
                                    {{ __('Get Started') }}
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="grow">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="mt-auto border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="text-center text-sm text-gray-500 md:text-left dark:text-gray-400">
                        &copy; {{ new Date().getFullYear() }} {{ businessSettings.business_name || 'Company Name' }}. {{ __('All rights reserved.') }}
                    </div>
                    <div class="flex flex-wrap justify-center gap-6">
                        <Link
                            :href="privacy.url()"
                            class="text-sm text-gray-500 transition-colors hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                        >
                            {{ __('Privacy Policy') }}
                        </Link>
                        <Link
                            :href="terms.url()"
                            class="text-sm text-gray-500 transition-colors hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                        >
                            {{ __('Terms & Conditions') }}
                        </Link>
                        <Link
                            :href="about.url()"
                            class="text-sm text-gray-500 transition-colors hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                        >
                            {{ __('About Us') }}
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
