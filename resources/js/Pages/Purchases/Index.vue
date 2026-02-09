<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const purchases = ref([
    { id: 1, ref: 'P-2024-001', supplier: 'Alpha Distrib', total: '5,000.00', method: 'Virement', date: '20-05-2024' },
    { id: 2, ref: 'P-2024-002', supplier: 'Coca Cola Maroc', total: '2,400.00', method: 'Chèque', date: '19-05-2024' },
]);
</script>

<template>
    <Head :title="$t('purchases')" />

    <PosLayout :title="$t('purchases')">
        <div class="mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('purchases') }}</h2>
            <button class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 dark:shadow-none">
                Nouvel Achat
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Référence</th>
                        <th class="px-6 py-4 font-semibold">Fournisseur</th>
                        <th class="px-6 py-4 font-semibold">Total</th>
                        <th class="px-6 py-4 font-semibold">Paiement</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 font-mono text-sm text-indigo-600 dark:text-indigo-400">{{ purchase.ref }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ purchase.supplier }}</td>
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">{{ purchase.total }} MAD</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ purchase.method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                             <button class="text-indigo-600 hover:text-indigo-800 uppercase text-xs font-bold">Détails</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </PosLayout>
</template>
