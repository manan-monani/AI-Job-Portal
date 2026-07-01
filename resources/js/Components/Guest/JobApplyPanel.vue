<script setup lang="ts">
import { login as loginRoute, register as registerRoute } from '@/routes';
import careers from '@/routes/careers';
import customer from '@/routes/customer';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { AlertCircle, FileText, Upload, User, UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    job: {
        id: number;
        slug: string;
        title: string;
        apply_instructions?: string;
        is_cv_required?: boolean;
    };
    hasApplied?: boolean;
}>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user as any);
const isGuest = computed(() => !authUser.value);

const mode = ref(isGuest.value ? 'guest' : 'profile'); // 'guest', 'profile', 'manual'
const localHasApplied = ref(props.hasApplied || false);

const form = useForm({
    name: authUser.value?.name || '',
    email: authUser.value?.email || '',
    phone: authUser.value?.customer_profile?.phone || '',
    cv: null as File | null,
    message: '',
});

const isCVMissingInProfile = computed(() => {
    return authUser.value && !authUser.value.resume?.file_path;
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        form.cv = target.files[0];
    }
};

const submit = () => {
    // If applying with profile but CV is missing
    if (mode.value === 'profile' && isCVMissingInProfile.value && props.job.is_cv_required) {
        return;
    }

    form.post(careers.apply.url(props.job.slug), {
        onSuccess: () => {
            form.reset();
            localHasApplied.value = true;
        },
    });
};

const switchToManual = () => {
    mode.value = 'manual';
    form.name = authUser.value?.name || '';
    form.email = authUser.value?.email || '';
    form.phone = authUser.value?.customer_profile?.phone || '';
};

const switchToProfile = () => {
    mode.value = 'profile';
    form.name = authUser.value?.name || '';
    form.email = authUser.value?.email || '';
    form.phone = authUser.value?.customer_profile?.phone || '';
};
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900"
    >
        <!-- Header -->
        <div class="border-b border-slate-50 bg-slate-50/50 p-6 dark:border-slate-800 dark:bg-slate-800/50">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Apply for this position') }}</h3>
            <p v-if="job.apply_instructions" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                {{ job.apply_instructions }}
            </p>
        </div>

        <!-- Suggestion Banner for Guests -->
        <div v-if="isGuest" class="border-b border-primary-100 bg-primary-50 p-6 dark:border-primary-900/20 dark:bg-primary-900/10">
            <div class="flex gap-4">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400"
                >
                    <UserPlus :size="20" />
                </div>
                <div>
                    <p class="text-sm font-bold text-primary-900 dark:text-primary-100">
                        {{ __('Already have an account?') }}
                    </p>
                    <p class="mt-1 text-xs text-primary-700/80 dark:text-primary-400/80">
                        {{ __('Log in for a faster application and to track your status.') }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <Link
                            :href="loginRoute.url()"
                            class="rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-primary-600 shadow-sm ring-1 ring-primary-200 hover:bg-primary-50 dark:bg-primary-900/20 dark:text-primary-400 dark:ring-primary-800"
                        >
                            {{ __('Log In') }}
                        </Link>
                        <Link
                            :href="registerRoute.url()"
                            class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-primary-700"
                        >
                            {{ __('Register Now') }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="!localHasApplied">
            <!-- Auth Toggle -->
            <div v-if="!isGuest" class="flex border-b border-slate-50 p-2 dark:border-slate-800">
                <button
                    @click="switchToProfile"
                    :class="
                        mode === 'profile'
                            ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-800 dark:text-primary-400'
                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                    "
                    class="flex-1 rounded-lg py-2 text-xs font-bold transition-all"
                >
                    {{ __('Apply with Profile') }}
                </button>
                <button
                    @click="switchToManual"
                    :class="
                        mode === 'manual'
                            ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-800 dark:text-primary-400'
                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                    "
                    class="flex-1 rounded-lg py-2 text-xs font-bold transition-all"
                >
                    {{ __('Apply Manually') }}
                </button>
            </div>

            <!-- CV Missing Warning -->
            <div
                v-if="!isGuest && mode === 'profile' && isCVMissingInProfile"
                class="mx-6 mt-6 rounded-xl border border-orange-100 bg-orange-50 p-4 dark:border-orange-900/30 dark:bg-orange-950/20"
            >
                <div class="flex gap-3">
                    <AlertCircle class="shrink-0 text-orange-500" :size="18" />
                    <div>
                        <p class="text-xs font-bold text-orange-900 dark:text-orange-200">
                            {{ __('CV not found in your profile.') }}
                        </p>
                        <Link
                            :href="customer.profile.edit.url()"
                            class="mt-2 text-xs font-bold text-orange-600 underline hover:text-orange-700 dark:text-orange-400"
                        >
                            {{ __('Update your CV now') }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5 p-6">
                <div v-if="mode === 'manual' || isGuest" class="flex flex-col space-y-5">
                    <div>
                        <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{
                            __('Full Name')
                        }}</label>
                        <input
                            v-model="form.name"
                            type="text"
                            minlength="2"
                            maxlength="100"
                            class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                            :placeholder="__('John Doe')"
                            required
                        />
                        <div v-if="form.errors.name" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{ __('Email') }}</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                            :placeholder="__('name@example.com')"
                            required
                        />
                        <div v-if="form.errors.email" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{ __('Phone') }}</label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            pattern="[\+]?[0-9\s\-\(\)]+"
                            title="Please enter a valid phone number, optionally starting with +"
                            class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                            :placeholder="__('+1 234 567 890')"
                            required
                        />
                        <div v-if="form.errors.phone" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.phone }}</div>
                    </div>
                </div>

                <!-- Profile Summary (ReadOnly) -->
                <div v-else class="space-y-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm dark:bg-slate-900 dark:text-slate-500"
                        >
                            <User :size="18" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ authUser.name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ authUser.email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                        <FileText :size="14" />
                        <span v-if="!isCVMissingInProfile" class="text-emerald-600 dark:text-emerald-400">{{ __('CV Attached from Profile') }}</span>
                        <span v-else class="text-rose-500">{{ __('No CV in Profile') }}</span>
                    </div>
                </div>

                <!-- File Upload -->
                <div v-if="mode === 'manual' || isGuest">
                    <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">
                        {{ __('CV / Resume') }}
                        <span v-if="!job.is_cv_required" class="text-[10px] font-normal lowercase">({{ __('optional') }})</span>
                    </label>
                    <div class="relative mt-2">
                        <input type="file" id="cv-upload" class="hidden" @change="handleFileChange" accept=".pdf,.doc,.docx" />
                        <label
                            for="cv-upload"
                            class="flex w-full cursor-pointer items-center justify-center gap-3 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-sm text-slate-500 transition-all hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:bg-slate-800"
                        >
                            <Upload v-if="!form.cv" :size="20" />
                            <FileText v-else class="text-primary-600" :size="20" />
                            <span v-if="!form.cv" class="font-bold">{{ __('Click to upload your CV') }}</span>
                            <span v-else class="font-bold text-slate-900 dark:text-white">{{ form.cv.name }}</span>
                        </label>
                    </div>
                    <div v-if="form.errors.cv" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.cv }}</div>
                </div>

                <div>
                    <label class="block text-xs font-bold tracking-widest text-slate-500 uppercase dark:text-slate-400">{{
                        __('Message / Cover Letter')
                    }}</label>
                    <textarea
                        v-model="form.message"
                        rows="4"
                        class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        :placeholder="__('Tell us why you are a great fit...')"
                    ></textarea>
                    <div v-if="form.errors.message" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.message }}</div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing || (mode === 'profile' && isCVMissingInProfile && job.is_cv_required)"
                    class="flex w-full transform items-center justify-center rounded-xl bg-primary-600 py-4 font-extrabold text-white shadow-lg shadow-primary-500/30 transition-all hover:bg-primary-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span v-if="form.processing">{{ __('Submitting Application...') }}</span>
                    <span v-else>{{ __('Submit Application') }}</span>
                </button>
            </form>
        </template>

        <!-- Applied Success State -->
        <div v-else class="flex flex-col items-center justify-center p-8 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                <FileText class="text-emerald-600 dark:text-emerald-400" :size="32" />
            </div>
            <h4 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                {{ __('Application Submitted!') }}
            </h4>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                {{ __('You have successfully applied for this position. We will review your application and get back to you soon.') }}
            </p>
            <div v-if="!isGuest" class="mt-6">
                <Link
                    :href="customer.dashboard.url()"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-6 py-2.5 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                >
                    {{ __('Track Application') }}
                </Link>
            </div>
        </div>
    </div>
</template>
