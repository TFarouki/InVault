<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { trans } from 'laravel-vue-i18n';
import axios from 'axios';
import ShiftModal from '@/Components/Pos/ShiftModal.vue';
import CashTransactionModal from '@/Components/Pos/CashTransactionModal.vue';

const props = defineProps({
    categories: Array,
    allProducts: Array,
    heldSalesCount: Number,
});

const page = usePage();

const cart = ref([]);
const searchQuery = ref('');
const selectedCategoryId = ref('all');
const showCheckoutModal = ref(false);
const showHistoryModal = ref(false);
const historySales = ref([]);
const historyTab = ref('completed'); // 'completed' or 'held'
const localHeldSalesCount = ref(props.heldSalesCount);
const editingSale = ref(null);

// Shift Management State
const currentShift = ref(null);
const showShiftModal = ref(false);
const shiftModalMode = ref('open'); // 'open' or 'close'
const denominations = ref([]);
const showCashTransactionModal = ref(false);

const checkShiftStatus = async () => {
    try {
        const response = await axios.get(route('shifts.status'));
        currentShift.value = response.data.shift;
        denominations.value = response.data.denominations || [];
        
        if (!response.data.has_open_shift) {
            openShiftModal();
        }
    } catch (error) {
        console.error('Error checking shift status:', error);
    }
};

const openShiftModal = () => {
    shiftModalMode.value = 'open';
    showShiftModal.value = true;
};

const openCloseShiftModal = () => {
    shiftModalMode.value = 'close';
    showShiftModal.value = true;
};

const handleShiftSubmitted = (shiftData) => {
    if (shiftModalMode.value === 'open') {
        currentShift.value = shiftData;
    } else {
        currentShift.value = null;
        // Optionally redirect or show summary
        router.visit(route('dashboard'));
    }
};

const filteredProducts = computed(() => {
    let products = selectedCategoryId.value === 'all' 
        ? props.allProducts 
        : props.categories.find(c => c.id === selectedCategoryId.value)?.products || [];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        products = products.filter(p => 
            p.name.toLowerCase().includes(query) || 
            p.code.toLowerCase().includes(query)
        );
    }
    return products;
});

const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price_ttc),
            quantity: 1,
            image: product.image,
        });
    }
};

const updateQuantity = (item, change) => {
    item.quantity += change;
    if (item.quantity <= 0) {
        removeFromCart(item);
    }
};

const removeFromCart = (item) => {
    const index = cart.value.indexOf(item);
    if (index > -1) {
        cart.value.splice(index, 1);
    }
};

const clearCart = () => {
    if (confirm(trans('Are you sure you want to clear the cart?'))) {
        cart.value = [];
    }
};

const form = useForm({
    items: [],
    total_amount: 0,
    payment_method: 'cash',
    status: 'completed',
    received_amount: 0,
});

const processSale = () => {
    if (!currentShift.value) {
        alert(trans('You must open a shift before processing sales.'));
        openShiftModal();
        return;
    }
    if (cart.value.length === 0) return;

    form.items = cart.value;
    form.total_amount = cartTotal.value;
    form.status = 'completed';

    form.post(route('pos.store'), {
        onSuccess: () => {
            if (editingSale.value) {
                // If we were editing, we need to void the old one now
                router.delete(route('pos.destroy', editingSale.value.id), {
                    preserveScroll: true
                });
            }
            cart.value = [];
            showCheckoutModal.value = false;
            editingSale.value = null;
        },
    });
};

const holdSale = () => {
    if (cart.value.length === 0) return;

    form.items = cart.value;
    form.total_amount = cartTotal.value;
    form.status = 'held';
    form.payment_method = 'cash'; // Default for hold

    form.post(route('pos.store'), {
        onSuccess: () => {
            cart.value = [];
            localHeldSalesCount.value++;
            // Notification handled by flash message
        },
    });
};

const fetchHistory = async () => {
    try {
        const response = await axios.get(route('pos.history'));
        historySales.value = response.data;
        showHistoryModal.value = true;
    } catch (error) {
        console.error('Error fetching history:', error);
    }
};

const loadSaleToCart = (sale, mode = 'edit') => {
    // Close modal immediately
    showHistoryModal.value = false;

    // Load items to cart
    cart.value = sale.items.map(item => ({
        id: item.product.id,
        name: item.product.name,
        price: parseFloat(item.price_unit), // Corrected from unit_price to price_unit
        quantity: item.quantity,
        image: item.product.image
    }));
    
    if (mode === 'refund' || sale.status === 'held') {
        editingSale.value = null;
        // Automatically void/delete immediately for Refund or Held
        router.delete(route('pos.destroy', sale.id), {
            onSuccess: () => {
                if (sale.status === 'held') {
                    localHeldSalesCount.value--;
                }
            }
        });
    } else {
        // Mode is Edit
        editingSale.value = sale;
    }
};

const cartTotalWithTax = computed(() => {
    return cartTotal.value;
});

const cartTaxAmount = computed(() => {
    const taxRate = parseFloat(page.props.settings.tax_percentage || 20) / 100;
    return cartTotal.value - (cartTotal.value / (1 + taxRate));
});

const cartSubtotalHT = computed(() => {
    return cartTotal.value - cartTaxAmount.value;
});

const editDifference = computed(() => {
    if (!editingSale.value) return 0;
    return cartTotal.value - parseFloat(editingSale.value.total_ttc);
});

// Smart Change Logic
const suggestedAmounts = computed(() => {
    const total = editingSale.value ? Math.abs(editDifference.value) : cartTotalWithTax.value;
    // Just simple suggestions for now: Exact, Next 10, Next 50, Next 100
    const amounts = [total];
    if (Math.ceil(total / 10) * 10 > total) amounts.push(Math.ceil(total / 10) * 10);
    if (Math.ceil(total / 50) * 50 > total && !amounts.includes(Math.ceil(total / 50) * 50)) amounts.push(Math.ceil(total / 50) * 50);
    if (Math.ceil(total / 100) * 100 > total && !amounts.includes(Math.ceil(total / 100) * 100)) amounts.push(Math.ceil(total / 100) * 100);
    if (Math.ceil(total / 200) * 200 > total && !amounts.includes(Math.ceil(total / 200) * 200)) amounts.push(Math.ceil(total / 200) * 200);
    
    return amounts.filter(a => a > 0).sort((a,b) => a-b);
});


// Barcode scanner listener (simple implementation)
onMounted(() => {
    checkShiftStatus();

    let barcodeBuffer = '';
    let lastKeyTime = Date.now();

    window.addEventListener('keydown', (e) => {
        const currentTime = Date.now();
        if (currentTime - lastKeyTime > 100) {
            barcodeBuffer = '';
        }
        lastKeyTime = currentTime;

        if (e.key === 'Enter') {
            if (barcodeBuffer.length > 2) {
                const product = props.allProducts.find(p => p.code === barcodeBuffer);
                if (product) {
                    addToCart(product);
                    barcodeBuffer = '';
                }
            }
        } else if (e.key.length === 1) {
            barcodeBuffer += e.key;
        }
    });
});
</script>

<template>
    <Head title="POS Terminal" />

    <div class="h-screen flex flex-col md:flex-row overflow-hidden bg-gray-100 dark:bg-gray-900">
        <!-- Left Side: Products -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 shrink-0 z-10">
                <div class="flex items-center gap-4">
                    <Link :href="route('dashboard')" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest leading-none">{{ $page.props.settings.app_name }}</span>
                        <h1 class="text-xl font-bold text-gray-800 dark:text-white">{{ $t('POS Terminal') }}</h1>
                    </div>
                    
                    <button @click="fetchHistory" class="ml-4 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $t('History') }}
                    </button>
                </div>

                <div class="flex-1 max-w-md mx-6">
                    <div class="relative">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            :placeholder="$t('search_product') + ' (Code/Name)...'" 
                            class="w-full pl-10 pr-4 py-2 rounded-xl border-none bg-gray-100 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500/50 text-gray-800 dark:text-white"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-3 mr-4">
                        <button 
                            v-if="currentShift"
                            @click="showCashTransactionModal = true"
                            class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 rounded-lg text-xs font-bold transition-colors"
                        >
                            {{ $t('Cash In/Out') }}
                        </button>
                        <button 
                            v-if="currentShift"
                            @click="openCloseShiftModal"
                            class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-bold transition-colors"
                        >
                            {{ $t('Close Shift') }}
                        </button>
                    </div>
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-gray-800 dark:text-white">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $page.props.auth.user.role?.name }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <!-- Categories -->
            <div class="h-14 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center px-4 gap-2 overflow-x-auto shrink-0">
                <button 
                    @click="selectedCategoryId = 'all'"
                    :class="[
                        'px-4 py-1.5 rounded-full text-sm font-bold whitespace-nowrap transition-colors',
                        selectedCategoryId === 'all' 
                            ? 'bg-blue-600 text-white' 
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                >
                    {{ $t('all') }}
                </button>
                <button 
                    v-for="category in categories" 
                    :key="category.id"
                    @click="selectedCategoryId = category.id"
                    :class="[
                        'px-4 py-1.5 rounded-full text-sm font-bold whitespace-nowrap transition-colors',
                        selectedCategoryId === category.id 
                            ? 'bg-blue-600 text-white' 
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                >
                    {{ category.name }}
                </button>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-4 content-start">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <button 
                        v-for="product in filteredProducts" 
                        :key="product.id"
                        @click="addToCart(product)"
                        class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-800 transition-all flex flex-col items-center text-center h-full group"
                    >
                        <div class="w-24 h-24 mb-3 bg-gray-50 dark:bg-gray-700 rounded-xl flex items-center justify-center text-gray-300 overflow-hidden">
                            <!-- Placeholder Icon or Image -->
                            <img v-if="product.image" :src="'/storage/'+product.image" class="w-full h-full object-cover">
                            <svg v-else class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800 dark:text-white line-clamp-2 text-sm mb-1 group-hover:text-blue-600 transition-colors">
                            {{ product.name }}
                        </h3>
                        <p class="mt-auto text-blue-600 dark:text-blue-400 font-black text-lg">
                            {{ product.price_ttc }} <span class="text-xs font-normal text-gray-500">{{ $page.props.settings.currency }}</span>
                        </p>
                    </button>
                </div>
                
                <div v-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="font-medium">{{ $t('No products found') }}</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Cart -->
        <div class="w-full md:w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col shadow-2xl z-20">
            <!-- Cart Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
                <h2 
                    @click="fetchHistory(); historyTab = 'held'"
                    class="text-lg font-bold flex items-center gap-2 transition-colors cursor-pointer hover:text-blue-600 text-gray-800 dark:text-white"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ $t('Cart') }} 
                    <span class="bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full">{{ cart.length }}</span>
                    
                    <span v-if="localHeldSalesCount > 0" class="ml-2 bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ localHeldSalesCount }}
                    </span>
                </h2>
                <button @click="clearCart" class="text-red-500 hover:text-red-700 text-sm font-bold flex items-center gap-1 px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ $t('clear') }}
                </button>
            </div>

            <!-- Editing Indicator -->
            <div v-if="editingSale" class="px-4 py-2 bg-blue-600 text-white text-xs flex justify-between items-center shrink-0">
                <span class="font-bold flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ $t('Editing Sale') }}: {{ editingSale.reference }}
                </span>
                <button @click="editingSale = null; cart = []" class="hover:bg-blue-700 p-1 rounded">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <p class="font-medium">{{ $t('Cart is empty') }}</p>
                    <p class="text-xs mt-1">{{ $t('Scan barcode or select products') }}</p>
                </div>

                <div v-for="item in cart" :key="item.id" class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 flex gap-3 group">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 dark:text-white text-sm line-clamp-1">{{ item.name }}</h4>
                        <p class="text-blue-600 dark:text-blue-400 font-bold text-xs mt-1">
                            {{ item.price }} {{ $page.props.settings.currency }}
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-1">
                            <button @click="updateQuantity(item, -1)" class="w-6 h-6 flex items-center justify-center rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 font-bold">-</button>
                            <span class="w-6 text-center text-sm font-bold text-gray-800 dark:text-white">{{ item.quantity }}</span>
                            <button @click="updateQuantity(item, 1)" class="w-6 h-6 flex items-center justify-center rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 font-bold">+</button>
                        </div>
                        <button @click="removeFromCart(item)" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cart Footer -->
            <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ $t('Subtotal') }} (HT)</span>
                        <span>{{ cartSubtotalHT.toFixed(2) }} {{ $page.props.settings.currency }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ $t('tax') }} ({{ $page.props.settings.tax_percentage }}%)</span>
                        <span>{{ cartTaxAmount.toFixed(2) }} {{ $page.props.settings.currency }}</span>
                    </div>
                    <div class="flex justify-between text-xl font-black text-gray-800 dark:text-white pt-2 border-t border-gray-100 dark:border-gray-700">
                        <template v-if="editingSale">
                            <span>{{ editDifference >= 0 ? $t('Pay Difference') : $t('Refund Difference') }}</span>
                            <span :class="editDifference >= 0 ? 'text-blue-600' : 'text-orange-500'">{{ Math.abs(editDifference).toFixed(2) }} {{ $page.props.settings.currency }}</span>
                        </template>
                        <template v-else>
                            <span>{{ $t('Total') }}</span>
                            <span class="text-blue-600 dark:text-blue-400">{{ cartTotalWithTax.toFixed(2) }} {{ $page.props.settings.currency }}</span>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <button 
                        @click="holdSale"
                        :disabled="cart.length === 0"
                        class="col-span-1 py-4 rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400 font-bold text-sm hover:bg-orange-200 dark:hover:bg-orange-900/40 transition-all disabled:opacity-50 flex flex-col items-center justify-center gap-1"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $t('Hold') }}
                    </button>
                    <button 
                        @click="showCheckoutModal = true"
                        :disabled="cart.length === 0"
                        class="col-span-2 py-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-lg shadow-lg shadow-blue-200 dark:shadow-none hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        {{ $t('Checkout') }}
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Checkout Modal -->
        <div v-if="showCheckoutModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('Complete Sale') }}</h3>
                    <button @click="showCheckoutModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-8">
                    <div class="text-center mb-8">
                        <template v-if="editingSale">
                            <div class="flex justify-between text-sm mb-2 text-gray-500">
                                <span>{{ $t('Original Total') }}</span>
                                <span>{{ parseFloat(editingSale.total_ttc).toFixed(2) }} {{ $page.props.settings.currency }}</span>
                            </div>
                            <div class="flex justify-between text-sm mb-4 text-gray-500">
                                <span>{{ $t('New Total') }}</span>
                                <span>{{ cartTotalWithTax.toFixed(2) }} {{ $page.props.settings.currency }}</span>
                            </div>
                            <div class="pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">
                                    {{ editDifference > 0 ? $t('Amount to Pay') : $t('Amount to Refund') }}
                                </p>
                                <p :class="[
                                    'text-4xl font-black',
                                    editDifference > 0 ? 'text-blue-600' : 'text-orange-500'
                                ]">
                                    {{ Math.abs(editDifference).toFixed(2) }} 
                                    <span class="text-lg text-gray-400">{{ $page.props.settings.currency }}</span>
                                </p>
                            </div>
                        </template>
                        <template v-else>
                            <p class="text-gray-500 dark:text-gray-400 mb-1">{{ $t('Total Amount') }}</p>
                            <p class="text-4xl font-black text-gray-800 dark:text-white">
                                {{ cartTotalWithTax.toFixed(2) }} 
                                <span class="text-lg text-gray-400">{{ $page.props.settings.currency }}</span>
                            </p>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <button 
                            @click="form.payment_method = 'cash'"
                            :class="[
                                'p-4 rounded-xl border-2 flex flex-col items-center gap-2 transition-all',
                                form.payment_method === 'cash' 
                                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' 
                                    : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]"
                        >
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="font-bold">{{ $t('Cash') }}</span>
                        </button>
                        <button 
                            @click="form.payment_method = 'card'"
                            :class="[
                                'p-4 rounded-xl border-2 flex flex-col items-center gap-2 transition-all',
                                form.payment_method === 'card' 
                                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' 
                                    : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]"
                        >
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span class="font-bold">{{ $t('Card') }}</span>
                        </button>
                    </div>

                    <div v-if="form.payment_method === 'cash'" class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ $t('Received Amount') }}</label>
                        
                        <!-- Smart Change Suggestions -->
                        <div class="flex gap-2 mb-4 overflow-x-auto pb-1 scrollbar-hide">
                            <button 
                                v-for="amount in suggestedAmounts" 
                                :key="amount"
                                @click="form.received_amount = amount"
                                class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-lg text-sm font-bold whitespace-nowrap transition-colors"
                            >
                                {{ amount.toFixed(2) }}
                            </button>
                        </div>

                        <!-- Denomination Selector -->
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            <button 
                                v-for="denom in denominations.filter(d => d.is_active)" 
                                :key="denom.id"
                                @click="form.received_amount = (parseFloat(form.received_amount) || 0) + parseFloat(denom.value)"
                                class="flex flex-col items-center p-2 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:border-blue-500 transition-all hover:scale-105"
                            >
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ denom.value }}</span>
                                <span class="text-[8px] text-gray-500">{{ $page.props.settings.currency }}</span>
                            </button>
                            <button @click="form.received_amount = 0" class="flex items-center justify-center p-2 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 border border-red-100 dark:border-red-900/30 text-xs font-bold uppercase">{{ $t('Clear') }}</button>
                        </div>

                        <input 
                            v-model="form.received_amount"
                            type="number" 
                            class="w-full text-center text-2xl font-bold py-3 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-blue-500/20"
                            placeholder="0.00"
                        >
                        <p v-if="form.received_amount > (editingSale ? Math.abs(editDifference.value) : cartTotalWithTax.value)" class="text-center mt-2 text-green-600 font-bold">
                            {{ $t('Change') }}: {{ (form.received_amount - (editingSale ? Math.abs(editDifference.value) : cartTotalWithTax.value)).toFixed(2) }} {{ $page.props.settings.currency }}
                        </p>
                    </div>

                    <button 
                        @click="processSale" 
                        :disabled="form.processing"
                        class="w-full py-4 rounded-xl bg-blue-600 text-white font-bold text-lg hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 dark:shadow-none disabled:opacity-50"
                    >
                        {{ form.processing ? $t('Processing...') : $t('Confirm Payment') }}
                    </button>
                </div>
            </div>
        </div>
        
        <!-- History Modal -->
        <div v-if="showHistoryModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-4xl h-[80vh] shadow-2xl overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center shrink-0">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('Sales History') }}</h3>
                    <div class="flex gap-2">
                        <button 
                            @click="historyTab = 'completed'"
                            :class="[
                                'px-4 py-2 rounded-lg font-bold text-sm transition-colors',
                                historyTab === 'completed' 
                                    ? 'bg-blue-600 text-white' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
                            ]"
                        >
                            {{ $t('Completed') }}
                        </button>
                        <button 
                            @click="historyTab = 'held'"
                            :class="[
                                'px-4 py-2 rounded-lg font-bold text-sm transition-colors',
                                historyTab === 'held' 
                                    ? 'bg-orange-500 text-white' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
                            ]"
                        >
                            {{ $t('Held') }}
                        </button>
                    </div>
                    <button @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <div v-if="historySales.filter(s => s.status === historyTab).length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
                        <p>{{ $t('No sales found') }}</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div 
                            v-for="sale in historySales.filter(s => s.status === historyTab)" 
                            :key="sale.id" 
                            class="bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700 rounded-xl p-4 flex justify-between items-center"
                        >
                            <div>
                                <div class="flex items-center gap-3">
                                    <h4 class="font-bold text-gray-800 dark:text-white">{{ sale.reference }}</h4>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(sale.created_at).toLocaleString() }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                    {{ sale.items.length }} {{ $t('items') }} • {{ parseFloat(sale.total_ttc).toFixed(2) }} {{ $page.props.settings.currency }}
                                </p>
                            </div>
                            <div>
                                <div class="flex gap-2">
                                    <template v-if="historyTab === 'completed'">
                                        <button 
                                            @click="loadSaleToCart(sale, 'edit')"
                                            class="px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 font-bold rounded-lg text-xs shadow-sm transition-colors"
                                        >
                                            {{ $t('Edit') }}
                                        </button>
                                        <button 
                                            @click="loadSaleToCart(sale, 'refund')"
                                            class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 font-bold rounded-lg text-xs shadow-sm transition-colors"
                                        >
                                            {{ $t('Refund') }}
                                        </button>
                                    </template>
                                    <button 
                                        v-else
                                        @click="loadSaleToCart(sale)"
                                        class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 font-bold rounded-lg text-sm shadow-sm transition-colors"
                                    >
                                        {{ $t('Resume') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Shift Modal -->
        <ShiftModal 
            :show="showShiftModal" 
            :mode="shiftModalMode"
            :denominations="denominations"
            :shift="currentShift"
            @close="showShiftModal = false"
            @submitted="handleShiftSubmitted"
        />

        <!-- Cash Transaction Modal -->
        <CashTransactionModal
            :show="showCashTransactionModal"
            @close="showCashTransactionModal = false"
            @submitted="checkShiftStatus" 
        />
    </div>
</template>
