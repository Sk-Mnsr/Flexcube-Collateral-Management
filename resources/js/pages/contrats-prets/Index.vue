<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { FileText, Eye, Search, Plus } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { computed, ref } from 'vue';

interface ContratPret {
    id: number;
    numero_pret: string;
    montant_accorde: number;
    date_mise_en_place: string;
    date_maturite?: string;
    statut: string;
    matricule_client: string;
    nom_client?: string;
    nature_juridique?: string;
    created_at: string;
}

interface Props {
    contratsPrets: {
        data: ContratPret[];
        links: any[];
        meta?: any;
        total?: number;
        current_page?: number;
        per_page?: number;
        last_page?: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref('');

const currentPage = computed(() => props.contratsPrets.current_page || props.contratsPrets.meta?.current_page || 1);
const totalItems = computed(() => props.contratsPrets.total || props.contratsPrets.meta?.total || 0);
const perPage = computed(() => props.contratsPrets.per_page || props.contratsPrets.meta?.per_page || 15);

const getStatutBadge = (statut: string) => {
    const badges: Record<string, string> = {
        actif: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        annule: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        solde: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return badges[statut] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

const columns: Column[] = [
    { key: 'numero_pret', title: 'N° PRÊT', sortable: true },
    { key: 'client', title: 'CLIENT' },
    { key: 'montant_accorde', title: 'MONTANT ACCORDÉ' },
    { key: 'date_mise_en_place', title: 'DATE MISE EN PLACE' },
    { key: 'date_maturite', title: 'DATE MATURITÉ' },
    { key: 'statut', title: 'STATUT' },
    { key: 'actions', title: 'ACTIONS' },
];

const tableData = computed(() => {
    return props.contratsPrets.data.map(contrat => ({
        id: contrat.id,
        numero_pret: contrat.numero_pret,
        client: contrat.nom_client || contrat.matricule_client,
        montant_accorde: contrat.montant_accorde?.toLocaleString('fr-FR', { style: 'currency', currency: 'XOF' }) || '0',
        date_mise_en_place: new Date(contrat.date_mise_en_place).toLocaleDateString('fr-FR'),
        date_maturite: contrat.date_maturite ? new Date(contrat.date_maturite).toLocaleDateString('fr-FR') : '-',
        statut: contrat.statut,
        contrat: contrat,
    }));
});

const handlePageChange = (page: number) => {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page.toString());
    if (searchQuery.value) {
        urlParams.set('search', searchQuery.value);
    }
    router.visit(`/contrats-prets?${urlParams.toString()}`, { preserveScroll: true });
};

const applySearch = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    params.set('page', '1');
    router.visit(`/contrats-prets?${params.toString()}`, { preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Contrats de prêts', href: '#' },
];
</script>

<template>
    <Head title="Contrats de prêts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-bold text-gray-900">Liste des contrats de prêts</h1>
                    <FileText class="h-5 w-5 text-gray-500" />
                </div>
            </div>

            <!-- Recherche -->
            <div class="flex gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex-1">
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher par numéro de prêt, matricule client..."
                        @keyup.enter="applySearch"
                    />
                </div>
                <Button @click="applySearch" variant="outline">
                    <Search class="h-4 w-4 mr-2" />
                    Rechercher
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
                <template #item.numero_pret="{ item }">
                    <span class="font-mono text-sm font-medium text-gray-900">{{ item.numero_pret }}</span>
                </template>

                <template #item.client="{ item }">
                    <div>
                        <div class="font-medium text-gray-900">{{ item.client }}</div>
                        <div class="text-xs text-gray-500">{{ item.contrat.matricule_client }}</div>
                    </div>
                </template>

                <template #item.montant_accorde="{ item }">
                    <span class="font-medium text-gray-900">{{ item.montant_accorde }}</span>
                </template>

                <template #item.date_mise_en_place="{ item }">
                    <span class="text-gray-700">{{ item.date_mise_en_place }}</span>
                </template>

                <template #item.date_maturite="{ item }">
                    <span class="text-gray-700">{{ item.date_maturite }}</span>
                </template>

                <template #item.statut="{ item }">
                    <span
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium',
                            getStatutBadge(item.statut),
                        ]"
                    >
                        {{ item.statut }}
                    </span>
                </template>

                <template #item.actions="{ item }">
                    <div class="flex items-center gap-1">
                        <Link
                            :href="`/contrats-prets/${item.id}`"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            title="Voir"
                        >
                            <Eye class="h-5 w-5" />
                        </Link>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>

