<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    suppliers: Object,
    filters: Object,
});

const showModal = ref(false);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const supplierToDelete = ref(null);
const editingSupplier = ref(null);
const search = ref(props.filters.search);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    ice: '',
    balance: 0,
});

watch(search, debounce((value) => {
    router.get(route('suppliers.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const openCreateModal = () => {
    editingSupplier.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (supplier) => {
    editingSupplier.value = supplier;
    form.name = supplier.name;
    form.email = supplier.email;
    form.phone = supplier.phone;
    form.address = supplier.address;
    form.ice = supplier.ice;
    form.balance = supplier.balance;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingSupplier.value = null;
    form.reset();
};

const submit = () => {
    if (editingSupplier.value) {
        form.put(route('suppliers.update', editingSupplier.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('suppliers.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (supplier) => {
    supplierToDelete.value = supplier;
    showDeleteModal.value = true;
};

const deleteSupplier = () => {
    deletingId.value = supplierToDelete.value.id;
    form.delete(route('suppliers.destroy', supplierToDelete.value.id), {
        onFinish: () => {
            deletingId.value = null;
            showDeleteModal.value = false;
            supplierToDelete.value = null;
        },
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    supplierToDelete.value = null;
};
</script>

<template>
    <Head :title="$t('suppliers')" />

    <PosLayout :title="$t('suppliers')">
        <div class="mb-8 flex justify-between items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('suppliers') }}</h2>
            <div class="flex items-center gap-4 flex-1 max-w-md">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input v-model="search" type="text" :placeholder="$t('Search suppliers...')" class="block w-full pl-10 pr-3 py-2 border border-gray-200 dark:border-gray-700 rounded-xl leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out" />
                </div>
                <button @click="openCreateModal" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 dark:shadow-none whitespace-nowrap">
                    {{ $t('add') }}
                </button>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">{{ $t('name') }}</th>
                        <th class="px-6 py-4 font-semibold">ICE</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('phone') }}</th>
                        <th class="px-6 py-4 font-semibold">Email</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('balance') }}</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="supplier in suppliers.data" :key="supplier.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ supplier.name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ supplier.ice || '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ supplier.phone || '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ supplier.email || '-' }}</td>
                        <td class="px-6 py-4">
                            <span :class="supplier.balance > 0 ? 'text-red-600' : 'text-green-600'" class="font-bold">
                                {{ supplier.balance }} MAD
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button @click="openEditModal(supplier)" class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors" :title="$t('edit')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button @click="confirmDelete(supplier)" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" :title="$t('delete')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @keydown.esc="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ editingSupplier ? $t('edit') : $t('add') }} {{ $t('supplier') }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('name') }}</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ICE</label>
                            <input v-model="form.ice" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                            <input v-model="form.email" type="email" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('phone') }}</label>
                            <input v-model="form.phone" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adresse</label>
                            <textarea v-model="form.address" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Solde Inicial</label>
                            <input v-model="form.balance" type="number" step="0.01" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        </div>
                    </div>
                    <div class="flex justify-end pt-4">
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50">
                            {{ form.processing ? '...' : $t('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Delete Supplier</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Are you sure you want to delete <strong>{{ supplierToDelete?.name }}</strong>? This action cannot be undone.
                    </p>
                    <div class="flex justify-center gap-3">
                        <button @click="cancelDelete" class="px-6 py-2 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </button>
                        <button @click="deleteSupplier" :disabled="form.processing" class="px-6 py-2 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 transition-colors disabled:opacity-50">
                            {{ form.processing ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PosLayout>
</template>
