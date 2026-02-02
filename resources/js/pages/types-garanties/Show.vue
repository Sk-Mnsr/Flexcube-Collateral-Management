<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Settings, Edit, ArrowLeft } from 'lucide-vue-next';

interface Props {
    typeGarantie: {
        id: number;
        code: string;
        libelle: string;
        type: string;
        description?: string;
        decote_pourcentage: number;
        ponderation_pourcentage: number;
        actif: boolean;
        created_at: string;
        updated_at: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Types de garanties', href: '/types-garanties' },
    { title: props.typeGarantie.code, href: '#' },
];
</script>

<template>
    <Head :title="`Type de garantie: ${props.typeGarantie.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold">{{ props.typeGarantie.code }}</h1>
                    <span
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium',
                            props.typeGarantie.actif
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        ]"
                    >
                        {{ props.typeGarantie.actif ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/types-garanties/${props.typeGarantie.id}/edit`">
                        <Button variant="outline">
                            <Edit class="h-4 w-4 mr-2" />
                            Modifier
                        </Button>
                    </Link>
                    <Link href="/types-garanties">
                        <Button variant="outline">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Retour
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Informations générales</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Code</div>
                            <div class="mt-1 font-mono text-lg font-bold text-gray-900">{{ props.typeGarantie.code }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Libellé</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">{{ props.typeGarantie.libelle }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Type</div>
                            <div class="mt-1 text-base text-gray-900">{{ props.typeGarantie.type }}</div>
                        </div>
                        <div v-if="props.typeGarantie.description">
                            <div class="text-sm font-medium text-gray-500">Description</div>
                            <div class="mt-1 text-base text-gray-900">{{ props.typeGarantie.description }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Paramètres financiers</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Décote</div>
                            <div class="mt-1 text-2xl font-bold text-orange-600">{{ props.typeGarantie.decote_pourcentage }}%</div>
                            <p class="mt-1 text-xs text-gray-500">Pourcentage de décote appliqué à la valeur</p>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Pondération</div>
                            <div class="mt-1 text-2xl font-bold text-green-600">{{ props.typeGarantie.ponderation_pourcentage }}%</div>
                            <p class="mt-1 text-xs text-gray-500">Pourcentage utilisé pour calculer la valeur réelle</p>
                        </div>
                        <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-900">
                            <div class="text-xs font-medium text-gray-500 mb-1">Exemple de calcul</div>
                            <div class="text-sm text-gray-700">
                                Pour une valeur de <span class="font-mono font-semibold">1 000 000 FCFA</span>:
                                <br />
                                Valeur réelle = 1 000 000 × {{ props.typeGarantie.ponderation_pourcentage }}% = 
                                <span class="font-mono font-semibold">
                                    {{ (1000000 * props.typeGarantie.ponderation_pourcentage / 100).toLocaleString('fr-FR') }} FCFA
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold">Dates</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-sm font-medium text-gray-500">Date de création</div>
                        <div class="mt-1 text-base text-gray-900">
                            {{ new Date(props.typeGarantie.created_at).toLocaleDateString('fr-FR', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Dernière modification</div>
                        <div class="mt-1 text-base text-gray-900">
                            {{ new Date(props.typeGarantie.updated_at).toLocaleDateString('fr-FR', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

