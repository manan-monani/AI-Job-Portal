<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { index, update } from '@/routes/admin/business/settings';
import { Settings, DollarSign, Globe, MapPin, Search, Clock } from 'lucide-vue-next';

const props = defineProps<{
    settings: {
        currency_symbol: string;
        timezone: string;
        country: string;
        language: string;
    };
}>();

const form = useForm({
    currency_symbol: props.settings.currency_symbol,
    timezone: props.settings.timezone,
    country: props.settings.country,
    language: props.settings.language,
});

const languages = [
    { code: 'en', name: 'English' },
    { code: 'ar', name: 'Arabic' },
];

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Form will be reset with new values from server
        },
    });
};

// Search states
const currencySearch = ref('');
const timezoneSearch = ref('');
const countrySearch = ref('');

// Common timezones list with regions
const timezones = [
    'UTC',
    'America/New_York',
    'America/Chicago',
    'America/Denver',
    'America/Los_Angeles',
    'America/Toronto',
    'America/Mexico_City',
    'America/Sao_Paulo',
    'America/Argentina/Buenos_Aires',
    'Europe/London',
    'Europe/Paris',
    'Europe/Berlin',
    'Europe/Rome',
    'Europe/Moscow',
    'Europe/Istanbul',
    'Africa/Cairo',
    'Africa/Johannesburg',
    'Asia/Dubai',
    'Asia/Karachi',
    'Asia/Kolkata',
    'Asia/Dhaka',
    'Asia/Bangkok',
    'Asia/Singapore',
    'Asia/Hong_Kong',
    'Asia/Tokyo',
    'Asia/Seoul',
    'Asia/Shanghai',
    'Australia/Sydney',
    'Australia/Melbourne',
    'Pacific/Auckland',
    'Pacific/Fiji',
];

// Get current time and UTC offset for a timezone
const getTimezoneInfo = (timezone: string) => {
    try {
        const now = new Date();
        
        // Get formatted time
        const timeFormatter = new Intl.DateTimeFormat('en-US', {
            timeZone: timezone,
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
        const time = timeFormatter.format(now);
        
        // Calculate UTC offset
        const utcDate = new Date(now.toLocaleString('en-US', { timeZone: 'UTC' }));
        const tzDate = new Date(now.toLocaleString('en-US', { timeZone: timezone }));
        const offsetMinutes = (tzDate.getTime() - utcDate.getTime()) / (1000 * 60);
        const offsetHours = offsetMinutes / 60;
        
        // Format offset as UTC+X or UTC-X
        const offsetSign = offsetHours >= 0 ? '+' : '';
        const offset = `UTC${offsetSign}${offsetHours}`;
        
        return { time, offset };
    } catch (e) {
        return { time: '', offset: 'UTC' };
    }
};

// Filtered timezones based on search
const filteredTimezones = computed(() => {
    if (!timezoneSearch.value) return timezones;
    const search = timezoneSearch.value.toLowerCase();
    return timezones.filter(tz => tz.toLowerCase().includes(search));
});

// Common countries list
const countries = [
    'Afghanistan', 'Albania', 'Algeria', 'Argentina', 'Australia', 'Austria',
    'Bangladesh', 'Belgium', 'Brazil', 'Canada', 'China', 'Colombia',
    'Denmark', 'Egypt', 'Finland', 'France', 'Germany', 'Greece',
    'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Italy', 'Japan',
    'Kenya', 'Malaysia', 'Mexico', 'Netherlands', 'New Zealand', 'Nigeria',
    'Norway', 'Pakistan', 'Philippines', 'Poland', 'Portugal', 'Russia',
    'Saudi Arabia', 'Singapore', 'South Africa', 'South Korea', 'Spain',
    'Sweden', 'Switzerland', 'Thailand', 'Turkey', 'Ukraine',
    'United Arab Emirates', 'United Kingdom', 'United States', 'Vietnam',
];

// Filtered countries based on search
const filteredCountries = computed(() => {
    if (!countrySearch.value) return countries;
    const search = countrySearch.value.toLowerCase();
    return countries.filter(country => country.toLowerCase().includes(search));
});

const currencyExamples = [
    { symbol: '$', name: 'US Dollar', search: 'usd dollar' },
    { symbol: '€', name: 'Euro', search: 'eur euro' },
    { symbol: '£', name: 'British Pound', search: 'gbp pound sterling' },
    { symbol: '¥', name: 'Japanese Yen / Chinese Yuan', search: 'jpy cny yen yuan' },
    { symbol: '₹', name: 'Indian Rupee', search: 'inr rupee' },
    { symbol: '৳', name: 'Bangladeshi Taka', search: 'bdt taka' },
    { symbol: 'R$', name: 'Brazilian Real', search: 'brl real' },
    { symbol: '₽', name: 'Russian Ruble', search: 'rub ruble' },
    { symbol: 'A$', name: 'Australian Dollar', search: 'aud australian' },
    { symbol: 'C$', name: 'Canadian Dollar', search: 'cad canadian' },
    { symbol: '₱', name: 'Philippine Peso', search: 'php peso' },
    { symbol: '₩', name: 'South Korean Won', search: 'krw won' },
    { symbol: 'CHF', name: 'Swiss Franc', search: 'chf franc' },
    { symbol: 'kr', name: 'Swedish Krona', search: 'sek krona' },
];

// Filtered currencies based on search
const filteredCurrencies = computed(() => {
    if (!currencySearch.value) return currencyExamples;
    const search = currencySearch.value.toLowerCase();
    return currencyExamples.filter(currency => 
        currency.symbol.toLowerCase().includes(search) ||
        currency.name.toLowerCase().includes(search) ||
        currency.search.toLowerCase().includes(search)
    );
});
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                        <Settings class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('Business Logic') }}
                    </h1>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('Configure your business currency, timezone, and location settings.') }}
                </p>
            </div>

            <!-- Settings Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Language Selection -->
                    <div>
                        <label for="language" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <Globe class="w-4 h-4" />
                            {{ __('System Language') }}
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button
                                v-for="lang in languages"
                                :key="lang.code"
                                type="button"
                                @click="form.language = lang.code"
                                class="flex items-center justify-between px-4 py-3 border rounded-lg transition-all"
                                :class="form.language === lang.code
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md ring-1 ring-primary-500'
                                    : 'border-gray-200 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-700 bg-white dark:bg-gray-800'"
                            >
                                <span class="font-medium text-gray-900 dark:text-white">{{ lang.name }}</span>
                                <div v-if="form.language === lang.code" class="w-2 h-2 rounded-full bg-primary-600"></div>
                            </button>
                        </div>
                        <p v-if="form.errors.language" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.language }}
                        </p>
                    </div>

                    <!-- Currency Symbol with Search -->
                    <div>
                        <label for="currency_symbol" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <DollarSign class="w-4 h-4" />
                            {{ __('Currency Symbol') }}
                        </label>
                        
                        <!-- Search Input -->
                        <div class="relative mb-3">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="currencySearch"
                                type="text"
                                placeholder="Search currency (e.g., USD, Euro, Taka)..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>

                        <!-- Currency Grid -->
                        <div class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700/50">
                            <div v-if="filteredCurrencies.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <button
                                    v-for="currency in filteredCurrencies"
                                    :key="currency.symbol"
                                    type="button"
                                    @click="form.currency_symbol = currency.symbol; currencySearch = ''"
                                    class="flex items-center gap-3 px-4 py-3 text-left bg-white dark:bg-gray-800 border rounded-lg transition-all"
                                    :class="form.currency_symbol === currency.symbol 
                                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md' 
                                        : 'border-gray-200 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50/50 dark:hover:bg-primary-900/10'"
                                >
                                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400 min-w-[40px]">{{ currency.symbol }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ currency.name }}</div>
                                    </div>
                                </button>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <Search class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No currencies found</p>
                            </div>
                        </div>
                        
                        <p v-if="form.errors.currency_symbol" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.currency_symbol }}
                        </p>
                    </div>

                    <!-- Timezone with Search and Time Display -->
                    <div>
                        <label for="timezone" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <Globe class="w-4 h-4" />
                            {{ __('Timezone') }}
                        </label>
                        
                        <!-- Search Input -->
                        <div class="relative mb-3">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="timezoneSearch"
                                type="text"
                                placeholder="Search timezone (e.g., Dhaka, New York, Tokyo)..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>

                        <!-- Timezone List -->
                        <div class="max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <div v-if="filteredTimezones.length > 0" class="divide-y divide-gray-200 dark:divide-gray-600">
                                <button
                                    v-for="tz in filteredTimezones"
                                    :key="tz"
                                    type="button"
                                    @click="form.timezone = tz; timezoneSearch = ''"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left transition-colors"
                                    :class="form.timezone === tz 
                                        ? 'bg-primary-50 dark:bg-primary-900/20 border-l-4 border-primary-500' 
                                        : 'hover:bg-white dark:hover:bg-gray-800'"
                                >
                                    <div class="flex-1 min-w-0 pr-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ tz }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ getTimezoneInfo(tz).offset }}</div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs font-mono text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-600 whitespace-nowrap">
                                        <Clock class="w-3 h-3" />
                                        {{ getTimezoneInfo(tz).time }}
                                    </div>
                                </button>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <Search class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No timezones found</p>
                            </div>
                        </div>
                        
                        <p v-if="form.errors.timezone" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.timezone }}
                        </p>
                    </div>

                    <!-- Country with Search -->
                    <div>
                        <label for="country" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <MapPin class="w-4 h-4" />
                            {{ __('Country') }}
                        </label>
                        
                        <!-- Search Input -->
                        <div class="relative mb-3">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="countrySearch"
                                type="text"
                                placeholder="Search country..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>

                        <!-- Country List -->
                        <div class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <div v-if="filteredCountries.length > 0" class="divide-y divide-gray-200 dark:divide-gray-600">
                                <button
                                    v-for="country in filteredCountries"
                                    :key="country"
                                    type="button"
                                    @click="form.country = country; countrySearch = ''"
                                    class="w-full px-4 py-3 text-left text-sm transition-colors"
                                    :class="form.country === country 
                                        ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-semibold border-l-4 border-primary-500' 
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800'"
                                >
                                    {{ country }}
                                </button>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <Search class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No countries found</p>
                            </div>
                        </div>
                        
                        <p v-if="form.errors.country" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.country }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-400 text-white rounded-lg font-medium transition-colors shadow-lg shadow-primary-500/30 disabled:shadow-none flex items-center gap-2"
                        >
                            <Settings class="w-4 h-4" :class="{ 'animate-spin': form.processing }" />
                            {{ form.processing ? __('Saving...') : __('Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
