<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('inventory.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const showAdjustModal = ref(false);
const selectedProduct = ref(null);

const form = useForm({
    type: 'add',
    quantity: 1,
    reason: '',
});

const openAdjustModal = (product) => {
    selectedProduct.value = product;
    form.reset();
    showAdjustModal.value = true;
};

const closeAdjustModal = () => {
    showAdjustModal.value = false;
    selectedProduct.value = null;
    form.reset();
};

const submitAdjustment = () => {
    form.post(route('inventory.adjust', selectedProduct.value.id), {
        onSuccess: () => closeAdjustModal(),
    });
};
</script>

<template>
    <Head :title="$t('inventory')" />

    <PosLayout :title="$t('inventory')">
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('stock_management') }}</h2>
            
            <div class="flex gap-2">
                <div class="relative">
                    <input 
                        v-model="search" 
                        type="text" 
                        :placeholder="$t('search_product') + '...'" 
                        class="pl-10 pr-4 py-2 rounded-xl border-none bg-white dark:bg-gray-800 shadow-sm focus:ring-2 focus:ring-blue-500/20 w-64 text-gray-700 dark:text-gray-300 placeholder-gray-400"
                    >
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                
                <Link :href="route('inventory.history')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 dark:shadow-none flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $t('history') }}
                </Link>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">{{ $t('product') }}</th>
                        <th class="px-6 py-4 font-semibold text-center">{{ $t('stock') }}</th>
                        <th class="px-6 py-4 font-semibold text-right">{{ $t('price') }}</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800 dark:text-white">{{ product.name }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ product.code }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span :class="[
                                'px-3 py-1 rounded-full text-lg font-black',
                                product.stock < 10 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                            ]">
                                {{ product.stock }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-gray-600 dark:text-gray-300">
                            {{ product.price_ttc }} {{ $page.props.settings.currency }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openAdjustModal(product)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-bold text-sm uppercase">
                                {{ $t('adjust') }}
                            </button>
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
                    v-for="(link, key) in products.links" 
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

        <!-- Adjust Modal -->
        <div v-if="showAdjustModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ $t('adjust_stock') }}: {{ selectedProduct?.name }}</h3>
                </div>
                <form @submit.prevent="submitAdjustment" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('type') }}</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button 
                                type="button"
                                @click="form.type = 'add'"
                                :class="[
                                    'py-3 rounded-xl font-bold border',
                                    form.type === 'add' 
                                        ? 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400' 
                                        : 'border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'
                                ]"
                            >
                                + {{ $t('add') }}
                            </button>
                            <button 
                                type="button"
                                @click="form.type = 'subtract'"
                                :class="[
                                    'py-3 rounded-xl font-bold border',
                                    form.type === 'subtract' 
                                        ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400' 
                                        : 'border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'
                                ]"
                            >
                                - {{ $t('remove') }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('quantity') }}</label>
                        <input v-model="form.quantity" type="number" min="1" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-center font-bold text-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('reason') }}</label>
                        <input v-model="form.reason" type="text" :placeholder="$t('optional')" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="closeAdjustModal" class="px-6 py-2 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            {{ $t('cancel') }}
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50">
                            {{ $t('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PosLayout>
</template>
