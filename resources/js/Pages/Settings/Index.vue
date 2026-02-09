<script setup>
import PosLayout from '@/Layouts/PosLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    settings: {
        app_name: props.settings.app_name || 'InVault',
        currency: props.settings.currency || 'MAD',
        address: props.settings.address || '',
        phone: props.settings.phone || '',
        tax_percentage: props.settings.tax_percentage || '20',
    },
    language: usePage().props.locale || 'fr',
    app_logo: null,
});

const onFileChange = (e) => {
    form.app_logo = e.target.files[0];
};

const submit = () => {
    // We use a POST request to handle file uploads in Inertia
    form.post(route('settings.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="$t('settings')" />

    <PosLayout :title="$t('settings')">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-8 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $t('Business Settings') }}</h2>
                </div>
                
                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ $t('name') }}</label>
                            <input 
                                v-model="form.settings.app_name"
                                type="text"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-blue-500/20"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Devise (Currency)</label>
                            <input 
                                v-model="form.settings.currency"
                                type="text"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-blue-500/20"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ $t('language') }}</label>
                            <select 
                                v-model="form.language"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-blue-500/20"
                            >
                                <option value="fr">Français</option>
                                <option value="en">English</option>
                                <option value="ar">العربية</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ $t('phone') }}</label>
                            <input 
                                v-model="form.settings.phone"
                                type="text"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-blue-500/20"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ $t('tax') }} (%)</label>
                            <input 
                                v-model="form.settings.tax_percentage"
                                type="number"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-blue-500/20"
                            >
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Logo de l'entreprise</label>
                            <div class="flex items-center gap-6 p-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl">
                                <div v-if="$page.props.settings.app_logo" class="w-20 h-20 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 shrink-0">
                                    <img :src="$page.props.settings.app_logo" alt="Logo" class="w-full h-full object-contain" />
                                </div>
                                <div class="flex-1">
                                    <input 
                                        type="file" 
                                        @change="onFileChange"
                                        accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all"
                                    />
                                    <p class="mt-2 text-xs text-gray-400">JPG, PNG or SVG. Max 2MB.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ $t('Adresse') }}</label>
                        <textarea 
                            v-model="form.settings.address"
                            rows="3"
                            class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-blue-500/20"
                        ></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 dark:shadow-none"
                        >
                            {{ $t('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PosLayout>
</template>
