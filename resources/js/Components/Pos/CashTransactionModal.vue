<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['close', 'submitted']);

const form = useForm({
    type: 'drop', // drop, payout
    amount: '',
    description: '',
    reference_id: '',
});

const submit = () => {
    form.post(route('cash-transactions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('submitted');
            close();
        },
    });
};

const close = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <Modal :show="show" :maxWidth="'md'" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $t('New Cash Transaction') }}
            </h2>

            <div class="space-y-4">
                <div>
                    <InputLabel :value="$t('Transaction Type')" />
                    <div class="grid grid-cols-1 gap-2 mt-1">
                        <label class="flex items-center p-3 rounded-xl border transition-colors cursor-pointer" :class="form.type === 'drop' ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800' : 'border-gray-200 dark:border-gray-700'">
                            <input type="radio" v-model="form.type" value="drop" class="text-blue-600 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-gray-900 dark:text-gray-100">{{ $t('Cash Drop') }}</span>
                                <span class="block text-xs text-gray-500">{{ $t('Transfer excess cash to safe') }}</span>
                            </div>
                        </label>
                        <label class="flex items-center p-3 rounded-xl border transition-colors cursor-pointer" :class="form.type === 'payout' ? 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' : 'border-gray-200 dark:border-gray-700'">
                            <input type="radio" v-model="form.type" value="payout" class="text-red-600 focus:ring-red-500">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-gray-900 dark:text-gray-100">{{ $t('Payout') }}</span>
                                <span class="block text-xs text-gray-500">{{ $t('Payment for expenses or vendors') }}</span>
                            </div>
                        </label>
                        <label class="flex items-center p-3 rounded-xl border transition-colors cursor-pointer" :class="form.type === 'split' ? 'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-800' : 'border-gray-200 dark:border-gray-700'">
                            <input type="radio" v-model="form.type" value="split" class="text-amber-600 focus:ring-amber-500">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-gray-900 dark:text-gray-100">{{ $t('Cash Split') }}</span>
                                <span class="block text-xs text-gray-500">{{ $t('Exchange large bills for change') }}</span>
                            </div>
                        </label>
                    </div>
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <div>
                    <InputLabel :value="$t('Amount')" />
                    <TextInput 
                        v-model="form.amount" 
                        type="number" 
                        step="0.01" 
                        class="mt-1 block w-full" 
                        placeholder="0.00" 
                        autofocus
                    />
                    <InputError :message="form.errors.amount" class="mt-2" />
                </div>

                <div>
                    <InputLabel :value="$t('Description / Reason')" />
                    <TextInput v-model="form.description" type="text" class="mt-1 block w-full" placeholder="e.g. Excess cash drop" />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>
                 <div>
                    <InputLabel :value="$t('Reference (Optional)')" />
                    <TextInput v-model="form.reference_id" type="text" class="mt-1 block w-full" placeholder="e.g. Receipt #123" />
                    <InputError :message="form.errors.reference_id" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="close">{{ $t('Cancel') }}</SecondaryButton>
                <PrimaryButton @click="submit" :disabled="form.processing">
                    {{ $t('Save Transaction') }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
