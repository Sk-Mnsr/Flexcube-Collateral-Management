<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { Users, Eye, Search } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { computed, ref } from 'vue';

interface Client {
    id: number;
    matricule: string;
    nom: string;
    prenom: string;
    telephone?: string;
    prets_count?: number;
    created_at: string;
}

interface Props {
    clients: {
        data: Client[];
        links: any[];
        meta?: any;
        total?: number;
        current_page?: number;
        per_page?: number;
        last_page?: number;
    };
    filters?: {
        search?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search || '');

const currentPage = computed(() => props.clients.current_page || props.clients.meta?.current_page || 1);
const totalItems = computed(() => props.clients.total || props.clients.meta?.total || 0);
const perPage = computed(() => props.clients.per_page || props.clients.meta?.per_page || 15);

const columns: Column[] = [
    { key: 'matricule', title: 'MATRICULE', sortable: true },
    { key: 'nom_complet', title: 'NOM ET PRÉNOM', sortable: true },
    { key: 'telephone', title: 'TÉLÉPHONE' },
    { key: 'prets_count', title: 'NOMBRE DE PRÊTS' },
    { key: 'created_at', title: 'DATE CRÉATION', sortable: true },
    { key: 'actions', title: 'ACTIONS' },
];

const tableData = computed(() => {
    return props.clients.data.map(client => ({
        id: client.id,
        matricule: client.matricule,
        nom_complet: `${client.prenom} ${client.nom}`,
        telephone: client.telephone || '-',
        prets_count: client.prets_count || 0,
        created_at: new Date(client.created_at).toLocaleDateString('fr-FR'),
        client: client,
    }));
});

const handlePageChange = (page: number) => {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page.toString());
    if (searchQuery.value) {
        urlParams.set('search', searchQuery.value);
    }
    router.visit(`/clients?${urlParams.toString()}`, { preserveScroll: true });
};

const applySearch = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    params.set('page', '1');
    router.visit(`/clients?${params.toString()}`, { preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '#' },
];
</script>

<template>
    <Head title="Clients" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-bold text-gray-900">Liste des clients</h1>
                    <Users class="h-5 w-5 text-gray-500" />
                </div>
            </div>

            <!-- Recherche -->
            <div class="flex gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex-1">
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher par matricule, nom, prénom, téléphone..."
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
                <template #item.matricule="{ item }">
                    <span class="font-mono font-medium text-gray-900">{{ item.matricule }}</span>
                </template>

                <template #item.nom_complet="{ item }">
                    <span class="font-medium text-gray-900">{{ item.nom_complet }}</span>
                </template>

                <template #item.telephone="{ item }">
                    <span class="text-gray-700">{{ item.telephone }}</span>
                </template>

                <template #item.prets_count="{ item }">
                    <span class="text-gray-700">{{ (item.prets_count || 0).toLocaleString('fr-FR') }}</span>
                </template>

                <template #item.created_at="{ item }">
                    <span class="text-gray-700">{{ item.created_at }}</span>
                </template>

                <template #item.actions="{ item }">
                    <div class="flex items-center gap-1">
                        <Link
                            :href="`/clients/${item.id}`"
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

