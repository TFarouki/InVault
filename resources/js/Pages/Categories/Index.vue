<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Object,
});

const showModal = ref(false);
const editingCategory = ref(null);

const form = useForm({
    name: '',
});

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
    form.reset();
};

const submit = () => {
    if (editingCategory.value) {
        form.put(route('categories.update', editingCategory.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = (category) => {
    if (confirm($t('Are you sure you want to delete this category?'))) {
        router.delete(route('categories.destroy', category.id));
    }
};
</script>

<template>
    <Head :title="$t('categories')" />

    <PosLayout :title="$t('categories')">
        <div class="mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('categories') }}</h2>
            <button @click="openCreateModal" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 dark:shadow-none">
                {{ $t('add') }}
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">{{ $t('name') }}</th>
                        <th class="px-6 py-4 font-semibold">Slug</th>
                        <th class="px-6 py-4 font-semibold">{{ $t('Products Count') }}</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ category.name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-mono text-sm">{{ category.slug }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ category.products_count }}</td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <button @click="openEditModal(category)" class="text-indigo-600 hover:text-indigo-800 uppercase text-xs font-bold">{{ $t('edit') }}</button>
                            <button @click="deleteCategory(category)" class="text-red-600 hover:text-red-800 uppercase text-xs font-bold">{{ $t('delete') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ editingCategory ? $t('edit') : $t('add') }} {{ $t('category') }}
                    </h3>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('name') }}</label>
                        <input v-model="form.name" type="text" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
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
