<script setup lang="ts">
import Toast from '@/Components/Common/Toast.vue';
import { login, register } from '@/routes';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const page = usePage();
const appName = computed(() => page.props.branding?.business_settings?.business_name || page.props.name || 'Laravel');

const isAuthenticating = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    isAuthenticating.value = true;
    form.post(register.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
            isAuthenticating.value = false;
        },
        onError: () => {
            isAuthenticating.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Register | ${appName}`" />
    <Toast />

    <div
        class="flex min-h-screen flex-col bg-white font-sans text-slate-800 antialiased transition-colors duration-300 lg:flex-row dark:bg-gray-900 dark:text-slate-200"
    >
        <!-- Left Side: Professional Branding -->
        <div class="mesh-gradient relative hidden flex-col justify-between overflow-hidden p-12 text-white lg:flex lg:w-[45%]">
            <!-- Abstract Pattern Overlay -->
            <div
                class="absolute inset-0 opacity-20"
                style="background-image: radial-gradient(#ffffff 0.5px, transparent 0.5px); background-size: 24px 24px"
            ></div>

            <!-- Top Branding -->
            <div class="relative z-10">
                <Link href="/" class="mb-16 flex">
                    <div class="flex items-center space-x-3">
                        <img
                            v-if="$page.props.branding.business_settings?.logo_url"
                            :src="$page.props.branding.business_settings.logo_url"
                            class="h-12 w-auto rounded-xl"
                        />
                        <div v-else class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-600 shadow-lg shadow-primary-500/20">
                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 576 512">
                                <path
                                    d="M0 80C0 53.5 21.5 32 48 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80zM64 96v64h64V96H64zM0 336c0-26.5 21.5-48 48-48h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V336zM64 352v64h64v-64H64zM309.7 185.3l-147.9 29.8C154.2 216.7 148 221.7 148 228v56c0 8.8 7.2 16 16 16h32c6.9 0 13.1-4.2 15.3-10.7l22-66H310l34.8 104.5c4.8 14.5-9.6 28.2-22.8 28.2H320c-8.8 0-16 7.2-16 16V392c0 8.8 7.2 16 16 16h32c12 0 22.4-8.2 25.1-19.9l32.3-139c1.9-8.3-5.2-16-13.8-16h-42.5l-23.4-70.3c-2.8-8.4-11.8-13.3-20.2-11.5l-63.5 12.7c36.4 7.2 63.8 39.2 63.8 77.2v.2c0 7.3-1.1 14.3-3 21.1zM480 32c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48h96c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48H480zm16 144v-64h64v64H496zm-16 112c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48h96c26.5 0 48-21.5 48-48V336c0-26.5-21.5-48-48-48H480zm16 144v-64h64v64H496z"
                                />
                            </svg>
                        </div>
                        <span v-if="!$page.props.branding.business_settings?.logo_url" class="text-2xl font-extrabold tracking-tight uppercase"
                            >{{ appName }}<span class="text-primary-400">.</span></span
                        >
                    </div>
                </Link>

                <div class="animate-in">
                    <h1 class="mb-6 text-4xl leading-[1.15] font-extrabold lg:text-5xl">
                        {{ $page.props.branding.business_settings?.business_name || __($page.props.branding.register?.headline) }}
                    </h1>
                    <p class="mb-8 max-w-md text-lg leading-relaxed text-slate-200">
                        {{ $page.props.branding.business_settings?.tagline || __($page.props.branding.register?.subheadline) }}
                    </p>
                </div>
            </div>

            <!-- Middle: Social Proof / Value Prop -->
            <div class="glass-panel animate-in relative z-10 max-w-sm rounded-2xl p-6" style="animation-delay: 0.2s">
                <div class="mb-2 flex items-center gap-4">
                    <div class="flex -space-x-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-slate-800 bg-primary-500 text-xs font-bold ring-2 ring-transparent"
                        >
                            A
                        </div>
                        <div
                            class="bg-secondary-500 flex h-8 w-8 items-center justify-center rounded-full border-2 border-slate-800 text-xs font-bold ring-2 ring-transparent"
                        >
                            B
                        </div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-slate-800 bg-primary-400 text-xs font-bold ring-2 ring-transparent"
                        >
                            C
                        </div>
                    </div>
                    <div class="text-sm font-semibold text-slate-200">
                        {{ __($page.props.branding.register?.social_proof) }}
                    </div>
                </div>
                <p class="text-xs text-slate-300">{{ __($page.props.branding.register?.social_proof_sub) }}</p>
            </div>

            <!-- Footer: System Stats -->
            <div class="relative z-10 mt-12 flex items-center space-x-8 border-t border-white/10 pt-8 text-sm text-slate-300">
                <div class="flex items-center space-x-2">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    <span>{{ __($page.props.branding.register?.registration_status) }}</span>
                </div>
                <span>{{ __($page.props.branding.register?.security_badge) }}</span>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="relative flex items-center justify-center bg-white p-8 transition-colors duration-300 lg:w-1/2 lg:p-12 dark:bg-gray-900">
            <div class="w-full max-w-md">
                <div class="mb-10 text-center lg:text-left">
                    <Link
                        href="/"
                        class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 p-3 shadow-sm lg:hidden dark:bg-slate-800"
                    >
                        <img
                            v-if="$page.props.branding.business_settings?.logo_url"
                            :src="$page.props.branding.business_settings.logo_url"
                            class="h-full w-full rounded-xl object-contain"
                        />
                        <svg v-else class="h-8 w-8 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 320 512">
                            <path
                                d="M296 160H180.6l42.6-129.8C227.2 15 215.7 0 200 0H56C44 0 33.8 8.9 32.2 20.8l-32 240C-1.7 275.2 25.2 288 40 288h115.4l-42.6 129.8C108.8 434.9 120.3 448 136 448h144c12 0 22.2-8.9 23.8-20.8l32-240c1.9-14.3-9.2-27.2-24-27.2zM250.7 220.3l-32 240c-.6 4.3-4.1 7.7-8.7 7.7h-144c-4.1 0-7.7-2.9-8.4-7L100.2 332H40c-4.1 0-7.7-2.9-8.4-7L-11.4 69.8c-.8-5.8 3.7-10.9 9.5-10.9h144c4.1 0 7.7 2.9 8.4 7L167.8 196H228c4.1 0 7.7 2.9 8.4 7l24.3 15.3z"
                            />
                        </svg>
                    </Link>
                    <h2 class="mb-2 text-2xl font-extrabold text-slate-900 transition-colors dark:text-white">
                        {{ __($page.props.branding.register?.form_title) }}
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __($page.props.branding.register?.form_subtitle) }}</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="name">{{
                            __('Full Name')
                        }}</label>
                        <div class="group relative">
                            <input
                                id="name"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('John Doe')"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />
                        </div>
                        <div v-if="form.errors.name" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="email">{{
                            __('Work Email')
                        }}</label>
                        <div class="group relative">
                            <input
                                id="email"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('name@company.com')"
                                v-model="form.email"
                                required
                                autocomplete="username"
                            />
                        </div>
                        <div v-if="form.errors.email" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="phone">{{
                            __('Phone Number')
                        }}</label>
                        <div class="group relative">
                            <input
                                id="phone"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('+123456789')"
                                v-model="form.phone"
                                required
                                autocomplete="tel"
                            />
                        </div>
                        <div v-if="form.errors.phone" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.phone }}</div>
                    </div>

                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="password">{{
                            __('Password')
                        }}</label>
                        <div class="group relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('••••••••')"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-300"
                            >
                                <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    ></path>
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>

                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="password_confirmation">{{
                            __('Confirm Password')
                        }}</label>
                        <div class="group relative">
                            <input
                                :type="showConfirmPassword ? 'text' : 'password'"
                                id="password_confirmation"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('••••••••')"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-300"
                            >
                                <svg v-if="!showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    ></path>
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password_confirmation" class="mt-2 ml-1 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing || isAuthenticating"
                            :class="{ 'btn-loading': form.processing || isAuthenticating }"
                            class="flex w-full transform items-center justify-center space-x-2 rounded-2xl bg-primary-600 py-3 font-bold text-white shadow-xl shadow-primary-200 transition-all hover:bg-primary-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-75 dark:shadow-none"
                        >
                            <span v-if="form.processing || isAuthenticating">{{ __('Creating Account...') }}</span>
                            <span v-else>{{ __('Create Account') }}</span>
                            <svg v-if="!(form.processing || isAuthenticating)" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                            <svg
                                v-else
                                class="ml-2 h-4 w-4 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Helper Footer -->
                <div class="mt-8 flex flex-col items-center space-y-4 border-t border-slate-50 pt-8 dark:border-gray-800">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Already have an account?') }}
                        <Link
                            :href="login.url()"
                            class="font-bold text-primary-600 transition-colors hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                            >{{ __('Sign In') }}</Link
                        >
                    </p>
                    <div class="flex space-x-4 text-[11px] font-bold tracking-tighter text-slate-400 uppercase dark:text-slate-500">
                        <a href="#" class="hover:text-slate-600 dark:hover:text-slate-300">{{ __('Privacy Policy') }}</a>
                        <span>•</span>
                        <a href="#" class="hover:text-slate-600 dark:hover:text-slate-300">{{ __('Terms of Service') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

.mesh-gradient {
    background: linear-gradient(135deg, var(--color-primary-900) 0%, var(--color-primary-600) 100%);
}

.glass-panel {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.input-transition {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-loading {
    pointer-events: none;
    opacity: 0.8;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in {
    animation: slideIn 0.5s ease-out forwards;
}
</style>
