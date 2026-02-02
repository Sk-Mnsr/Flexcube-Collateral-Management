<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { FileText, ArrowLeft, Link as LinkIcon, Unlink } from 'lucide-vue-next';
import { ref } from 'vue';

interface Garantie {
    id: number;
    reference_unique: string;
    nom: string;
    valeur_reelle: number;
    montant_restant?: number;
    disponible_pour_pret?: boolean;
    pivot?: {
        pourcentage_utilisation: number;
        montant_utilise: number;
    };
}

interface Props {
    contratPret: {
        id: number;
        numero_pret: string;
        montant_accorde: number;
        date_mise_en_place: string;
        date_maturite?: string;
        statut: string;
        code_gestionnaire?: string;
        code_agence?: string;
        matricule_client: string;
        nom_client?: string;
        nature_juridique?: string;
        secteur_activite?: string;
        garanties?: Garantie[];
        created_at: string;
    };
    garantiesDisponibles?: Garantie[];
}

const props = defineProps<Props>();

const showLinkForm = ref(false);

const linkForm = useForm({
    garantie_id: '',
    pourcentage_utilisation: '',
});

const submitLink = () => {
    linkForm.post(`/contrats-prets/${props.contratPret.id}/lier-garantie`, {
        preserveScroll: true,
        onSuccess: () => {
            linkForm.reset();
            showLinkForm.value = false;
        },
    });
};

const unlinkGarantie = (garantieId: number) => {
    if (confirm('Êtes-vous sûr de vouloir délier cette garantie ?')) {
        router.delete(`/contrats-prets/${props.contratPret.id}/garanties/${garantieId}`, {
            preserveScroll: true,
        });
    }
};

const getStatutBadge = (statut: string) => {
    const badges: Record<string, string> = {
        actif: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        annule: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        solde: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return badges[statut] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Contrats de prêts', href: '/contrats-prets' },
    { title: props.contratPret.numero_pret, href: '#' },
];
</script>

<template>
    <Head :title="`Contrat de prêt: ${props.contratPret.numero_pret}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold">{{ props.contratPret.numero_pret }}</h1>
                    <span
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium',
                            getStatutBadge(props.contratPret.statut),
                        ]"
                    >
                        {{ props.contratPret.statut }}
                    </span>
                </div>
                <Link href="/contrats-prets">
                    <Button variant="outline">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Retour
                    </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Informations du contrat</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Numéro de prêt</div>
                            <div class="mt-1 font-mono text-lg font-bold text-gray-900">{{ props.contratPret.numero_pret }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Montant accordé</div>
                            <div class="mt-1 text-xl font-bold text-gray-900">
                                {{ props.contratPret.montant_accorde.toLocaleString('fr-FR') }} FCFA
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Date de mise en place</div>
                            <div class="mt-1 text-base text-gray-900">
                                {{ new Date(props.contratPret.date_mise_en_place).toLocaleDateString('fr-FR') }}
                            </div>
                        </div>
                        <div v-if="props.contratPret.date_maturite">
                            <div class="text-sm font-medium text-gray-500">Date de maturité</div>
                            <div class="mt-1 text-base text-gray-900">
                                {{ new Date(props.contratPret.date_maturite).toLocaleDateString('fr-FR') }}
                            </div>
                        </div>
                        <div v-if="props.contratPret.code_gestionnaire">
                            <div class="text-sm font-medium text-gray-500">Code gestionnaire</div>
                            <div class="mt-1 text-base text-gray-900">{{ props.contratPret.code_gestionnaire }}</div>
                        </div>
                        <div v-if="props.contratPret.code_agence">
                            <div class="text-sm font-medium text-gray-500">Code agence</div>
                            <div class="mt-1 text-base text-gray-900">{{ props.contratPret.code_agence }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Informations client</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Matricule client</div>
                            <div class="mt-1 font-mono text-lg font-bold text-gray-900">{{ props.contratPret.matricule_client }}</div>
                        </div>
                        <div v-if="props.contratPret.nom_client">
                            <div class="text-sm font-medium text-gray-500">Nom du client</div>
                            <div class="mt-1 text-base text-gray-900">{{ props.contratPret.nom_client }}</div>
                        </div>
                        <div v-if="props.contratPret.nature_juridique">
                            <div class="text-sm font-medium text-gray-500">Nature juridique</div>
                            <div class="mt-1 text-base text-gray-900">{{ props.contratPret.nature_juridique }}</div>
                        </div>
                        <div v-if="props.contratPret.secteur_activite">
                            <div class="text-sm font-medium text-gray-500">Secteur d'activité</div>
                            <div class="mt-1 text-base text-gray-900">{{ props.contratPret.secteur_activite }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Garanties liées -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Garanties liées ({{ props.contratPret.garanties?.length || 0 }})</h2>
                    <Button v-if="!showLinkForm" @click="showLinkForm = true" variant="outline" size="sm">
                        <LinkIcon class="h-4 w-4 mr-2" />
                        Lier une garantie
                    </Button>
                </div>

                <!-- Formulaire de liaison -->
                <div v-if="showLinkForm" class="mb-6 rounded-md border border-gray-200 bg-gray-50 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Lier une nouvelle garantie</h3>
                    <form @submit.prevent="submitLink" class="space-y-4">
                        <div>
                            <Label for="garantie_id">Garantie *</Label>
                            <select
                                id="garantie_id"
                                v-model="linkForm.garantie_id"
                                required
                                class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm"
                            >
                                <option value="">Sélectionner une garantie</option>
                                <option v-for="garantie in garantiesDisponibles" :key="garantie.id" :value="garantie.id">
                                    {{ garantie.reference_unique }} - {{ garantie.nom }}
                                    ({{ garantie.montant_restant?.toLocaleString('fr-FR') }} FCFA disponibles)
                                </option>
                            </select>
                            <InputError :message="linkForm.errors.garantie_id" />
                        </div>
                        <div>
                            <Label for="pourcentage_utilisation">Pourcentage d'utilisation (%) *</Label>
                            <Input
                                id="pourcentage_utilisation"
                                v-model.number="linkForm.pourcentage_utilisation"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                required
                                class="mt-1.5"
                            />
                            <InputError :message="linkForm.errors.pourcentage_utilisation" />
                        </div>
                        <div class="flex gap-2">
                            <Button type="submit" :disabled="linkForm.processing" size="sm">
                                Lier
                            </Button>
                            <Button type="button" variant="outline" @click="showLinkForm = false" size="sm">
                                Annuler
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Liste des garanties -->
                <div v-if="props.contratPret.garanties && props.contratPret.garanties.length > 0" class="space-y-3">
                    <div
                        v-for="garantie in props.contratPret.garanties"
                        :key="garantie.id"
                        class="flex items-center justify-between rounded-md border border-gray-200 p-4"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <Link :href="`/garanties/${garantie.id}`" class="font-medium text-blue-600 hover:underline">
                                    {{ garantie.reference_unique }}
                                </Link>
                                <span class="text-gray-700">-</span>
                                <span class="text-gray-900">{{ garantie.nom }}</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-600">
                                Pourcentage utilisé: <span class="font-medium">{{ garantie.pivot?.pourcentage_utilisation }}%</span>
                                | Montant utilisé: <span class="font-medium">{{ garantie.pivot?.montant_utilise?.toLocaleString('fr-FR') }} FCFA</span>
                            </div>
                        </div>
                        <Button
                            @click="unlinkGarantie(garantie.id)"
                            variant="outline"
                            size="sm"
                            class="text-red-600 hover:text-red-700"
                        >
                            <Unlink class="h-4 w-4 mr-2" />
                            Délier
                        </Button>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-8">
                    Aucune garantie liée à ce contrat
                </div>
            </div>
        </div>
    </AppLayout>
</template>

