<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
    stats: Object,
});
</script>

<template>
    <Head title="Rapport d'Inventaire" />

    <PosLayout title="Inventaire & Stock">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">État du Stock</h2>
            <div class="flex gap-2">
                <Link :href="route('reports.index')" class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-xl font-bold border border-gray-100 dark:border-gray-700 hover:bg-gray-50 transition-colors">
                    Rapports de Ventes
                </Link>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Articles en Stock</p>
                <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ stats.total_items }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Valeur d'Achat</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white">{{ stats.total_value_cost.toFixed(2) }} {{ $page.props.settings.currency }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Valeur de Vente</p>
                <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ stats.total_value_retail.toFixed(2) }} {{ $page.props.settings.currency }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Stock Faible</p>
                <p class="text-3xl font-black text-red-600">{{ stats.low_stock_count }}</p>
            </div>
        </div>

        <!-- Inventory List -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider font-bold">
                        <th class="px-8 py-4">Produit</th>
                        <th class="px-8 py-4 text-center">Stock</th>
                        <th class="px-8 py-4 text-right">Prix Achat</th>
                        <th class="px-8 py-4 text-right">Prix Vente</th>
                        <th class="px-8 py-4 text-right">Valeur Stock (Achat)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-8 py-4">
                            <div class="font-bold text-gray-800 dark:text-white">{{ product.name }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ product.code }}</div>
                        </td>
                        <td class="px-8 py-4 text-center">
                            <span :class="[
                                'px-3 py-1 rounded-full text-xs font-black',
                                product.stock < 10 ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'
                            ]">
                                {{ product.stock }}
                            </span>
                        </td>
                        <td class="px-8 py-4 text-right text-gray-600 dark:text-gray-300 font-medium">
                            {{ product.cost_price.toFixed(2) }}
                        </td>
                        <td class="px-8 py-4 text-right text-gray-600 dark:text-gray-300 font-medium">
                            {{ product.price_ttc.toFixed(2) }}
                        </td>
                        <td class="px-8 py-4 text-right font-bold text-gray-800 dark:text-white">
                            {{ (product.cost_price * product.stock).toFixed(2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </PosLayout>
</template>
