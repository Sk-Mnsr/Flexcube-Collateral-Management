<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { User, Edit, ArrowLeft } from 'lucide-vue-next';

interface Garantie {
    id: number;
    reference_unique: string;
    nom: string;
    statut: string;
}

interface Props {
    garant: {
        id: number;
        civilite: string;
        nom: string;
        prenom: string;
        adresse?: string;
        date_naissance: string;
        lieu_naissance?: string;
        nationalite?: string;
        activite?: string;
        adresse_activite?: string;
        type_piece_identite: string;
        numero_piece_identite: string;
        fichier_piece_identite?: string;
        date_delivrance_piece_identite?: string;
        date_expiration_piece_identite?: string;
        telephone?: string;
        garanties?: Garantie[];
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Garants', href: '/garants' },
    { title: `${props.garant.prenom} ${props.garant.nom}`, href: '#' },
];
</script>

<template>
    <Head :title="`Garant: ${props.garant.prenom} ${props.garant.nom}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ props.garant.civilite }} {{ props.garant.prenom }} {{ props.garant.nom }}</h1>
                <div class="flex gap-2">
                    <Link :href="`/garants/${props.garant.id}/edit`">
                        <Button variant="outline">
                            <Edit class="h-4 w-4 mr-2" />
                            Modifier
                        </Button>
                    </Link>
                    <Link href="/garants">
                        <Button variant="outline">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Retour
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Informations personnelles</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Civilité</div>
                            <div class="mt-1 text-base">{{ props.garant.civilite }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Nom</div>
                            <div class="mt-1 text-base">{{ props.garant.nom }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Prénom</div>
                            <div class="mt-1 text-base">{{ props.garant.prenom }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Date de naissance</div>
                            <div class="mt-1 text-base">{{ new Date(props.garant.date_naissance).toLocaleDateString('fr-FR') }}</div>
                        </div>
                        <div v-if="props.garant.lieu_naissance">
                            <div class="text-sm font-medium text-gray-500">Lieu de naissance</div>
                            <div class="mt-1 text-base">{{ props.garant.lieu_naissance }}</div>
                        </div>
                        <div v-if="props.garant.nationalite">
                            <div class="text-sm font-medium text-gray-500">Nationalité</div>
                            <div class="mt-1 text-base">{{ props.garant.nationalite }}</div>
                        </div>
                        <div v-if="props.garant.telephone">
                            <div class="text-sm font-medium text-gray-500">Téléphone</div>
                            <div class="mt-1 text-base">{{ props.garant.telephone }}</div>
                        </div>
                        <div v-if="props.garant.activite">
                            <div class="text-sm font-medium text-gray-500">Activité</div>
                            <div class="mt-1 text-base">{{ props.garant.activite }}</div>
                        </div>
                        <div v-if="props.garant.adresse_activite">
                            <div class="text-sm font-medium text-gray-500">Adresse de l'activité</div>
                            <div class="mt-1 text-base whitespace-pre-line">{{ props.garant.adresse_activite }}</div>
                        </div>
                        <div v-if="props.garant.adresse">
                            <div class="text-sm font-medium text-gray-500">Adresse</div>
                            <div class="mt-1 text-base whitespace-pre-line">{{ props.garant.adresse }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Pièce d'identité</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Type</div>
                            <div class="mt-1 text-base">{{ props.garant.type_piece_identite }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Numéro</div>
                            <div class="mt-1 font-mono text-base">{{ props.garant.numero_piece_identite }}</div>
                        </div>
                        <div v-if="props.garant.date_delivrance_piece_identite">
                            <div class="text-sm font-medium text-gray-500">Date de délivrance</div>
                            <div class="mt-1 text-base">{{ new Date(props.garant.date_delivrance_piece_identite).toLocaleDateString('fr-FR') }}</div>
                        </div>
                        <div v-if="props.garant.date_expiration_piece_identite">
                            <div class="text-sm font-medium text-gray-500">Date d'expiration</div>
                            <div class="mt-1 text-base">{{ new Date(props.garant.date_expiration_piece_identite).toLocaleDateString('fr-FR') }}</div>
                        </div>
                        <div v-if="props.garant.fichier_piece_identite">
                            <div class="text-sm font-medium text-gray-500">Fichier</div>
                            <div class="mt-1">
                                <a :href="`/storage/${props.garant.fichier_piece_identite}`" target="_blank" class="text-blue-600 hover:underline">
                                    Voir le fichier
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Garanties liées -->
            <div v-if="props.garant.garanties && props.garant.garanties.length > 0" class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold">Garanties liées ({{ props.garant.garanties.length }})</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="garantie in props.garant.garanties"
                        :key="garantie.id"
                        :href="`/garanties/${garantie.id}`"
                        class="rounded-md border border-gray-200 p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="font-medium text-gray-900">{{ garantie.reference_unique }}</div>
                        <div class="text-sm text-gray-600">{{ garantie.nom }}</div>
                        <div class="mt-2">
                            <span
                                :class="[
                                    'rounded-full px-2 py-1 text-xs font-medium',
                                    garantie.statut === 'normal' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                                ]"
                            >
                                {{ garantie.statut }}
                            </span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

