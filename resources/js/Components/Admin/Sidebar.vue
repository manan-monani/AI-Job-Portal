<script setup lang="ts">
import admin from '@/routes/admin';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Activity,
    BarChart2,
    Briefcase,
    Building2,
    ChevronLeft,
    GitBranch,
    Hash,
    History,
    LayoutDashboard,
    LifeBuoy,
    Mail,
    Palette,
    Scale,
    Settings,
    ShieldCheck,
    Tag,
    User,
    UserPlus,
    Users,
    Zap,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// Icon mapping
const icons: Record<string, any> = {
    LayoutDashboard,
    Users,
    Briefcase,
    ShieldCheck,
    ChevronLeft,
    LifeBuoy,
    Activity,
    BarChart2,
    User,
    Palette,
    Zap,
    History,
    Scale,
    Settings,
    Building2,
    Tag,
    Hash,
    UserPlus,
    GitBranch,
    Mail,
};

const props = defineProps<{
    isCollapsed: boolean;
}>();

const emit = defineEmits(['toggleCollapse', 'update:activeSection']);

const page = usePage();
const permissions = computed(() => (page.props as any).auth.available_permissions || {});

const can = (permission: string) => {
    const auth = (page.props as any).auth;
    return auth?.is_super_admin || auth?.permissions?.includes(permission);
};

// Initialize active section based on current URL
const getInitialSection = () => {
    const url = page.url;
    // Check if URL matches any sub-module route
    // Since we don't have route name -> URL map here easily without traversing,
    // we rely on specific checks or passed 'active' state if we verified it.
    // For now, keep the heuristic logic but iterate permissions if possible?
    // Let's stick to the simpler heuristic or a "findSectionByUrl" logic if robust.

    // Heuristic fallback matching the static logic's intent:
    if (url.startsWith('/admin/users') || url.startsWith('/admin/roles') || url.startsWith('/admin/profile')) return 'account_section';
    if (url.startsWith('/admin/business') || url.startsWith('/admin/legal')) return 'business_settings';
    if (
        url.startsWith('/admin/departments') ||
        url.startsWith('/admin/tags') ||
        url.startsWith('/admin/jobs') ||
        url.startsWith('/admin/evaluations') ||
        url.startsWith('/admin/candidates')
    )
        return 'hiring_section';
    if (url.startsWith('/admin/dashboard') || url === '/admin' || url.startsWith('/admin/activity-logs')) return 'system_section';

    return 'system_section'; // Default
};

const activeSectionKey = ref(getInitialSection());

// Watch for URL changes to update active section
watch(
    () => page.url,
    () => {
        activeSectionKey.value = getInitialSection();
    },
);

const activeSection = computed(() => permissions.value[activeSectionKey.value]);

const switchSection = (key: string) => {
    activeSectionKey.value = key;
    emit('update:activeSection', key);
};

// Branding helper
const t = (key: string) => {
    return page.props.branding?.admin?.sidebar?.[key] || key;
};

// Helper to resolve route URL safely
// Note: In a real dynamic setup, we'd need route() helper available or pass URLs.
// For now, we assume 'admin' route object is imported and we can map route names or use the ziggy route() global if available.
// Since we have `admin` imported from routes/admin, we can try to find it there or use the global route() function provided by Ziggy (Laravel).
// However, the Permissions.php file stores route NAMES (e.g. 'admin.dashboard').
// We need a way to resolve 'admin.dashboard' to a URL string.
// If Ziggy is global: route('admin.dashboard').
// Let's assume global route() is available in the template, but in script we might need it.
</script>

<template>
    <aside
        id="admin-sidebar"
        class="fixed inset-y-0 start-0 top-0 z-50 flex h-screen border-e border-admin-sidebar-border bg-admin-sidebar transition-all duration-300 ease-in-out lg:sticky dark:border-admin-sidebar-border-dark dark:bg-admin-sidebar-dark"
        :class="{ 'w-[72px]': isCollapsed, 'w-[300px]': !isCollapsed }"
    >
        <!-- TIER 1: Command Rail -->
        <div class="z-20 flex w-[72px] flex-shrink-0 flex-col items-center space-y-6 bg-admin-sidebar-rail py-4 dark:bg-admin-sidebar-rail-dark">
            <Link
                :href="admin.dashboard.url()"
                class="flex h-10 w-10 cursor-pointer items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg transition-colors hover:brightness-110 dark:border-slate-700 dark:bg-slate-800"
            >
                <img
                    v-if="$page.props.branding.business_settings?.logo_url"
                    :src="$page.props.branding.business_settings.logo_url"
                    class="h-full w-full rounded-xl object-contain p-1"
                />
                <Zap v-else :size="20" fill="currentColor" class="text-[var(--brand-primary)]" />
            </Link>

            <div class="flex w-full flex-1 flex-col items-center space-y-2">
                <template v-for="(section, key) in permissions" :key="key">
                    <button
                        v-if="Object.keys(section.sub_modules || {}).some((k) => can(k))"
                        @click="switchSection(String(key))"
                        class="admin-rail-btn group relative flex w-full flex-col items-center py-3 transition-all"
                        :class="activeSectionKey === key ? 'active-rail' : 'rail-icon hover:brightness-110'"
                    >
                        <component :is="icons[section.icon] || Zap" :size="20" class="mb-1" />
                        <span
                            class="rail-label pointer-events-none absolute left-[70px] z-50 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold whitespace-nowrap text-white opacity-0 shadow-xl transition-opacity group-hover:opacity-100"
                        >
                            {{ __(t(String(section.label))) }}
                        </span>
                    </button>
                </template>
            </div>

            <div class="flex w-full flex-col items-center space-y-4 border-t border-slate-800/50 px-4 pt-4">
                <button
                    @click="emit('toggleCollapse')"
                    class="rail-icon group relative hidden h-10 w-10 items-center justify-center rounded-xl transition-all hover:bg-slate-800 hover:text-sky-400 lg:flex"
                >
                    <ChevronLeft :size="20" :class="{ 'rotate-180': isCollapsed }" />
                    <span
                        class="rail-label pointer-events-none absolute left-[70px] z-50 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold whitespace-nowrap text-white opacity-0 shadow-xl transition-opacity group-hover:opacity-100"
                        >{{ __(t('collapse')) }}</span
                    >
                </button>
                <button
                    class="rail-icon group relative flex h-10 w-10 items-center justify-center rounded-xl transition-all hover:bg-slate-800 hover:text-sky-400"
                >
                    <LifeBuoy :size="20" />
                    <span
                        class="rail-label pointer-events-none absolute left-[70px] z-50 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold whitespace-nowrap text-white opacity-0 shadow-xl transition-opacity group-hover:opacity-100"
                        >{{ __(t('support')) }}</span
                    >
                </button>
            </div>
        </div>

        <!-- TIER 2: Sub-menu -->
        <div
            id="tier-2-container"
            class="flex h-full flex-col overflow-hidden bg-admin-sidebar transition-all duration-300 ease-in-out dark:bg-admin-sidebar-dark"
            :class="isCollapsed ? 'pointer-events-none w-0 opacity-0' : 'w-[228px] opacity-100'"
        >
            <div class="border-sidebar-border dark:border-sidebar-border-dark border-b p-5">
                <h2
                    class="line-clamp-1 text-xl font-extrabold tracking-tight text-slate-900 dark:text-white"
                    :title="$page.props.branding.business_settings?.business_name"
                >
                    {{ $page.props.branding.business_settings?.business_name || __(t('tier_title')) }}
                </h2>
                <p class="mt-1.5 text-xs leading-snug font-semibold text-slate-500">
                    {{ $page.props.branding.business_settings?.tagline || __(t('tier_subtitle')) }}
                </p>
            </div>

            <div class="flex-1 space-y-1 overflow-y-auto p-4">
                <div v-if="activeSection" class="animate-fade-in">
                    <div class="mb-3 px-4 text-[11px] font-bold tracking-widest text-slate-400 uppercase">
                        {{ __(t(activeSection.label)) }}
                    </div>

                    <template v-for="(module, key) in activeSection.sub_modules" :key="key">
                        <Link
                            v-if="can(String(key))"
                            :href="module.url"
                            class="flex items-center space-x-3 rounded-lg px-3 py-2.5 text-sm whitespace-nowrap transition-all"
                            :class="
                                $page.url.startsWith(module.url)
                                    ? 'nav-link-active bg-admin-active font-bold text-admin-active-text'
                                    : 'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800'
                            "
                        >
                            <component :is="icons[module.icon] || Zap" :size="16" :class="{ 'opacity-70': !$page.url.startsWith(module.url) }" />
                            <span>{{ __(t(String(module.label))) }}</span>
                        </Link>
                    </template>

                    <!-- Special Case: My Profile (if not in permissions, user might still need it) -->
                    <!-- Usually Profile is common. If it's not in permissions, add it manually under Account section or generic? -->
                    <!-- For now, we only render what's in permissions. Profile is handled via User dropdown usually, but if we want it in sidebar: -->
                    <template v-if="activeSectionKey === 'account_section'">
                        <Link
                            :href="admin.profile.edit.url()"
                            class="flex items-center space-x-3 rounded-lg px-3 py-2.5 text-sm whitespace-nowrap transition-all"
                            :class="
                                $page.url.startsWith(admin.profile.edit.definition.url)
                                    ? 'nav-link-active bg-admin-active font-bold text-admin-active-text'
                                    : 'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800'
                            "
                        >
                            <User :size="16" class="opacity-70" />
                            <span class="text-sm">{{ __(t('my_profile')) }}</span>
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.active-rail {
    background-color: var(--admin-active-item-bg);
    color: var(--admin-active-item-text) !important;
    border-right: 4px solid var(--admin-active-item-border);
}
:global(.dark) .active-rail {
    background-color: var(--admin-active-item-bg-dark);
    color: var(--admin-active-item-text-dark) !important;
    border-right: 4px solid var(--admin-active-item-border-dark);
}

[dir='rtl'] .active-rail {
    border-right: none;
    border-left: 4px solid var(--admin-active-item-border);
}
[dir='rtl'] :global(.dark) .active-rail {
    border-left: 4px solid var(--admin-active-item-border-dark);
}

.nav-link-active {
    background-color: var(--admin-active-item-bg);
    color: var(--admin-active-item-text);
    font-weight: 700;
    border-left: 4px solid var(--admin-active-item-border);
    padding-left: 0.5rem !important; /* Adjust for border to keep alignment */
}
:global(.dark) .nav-link-active {
    background-color: var(--admin-active-item-bg-dark);
    color: var(--admin-active-item-text-dark);
    border-left: 4px solid var(--admin-active-item-border-dark);
}

[dir='rtl'] .nav-link-active {
    border-left: none;
    border-right: 4px solid var(--admin-active-item-border);
    padding-left: 0.75rem !important;
    padding-right: 0.5rem !important;
}
[dir='rtl'] :global(.dark) .nav-link-active {
    border-right: 4px solid var(--admin-active-item-border-dark);
}

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

.rail-icon {
    color: var(--admin-sidebar-icon);
}
:global(.dark) .rail-icon {
    color: var(--admin-sidebar-icon-dark);
}
</style>
