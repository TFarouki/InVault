<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    sales: Object,
});
</script>

<template>
    <Head :title="$t('sales')" />

    <PosLayout :title="$t('sales')">
        <div class="mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('sales') }}</h2>
            <Link :href="route('sales.create')" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 dark:shadow-none">
                {{ $t('add') }}
            </Link>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">{{ $t('reference') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('customer') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('total') }}</th>
                        <th class="px-6 py-4 font-semibold">Paiement</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs text-blue-600 dark:text-blue-400">{{ sale.reference }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ sale.customer?.name || 'Walk-in' }}</td>
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">{{ sale.total_ttc }} MAD</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ sale.payment_method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                             <button class="text-blue-600 hover:text-blue-800 uppercase text-xs font-bold">{{ $t('View') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </PosLayout>
</template>
