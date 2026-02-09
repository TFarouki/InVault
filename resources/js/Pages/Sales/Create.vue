<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array,
    customers: Array,
});

const cart = ref([]);

const addToCart = (product) => {
    const existing = cart.value.find(item => item.id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({ 
            id: product.id, 
            name: product.name, 
            price: parseFloat(product.price_ht), 
            tax_percentage: parseFloat(product.tax_percentage),
            quantity: 1 
        });
    }
};

const removeFromCart = (productId) => {
    cart.value = cart.value.filter(item => item.id !== productId);
};

const subtotal = computed(() => {
    return cart.value.reduce((acc, item) => acc + (item.price * item.quantity), 0);
});

const tax = computed(() => {
    return cart.value.reduce((acc, item) => {
        return acc + (item.price * (item.tax_percentage / 100) * item.quantity);
    }, 0);
});

const total = computed(() => subtotal.value + tax.value);

const form = useForm({
    customer_id: '',
    items: [],
    payment_method: 'cash',
    discount: 0,
});

const submitSale = () => {
    form.items = cart.value.map(item => ({
        id: item.id,
        quantity: item.quantity
    }));
    
    form.post(route('sales.store'), {
        onSuccess: () => {
            cart.value = [];
        }
    });
};

</script>

<template>
    <Head :title="$t('sales')" />

    <PosLayout :title="$t('Caisse')">
        <div class="flex flex-col lg:flex-row gap-8 h-[calc(100vh-160px)]">
            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto pr-4 custom-scrollbar">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                    <button 
                        v-for="product in products" 
                        :key="product.id"
                        @click="addToCart(product)"
                        class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-blue-500 hover:ring-2 hover:ring-blue-500/20 transition-all text-left flex flex-col items-center group"
                    >
                        <div class="w-full aspect-square bg-gray-50 dark:bg-gray-900 rounded-xl mb-4 flex items-center justify-center text-gray-300 group-hover:bg-blue-50 dark:group-hover:bg-blue-900/10 transition-colors">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                        <h4 class="font-bold text-gray-800 dark:text-white text-center text-sm mb-1 leading-tight">{{ product.name }}</h4>
                        <p class="text-blue-600 dark:text-blue-400 font-bold">{{ product.price_ttc }} {{ $page.props.settings.currency }}</p>
                    </button>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="w-full lg:w-96 flex flex-col bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ $t('Panier') }}</h3>
                </div>

                <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                    <select v-model="form.customer_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">{{ $t('Client de passage') }}</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                    </select>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 opacity-50">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        <p>{{ $t('Le panier est vide') }}</p>
                    </div>

                    <div v-for="item in cart" :key="item.id" class="flex items-center justify-between group">
                        <div class="flex-1 min-w-0 pr-4">
                            <h5 class="font-bold text-gray-800 dark:text-white truncate">{{ item.name }}</h5>
                            <p class="text-xs text-gray-500">{{ item.quantity }} x {{ item.price.toFixed(2) }} {{ $page.props.settings.currency }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="font-bold text-gray-800 dark:text-white whitespace-nowrap">{{ (item.price * item.quantity).toFixed(2) }}</span>
                            <button @click="removeFromCart(item.id)" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 dark:bg-gray-700/30 space-y-3">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>{{ $t('Sous-total') }}</span>
                        <span>{{ subtotal.toFixed(2) }} {{ $page.props.settings.currency }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>{{ $t('tax') }}</span>
                        <span>{{ tax.toFixed(2) }} {{ $page.props.settings.currency }}</span>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-600">
                        <select v-model="form.payment_method" class="w-full mb-3 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <option value="cash">{{ $t('Espèces') }}</option>
                            <option value="card">{{ $t('Carte Bancaire') }}</option>
                            <option value="transfer">{{ $t('Virement') }}</option>
                        </select>
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-800 dark:text-white">{{ $t('total') }}</span>
                            <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ total.toFixed(2) }} {{ $page.props.settings.currency }}</span>
                        </div>
                    </div>
                    
                    <button 
                        @click="submitSale"
                        :disabled="cart.length === 0 || form.processing"
                        class="w-full mt-4 py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-lg font-bold transition-all shadow-lg shadow-blue-200 dark:shadow-none"
                    >
                        {{ form.processing ? '...' : $t('Valider la Vente') }}
                    </button>
                </div>
            </div>
        </div>
    </PosLayout>
</template>
