<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';

interface Props {
    client: {
        id: number;
        matricule: string;
        nom: string;
        prenom: string;
        telephone?: string;
        created_at: string;
        prets?: Array<{
            id: number;
            numero_pret: string;
            montant_accorde: number;
            created_at: string;
        }>;
    };
    contratsPrets?: Array<{
        id: number;
        numero_pret: string;
        montant_accorde: number;
        statut: string;
        created_at: string;
    }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: props.client.matricule, href: '#' },
];
</script>

<template>
    <Head :title="`Client: ${props.client.matricule}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ props.client.prenom }} {{ props.client.nom }}</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ props.client.matricule }}</p>
                </div>
                <Link href="/clients">
                    <Button variant="outline">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Retour
                    </Button>
                </Link>
            </div>

            <!-- Informations principales -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Informations du client</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Matricule</div>
                            <div class="mt-1 text-base font-mono font-medium">{{ props.client.matricule }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Nom</div>
                            <div class="mt-1 text-base">{{ props.client.nom }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Prénom</div>
                            <div class="mt-1 text-base">{{ props.client.prenom }}</div>
                        </div>
                        <div v-if="props.client.telephone">
                            <div class="text-sm font-medium text-gray-500">Téléphone</div>
                            <div class="mt-1 text-base">{{ props.client.telephone }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Date de création</div>
                            <div class="mt-1 text-base">{{ new Date(props.client.created_at).toLocaleDateString('fr-FR') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Liste des contrats de prêts -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Contrats de prêts associés ({{ props.contratsPrets?.length || 0 }})</h2>
                    <div v-if="props.contratsPrets && props.contratsPrets.length > 0" class="space-y-3">
                        <div v-for="contrat in props.contratsPrets" :key="contrat.id" class="rounded-md border border-gray-200 p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ contrat.numero_pret }}</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ contrat.montant_accorde.toLocaleString('fr-FR') }} FCFA
                                    </div>
                                    <span class="mt-1 inline-block text-xs px-2 py-0.5 rounded" :class="contrat.statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                        {{ contrat.statut }}
                                    </span>
                                </div>
                                <Link :href="`/contrats-prets/${contrat.id}`" class="text-blue-600 hover:underline text-sm">
                                    Voir
                                </Link>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-sm">Aucun contrat de prêt associé</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

