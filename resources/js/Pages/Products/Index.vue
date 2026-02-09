<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const showModal = ref(false);
const editingProduct = ref(null);
const search = ref(props.filters.search);
const category_id = ref(props.filters.category_id);

const form = useForm({
    code: '',
    name: '',
    category_id: '',
    price_ht: 0,
    tax_percentage: 20,
    stock: 0,
    cost_price: 0,
    active: true,
});

watch([search, category_id], debounce(() => {
    router.get(route('products.index'), { 
        search: search.value, 
        category_id: category_id.value 
    }, { preserveState: true, replace: true });
}, 300));

const openCreateModal = () => {
    editingProduct.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (product) => {
    editingProduct.value = product;
    form.code = product.code;
    form.name = product.name;
    form.category_id = product.category_id;
    form.price_ht = product.price_ht;
    form.tax_percentage = product.tax_percentage;
    form.stock = product.stock;
    form.cost_price = product.cost_price;
    form.active = Boolean(product.active);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingProduct.value = null;
    form.reset();
};

const submit = () => {
    if (editingProduct.value) {
        form.put(route('products.update', editingProduct.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('products.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProduct = (product) => {
    if (confirm($t('Are you sure you want to delete this product?'))) {
        router.delete(route('products.destroy', product.id));
    }
};
</script>

<template>
    <Head :title="$t('products')" />

    <PosLayout :title="$t('products')">
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('products') }}</h2>
            
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input v-model="search" type="text" :placeholder="$t('search')" class="block w-full pl-10 pr-3 py-2 border border-gray-200 dark:border-gray-700 rounded-xl leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out" />
                </div>

                <select v-model="category_id" class="block w-full sm:w-48 py-2 px-3 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-gray-300">
                    <option value="">{{ $t('Toutes les catégories') }}</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>

                <button @click="openCreateModal" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 dark:shadow-none whitespace-nowrap w-full sm:w-auto">
                    {{ $t('add') }}
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">{{ $t('code') }} / {{ $t('name') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('category') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('price') }} (TTC)</th>
                        <th class="px-6 py-4 font-semibold">Stock</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-mono text-indigo-600 dark:text-indigo-400">{{ product.code }}</div>
                            <div class="font-bold text-gray-800 dark:text-white">{{ product.name }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ product.category?.name || 'N/A' }}</td>
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">{{ product.price_ttc }} MAD</td>
                        <td class="px-6 py-4">
                            <span :class="product.stock < 10 ? 'text-red-500 font-bold' : 'text-gray-600 dark:text-gray-300'">
                                {{ product.stock }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            <span :class="product.active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded-full text-xs font-bold">
                                {{ product.active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <button @click="openEditModal(product)" class="text-indigo-600 hover:text-indigo-800 uppercase text-xs font-bold">{{ $t('edit') }}</button>
                            <button @click="deleteProduct(product)" class="text-red-600 hover:text-red-800 uppercase text-xs font-bold">{{ $t('delete') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl shadow-2xl">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ editingProduct ? $t('edit') : $t('add') }} {{ $t('product') }}
                    </h3>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('code') }}</label>
                            <input v-model="form.code" type="text" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('name') }}</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('category') }}</label>
                            <select v-model="form.category_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Choisir...</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('price') }} (HT)</label>
                            <input v-model="form.price_ht" type="number" step="0.01" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">TVA (%)</label>
                            <input v-model="form.tax_percentage" type="number" step="0.01" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stock Initial</label>
                            <input v-model="form.stock" type="number" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prix d'Achat (Cost)</label>
                            <input v-model="form.cost_price" type="number" step="0.01" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="form.active" type="checkbox" id="active" class="rounded text-indigo-600 focus:ring-indigo-500" />
                        <label for="active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Actif</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="closeModal" class="px-6 py-2 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            {{ $t('cancel') }}
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50">
                            {{ form.processing ? '...' : $t('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PosLayout>
</template>
