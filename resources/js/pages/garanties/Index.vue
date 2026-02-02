<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { Shield, Eye, Pencil, Trash2, Filter, Plus } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { computed, ref } from 'vue';

interface Garantie {
    id: number;
    reference_unique: string;
    nom: string;
    statut: string;
    valeur: number;
    valeur_reelle: number;
    montant_utilise?: number;
    montant_restant?: number;
    pourcentage_utilisation?: number;
    disponible_pour_pret?: boolean;
    type_garantie?: {
        id: number;
        libelle: string;
    };
    garant?: {
        id: number;
        nom: string;
        prenom: string;
    };
    date_creation: string;
    created_at: string;
}

interface Props {
    garanties: {
        data: Garantie[];
        links: any[];
        meta?: any;
        total?: number;
        current_page?: number;
        per_page?: number;
        last_page?: number;
    };
    filter?: string;
    typesGaranties?: Array<{ id: number; libelle: string }>;
}

const props = defineProps<Props>();

const searchQuery = ref('');
const filterStatut = ref(props.filter || 'all');
const filterType = ref('');

const currentPage = computed(() => props.garanties.current_page || props.garanties.meta?.current_page || 1);
const totalItems = computed(() => props.garanties.total || props.garanties.meta?.total || 0);
const perPage = computed(() => props.garanties.per_page || props.garanties.meta?.per_page || 15);

const getStatutBadge = (statut: string) => {
    const badges: Record<string, string> = {
        normal: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        contentieux: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        realisation: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        mutation_tiers: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        mutation_cofina: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        vendu: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        main_leve: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        dation: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    };
    return badges[statut] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

const getStatutLabel = (statut: string) => {
    const labels: Record<string, string> = {
        normal: 'Normal',
        contentieux: 'Contentieux',
        realisation: 'Réalisation',
        mutation_tiers: 'Mutation à un tiers',
        mutation_cofina: 'Mutation au nom de Cofina',
        vendu: 'Vendu',
        main_leve: 'Main levé',
        dation: 'Dation',
    };
    return labels[statut] || statut;
};

const applyFilters = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (filterStatut.value && filterStatut.value !== 'all') params.set('statut', filterStatut.value);
    if (filterType.value) params.set('type_garantie_id', filterType.value);
    params.set('page', '1');
    router.visit(`/garanties?${params.toString()}`, { preserveScroll: true });
};

const columns: Column[] = [
    { key: 'reference_unique', title: 'RÉFÉRENCE', sortable: true },
    { key: 'nom', title: 'NOM', sortable: true },
    { key: 'type_garantie', title: 'TYPE' },
    { key: 'garant', title: 'PROPRIÉTAIRE' },
    { key: 'valeur_reelle', title: 'VALEUR RÉELLE' },
    { key: 'utilisation', title: 'UTILISATION' },
    { key: 'statut', title: 'STATUT' },
    { key: 'date_creation', title: 'DATE CRÉATION', sortable: true },
    { key: 'actions', title: 'ACTIONS' },
];

const tableData = computed(() => {
    return props.garanties.data.map(garantie => ({
        id: garantie.id,
        reference_unique: garantie.reference_unique,
        nom: garantie.nom,
        type_garantie: garantie.type_garantie?.libelle || 'N/A',
        garant: garantie.garant ? `${garantie.garant.prenom} ${garantie.garant.nom}` : 'N/A',
        valeur_reelle: garantie.valeur_reelle?.toLocaleString('fr-FR', { style: 'currency', currency: 'XOF' }) || '0',
        utilisation: garantie.pourcentage_utilisation !== undefined 
            ? `${garantie.pourcentage_utilisation.toFixed(1)}%` 
            : '0%',
        statut: garantie.statut,
        date_creation: new Date(garantie.date_creation || garantie.created_at).toLocaleDateString('fr-FR'),
        disponible: garantie.disponible_pour_pret,
        garantie: garantie,
    }));
});

const handlePageChange = (page: number) => {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page.toString());
    router.visit(`/garanties?${urlParams.toString()}`, { preserveScroll: true });
};

const deleteGarantie = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette garantie ?')) {
        router.delete(`/garanties/${id}`);
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Garanties', href: '#' },
];
</script>

<template>
    <Head title="Garanties" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-bold text-gray-900">Liste des garanties</h1>
                    <Shield class="h-5 w-5 text-gray-500" />
                </div>
                <Link href="/garanties/create">
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        Nouvelle garantie
                    </Button>
                </Link>
            </div>

            <!-- Filtres -->
            <div class="flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Recherche</label>
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Référence, nom, garant..."
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="min-w-[150px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Statut</label>
                    <select
                        v-model="filterStatut"
                        class="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                    >
                        <option value="all">Tous</option>
                        <option value="normal">Normal</option>
                        <option value="contentieux">Contentieux</option>
                        <option value="realisation">Réalisation</option>
                        <option value="dation">Dation</option>
                    </select>
                </div>
                <div class="min-w-[150px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                    <select
                        v-model="filterType"
                        class="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                    >
                        <option value="">Tous</option>
                        <option v-for="type in typesGaranties" :key="type.id" :value="type.id">
                            {{ type.libelle }}
                        </option>
                    </select>
                </div>
                <Button @click="applyFilters" variant="outline">
                    <Filter class="h-4 w-4 mr-2" />
                    Filtrer
                </Button>
            </div>

            <DataTable
                :headers="columns"
                :items="tableData"
                :current-page="currentPage"
                :items-per-page="perPage"
                :total-items="totalItems"
                @page-change="handlePageChange"
            >
                <template #item.reference_unique="{ item }">
                    <span class="font-mono text-sm text-gray-900">{{ item.reference_unique }}</span>
                </template>

                <template #item.nom="{ item }">
                    <span class="font-medium text-gray-900">{{ item.nom }}</span>
                </template>

                <template #item.type_garantie="{ item }">
                    <span class="text-gray-700">{{ item.type_garantie }}</span>
                </template>

                <template #item.garant="{ item }">
                    <span class="text-gray-700">{{ item.garant }}</span>
                </template>

                <template #item.valeur_reelle="{ item }">
                    <span class="font-medium text-gray-900">{{ item.valeur_reelle }}</span>
                </template>

                <template #item.utilisation="{ item }">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700">{{ item.utilisation }}</span>
                        <div class="h-2 w-20 rounded-full bg-gray-200">
                            <div 
                                class="h-2 rounded-full"
                                :class="parseFloat(item.utilisation) > 80 ? 'bg-red-500' : parseFloat(item.utilisation) > 50 ? 'bg-yellow-500' : 'bg-green-500'"
                                :style="{ width: `${Math.min(100, parseFloat(item.utilisation))}%` }"
                            ></div>
                        </div>
                    </div>
                </template>

                <template #item.statut="{ item }">
                    <span
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium',
                            getStatutBadge(item.statut),
                        ]"
                    >
                        {{ getStatutLabel(item.statut) }}
                    </span>
                </template>

                <template #item.date_creation="{ item }">
                    <span class="text-gray-700">{{ item.date_creation }}</span>
                </template>

                <template #item.actions="{ item }">
                    <div class="flex items-center gap-1">
                        <Link
                            :href="`/garanties/${item.id}`"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            title="Voir"
                        >
                            <Eye class="h-5 w-5" />
                        </Link>
                        <Link
                            :href="`/garanties/${item.id}/edit`"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            title="Modifier"
                        >
                            <Pencil class="h-5 w-5" />
                        </Link>
                        <button
                            v-if="item.disponible"
                            @click="deleteGarantie(item.id)"
                            class="inline-flex items-center justify-center rounded-md p-2 text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors"
                            title="Supprimer"
                        >
                            <Trash2 class="h-5 w-5" />
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>

