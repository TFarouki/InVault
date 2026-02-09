<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    sale: Object,
    settings: Object,
});

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head :title="'Vente #' + sale.reference" />

    <PosLayout :title="'Détails de la Vente'">
        <div class="max-w-2xl mx-auto py-8">
            <div class="mb-6 flex justify-between items-center no-print">
                <Link :href="route('sales.index')" class="text-blue-600 hover:underline flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Retour aux ventes
                </Link>
                <button @click="printReceipt" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition-colors shadow-lg">
                    Imprimer le reçu
                </button>
            </div>

            <!-- Receipt Content -->
            <div id="receipt" class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 receipt-container">
                <div class="text-center mb-8">
                    <div v-if="$page.props.settings.app_logo" class="w-16 h-16 mx-auto mb-4">
                        <img :src="$page.props.settings.app_logo" class="w-full h-full object-contain" />
                    </div>
                    <h2 class="text-2xl font-black text-gray-800 uppercase">{{ settings.app_name || 'InVault' }}</h2>
                    <p class="text-gray-500 text-sm mt-1 whitespace-pre-line">{{ settings.address }}</p>
                    <p class="text-gray-500 text-sm">{{ settings.phone }}</p>
                </div>

                <div class="flex justify-between items-start mb-8 pb-6 border-b border-gray-100">
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Détails</p>
                        <p class="text-sm font-bold text-gray-800">Réf: {{ sale.reference }}</p>
                        <p class="text-sm text-gray-500">{{ new Date(sale.created_at).toLocaleString() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Client</p>
                        <p class="text-sm font-bold text-gray-800">{{ sale.customer?.name || 'Client de passage' }}</p>
                        <p class="text-sm text-gray-500">{{ sale.customer?.phone || '' }}</p>
                    </div>
                </div>

                <table class="w-full mb-8">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 uppercase font-bold border-b border-gray-100">
                            <th class="py-2">Item</th>
                            <th class="py-2 text-center">Qté</th>
                            <th class="py-2 text-right">Unit</th>
                            <th class="py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="item in sale.items" :key="item.id" class="text-sm">
                            <td class="py-4 font-medium text-gray-800">{{ item.product.name }}</td>
                            <td class="py-4 text-center text-gray-500">{{ item.quantity }}</td>
                            <td class="py-4 text-right text-gray-500">{{ item.price_unit.toFixed(2) }}</td>
                            <td class="py-4 text-right font-bold text-gray-800">{{ item.total.toFixed(2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="text-sm">
                            <td colspan="3" class="py-4 text-right text-gray-400 font-medium">Sous-total</td>
                            <td class="py-4 text-right font-bold text-gray-800">{{ sale.total_ht.toFixed(2) }}</td>
                        </tr>
                        <tr class="text-sm">
                            <td colspan="3" class="py-2 text-right text-gray-400 font-medium">Taxe (TVA)</td>
                            <td class="py-2 text-right font-bold text-gray-800">{{ sale.tax_amount.toFixed(2) }}</td>
                        </tr>
                        <tr v-if="sale.discount > 0" class="text-sm">
                            <td colspan="3" class="py-2 text-right text-gray-400 font-medium">Remise</td>
                            <td class="py-2 text-right font-bold text-red-500">-{{ sale.discount.toFixed(2) }}</td>
                        </tr>
                        <tr class="text-lg">
                            <td colspan="3" class="pt-6 text-right font-black text-gray-800 uppercase">Total</td>
                            <td class="pt-6 text-right font-black text-blue-600">{{ sale.total_ttc.toFixed(2) }} {{ settings.currency || 'MAD' }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="text-center mt-12 pt-8 border-t border-dashed border-gray-200">
                    <p class="text-sm font-bold text-gray-800">Merci de votre visite !</p>
                    <p class="text-xs text-gray-400 mt-1">Généré par InVault</p>
                </div>
            </div>
        </div>
    </PosLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    body {
        background-color: white !important;
    }
    .receipt-container {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    #receipt {
        width: 100% !important;
        max-width: none !important;
    }
}
</style>
