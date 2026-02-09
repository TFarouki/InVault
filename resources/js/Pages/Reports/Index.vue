<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    reports: Object,
    filters: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

watch([startDate, endDate], () => {
    router.get(route('reports.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>

<template>
    <Head :title="$t('reports')" />

    <PosLayout :title="$t('reports')">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('reports') }}</h2>
            
            <div class="flex items-center gap-4 bg-white dark:bg-gray-800 p-2 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <input 
                    type="date" 
                    v-model="startDate"
                    class="rounded-xl border-none bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500/20"
                >
                <span class="text-gray-400">→</span>
                <input 
                    type="date" 
                    v-model="endDate"
                    class="rounded-xl border-none bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500/20"
                >
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $t('total') }} {{ $t('sales') }}</p>
                <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ reports.total_sales.toFixed(2) }} {{ $page.props.settings.currency }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $t('total') }} {{ $t('purchases') }}</p>
                <p class="text-3xl font-black text-red-600 dark:text-red-400">{{ reports.total_purchases.toFixed(2) }} {{ $page.props.settings.currency }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Profit</p>
                <p :class="[
                    'text-3xl font-black',
                    reports.profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600'
                ]">{{ reports.profit.toFixed(2) }} {{ $page.props.settings.currency }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Détails de la période</h3>
            </div>
            <div class="p-8 space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Nombre de ventes</span>
                    <span class="text-xl font-bold text-gray-800 dark:text-white">{{ reports.sales_count }}</span>
                </div>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Nombre d'achats</span>
                    <span class="text-xl font-bold text-gray-800 dark:text-white">{{ reports.purchases_count }}</span>
                </div>
            </div>
        </div>
    </PosLayout>
</template>
