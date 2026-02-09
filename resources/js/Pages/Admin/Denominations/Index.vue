<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    denominations: Array,
});

const showModal = ref(false);
const editingDenomination = ref(null);
const form = useForm({
    name: '',
    value: '',
    currency: 'MAD',
    image: null,
    sort_order: 0,
    is_active: true,
});

const openModal = (denomination = null) => {
    editingDenomination.value = denomination;
    if (denomination) {
        form.name = denomination.name;
        form.value = denomination.value;
        form.currency = denomination.currency;
        form.sort_order = denomination.sort_order;
        form.is_active = !!denomination.is_active;
        form.image = null; // Don't verify image on edit
    } else {
        form.reset();
        form.currency = 'MAD';
        form.is_active = true;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingDenomination.value = null;
};

const submit = () => {
    if (editingDenomination.value) {
        form.post(route('cash-denominations.update', editingDenomination.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            forceFormData: true, // Important for file upload with PUT/PATCH simulation
            _method: 'put',
        });
    } else {
        form.post(route('cash-denominations.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteDenomination = (denomination) => {
    if (confirm('Are you sure you want to delete this denomination?')) {
        useForm({}).delete(route('cash-denominations.destroy', denomination.id), {
            preserveScroll: true,
        });
    }
};

const toggleActive = (denomination) => {
    useForm({}).post(route('cash-denominations.toggle', denomination.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Cash Denominations">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $t('Cash Denominations') }}
                </h2>
                <PrimaryButton @click="openModal()">
                    {{ $t('Add New') }}
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="denom in denominations" :key="denom.id" 
                                class="border dark:border-gray-700 rounded-lg p-4 flex items-center justify-between bg-gray-50 dark:bg-gray-700/50"
                                :class="{'opacity-50': !denom.is_active}"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center overflow-hidden">
                                        <img v-if="denom.image_path" :src="'/storage/' + denom.image_path" class="w-full h-full object-cover">
                                        <span v-else class="text-xs font-bold">{{ denom.value }}</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg">{{ denom.name }}</h3>
                                        <p class="text-sm text-gray-500">{{ denom.value }} {{ denom.currency }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <button @click="openModal(denom)" class="text-blue-600 hover:underline text-sm">{{ $t('Edit') }}</button>
                                    <button @click="toggleActive(denom)" class="text-sm" :class="denom.is_active ? 'text-green-600' : 'text-gray-400'">
                                        {{ denom.is_active ? $t('Active') : $t('Inactive') }}
                                    </button>
                                    <button @click="deleteDenomination(denom)" class="text-red-600 hover:underline text-sm">{{ $t('Delete') }}</button>
                                </div>
                            </div>
                        </div>

                        <div v-if="denominations.length === 0" class="text-center py-10 text-gray-500">
                            {{ $t('No denominations found. Add one to get started.') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ editingDenomination ? $t('Edit Denomination') : $t('Add Denomination') }}
                </h2>

                <div class="space-y-4">
                    <div>
                        <InputLabel :value="$t('Name / Label')" />
                        <TextInput v-model="form.name" type="text" class="mt-1 block w-full" placeholder="e.g. 200 Dirhams" autofocus />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel :value="$t('Value')" />
                            <TextInput v-model="form.value" type="number" step="0.01" class="mt-1 block w-full" placeholder="200.00" />
                            <div v-if="form.errors.value" class="text-red-500 text-sm mt-1">{{ form.errors.value }}</div>
                        </div>
                        <div>
                            <InputLabel :value="$t('Currency')" />
                            <TextInput v-model="form.currency" type="text" class="mt-1 block w-full" />
                            <div v-if="form.errors.currency" class="text-red-500 text-sm mt-1">{{ form.errors.currency }}</div>
                        </div>
                    </div>

                    <div>
                        <InputLabel :value="$t('Image (Optional)')" />
                        <input type="file" @change="form.image = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300" />
                        <div v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</div>
                    </div>
                     <div>
                        <InputLabel :value="$t('Sort Order')" />
                        <TextInput v-model="form.sort_order" type="number" class="mt-1 block w-full" placeholder="0" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">{{ $t('Cancel') }}</SecondaryButton>
                    <PrimaryButton @click="submit" :disabled="form.processing">
                        {{ editingDenomination ? $t('Update') : $t('Save') }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
