<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, FileText, Mail, Phone, Save, Upload, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    user: {
        name: string;
        email: string;
        profile_image?: string;
        customer_profile?: {
            phone: string;
        };
        resume?: {
            cv_title: string;
            file_path: string;
        };
    };
}>();

const page = usePage();

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const preview = ref(props.user.profile_image ? '/storage/' + props.user.profile_image : null);
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.customer_profile?.phone || '',
    cv: null as File | null,
    profile_image: null as File | null,
    current_password: '',
    password: '',
    password_confirmation: '',
});

const onProfileImageChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.profile_image = file;
        preview.value = URL.createObjectURL(file);
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const currentCvName = computed(() => {
    return props.user.resume?.cv_title || null;
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        form.cv = target.files[0];
    }
};

const submit = () => {
    form.post(customer.profile.update.post.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Form reset is not needed for profile update as we keep the values
            form.cv = null;
            form.reset('current_password', 'password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="__('Edit Profile')" />
    <CustomerLayout>
        <div class="animate-fade-in mx-auto max-w-4xl space-y-8 p-2 sm:p-0">
            <!-- Header -->
            <div class="border-b border-slate-100 pb-6 dark:border-slate-800">
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-900 dark:text-white">
                    <User class="text-primary-600" :size="28" />
                    {{ __('Candidate Profile') }}
                </h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Manage your personal information and resume to keep your applications professional.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <!-- Sidebar / Tips -->
                <div class="space-y-6">
                    <div class="rounded-2xl border border-primary-100 bg-primary-50 p-6 dark:border-primary-900/20 dark:bg-primary-900/10">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-primary-900 dark:text-primary-100">
                            <AlertCircle :size="16" />
                            {{ __('Job Search Tip') }}
                        </h4>
                        <p class="mt-3 text-xs leading-relaxed text-primary-700 dark:text-primary-400">
                            {{
                                __(
                                    'Keeping your CV updated increases your chances of being noticed by recruiters. Make sure it highlights your most recent achievements.',
                                )
                            }}
                        </p>
                    </div>

                    <div
                        v-if="currentCvName"
                        class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h4 class="text-xs font-bold tracking-widest text-slate-400 uppercase">{{ __('Saved Resume') }}</h4>
                        <div class="mt-4 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400"
                            >
                                <FileText :size="20" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ currentCvName }}</p>
                                <p class="text-[10px] font-bold text-emerald-600 uppercase">{{ __('Active & Verified') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Form -->
                <div class="md:col-span-2">
                    <form
                        @submit.prevent="submit"
                        class="space-y-8 rounded-3xl border border-slate-200 bg-white p-8 shadow-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Basic Info Section -->
                        <div class="space-y-6">
                            <h3
                                class="border-b border-slate-50 pb-2 text-sm font-bold tracking-widest text-slate-400 uppercase dark:border-slate-800"
                            >
                                {{ __('Personal Details') }}
                            </h3>

                            <div class="grid grid-cols-1 gap-6">
                                <!-- Image Upload -->
                                <div class="flex items-center space-x-6">
                                    <div class="group relative">
                                        <div class="h-20 w-20 overflow-hidden rounded-full border-4 border-slate-100 shadow-md dark:border-slate-800">
                                            <img
                                                :src="
                                                    preview || `https://ui-avatars.com/api/?name=${encodeURIComponent(form.name)}&background=random`
                                                "
                                                class="h-full w-full object-cover"
                                                alt="Profile"
                                            />
                                        </div>
                                        <div
                                            @click="triggerFileInput"
                                            class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-full bg-black/40 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                        >
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                                ></path>
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">{{ __('Profile Photo') }}</h4>
                                        <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">{{ __('Accepts JPG, PNG or GIF. Max 2MB.') }}</p>
                                        <button
                                            type="button"
                                            @click="triggerFileInput"
                                            class="text-xs font-bold text-primary-600 transition-colors hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                        >
                                            {{ __('Change Photo') }}
                                        </button>
                                        <input type="file" ref="fileInput" class="hidden" @change="onProfileImageChange" accept="image/*" />
                                        <div v-if="form.errors.profile_image" class="mt-1 text-xs font-bold text-rose-500">
                                            {{ form.errors.profile_image }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{
                                        __('Full Name')
                                    }}</label>
                                    <div class="relative mt-2">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <User :size="18" />
                                        </div>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pr-4 pl-11 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                                            required
                                        />
                                    </div>
                                    <div v-if="form.errors.name" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.name }}</div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{
                                            __('Email Address')
                                        }}</label>
                                        <div class="relative mt-2">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                                <Mail :size="18" />
                                            </div>
                                            <input
                                                v-model="form.email"
                                                type="email"
                                                class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pr-4 pl-11 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                                                required
                                            />
                                        </div>
                                        <div v-if="form.errors.email" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.email }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{
                                            __('Phone Number')
                                        }}</label>
                                        <div class="relative mt-2">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                                <Phone :size="18" />
                                            </div>
                                            <input
                                                v-model="form.phone"
                                                type="tel"
                                                class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pr-4 pl-11 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                                                required
                                            />
                                        </div>
                                        <div v-if="form.errors.phone" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.phone }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resume Upload Section -->
                        <div class="space-y-6">
                            <h3
                                class="border-b border-slate-50 pb-2 text-sm font-bold tracking-widest text-slate-400 uppercase dark:border-slate-800"
                            >
                                {{ __('CV / Resume') }}
                            </h3>

                            <div>
                                <div class="group relative">
                                    <input type="file" id="profile-cv" class="hidden" @change="handleFileChange" accept=".pdf,.doc,.docx" />
                                    <label
                                        for="profile-cv"
                                        class="flex w-full cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-10 transition-all hover:border-primary-400 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:bg-slate-800/80"
                                    >
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm transition-all group-hover:scale-110 group-hover:text-primary-600 dark:bg-slate-900"
                                        >
                                            <Upload v-if="!form.cv" :size="24" />
                                            <CheckCircle v-else class="text-emerald-500" :size="24" />
                                        </div>
                                        <div class="text-center">
                                            <p v-if="!form.cv" class="text-sm font-bold text-slate-900 dark:text-white">
                                                {{ __('Click to upload new resume') }}
                                            </p>
                                            <p v-else class="text-sm font-bold text-emerald-600">{{ form.cv.name }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('PDF, DOC or DOCX (Max 5MB)') }}</p>
                                        </div>
                                    </label>
                                </div>
                                <div v-if="form.errors.cv" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.cv }}</div>
                            </div>
                        </div>

                        <!-- Password Update Section -->
                        <div class="space-y-6 border-t border-slate-100 pt-6 dark:border-slate-800">
                            <h3
                                class="border-b border-slate-50 pb-2 text-sm font-bold tracking-widest text-slate-400 uppercase dark:border-slate-800"
                            >
                                {{ __('Change Password') }}
                            </h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Current Password -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{
                                        __('Current Password')
                                    }}</label>
                                    <div class="relative mt-2">
                                        <input
                                            v-model="form.current_password"
                                            :type="showCurrentPassword ? 'text' : 'password'"
                                            class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pr-10 pl-4 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                                        />
                                        <button
                                            type="button"
                                            @click="showCurrentPassword = !showCurrentPassword"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 focus:outline-none"
                                        >
                                            <svg v-if="!showCurrentPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                ></path>
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
                                    <div v-if="form.errors.current_password" class="mt-1 text-xs font-bold text-rose-500">
                                        {{ form.errors.current_password }}
                                    </div>
                                </div>
                                <!-- New Password -->
                                <div>
                                    <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{
                                        __('New Password')
                                    }}</label>
                                    <div class="relative mt-2">
                                        <input
                                            v-model="form.password"
                                            :type="showNewPassword ? 'text' : 'password'"
                                            class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pr-10 pl-4 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                                        />
                                        <button
                                            type="button"
                                            @click="showNewPassword = !showNewPassword"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 focus:outline-none"
                                        >
                                            <svg v-if="!showNewPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                ></path>
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
                                    <div v-if="form.errors.password" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.password }}</div>
                                </div>
                                <!-- Confirm Password -->
                                <div>
                                    <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{
                                        __('Confirm Password')
                                    }}</label>
                                    <div class="relative mt-2">
                                        <input
                                            v-model="form.password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pr-10 pl-4 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                                        />
                                        <button
                                            type="button"
                                            @click="showConfirmPassword = !showConfirmPassword"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 focus:outline-none"
                                        >
                                            <svg v-if="!showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                ></path>
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
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex transform items-center justify-center gap-2 rounded-xl bg-primary-600 px-10 py-4 font-extrabold text-white shadow-lg shadow-primary-500/30 transition-all hover:bg-primary-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Save :size="18" />
                                <span v-if="form.processing">{{ __('Saving Changes...') }}</span>
                                <span v-else>{{ __('Save Profile') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
