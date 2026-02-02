<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { Settings, Eye, Pencil, Trash2, Plus, Search } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { computed, ref } from 'vue';

interface TypeGarantie {
    id: number;
    code: string;
    libelle: string;
    type: string;
    decote_pourcentage: number;
    ponderation_pourcentage: number;
    actif: boolean;
    created_at: string;
}

interface Props {
    typesGaranties: {
        data: TypeGarantie[];
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

const currentPage = computed(() => props.typesGaranties.current_page || props.typesGaranties.meta?.current_page || 1);
const totalItems = computed(() => props.typesGaranties.total || props.typesGaranties.meta?.total || 0);
const perPage = computed(() => props.typesGaranties.per_page || props.typesGaranties.meta?.per_page || 15);

const getStatusBadge = (actif: boolean) => {
    return actif 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
};

const columns: Column[] = [
    { key: 'code', title: 'CODE', sortable: true },
    { key: 'libelle', title: 'LIBELLÉ', sortable: true },
    { key: 'type', title: 'TYPE' },
    { key: 'decote', title: 'DÉCOTE %' },
    { key: 'ponderation', title: 'PONDÉRATION %' },
    { key: 'actif', title: 'STATUT' },
    { key: 'actions', title: 'ACTIONS' },
];

const tableData = computed(() => {
    return props.typesGaranties.data.map(type => ({
        id: type.id,
        code: type.code,
        libelle: type.libelle,
        type: type.type,
        decote: `${type.decote_pourcentage}%`,
        ponderation: `${type.ponderation_pourcentage}%`,
        actif: type.actif,
        typeGarantie: type,
    }));
});

const handlePageChange = (page: number) => {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page.toString());
    if (searchQuery.value) {
        urlParams.set('search', searchQuery.value);
    }
    router.visit(`/types-garanties?${urlParams.toString()}`, { preserveScroll: true });
};

const applySearch = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    params.set('page', '1');
    router.visit(`/types-garanties?${params.toString()}`, { preserveScroll: true });
};

const deleteTypeGarantie = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce type de garantie ?')) {
        router.delete(`/types-garanties/${id}`);
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Types de garanties', href: '#' },
];
</script>

<template>
    <Head title="Types de garanties" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-bold text-gray-900">Liste des types de garanties</h1>
                    <Settings class="h-5 w-5 text-gray-500" />
                </div>
                <Link href="/types-garanties/create">
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        Nouveau type
                    </Button>
                </Link>
            </div>

            <!-- Recherche -->
            <div class="flex gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex-1">
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher par code ou libellé..."
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
                <template #item.code="{ item }">
                    <span class="font-mono text-sm font-medium text-gray-900">{{ item.code }}</span>
                </template>

                <template #item.libelle="{ item }">
                    <span class="font-medium text-gray-900">{{ item.libelle }}</span>
                </template>

                <template #item.type="{ item }">
                    <span class="text-gray-700">{{ item.type }}</span>
                </template>

                <template #item.decote="{ item }">
                    <span class="text-orange-600 font-medium">{{ item.decote }}</span>
                </template>

                <template #item.ponderation="{ item }">
                    <span class="text-green-600 font-medium">{{ item.ponderation }}</span>
                </template>

                <template #item.actif="{ item }">
                    <span
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium',
                            getStatusBadge(item.actif),
                        ]"
                    >
                        {{ item.actif ? 'Actif' : 'Inactif' }}
                    </span>
                </template>

                <template #item.actions="{ item }">
                    <div class="flex items-center gap-1">
                        <Link
                            :href="`/types-garanties/${item.id}`"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            title="Voir"
                        >
                            <Eye class="h-5 w-5" />
                        </Link>
                        <Link
                            :href="`/types-garanties/${item.id}/edit`"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            title="Modifier"
                        >
                            <Pencil class="h-5 w-5" />
                        </Link>
                        <button
                            @click="deleteTypeGarantie(item.id)"
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

