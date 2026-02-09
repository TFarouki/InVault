<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const showModal = ref(false);
const editingCustomer = ref(null);
const search = ref(props.filters.search);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    balance: 0,
});

watch(search, debounce((value) => {
    router.get(route('customers.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const openCreateModal = () => {
    editingCustomer.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (customer) => {
    editingCustomer.value = customer;
    form.name = customer.name;
    form.email = customer.email;
    form.phone = customer.phone;
    form.address = customer.address;
    form.balance = customer.balance;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCustomer.value = null;
    form.reset();
};

const submit = () => {
    if (editingCustomer.value) {
        form.put(route('customers.update', editingCustomer.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('customers.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCustomer = (customer) => {
    if (confirm($t('Are you sure you want to delete this customer?'))) {
        router.delete(route('customers.destroy', customer.id));
    }
};
</script>

<template>
    <Head :title="$t('customers')" />

    <PosLayout :title="$t('customers')">
        <div class="mb-8 flex justify-between items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('customers') }}</h2>
            <div class="flex items-center gap-4 flex-1 max-w-md">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input v-model="search" type="text" :placeholder="$t('Search customers...')" class="block w-full pl-10 pr-3 py-2 border border-gray-200 dark:border-gray-700 rounded-xl leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out" />
                </div>
                <button @click="openCreateModal" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 dark:shadow-none whitespace-nowrap">
                    {{ $t('add') }}
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">{{ $t('name') }}</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('phone') }}</th>
                        <th class="px-6 py-4 font-semibold">Email</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('balance') }}</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ customer.name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ customer.phone || '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ customer.email || '-' }}</td>
                        <td class="px-6 py-4">
                            <span :class="customer.balance > 0 ? 'text-red-600' : 'text-green-600'" class="font-bold">
                                {{ customer.balance }} MAD
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <button @click="openEditModal(customer)" class="text-indigo-600 hover:text-indigo-800 uppercase text-xs font-bold">{{ $t('edit') }}</button>
                            <button @click="deleteCustomer(customer)" class="text-red-600 hover:text-red-800 uppercase text-xs font-bold">{{ $t('delete') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ editingCustomer ? $t('edit') : $t('add') }} {{ $t('customer') }}
                    </h3>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('name') }}</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
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
