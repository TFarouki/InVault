<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recent_sales: Array,
});

const statConfigs = [
    { key: 'total_sales', name: 'Total Sales', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', color: 'blue' },
    { key: 'transactions_count', name: 'Transactions', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2', color: 'indigo' },
    { key: 'customers_count', name: 'Customers', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', color: 'emerald' },
    { key: 'low_stock_count', name: 'Alertes Stock', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', color: 'amber' },
];
</script>

<template>
    <Head :title="$t('dashboard')" />

    <PosLayout :title="$t('dashboard')">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div 
                v-for="stat in statConfigs" 
                :key="stat.key"
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4"
            >
                <div :class="`p-3 rounded-xl bg-${stat.color}-50 dark:bg-${stat.color}-900/20 text-${stat.color}-600 dark:text-${stat.color}-400`">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t(stat.name) }}</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ stats[stat.key] }} {{ stat.key === 'total_sales' ? $page.props.settings.currency : '' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Sales Table -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ $t('recent_sales') }}</h2>
                    <Link :href="route('sales.index')" class="text-blue-600 dark:text-blue-400 text-sm font-medium hover:underline">{{ $t('Voir tout') }}</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">{{ $t('reference') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ $t('name') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ $t('total') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ $t('status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="sale in recent_sales" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs text-indigo-600 dark:text-indigo-400">{{ sale.reference }}</td>
                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">{{ sale.customer?.name || 'Walk-in' }}</td>
                                <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">{{ sale.total_ttc }} {{ $page.props.settings.currency }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium capitalize bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                                        {{ $t(sale.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">{{ $t('Actions Rapides') }}</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <Link :href="route('sales.index')" class="flex flex-col items-center justify-center p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span class="text-sm font-semibold">{{ $t('sales') }}</span>
                        </Link>
                        <Link :href="route('products.index')" class="flex flex-col items-center justify-center p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            <span class="text-sm font-semibold">{{ $t('products') }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PosLayout>
</template>
