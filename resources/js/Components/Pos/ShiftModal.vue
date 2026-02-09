<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    show: Boolean,
    mode: {
        type: String,
        default: 'open', // 'open' or 'close'
    },
    denominations: Array,
    shift: Object, // Required for close mode
});

const emit = defineEmits(['close', 'submitted']);

const counts = ref({});
const notes = ref('');

// Initialize counts with 0 for each denomination
watch(() => props.denominations, (newDenoms) => {
    if (newDenoms) {
        newDenoms.forEach(d => {
            if (counts.value[d.id] === undefined) {
                counts.value[d.id] = 0;
            }
        });
    }
}, { immediate: true });

const totalAmount = computed(() => {
    let total = 0;
    props.denominations.forEach(d => {
        total += (counts.value[d.id] || 0) * parseFloat(d.value);
    });
    return total;
});

const summaryData = ref(null);

const submit = () => {
    const formattedCounts = Object.entries(counts.value).map(([denomId, qty]) => ({
        denomination_id: parseInt(denomId),
        quantity: qty
    })).filter(c => c.quantity > 0);

    if (props.mode === 'open') {
        axios.post(route('shifts.open'), {
            start_cash: totalAmount.value,
            counts: formattedCounts
        }).then(response => {
            emit('submitted', response.data.shift);
            emit('close');
        }).catch(error => {
            console.error('Error opening shift:', error);
        });
    } else {
        axios.post(route('shifts.close'), {
            end_cash: totalAmount.value,
            counts: formattedCounts,
            notes: notes.value
        }).then(response => {
            summaryData.value = response.data;
            emit('submitted', response.data);
        }).catch(error => {
            console.error('Error closing shift:', error);
        });
    }
};

const handleNumericInput = (event, denomId) => {
    // Prevent negative numbers
    if (event.target.value < 0) {
        counts.value[denomId] = 0;
    }
};

watch(() => props.show, (newVal) => {
    if (!newVal) {
        summaryData.value = null;
    }
});
</script>

<template>
    <Modal :show="show" :maxWidth="'2xl'" @close="$emit('close')">
        <div class="p-6">
            <template v-if="!summaryData">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ mode === 'open' ? $t('Open Shift - Cash Count') : $t('Close Shift - Cash Count') }}
                </h2>

                <!-- Cash Counting Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6 max-h-[60vh] overflow-y-auto pr-2">
                    <div v-for="denom in denominations" :key="denom.id" 
                         class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg flex flex-col items-center border border-gray-200 dark:border-gray-600">
                        
                        <!-- Denomination Visual -->
                        <div class="mb-2 h-12 flex items-center justify-center w-full">
                            <img v-if="denom.image_path" :src="'/storage/' + denom.image_path" class="max-h-full max-w-full object-contain">
                            <span v-else class="text-xl font-bold font-mono">{{ denom.value }}</span>
                        </div>

                        <InputLabel :value="denom.name" class="text-xs text-center mb-1" />
                        
                        <div class="flex items-center w-full">
                            <span class="text-xs text-gray-500 mr-2">x</span>
                            <TextInput 
                                v-model="counts[denom.id]" 
                                type="number" 
                                min="0"
                                class="w-full text-center py-1 text-sm font-bold" 
                                @input="e => handleNumericInput(e, denom.id)"
                            />
                        </div>
                        <div class="mt-1 text-xs text-gray-500 font-mono">
                            {{ ((counts[denom.id] || 0) * denom.value).toFixed(2) }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-end border-t pt-4 dark:border-gray-700">
                    <div v-if="mode === 'close'" class="w-1/2 pr-4">
                        <InputLabel :value="$t('Notes / Remarks')" />
                        <textarea v-model="notes" class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" rows="2"></textarea>
                    </div>
                    <div class="text-right flex-1">
                        <p class="text-sm text-gray-500 mb-1">{{ $t('Total Cash Amount') }}</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ totalAmount.toFixed(2) }} <span class="text-sm font-normal">{{ denominations[0]?.currency || 'MAD' }}</span></p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="$emit('close')">
                        {{ $t('Cancel') }}
                    </SecondaryButton>
                    <PrimaryButton @click="submit">
                        {{ mode === 'open' ? $t('Open Shift') : $t('Close Shift') }}
                    </PrimaryButton>
                </div>
            </template>

            <template v-else>
                <div class="text-center py-4">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-gray-800 dark:text-white mb-2">{{ $t('Shift Closed Successfully') }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-8">{{ $t('Here is the summary of your shift.') }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600 text-center">
                            <p class="text-xs text-gray-500 uppercase font-black mb-1">{{ $t('Expected Cash') }}</p>
                            <p class="text-xl font-bold text-gray-800 dark:text-white">{{ parseFloat(summaryData.shift.expected_cash).toFixed(2) }}</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600 text-center">
                            <p class="text-xs text-gray-500 uppercase font-black mb-1">{{ $t('Actual Cash') }}</p>
                            <p class="text-xl font-bold text-gray-800 dark:text-white">{{ parseFloat(summaryData.shift.end_cash).toFixed(2) }}</p>
                        </div>
                        <div class="p-4 rounded-2xl text-center border-2 border-dashed" :class="summaryData.discrepancy == 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-600' : 'bg-red-50 border-red-200 text-red-600'">
                            <p class="text-xs uppercase font-black mb-1">{{ $t('Discrepancy') }}</p>
                            <p class="text-xl font-black">{{ parseFloat(summaryData.discrepancy).toFixed(2) }}</p>
                        </div>
                    </div>

                    <PrimaryButton @click="$emit('close')" class="w-full justify-center py-4">
                        {{ $t('Close & Logout') }}
                    </PrimaryButton>
                </div>
            </template>
        </div>
    </Modal>
</template>
