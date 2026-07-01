<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { index as careersIndex, show as careersShow } from '@/routes/careers';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

interface Job {
    id: number;
    slug: string;
    title: string;
    description: string;
    job_type: string;
    employment_type: string;
    salary_type: string;
    min_salary: string | number;
    max_salary: string | number;
    min_experience: string | number;
    max_experience: string | number;
    deadline: string;
    department: string;
}

interface Department {
    id: number;
    name: string;
}

const props = defineProps<{
    jobs: Job[];
    departments: Department[];
    filters: {
        department_id?: string | number;
    };
}>();

const page = usePage();
const businessSettings = computed(() => (page.props.branding as any).business_settings || {});
const currencySymbol = computed(() => businessSettings.value.currency_symbol || '$');
const timezone = computed(() => businessSettings.value.timezone || 'UTC');

const formatNumber = (value: string | number) => {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    if (isNaN(num)) {
        return value;
    }
    return parseFloat(num.toFixed(2)).toString();
};

const formatDate = (dateString: string) => {
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            timeZone: timezone.value,
        }).format(date);
    } catch (e) {
        return new Date(dateString).toLocaleDateString();
    }
};

const filterByDepartment = (id?: number) => {
    router.get(careersIndex.url(), { department_id: id }, { preserveState: true, replace: true });
};

// Slider logic
const scrollContainer = ref<HTMLElement | null>(null);
const showLeftArrow = ref(false);
const showRightArrow = ref(false);

// Drag to scroll logic
const isDragging = ref(false);
const startX = ref(0);
const scrollLeftPos = ref(0);

const startDrag = (e: MouseEvent) => {
    isDragging.value = true;
    if (scrollContainer.value) {
        scrollContainer.value.classList.add('cursor-grabbing');
        scrollContainer.value.classList.remove('cursor-auto');
        startX.value = e.pageX - scrollContainer.value.offsetLeft;
        scrollLeftPos.value = scrollContainer.value.scrollLeft;
    }
};

const stopDrag = () => {
    isDragging.value = false;
    if (scrollContainer.value) {
        scrollContainer.value.classList.remove('cursor-grabbing');
        scrollContainer.value.classList.add('cursor-auto');
    }
};

const doDrag = (e: MouseEvent) => {
    if (!isDragging.value || !scrollContainer.value) return;
    e.preventDefault();
    const x = e.pageX - scrollContainer.value.offsetLeft;
    const walk = (x - startX.value) * 2; // Scroll speed multiplier
    scrollContainer.value.scrollLeft = scrollLeftPos.value - walk;
};

const checkScrollState = () => {
    if (!scrollContainer.value) return;
    const { scrollLeft, scrollWidth, clientWidth } = scrollContainer.value;
    showLeftArrow.value = scrollLeft > 0;
    // adding a small tolerance for fractional pixel values
    showRightArrow.value = Math.ceil(scrollLeft + clientWidth) < scrollWidth;
};

const scrollLeft = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: -200, behavior: 'smooth' });
    }
};

const scrollRight = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: 200, behavior: 'smooth' });
    }
};

const scrollToActiveTab = async () => {
    await nextTick();
    if (!scrollContainer.value) return;

    const activeTab = scrollContainer.value.querySelector('.active-department-tab') as HTMLElement;
    if (activeTab) {
        const containerRect = scrollContainer.value.getBoundingClientRect();
        const tabRect = activeTab.getBoundingClientRect();

        // Check if the tab is fully visible
        const isVisible = tabRect.left >= containerRect.left && tabRect.right <= containerRect.right;

        if (!isVisible) {
            // Calculate scroll position to center the tab
            const scrollLeftPos = activeTab.offsetLeft - scrollContainer.value.clientWidth / 2 + activeTab.clientWidth / 2;
            scrollContainer.value.scrollTo({ left: scrollLeftPos, behavior: 'smooth' });
        }
    }
    // Check state after scrolling
    setTimeout(checkScrollState, 350);
};

onMounted(() => {
    checkScrollState();
    window.addEventListener('resize', checkScrollState);
    if (scrollContainer.value) {
        scrollContainer.value.addEventListener('scroll', checkScrollState);
    }
    scrollToActiveTab();
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScrollState);
    if (scrollContainer.value) {
        scrollContainer.value.removeEventListener('scroll', checkScrollState);
    }
});

watch(
    () => props.filters.department_id,
    () => {
        scrollToActiveTab();
    },
);
</script>

<template>
    <Head title="Careers" />

    <PublicLayout>
        <div class="space-y-16 pb-12">
            <!-- Hero Section -->
            <div class="relative isolate overflow-hidden bg-gray-50/50 py-24 dark:bg-gray-900/50">
                <!-- Premium Background: Mesh Gradient + Grid Pattern -->
                <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                    <div
                        class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-linear-to-tr from-emerald-400 to-emerald-100 opacity-40 sm:left-[calc(50%-30rem)] sm:w-288.75 dark:from-emerald-500 dark:to-emerald-800 dark:opacity-20"
                        style="
                            clip-path: polygon(
                                74.1% 44.1%,
                                100% 61.6%,
                                97.5% 26.9%,
                                85.5% 0.1%,
                                80.7% 2%,
                                72.5% 32.5%,
                                60.2% 62.4%,
                                52.4% 68.1%,
                                47.5% 58.3%,
                                45.2% 34.5%,
                                27.5% 76.7%,
                                0.1% 64.9%,
                                17.9% 100%,
                                27.6% 76.8%,
                                76.1% 97.7%,
                                74.1% 44.1%
                            );
                        "
                    ></div>
                </div>

                <!-- Grid Pattern Overlay -->
                <div class="absolute inset-0 -z-10 mask-[radial-gradient(100%_100%_at_top_right,white,transparent)]">
                    <svg class="h-full w-full stroke-gray-200/50 dark:stroke-gray-700/30" aria-hidden="true">
                        <defs>
                            <pattern id="grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse" x="50%" y="-1">
                                <path d="M.5 40V.5H40" fill="none" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" stroke-width="0" fill="url(#grid-pattern)" />
                    </svg>
                </div>

                <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                    <div
                        class="animate-fade-in inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-600 dark:border-emerald-800/50 dark:bg-emerald-950/30 dark:text-emerald-400"
                    >
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-600 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                        </span>
                        {{ __("We're hiring!") }}
                    </div>

                    <h1 class="animate-fade-in-up mt-8 text-4xl font-extrabold tracking-tight text-gray-900 lg:text-7xl dark:text-white">
                        <span class="bg-linear-to-b from-gray-900 to-gray-500 bg-clip-text text-transparent dark:from-white dark:to-gray-400">
                            Be part of our mission
                        </span>
                    </h1>

                    <p class="animate-fade-in-up mx-auto mt-6 max-w-2xl text-lg text-gray-600 [animation-delay:200ms] dark:text-gray-400">
                        {{
                            __(
                                "We're looking for passionate people to join us on our mission. We value flat hierarchies, clear communication, and full ownership and responsibility.",
                            )
                        }}
                    </p>
                </div>

                <!-- Second Blob for Symmetry -->
                <div
                    class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
                    aria-hidden="true"
                >
                    <div
                        class="relative left-[calc(50%+3rem)] aspect-1155/678 w-144.5 -translate-x-1/2 bg-linear-to-tr from-emerald-300 to-emerald-500 opacity-30 sm:left-[calc(50%+36rem)] sm:w-288.75 dark:from-emerald-700 dark:to-emerald-900 dark:opacity-25"
                        style="
                            clip-path: polygon(
                                74.1% 44.1%,
                                100% 61.6%,
                                97.5% 26.9%,
                                85.5% 0.1%,
                                80.7% 2%,
                                72.5% 32.5%,
                                60.2% 62.4%,
                                52.4% 68.1%,
                                47.5% 58.3%,
                                45.2% 34.5%,
                                27.5% 76.7%,
                                0.1% 64.9%,
                                17.9% 100%,
                                27.6% 76.8%,
                                76.1% 97.7%,
                                74.1% 44.1%
                            );
                        "
                    ></div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl space-y-16 px-4 sm:px-6 lg:px-8">
                <!-- Department Filter (Slider) -->
                <div class="relative flex items-center justify-center">
                    <!-- Left Arrow -->
                    <button
                        v-show="showLeftArrow"
                        @click="scrollLeft"
                        class="absolute -left-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-600 shadow-md ring-1 ring-gray-900/5 transition-all hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-gray-700"
                        aria-label="Scroll Left"
                    >
                        <ChevronLeft class="h-5 w-5" />
                    </button>

                    <!-- Scroll Container -->
                    <div
                        ref="scrollContainer"
                        @mousedown="startDrag"
                        @mouseleave="stopDrag"
                        @mouseup="stopDrag"
                        @mousemove="doDrag"
                        class="scrollbar-hide flex w-full cursor-auto gap-3 overflow-x-auto scroll-smooth px-2 py-2 select-none md:w-auto md:max-w-3xl lg:max-w-4xl xl:max-w-6xl"
                    >
                        <button
                            @click="filterByDepartment()"
                            id="dept-all"
                            :class="[
                                'inline-flex flex-shrink-0 cursor-pointer items-center justify-center rounded-full px-6 py-2 text-sm font-medium whitespace-nowrap transition-all duration-200',
                                !filters.department_id
                                    ? 'active-department-tab bg-gray-900 text-white shadow-lg dark:bg-white dark:text-gray-900'
                                    : 'border border-gray-200 bg-white text-gray-600 hover:border-gray-900 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-white dark:hover:text-white',
                            ]"
                        >
                            View all
                        </button>

                        <button
                            v-for="dept in departments"
                            :key="dept.id"
                            :id="`dept-${dept.id}`"
                            @click="filterByDepartment(dept.id)"
                            :class="[
                                'inline-flex flex-shrink-0 cursor-pointer items-center justify-center rounded-full px-6 py-2 text-sm font-medium whitespace-nowrap transition-all duration-200',
                                filters.department_id == dept.id
                                    ? 'active-department-tab bg-gray-900 text-white shadow-lg dark:bg-white dark:text-gray-900'
                                    : 'border border-gray-200 bg-white text-gray-600 hover:border-gray-900 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-white dark:hover:text-white',
                            ]"
                        >
                            {{ dept.name }}
                        </button>
                    </div>

                    <!-- Right Arrow -->
                    <button
                        v-show="showRightArrow"
                        @click="scrollRight"
                        class="absolute -right-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-600 shadow-md ring-1 ring-gray-900/5 transition-all hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-gray-700"
                        aria-label="Scroll Right"
                    >
                        <ChevronRight class="h-5 w-5" />
                    </button>
                </div>

                <!-- Job Listing -->
                <div class="divide-y divide-gray-200 border-t border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                    <div v-for="job in jobs" :key="job.id" class="group relative py-12 transition-all">
                        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-4">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    <Link :href="careersShow(job.slug).url" class="cursor-pointer hover:text-primary-600 hover:underline">
                                        {{ job.title }}
                                    </Link>
                                </h2>
                                <div v-html="job.description" class="line-clamp-2 max-w-xl text-gray-600 dark:text-gray-400"></div>

                                <div class="flex flex-wrap items-center gap-3">
                                    <div
                                        class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-600 capitalize dark:border-gray-700 dark:text-gray-400"
                                    >
                                        <div class="h-1.5 w-1.5 rounded-full bg-gray-400"></div>
                                        {{ job.job_type }}
                                    </div>
                                    <div
                                        class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-600 capitalize dark:border-gray-700 dark:text-gray-400"
                                    >
                                        <div class="h-1.5 w-1.5 rounded-full bg-gray-400"></div>
                                        {{ job.employment_type }}
                                    </div>
                                    <div
                                        v-if="job.department"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-600 capitalize dark:border-gray-700 dark:text-gray-400"
                                    >
                                        <div class="h-1.5 w-1.5 rounded-full bg-gray-400"></div>
                                        {{ job.department }}
                                    </div>
                                </div>
                            </div>

                            <Link
                                :href="careersShow(job.slug).url"
                                class="inline-flex cursor-pointer items-center gap-2 text-lg font-bold text-gray-900 hover:underline dark:text-white"
                            >
                                Apply
                                <ArrowUpRight class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="jobs.length === 0" class="py-20 text-center">
                        <p class="text-xl text-gray-500 dark:text-gray-400">No jobs found matching your criteria.</p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 1s ease-out forwards;
}

.animate-fade-in-up {
    animation: fadeInUp 1s ease-out forwards;
}

[animation-delay='200ms'] {
    animation-delay: 200ms;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
</style>
