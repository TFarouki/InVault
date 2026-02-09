<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { wTrans, loadLanguageAsync } from 'laravel-vue-i18n';
import FloatingCalculator from '@/Components/Pos/FloatingCalculator.vue';
import { watch } from 'vue';

const props = defineProps({
    title: String,
});

const isSidebarOpen = ref(true);
const isUserDropdownOpen = ref(false);
const showCalculator = ref(false);
const page = usePage();

const menuItems = [
    { name: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', route: 'dashboard', roles: ['admin', 'manager', 'cashier'] },
    { name: 'sales', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', route: 'sales.index', roles: ['admin', 'manager', 'cashier'] },
    { name: 'customers', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', route: 'customers.index', roles: ['admin', 'manager'] },
    { name: 'products', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', route: 'products.index', roles: ['admin', 'manager'] },
    { name: 'purchases', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', route: 'purchases.index', roles: ['admin', 'manager'] },
    { name: 'reports', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', route: 'reports.index', roles: ['admin', 'manager'] },
    { name: 'inventory', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', route: 'inventory.index', roles: ['admin', 'manager'] },
];

const filteredMenuItems = computed(() => {
    const userRole = page.props.auth.user?.role?.slug || 'cashier'; // Default to cashier if no role
    return menuItems.filter(item => item.roles.includes(userRole));
});

const toggleLanguage = () => {
    const currentLang = page.props.locale || 'fr';
    let nextLang = 'fr';
    if (currentLang === 'fr') nextLang = 'en';
    else if (currentLang === 'en') nextLang = 'ar';
    else nextLang = 'fr';
    
    window.location.href = `/language/${nextLang}`;
};

const isRtl = () => page.props.locale === 'ar';

const isDarkMode = ref(false);

const initTheme = () => {
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDarkMode.value = false;
        document.documentElement.classList.remove('dark');
    }
};

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        localStorage.setItem('theme', 'dark');
        document.documentElement.classList.add('dark');
    } else {
        localStorage.setItem('theme', 'light');
        document.documentElement.classList.remove('dark');
    }
};

// Watch for locale changes from backend (Inertia Props)
watch(() => page.props.locale, (newLocale) => {
    if (newLocale) {
        loadLanguageAsync(newLocale);
    }
}, { immediate: true });

initTheme();

</script>

<template>
    <div :dir="isRtl() ? 'rtl' : 'ltr'" class="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 transition-all duration-300 flex flex-col',
                isSidebarOpen ? 'w-64' : 'w-20',
                isRtl() ? 'border-l' : 'border-r'
            ]"
        >
            <div class="h-16 flex items-center px-6 gap-3 border-b border-gray-200 dark:border-gray-700 overflow-hidden shrink-0">
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors shrink-0">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="isSidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
                <span v-if="isSidebarOpen" class="text-2xl font-black bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent truncate">
                    {{ $page.props.settings.app_name }}
                </span>
            </div>

            <nav class="flex-1 mt-6 px-4 space-y-2 overflow-y-auto">
                <Link 
                    v-for="item in menuItems" 
                    :key="item.name"
                    :href="route().has(item.route) ? route(item.route) : '#'"
                    :class="[
                        'flex items-center p-3 rounded-xl transition-all duration-200 group',
                        route().current(item.route) 
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' 
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]"
                >
                    <svg class="w-6 h-6 shrink-0 transition-colors" fill="none" stroke="currentColor" :viewBox="item.viewBox || '0 0 24 24'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    <span v-if="isSidebarOpen" :class="isRtl() ? 'mr-4' : 'ml-4'" class="font-medium">{{ $t(item.name) }}</span>
                    <div v-if="!isSidebarOpen" :class="isRtl() ? 'right-20' : 'left-20'" class="absolute bg-gray-900 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                        {{ $t(item.name) }}
                    </div>
                </Link>
            </nav>

            <!-- Sidebar Footer (Empty for now) -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-8 shrink-0">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white capitalize">{{ title }}</h1>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Calculator Shortcut -->
                    <button 
                        @click="showCalculator = !showCalculator" 
                        class="p-2 rounded-xl transition-all duration-200 border"
                        :class="showCalculator 
                            ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30' 
                            : 'bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border-gray-200 dark:border-gray-600'"
                        :title="$t('Calculator')"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </button>

                    <!-- POS Terminal Shortcut -->
                    <Link :href="route('pos.terminal')" class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-600" :title="$t('POS Terminal')">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 -960 960 960">
                            <path d="M280-640q-33 0-56.5-23.5T200-720v-80q0-33 23.5-56.5T280-880h400q33 0 56.5 23.5T760-800v80q0 33-23.5 56.5T680-640H280Zm0-80h400v-80H280v80ZM160-80q-33 0-56.5-23.5T80-160v-40h800v40q0 33-23.5 56.5T800-80H160ZM80-240l139-313q10-22 30-34.5t43-12.5h376q23 0 43 12.5t30 34.5l139 313H80Zm260-80h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Zm0-80h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Zm0-80h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Zm120 160h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Zm0-80h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Zm0-80h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Zm120 160h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Zm0-80h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Zm0-80h40q8 0 14-6t6-14q0-8-6-14t-14-6h-40q-8 0-14 6t-6 14q0 8 6 14t14 6Z" />
                        </svg>
                    </Link>

                    <!-- Theme Toggle -->
                    <button @click="toggleTheme" class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-600">
                        <svg v-if="isDarkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.364l-.707.707M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- User Dropdown -->
                    <div class="relative">
                        <button @click="isUserDropdownOpen = !isUserDropdownOpen" :class="isRtl() ? 'pr-3 pl-1' : 'pl-3 pr-1'" class="flex items-center gap-3 bg-gray-50 dark:bg-gray-700/50 p-1 rounded-full border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden md:block">{{ $page.props.auth.user.name }}</span>
                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold uppercase">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="isUserDropdownOpen" class="absolute top-12 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-2 z-50 ltr:right-0 rtl:left-0">
                            <!-- Admin Links -->
                            <div v-if="['admin', 'manager'].includes($page.props.auth.user.role?.slug)" class="border-b border-gray-100 dark:border-gray-700 mb-2 pb-2">
                                <Link v-if="$page.props.auth.user.role?.slug === 'admin'" :href="route('users.index')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ $t('users') }}
                                </Link>
                                <Link :href="route('settings.index')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ $t('settings') }}
                                </Link>
                            </div>

                            <!-- Logout -->
                            <Link :href="route('logout')" method="post" as="button" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 font-bold">
                                {{ $t('logout') }}
                            </Link>
                        </div>

                        <!-- Backdrop -->
                        <div v-if="isUserDropdownOpen" @click="isUserDropdownOpen = false" class="fixed inset-0 z-40"></div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto p-8">
                <slot />
            </div>
        </main>
        
        <!-- Floating Calculator -->
        <FloatingCalculator 
            :show="showCalculator" 
            @close="showCalculator = false"
            @minimize="showCalculator = false"
        />
    </div>
</template>

<style scoped>
.router-link-active {
    @apply bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400;
}
</style>
