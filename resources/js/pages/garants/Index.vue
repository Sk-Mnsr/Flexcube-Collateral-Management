<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { User, Eye, Pencil, Trash2, Plus, Search } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { computed, ref } from 'vue';

interface Garant {
    id: number;
    civilite: string;
    nom: string;
    prenom: string;
    date_naissance: string;
    telephone?: string;
    numero_piece_identite: string;
    created_at: string;
}

interface Props {
    garants: {
        data: Garant[];
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

const currentPage = computed(() => props.garants.current_page || props.garants.meta?.current_page || 1);
const totalItems = computed(() => props.garants.total || props.garants.meta?.total || 0);
const perPage = computed(() => props.garants.per_page || props.garants.meta?.per_page || 15);

const columns: Column[] = [
    { key: 'civilite', title: 'CIVILITÉ' },
    { key: 'nom_complet', title: 'NOM ET PRÉNOM', sortable: true },
    { key: 'date_naissance', title: 'DATE DE NAISSANCE' },
    { key: 'telephone', title: 'TÉLÉPHONE' },
    { key: 'numero_piece_identite', title: 'PIÈCE D\'IDENTITÉ' },
    { key: 'created_at', title: 'DATE CRÉATION', sortable: true },
    { key: 'actions', title: 'ACTIONS' },
];

const tableData = computed(() => {
    return props.garants.data.map(garant => ({
        id: garant.id,
        civilite: garant.civilite,
        nom_complet: `${garant.prenom} ${garant.nom}`,
        date_naissance: new Date(garant.date_naissance).toLocaleDateString('fr-FR'),
        telephone: garant.telephone || '-',
        numero_piece_identite: garant.numero_piece_identite,
        created_at: new Date(garant.created_at).toLocaleDateString('fr-FR'),
        garant: garant,
    }));
});

const handlePageChange = (page: number) => {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page.toString());
    if (searchQuery.value) {
        urlParams.set('search', searchQuery.value);
    }
    router.visit(`/garants?${urlParams.toString()}`, { preserveScroll: true });
};

const applySearch = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    params.set('page', '1');
    router.visit(`/garants?${params.toString()}`, { preserveScroll: true });
};

const deleteGarant = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce garant ?')) {
        router.delete(`/garants/${id}`);
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Garants', href: '#' },
];
</script>

<template>
    <Head title="Garants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-bold text-gray-900">Liste des garants</h1>
                    <User class="h-5 w-5 text-gray-500" />
                </div>
                <Link href="/garants/create">
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        Nouveau garant
                    </Button>
                </Link>
            </div>

            <!-- Recherche -->
            <div class="flex gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex-1">
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher par nom, prénom, numéro de pièce..."
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
                <template #item.civilite="{ item }">
                    <span class="text-gray-700">{{ item.civilite }}</span>
                </template>

                <template #item.nom_complet="{ item }">
                    <span class="font-medium text-gray-900">{{ item.nom_complet }}</span>
                </template>

                <template #item.date_naissance="{ item }">
                    <span class="text-gray-700">{{ item.date_naissance }}</span>
                </template>

                <template #item.telephone="{ item }">
                    <span class="text-gray-700">{{ item.telephone }}</span>
                </template>

                <template #item.numero_piece_identite="{ item }">
                    <span class="font-mono text-sm text-gray-700">{{ item.numero_piece_identite }}</span>
                </template>

                <template #item.created_at="{ item }">
                    <span class="text-gray-700">{{ item.created_at }}</span>
                </template>

                <template #item.actions="{ item }">
                    <div class="flex items-center gap-1">
                        <Link
                            :href="`/garants/${item.id}`"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            title="Voir"
                        >
                            <Eye class="h-5 w-5" />
                        </Link>
                        <Link
                            :href="`/garants/${item.id}/edit`"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            title="Modifier"
                        >
                            <Pencil class="h-5 w-5" />
                        </Link>
                        <button
                            @click="deleteGarant(item.id)"
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

