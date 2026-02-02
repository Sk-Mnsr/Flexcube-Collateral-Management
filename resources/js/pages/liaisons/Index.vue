<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import GarantieCombobox from '@/components/GarantieCombobox.vue';
import { Link2, Search, X, Plus, Shield, CreditCard, TrendingUp, TrendingDown, AlertCircle, CheckCircle2, Info } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Garantie {
    id: number;
    reference_unique: string;
    nom: string;
    valeur_reelle: number;
    statut: string;
    montant_utilise?: number;
    montant_restant?: number;
    type_garantie?: {
        libelle: string;
    };
    garant?: {
        nom: string;
        prenom: string;
    };
    client?: {
        id: number;
        matricule: string;
        nom: string;
        prenom: string;
        telephone?: string;
    } | null;
    contratsPret?: Array<{
        id: number;
        numero_pret: string;
        montant_accorde: number;
        statut: string;
        pivot?: {
            pourcentage_utilisation: number;
            montant_utilise: number;
        };
    }>;
}

interface ContratPret {
    id: number;
    numero_pret: string;
    montant_accorde: number;
    statut: string;
    matricule_client: string;
    nom_client?: string;
    garanties?: Array<{
        id: number;
    }>;
}

interface Props {
    garantie?: Garantie;
    garanties: Garantie[];
    contratsPretsDisponibles: ContratPret[];
    contratsPretsLies?: Array<{
        id: number;
        numero_pret: string;
        montant_accorde: number;
        statut: string;
        pivot?: {
            pourcentage_utilisation: number;
            montant_utilise: number;
        };
    }>;
    warning?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Liaison garantie-contrat de prêt', href: '#' },
];

const selectedGarantieId = ref(props.garantie?.id?.toString() || '');
const searchQuery = ref('');

const linkForm = useForm({
    contrat_pret_id: '',
    montant_utilise: '',
});

const filteredContratsPrets = computed(() => {
    if (!props.garantie) return [];
    
    let contrats = props.contratsPretsDisponibles;
    
    // Filtrer par recherche
    if (searchQuery.value) {
        contrats = contrats.filter(contrat => 
            contrat.numero_pret.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            contrat.matricule_client.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (contrat.nom_client && contrat.nom_client.toLowerCase().includes(searchQuery.value.toLowerCase()))
        );
    }

    // Exclure les contrats déjà liés à cette garantie
    if (props.garantie.contratsPret) {
        const idsLies = props.garantie.contratsPret.map(cp => cp.id);
        contrats = contrats.filter(contrat => !idsLies.includes(contrat.id));
    }

    return contrats;
});

const selectGarantie = (garantieId: number | null) => {
    selectedGarantieId.value = garantieId?.toString() || '';
    if (garantieId) {
        router.visit(`/liaisons?garantie_id=${garantieId}`, {
            preserveScroll: true,
        });
    }
};

const onContratPretSelected = () => {
    if (linkForm.contrat_pret_id && props.contratsPretsDisponibles && props.garantie) {
        const contratPret = props.contratsPretsDisponibles.find(cp => cp.id === parseInt(linkForm.contrat_pret_id.toString()));
        if (contratPret && contratPret.montant_accorde) {
            // Remplir automatiquement avec le montant accordé du contrat
            // Mais ne pas dépasser le montant restant disponible
            const montantRestant = props.garantie.montant_restant || 0;
            linkForm.montant_utilise = Math.min(contratPret.montant_accorde, montantRestant).toString();
        }
    }
};

const submitLink = () => {
    if (!props.garantie) return;
    
    linkForm.post(`/garanties/${props.garantie.id}/lier-contrat-pret`, {
        preserveScroll: true,
        onSuccess: () => {
            linkForm.reset();
        },
    });
};

const unlinkContratPret = (contratPretId: number) => {
    if (!props.garantie) return;
    
    if (confirm('Êtes-vous sûr de vouloir délier ce contrat de prêt ?')) {
        router.delete(`/garanties/${props.garantie.id}/delier-contrat-pret/${contratPretId}`, {
            preserveScroll: true,
        });
    }
};

const calculatedPourcentage = computed(() => {
    if (!linkForm.montant_utilise || !props.garantie) return 0;
    const montant = parseFloat(linkForm.montant_utilise.toString());
    if (isNaN(montant) || props.garantie.valeur_reelle === 0) return 0;
    return (montant / props.garantie.valeur_reelle) * 100;
});

const montantRestantApresLiaison = computed(() => {
    if (!props.garantie) return 0;
    const montantSaisi = parseFloat(linkForm.montant_utilise?.toString() || '0') || 0;
    return (props.garantie.montant_restant || 0) - montantSaisi;
});

// Calcul du taux de couverture (Stock Disponible / montant du prêt * 100)
const tauxCouverture = computed(() => {
    if (!linkForm.contrat_pret_id || !props.garantie || !props.contratsPretsDisponibles) return null;
    
    const contratPret = props.contratsPretsDisponibles.find(cp => cp.id === parseInt(linkForm.contrat_pret_id.toString()));
    if (!contratPret || !contratPret.montant_accorde) return null;
    
    const stockDisponible = props.garantie.montant_restant || 0;
    if (stockDisponible === 0) return null;
    
    return (stockDisponible / contratPret.montant_accorde) * 100;
});

// Couleur selon le taux de couverture
const tauxCouvertureColor = computed(() => {
    if (!tauxCouverture.value) return 'gray';
    if (tauxCouverture.value < 80) return 'red';
    if (tauxCouverture.value === 80) return 'orange';
    return 'green';
});

// Message d'alerte selon le taux de couverture
const tauxCouvertureMessage = computed(() => {
    if (!tauxCouverture.value) return null;
    
    if (tauxCouverture.value < 80) {
        return {
            type: 'error',
            message: `⚠️ Stock insuffisant : Le taux de couverture est de ${tauxCouverture.value.toFixed(2)}%, ce qui est inférieur au minimum requis de 80%. La garantie ne couvre pas suffisamment le prêt.`,
        };
    } else if (tauxCouverture.value === 80) {
        return {
            type: 'warning',
            message: `⚠️ Stock juste suffisant : Le taux de couverture est exactement de 80%. La garantie couvre le minimum requis.`,
        };
    } else {
        return {
            type: 'success',
            message: `✅ Stock suffisant : Le taux de couverture est de ${tauxCouverture.value.toFixed(2)}%, ce qui est supérieur au minimum requis de 80%. La garantie couvre suffisamment le prêt.`,
        };
    }
});

const getStatutBadge = (statut: string) => {
    const badges: Record<string, string> = {
        actif: 'bg-green-100 text-green-800',
        soldé: 'bg-gray-100 text-gray-800',
        annulé: 'bg-red-100 text-red-800',
    };
    return badges[statut] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Liaison garantie-contrat de prêt" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-bold text-gray-900">Liaison garantie-contrat de prêt</h1>
                    <Link2 class="h-5 w-5 text-gray-500" />
                </div>
            </div>

            <!-- Sélection de la garantie -->
            <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-6 shadow-sm dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
                <div class="flex items-center gap-2 mb-4">
                    <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                        <Search class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Sélectionner une garantie</h2>
                </div>
                <GarantieCombobox
                    :garanties="props.garanties"
                    :model-value="selectedGarantieId ? Number(selectedGarantieId) : null"
                    label="Garantie"
                    placeholder="Rechercher par référence, nom, garant ou type..."
                    @update:model-value="selectGarantie"
                />
            </div>

            <!-- Informations de la garantie sélectionnée -->
            <div v-if="props.garantie" class="space-y-6">
                <!-- Message d'avertissement si pas de client -->
                <div v-if="props.warning" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <div class="flex items-center gap-2">
                        <Shield class="h-5 w-5 text-yellow-600" />
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{ props.warning }}</p>
                    </div>
                </div>

                <!-- Résumé de la garantie -->
                <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-6 shadow-sm dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-start gap-4">
                            <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/30">
                                <Shield class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                        <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ props.garantie.reference_unique }}</h2>
                                <p class="mt-1 text-base text-gray-600 dark:text-gray-400">{{ props.garantie.nom }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ props.garantie.type_garantie?.libelle }}
                                    </span>
                                    <span :class="['rounded-full px-3 py-1 text-xs font-medium', getStatutBadge(props.garantie.statut)]">
                                        {{ props.garantie.statut }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <Link :href="`/garanties/${props.garantie.id}`">
                            <Button variant="outline" size="sm" class="shadow-sm">
                                Voir la garantie
                            </Button>
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg bg-white p-4 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="rounded-full bg-purple-100 p-1.5 dark:bg-purple-900/30">
                                    <CreditCard class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</div>
                            </div>
                            <div class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ props.garantie.type_garantie?.libelle }}</div>
                        </div>
                        
                        <div class="rounded-lg bg-white p-4 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="rounded-full bg-indigo-100 p-1.5 dark:bg-indigo-900/30">
                                    <Shield class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Propriétaire du bien (Garant)</div>
                            </div>
                            <div class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.garantie.garant?.prenom }} {{ props.garantie.garant?.nom }}
                            </div>
                        </div>
                        
                        <div v-if="props.garantie.client" class="rounded-lg bg-white p-4 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="rounded-full bg-green-100 p-1.5 dark:bg-green-900/30">
                                    <CreditCard class="h-4 w-4 text-green-600 dark:text-green-400" />
                                </div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Client</div>
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.garantie.client.matricule }}
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                {{ props.garantie.client.prenom }} {{ props.garantie.client.nom }}
                            </div>
                        </div>
                        
                        <div class="rounded-lg bg-white p-4 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="rounded-full bg-emerald-100 p-1.5 dark:bg-emerald-900/30">
                                    <TrendingUp class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Valeur Pondérée</div>
                            </div>
                            <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ props.garantie.valeur_reelle.toLocaleString('fr-FR') }} FCFA
                            </div>
                        </div>
                        
                        <div class="rounded-lg bg-white p-4 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-2">
                                <div :class="['rounded-full p-1.5', (props.garantie.montant_restant || 0) > 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30']">
                                    <TrendingDown :class="['h-4 w-4', (props.garantie.montant_restant || 0) > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']" />
                                </div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Stock Disponible</div>
                            </div>
                            <div class="text-lg font-bold" :class="(props.garantie.montant_restant || 0) > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                {{ (props.garantie.montant_restant || 0).toLocaleString('fr-FR') }} FCFA
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Contrats de prêts liés -->
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                <Link2 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Contrats de prêts liés</h3>
                            <span v-if="props.contratsPretsLies && props.contratsPretsLies.length > 0" class="ml-auto rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ props.contratsPretsLies.length }} contrat(s)
                            </span>
                        </div>
                        <div v-if="props.contratsPretsLies && props.contratsPretsLies.length > 0" class="space-y-3">
                            <div
                                v-for="contrat in props.contratsPretsLies"
                                :key="contrat.id"
                                class="group rounded-lg border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-4 transition-all hover:border-blue-300 hover:shadow-md dark:border-gray-700 dark:from-gray-800 dark:to-gray-900 dark:hover:border-blue-600"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="rounded-full bg-blue-100 p-1.5 dark:bg-blue-900/30">
                                                <CreditCard class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ contrat.numero_pret }}</div>
                                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', getStatutBadge(contrat.statut)]">
                                                {{ contrat.statut }}
                                            </span>
                                        </div>
                                        <div class="ml-7 space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Montant :</span>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ contrat.montant_accorde.toLocaleString('fr-FR') }} FCFA
                                                </span>
                                            </div>
                                            <div v-if="contrat.pivot" class="flex flex-wrap gap-3">
                                                <div class="flex items-center gap-1.5">
                                                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">{{ contrat.pivot.pourcentage_utilisation }}%</span> utilisé
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">{{ contrat.pivot.montant_utilise.toLocaleString('fr-FR') }} FCFA</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 ml-4">
                                        <Link :href="`/contrats-prets/${contrat.id}`">
                                            <Button variant="outline" size="sm" class="shadow-sm">
                                                Voir
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="unlinkContratPret(contrat.id)"
                                            class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 shadow-sm"
                                        >
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                            <Link2 class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Aucun contrat de prêt lié</p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Les contrats liés apparaîtront ici</p>
                        </div>
                    </div>

                    <!-- Contrats de prêts disponibles -->
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                                <Plus class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lier un contrat de prêt</h3>
                        </div>
                        <p v-if="props.garantie.client" class="mb-4 text-sm text-gray-600">
                            Seuls les contrats de prêts du client <strong>{{ props.garantie.client.matricule }} - {{ props.garantie.client.prenom }} {{ props.garantie.client.nom }}</strong> sont disponibles.
                        </p>
                        <div v-if="!props.garantie.client" class="mb-4 rounded-md border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-800 dark:bg-yellow-900/20">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                ⚠️ Cette garantie n'a pas de client associé. Veuillez d'abord associer un client à la garantie pour pouvoir lier des contrats de prêts.
                            </p>
                        </div>
                        
                        <!-- Recherche -->
                        <div class="mb-4">
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Rechercher un contrat de prêt..."
                                class="w-full"
                            >
                                <Search class="h-4 w-4" />
                            </Input>
                        </div>

                        <!-- Formulaire de liaison -->
                        <form @submit.prevent="submitLink" class="space-y-4">
                            <div>
                                <Label for="contrat_pret_id" class="text-base font-medium text-gray-700">Contrat de prêt *</Label>
                                <select
                                    id="contrat_pret_id"
                                    v-model="linkForm.contrat_pret_id"
                                    required
                                    @change="onContratPretSelected"
                                    class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                                >
                                    <option value="">Sélectionner un contrat</option>
                                    <option v-for="contrat in filteredContratsPrets" :key="contrat.id" :value="contrat.id">
                                        {{ contrat.numero_pret }} - {{ contrat.montant_accorde.toLocaleString('fr-FR') }} FCFA
                                        <span v-if="contrat.nom_client"> ({{ contrat.nom_client }})</span>
                                    </option>
                                </select>
                                <InputError :message="linkForm.errors.contrat_pret_id" />
                            </div>

                            <!-- Bande de taux de couverture -->
                            <div v-if="tauxCouverture !== null" class="rounded-xl border-2 p-5 shadow-sm transition-all duration-300" :class="{
                                'border-red-300 bg-gradient-to-br from-red-50 to-red-100 dark:border-red-800 dark:from-red-900/20 dark:to-red-900/10': tauxCouvertureColor === 'red',
                                'border-orange-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:border-orange-800 dark:from-orange-900/20 dark:to-orange-900/10': tauxCouvertureColor === 'orange',
                                'border-green-300 bg-gradient-to-br from-green-50 to-green-100 dark:border-green-800 dark:from-green-900/20 dark:to-green-900/10': tauxCouvertureColor === 'green',
                            }">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div :class="['rounded-lg p-2', {
                                            'bg-red-200 dark:bg-red-900/40': tauxCouvertureColor === 'red',
                                            'bg-orange-200 dark:bg-orange-900/40': tauxCouvertureColor === 'orange',
                                            'bg-green-200 dark:bg-green-900/40': tauxCouvertureColor === 'green',
                                        }]">
                                            <TrendingUp :class="['h-5 w-5', {
                                                'text-red-600 dark:text-red-400': tauxCouvertureColor === 'red',
                                                'text-orange-600 dark:text-orange-400': tauxCouvertureColor === 'orange',
                                                'text-green-600 dark:text-green-400': tauxCouvertureColor === 'green',
                                            }]" />
                                        </div>
                                        <div>
                                            <Label class="text-base font-semibold text-gray-700 dark:text-gray-300">Taux de couverture</Label>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Seuil minimum : 80%</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-3xl font-bold" :class="{
                                            'text-red-600 dark:text-red-400': tauxCouvertureColor === 'red',
                                            'text-orange-600 dark:text-orange-400': tauxCouvertureColor === 'orange',
                                            'text-green-600 dark:text-green-400': tauxCouvertureColor === 'green',
                                        }">
                                            {{ tauxCouverture.toFixed(2) }}%
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Barre de progression améliorée -->
                                <div class="relative w-full mb-4">
                                    <div class="w-full bg-gray-200 rounded-full h-6 dark:bg-gray-700 shadow-inner relative">
                                        <!-- Barre de progression (limitée à 100% pour l'affichage visuel) -->
                                        <div 
                                            class="h-6 rounded-full transition-all duration-500 ease-out shadow-md relative overflow-hidden" 
                                            :class="{
                                                'bg-gradient-to-r from-red-500 to-red-600': tauxCouvertureColor === 'red',
                                                'bg-gradient-to-r from-orange-500 to-orange-600': tauxCouvertureColor === 'orange',
                                                'bg-gradient-to-r from-green-500 to-green-600': tauxCouvertureColor === 'green',
                                            }"
                                            :style="{ width: Math.min((tauxCouverture || 0), 100) + '%' }"
                                        >
                                            <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                                        </div>
                                        
                                        <!-- Indicateur si taux > 100% -->
                                        <div v-if="(tauxCouverture || 0) > 100" class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                            <div class="h-3 w-3 rounded-full bg-white shadow-md animate-pulse"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Seuil de 80% avec marqueur -->
                                    <div class="absolute left-[80%] top-0 bottom-0 w-0.5 bg-gray-600 dark:bg-gray-400 z-10">
                                        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2">
                                            <div class="bg-gray-600 dark:bg-gray-400 text-white text-xs px-2 py-0.5 rounded font-medium whitespace-nowrap shadow-md">
                                                80%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Légende -->
                                    <div class="mt-2 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                        <span>0%</span>
                                        <span class="font-medium">Seuil minimum : 80%</span>
                                        <span>100%+</span>
                                    </div>
                                </div>
                                
                                <!-- Message d'alerte amélioré -->
                                <div v-if="tauxCouvertureMessage" class="mt-4 rounded-lg p-4 border-2 shadow-sm" :class="{
                                    'bg-red-100 border-red-300 text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-200': tauxCouvertureMessage.type === 'error',
                                    'bg-orange-100 border-orange-300 text-orange-800 dark:bg-orange-900/30 dark:border-orange-800 dark:text-orange-200': tauxCouvertureMessage.type === 'warning',
                                    'bg-green-100 border-green-300 text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-200': tauxCouvertureMessage.type === 'success',
                                }">
                                    <div class="flex items-start gap-3">
                                        <AlertCircle v-if="tauxCouvertureMessage.type === 'error'" class="h-5 w-5 flex-shrink-0 mt-0.5" />
                                        <Info v-if="tauxCouvertureMessage.type === 'warning'" class="h-5 w-5 flex-shrink-0 mt-0.5" />
                                        <CheckCircle2 v-if="tauxCouvertureMessage.type === 'success'" class="h-5 w-5 flex-shrink-0 mt-0.5" />
                                        <p class="text-sm font-medium leading-relaxed">{{ tauxCouvertureMessage.message }}</p>
                                    </div>
                                </div>
                                
                                <!-- Détails améliorés -->
                                <div class="mt-4 grid grid-cols-2 gap-3 rounded-lg bg-white/60 dark:bg-gray-800/60 p-3">
                                    <div class="text-center">
                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Stock Disponible</div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ (props.garantie.montant_restant || 0).toLocaleString('fr-FR') }} FCFA
                                        </div>
                                    </div>
                                    <div v-if="linkForm.contrat_pret_id && props.contratsPretsDisponibles" class="text-center">
                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Montant du prêt</div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ props.contratsPretsDisponibles.find(cp => cp.id === parseInt(linkForm.contrat_pret_id.toString()))?.montant_accorde.toLocaleString('fr-FR') }} FCFA
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <Label for="montant_utilise" class="text-base font-medium text-gray-700">
                                    Montant à utiliser (FCFA) *
                                </Label>
                                <Input
                                    id="montant_utilise"
                                    v-model.number="linkForm.montant_utilise"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :max="props.garantie?.montant_restant || 0"
                                    required
                                    placeholder="0.00"
                                    class="mt-1.5"
                                />
                                <InputError :message="linkForm.errors.montant_utilise" />
                                <p v-if="linkForm.montant_utilise" class="mt-1 text-sm text-gray-600">
                                    Saisi : <span class="font-medium">{{ parseFloat(linkForm.montant_utilise.toString() || '0').toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} FCFA</span>
                                </p>
                                <div v-if="linkForm.montant_utilise && props.garantie" class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-600">
                                        Pourcentage calculé: <span class="font-medium">{{ calculatedPourcentage.toFixed(2) }}%</span>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Montant restant après liaison: 
                                        <span class="font-medium" :class="montantRestantApresLiaison >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ montantRestantApresLiaison.toLocaleString('fr-FR') }} FCFA
                                        </span>
                                    </p>
                                    <p v-if="montantRestantApresLiaison < 0" class="text-xs text-red-600">
                                        ⚠️ Le montant saisi dépasse le montant restant disponible
                                    </p>
                                </div>
                            </div>

                            <Button 
                                type="submit" 
                                :disabled="linkForm.processing || (tauxCouverture !== null && tauxCouverture < 80)" 
                                class="w-full shadow-md hover:shadow-lg transition-all duration-200"
                                :class="{
                                    'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800': tauxCouverture !== null && tauxCouverture >= 80,
                                    'opacity-50 cursor-not-allowed': tauxCouverture !== null && tauxCouverture < 80,
                                }"
                            >
                                <Plus class="h-4 w-4 mr-2" />
                                {{ linkForm.processing ? 'Liaison en cours...' : 'Lier le contrat de prêt' }}
                            </Button>
                            <p v-if="tauxCouverture !== null && tauxCouverture < 80" class="text-xs text-center text-red-600 dark:text-red-400 mt-2">
                                ⚠️ Impossible de lier : Le taux de couverture est insuffisant (minimum 80% requis)
                            </p>
                        </form>

                        <div v-if="filteredContratsPrets.length === 0 && searchQuery" class="mt-4 text-sm text-gray-500">
                            Aucun contrat de prêt trouvé pour "{{ searchQuery }}"
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800 text-center">
                <p class="text-gray-500">Veuillez sélectionner une garantie pour commencer</p>
            </div>
        </div>
    </AppLayout>
</template>

