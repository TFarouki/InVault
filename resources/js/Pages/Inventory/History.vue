<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    movements: Object,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <Head :title="$t('history')" />

    <PosLayout :title="$t('history')">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('stock_movements_history') }}</h2>
            
            <Link :href="route('inventory.index')" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ $t('back') }}
            </Link>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">{{ $t('date') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('product') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('type') }}</th>
                        <th class="px-6 py-4 font-semibold text-center">{{ $t('quantity') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('user') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('reason') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ formatDate(movement.created_at) }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">
                            {{ movement.product?.name }}
                        </td>
                        <td class="px-6 py-4">
                            <span :class="[
                                'px-2 py-1 rounded text-xs font-bold uppercase',
                                movement.type === 'adjustment' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                                movement.type === 'sale' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                movement.type === 'purchase' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                'bg-gray-100 text-gray-600'
                            ]">
                                {{ $t(movement.type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center font-mono font-bold" :class="movement.quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                            {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ movement.user?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 italic">
                            {{ movement.reason }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            <div class="flex gap-1">
                <component 
                    :is="link.url ? 'Link' : 'span'"
                    v-for="(link, key) in movements.links" 
                    :key="key"
                    :href="link.url" 
                    v-html="link.label"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium',
                        link.url 
                            ? (link.active ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700')
                            : 'text-gray-400 bg-gray-100 dark:bg-gray-800 dark:text-gray-600 cursor-not-allowed'
                    ]"
                />
            </div>
        </div>
    </PosLayout>
</template>
